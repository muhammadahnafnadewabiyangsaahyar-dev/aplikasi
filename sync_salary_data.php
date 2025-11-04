<?php
/**
 * Salary Data Sync Utility
 * Purpose: Sync salary data from pegawai_whitelist to komponen_gaji
 *          for employees who registered before the auto-sync feature was implemented
 */

session_start();
include 'connect.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

$sync_report = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_sync'])) {
    try {
        $report = [
            'total_checked' => 0,
            'synced' => 0,
            'skipped_no_salary' => 0,
            'skipped_already_exists' => 0,
            'errors' => 0,
            'details' => []
        ];
        
        // Get all registered employees (status_registrasi = 'terdaftar')
        $sql = "SELECT pw.id, pw.nama_lengkap, pw.posisi, 
                       pw.gaji_pokok, pw.tunjangan_transport, pw.tunjangan_makan, 
                       pw.overwork, pw.tunjangan_jabatan, pw.bonus_kehadiran, 
                       pw.bonus_marketing, pw.insentif_omset,
                       r.id as register_id
                FROM pegawai_whitelist pw
                INNER JOIN register r ON r.nama_lengkap = pw.nama_lengkap
                WHERE pw.status_registrasi = 'terdaftar'";
        
        $stmt = $pdo->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $report['total_checked'] = count($employees);
        
        foreach ($employees as $emp) {
            $nama = $emp['nama_lengkap'];
            $register_id = $emp['register_id'];
            
            // Check if salary data exists in pegawai_whitelist
            $has_salary = ($emp['gaji_pokok'] > 0 || $emp['tunjangan_transport'] > 0 || $emp['tunjangan_makan'] > 0);
            
            if (!$has_salary) {
                $report['skipped_no_salary']++;
                $report['details'][] = [
                    'nama' => $nama,
                    'status' => 'skipped',
                    'reason' => 'No salary data in whitelist'
                ];
                continue;
            }
            
            // Check if komponen_gaji already exists
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM komponen_gaji WHERE register_id = ?");
            $stmt_check->execute([$register_id]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $report['skipped_already_exists']++;
                $report['details'][] = [
                    'nama' => $nama,
                    'status' => 'skipped',
                    'reason' => 'Salary data already exists in komponen_gaji'
                ];
                continue;
            }
            
            // Insert salary data to komponen_gaji
            try {
                $stmt_insert = $pdo->prepare("
                    INSERT INTO komponen_gaji 
                    (register_id, jabatan, gaji_pokok, tunjangan_transport, tunjangan_makan, 
                     overwork, tunjangan_jabatan, bonus_kehadiran, bonus_marketing, insentif_omset) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt_insert->execute([
                    $register_id,
                    $emp['posisi'],
                    $emp['gaji_pokok'] ?? 0,
                    $emp['tunjangan_transport'] ?? 0,
                    $emp['tunjangan_makan'] ?? 0,
                    $emp['overwork'] ?? 0,
                    $emp['tunjangan_jabatan'] ?? 0,
                    $emp['bonus_kehadiran'] ?? 0,
                    $emp['bonus_marketing'] ?? 0,
                    $emp['insentif_omset'] ?? 0
                ]);
                
                $report['synced']++;
                $report['details'][] = [
                    'nama' => $nama,
                    'status' => 'synced',
                    'reason' => 'Salary data synced successfully',
                    'gaji_pokok' => number_format($emp['gaji_pokok'], 0, ',', '.')
                ];
                
            } catch (PDOException $e) {
                $report['errors']++;
                $report['details'][] = [
                    'nama' => $nama,
                    'status' => 'error',
                    'reason' => $e->getMessage()
                ];
            }
        }
        
        $sync_report = $report;
        
    } catch (Exception $e) {
        $error = 'Sync failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Salary Data Sync Utility</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #FF9800;
            padding-bottom: 15px;
        }
        .info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        button {
            background-color: #FF9800;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background-color: #F57C00;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .report-table th {
            background-color: #FF9800;
            color: white;
        }
        .status-synced { background-color: #d4edda; }
        .status-skipped { background-color: #fff3cd; }
        .status-error { background-color: #f8d7da; font-weight: bold; }
        .summary-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .summary-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #ddd;
        }
        .summary-item h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
        }
        .summary-item .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Salary Data Sync Utility</h1>
        
        <div class="info">
            <strong>ℹ️ Apa fungsi tool ini?</strong><br>
            Tool ini akan mensinkronkan data gaji dari tabel <code>pegawai_whitelist</code> ke tabel <code>komponen_gaji</code> untuk pegawai yang:
            <ul>
                <li>✅ Sudah terdaftar (status_registrasi = 'terdaftar')</li>
                <li>✅ Punya data gaji di pegawai_whitelist</li>
                <li>❌ Belum punya data di komponen_gaji (untuk menghindari duplikasi)</li>
            </ul>
        </div>
        
        <div class="warning">
            <strong>⚠️ Catatan Penting:</strong><br>
            - Tool ini <strong>aman</strong> - tidak akan overwrite data existing di komponen_gaji<br>
            - Hanya akan insert data baru untuk pegawai yang belum punya komponen_gaji<br>
            - Untuk pegawai baru yang mendaftar setelah update, sync otomatis sudah berjalan<br>
            - Tool ini berguna untuk <strong>migrasi data lama</strong> saja
        </div>
        
        <?php if ($error): ?>
            <div class="warning">
                <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($sync_report): ?>
            <div class="success">
                <strong>✅ Sync Complete!</strong>
            </div>
            
            <div class="summary-box">
                <div class="summary-item">
                    <h3>Total Checked</h3>
                    <div class="number"><?= $sync_report['total_checked'] ?></div>
                </div>
                <div class="summary-item" style="border-color: #28a745;">
                    <h3>✅ Synced</h3>
                    <div class="number" style="color: #28a745;"><?= $sync_report['synced'] ?></div>
                </div>
                <div class="summary-item" style="border-color: #ffc107;">
                    <h3>⏭️ Skipped (No Salary)</h3>
                    <div class="number" style="color: #ffc107;"><?= $sync_report['skipped_no_salary'] ?></div>
                </div>
                <div class="summary-item" style="border-color: #17a2b8;">
                    <h3>⏭️ Skipped (Exists)</h3>
                    <div class="number" style="color: #17a2b8;"><?= $sync_report['skipped_already_exists'] ?></div>
                </div>
                <div class="summary-item" style="border-color: #dc3545;">
                    <h3>❌ Errors</h3>
                    <div class="number" style="color: #dc3545;"><?= $sync_report['errors'] ?></div>
                </div>
            </div>
            
            <h2>📋 Detailed Report</h2>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Pegawai</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Gaji Pokok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sync_report['details'] as $index => $detail): ?>
                        <tr class="status-<?= $detail['status'] ?>">
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($detail['nama']) ?></td>
                            <td><strong><?= strtoupper($detail['status']) ?></strong></td>
                            <td><?= htmlspecialchars($detail['reason']) ?></td>
                            <td><?= $detail['gaji_pokok'] ?? '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <form method="post">
                <p><strong>Siap untuk menjalankan sync?</strong></p>
                <p>Klik tombol di bawah untuk memulai proses sinkronisasi.</p>
                <button type="submit" name="run_sync" onclick="return confirm('Yakin ingin menjalankan sync? Tool ini aman dan tidak akan overwrite data existing.')">
                    🚀 Run Salary Sync
                </button>
            </form>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd;">
            <a href="whitelist.php" style="color: #2196F3; text-decoration: none;">← Back to Whitelist</a> |
            <a href="mainpage.php" style="color: #2196F3; text-decoration: none;">🏠 Main Page</a>
        </div>
    </div>
</body>
</html>
