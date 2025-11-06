<?php
session_start();
require_once 'connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = '';

// Helper function
function getNamaBulan($bulan) {
    $names = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 
              7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
    return $names[(int)$bulan] ?? '';
}

// ============ HANDLE ACTIONS ============

// Manual Generate (Run script)
if (isset($_POST['manual_generate'])) {
    $output = [];
    $return_var = 0;
    exec('php ' . __DIR__ . '/auto_generate_slipgaji.php 2>&1', $output, $return_var);
    
    if ($return_var === 0) {
        $message = '<div class="alert alert-success">✓ Slip gaji berhasil di-generate! Refresh halaman untuk melihat hasil.</div>';
    } else {
        $message = '<div class="alert alert-error">✗ Gagal generate slip gaji. Error: ' . implode('<br>', $output) . '</div>';
    }
}

// Update komponen tambahan
if (isset($_POST['update_komponen'])) {
    $riwayat_id = (int)$_POST['riwayat_id'];
    $kasbon = (float)($_POST['kasbon'] ?? 0);
    $piutang_toko = (float)($_POST['piutang_toko'] ?? 0);
    $bonus_marketing = (float)($_POST['bonus_marketing'] ?? 0);
    $insentif_omset = (float)($_POST['insentif_omset'] ?? 0);
    $bonus_lainnya = (float)($_POST['bonus_lainnya'] ?? 0);
    
    try {
        $pdo->beginTransaction();
        
        // Get current data
        $stmt = $pdo->prepare("SELECT * FROM riwayat_gaji WHERE id = ?");
        $stmt->execute([$riwayat_id]);
        $riwayat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$riwayat) {
            throw new Exception('Data tidak ditemukan');
        }
        
        // Recalculate
        $total_pendapatan = $riwayat['gaji_pokok_aktual'] + $riwayat['tunjangan_makan'] + 
                           $riwayat['tunjangan_transportasi'] + $riwayat['tunjangan_jabatan'] +
                           $riwayat['overwork'] + $bonus_marketing + $insentif_omset + $bonus_lainnya;
        
        $total_potongan = $riwayat['potongan_tidak_hadir'] + $riwayat['potongan_telat_atas_20'] + 
                         $riwayat['potongan_telat_bawah_20'] + $kasbon + $piutang_toko;
        
        $gaji_bersih = $total_pendapatan - $total_potongan;
        
        // Update riwayat_gaji
        $stmt = $pdo->prepare("
            UPDATE riwayat_gaji SET
                kasbon = ?,
                piutang_toko = ?,
                bonus_marketing = ?,
                insentif_omset = ?,
                bonus_lainnya = ?,
                total_pendapatan = ?,
                total_potongan = ?,
                gaji_bersih = ?,
                updated_by = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $kasbon, $piutang_toko, $bonus_marketing, $insentif_omset, $bonus_lainnya,
            $total_pendapatan, $total_potongan, $gaji_bersih, $admin_id, $riwayat_id
        ]);
        
        $pdo->commit();
        $message = '<div class="alert alert-success">✓ Komponen gaji berhasil diupdate!</div>';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-error">✗ Error: ' . $e->getMessage() . '</div>';
    }
}

// Bulk send email
if (isset($_POST['bulk_send_email'])) {
    $periode_bulan = (int)$_POST['periode_bulan'];
    $periode_tahun = (int)$_POST['periode_tahun'];
    
    // Get all salaries for this period
    $stmt = $pdo->prepare("
        SELECT rg.*, r.nama_lengkap, r.email
        FROM riwayat_gaji rg
        JOIN register r ON rg.register_id = r.id
        WHERE rg.periode_bulan = ? AND rg.periode_tahun = ?
        AND rg.email_sent = 0
        AND r.email IS NOT NULL AND r.email != ''
    ");
    $stmt->execute([$periode_bulan, $periode_tahun]);
    $salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sent_count = 0;
    $failed_count = 0;
    
    require_once 'PHPMailer/PHPMailerAutoload.php';
    
    foreach ($salaries as $salary) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kaori.aplikasi.notif@gmail.com';
            $mail->Password = 'imjq nmeq vyig umgn';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            
            $mail->setFrom('kaori.aplikasi.notif@gmail.com', 'KAORI Payroll System');
            $mail->addAddress($salary['email'], $salary['nama_lengkap']);
            
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Slip Gaji - ' . getNamaBulan($periode_bulan) . ' ' . $periode_tahun;
            
            // Email body
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                    .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
                    .salary-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    .salary-table th, .salary-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                    .salary-table th { background: #f8f9fa; font-weight: bold; }
                    .total-row { background: #e8f5e9; font-weight: bold; font-size: 18px; }
                    .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>💰 Slip Gaji</h1>
                        <p>" . getNamaBulan($periode_bulan) . " " . $periode_tahun . "</p>
                    </div>
                    <div class='content'>
                        <h3>Yth. " . htmlspecialchars($salary['nama_lengkap']) . "</h3>
                        <p>Berikut rincian gaji Anda untuk periode " . getNamaBulan($periode_bulan) . " " . $periode_tahun . ":</p>
                        
                        <table class='salary-table'>
                            <tr><th colspan='2'>PENDAPATAN</th></tr>
                            <tr><td>Gaji Pokok</td><td>Rp " . number_format($salary['gaji_pokok_aktual'], 0, ',', '.') . "</td></tr>
                            <tr><td>Tunjangan Transport</td><td>Rp " . number_format($salary['tunjangan_transportasi'], 0, ',', '.') . "</td></tr>
                            <tr><td>Tunjangan Makan</td><td>Rp " . number_format($salary['tunjangan_makan'], 0, ',', '.') . "</td></tr>
                            <tr><td>Tunjangan Jabatan</td><td>Rp " . number_format($salary['tunjangan_jabatan'], 0, ',', '.') . "</td></tr>
                            <tr><td>Overwork</td><td>Rp " . number_format($salary['overwork'], 0, ',', '.') . "</td></tr>";
            
            if ($salary['bonus_marketing'] > 0) {
                $mail->Body .= "<tr><td>Bonus Marketing</td><td>Rp " . number_format($salary['bonus_marketing'], 0, ',', '.') . "</td></tr>";
            }
            if ($salary['insentif_omset'] > 0) {
                $mail->Body .= "<tr><td>Insentif Omset</td><td>Rp " . number_format($salary['insentif_omset'], 0, ',', '.') . "</td></tr>";
            }
            if ($salary['bonus_lainnya'] > 0) {
                $mail->Body .= "<tr><td>Bonus Lainnya</td><td>Rp " . number_format($salary['bonus_lainnya'], 0, ',', '.') . "</td></tr>";
            }
            
            $mail->Body .= "
                            <tr><th>Total Pendapatan</th><th>Rp " . number_format($salary['total_pendapatan'], 0, ',', '.') . "</th></tr>
                            
                            <tr><th colspan='2'>POTONGAN</th></tr>
                            <tr><td>Potongan Tidak Hadir</td><td>Rp " . number_format($salary['potongan_tidak_hadir'], 0, ',', '.') . "</td></tr>
                            <tr><td>Potongan Keterlambatan</td><td>Rp " . number_format($salary['potongan_telat_atas_20'] + $salary['potongan_telat_bawah_20'], 0, ',', '.') . "</td></tr>";
            
            if ($salary['kasbon'] > 0) {
                $mail->Body .= "<tr><td>Kasbon</td><td>Rp " . number_format($salary['kasbon'], 0, ',', '.') . "</td></tr>";
            }
            if ($salary['piutang_toko'] > 0) {
                $mail->Body .= "<tr><td>Piutang Toko</td><td>Rp " . number_format($salary['piutang_toko'], 0, ',', '.') . "</td></tr>";
            }
            
            $mail->Body .= "
                            <tr><th>Total Potongan</th><th>Rp " . number_format($salary['total_potongan'], 0, ',', '.') . "</th></tr>
                            
                            <tr class='total-row'>
                                <td>GAJI BERSIH (THP)</td>
                                <td>Rp " . number_format($salary['gaji_bersih'], 0, ',', '.') . "</td>
                            </tr>
                        </table>
                        
                        <h4>Rekap Kehadiran:</h4>
                        <ul>
                            <li>Hadir: " . $salary['jumlah_hadir'] . " hari</li>
                            <li>Terlambat: " . $salary['jumlah_terlambat'] . " kali</li>
                            <li>Tidak Hadir: " . $salary['hari_tidak_hadir'] . " hari</li>
                            <li>Sakit: " . $salary['jumlah_sakit'] . " hari</li>
                            <li>Izin (Approved): " . $salary['jumlah_izin_approved'] . " hari</li>
                            <li>Overwork: " . $salary['jumlah_overwork'] . " hari</li>
                        </ul>
                        
                        <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 13px;'>
                            Jika ada pertanyaan mengenai slip gaji ini, silakan hubungi bagian HRD.
                        </p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " KAORI Indonesia. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->send();
            
            // Update email_sent status
            $stmt = $pdo->prepare("UPDATE riwayat_gaji SET email_sent = 1, email_sent_at = NOW() WHERE id = ?");
            $stmt->execute([$salary['id']]);
            
            $sent_count++;
            
        } catch (Exception $e) {
            error_log("Failed to send email to {$salary['email']}: " . $e->getMessage());
            $failed_count++;
        }
    }
    
    $message = "<div class='alert alert-success'>✓ Email terkirim: $sent_count | Gagal: $failed_count</div>";
}

// ============ GET DATA ============

// Get batch history
$stmt = $pdo->query("
    SELECT * FROM slip_gaji_batch 
    ORDER BY periode_tahun DESC, periode_bulan DESC 
    LIMIT 12
");
$batch_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get filter parameters
$filter_bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$filter_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Get salary data
$stmt = $pdo->prepare("
    SELECT rg.*, r.nama_lengkap, r.email, r.role
    FROM riwayat_gaji rg
    JOIN register r ON rg.register_id = r.id
    WHERE rg.periode_bulan = ? AND rg.periode_tahun = ?
    ORDER BY r.nama_lengkap ASC
");
$stmt->execute([$filter_bulan, $filter_tahun]);
$salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Slip Gaji - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .salary-management {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-success {
            background: #4CAF50;
            color: white;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .salary-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .salary-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .salary-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        .salary-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .salary-table tr:hover {
            background: #f8f9fa;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            overflow-y: auto;
        }
        
        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="salary-management">
        <h1>💰 Manajemen Slip Gaji</h1>
        
        <?= $message ?>
        
        <div class="action-buttons">
            <form method="POST" style="display: inline;">
                <button type="submit" name="manual_generate" class="btn btn-primary" onclick="return confirm('Generate slip gaji untuk periode ini?')">
                    🔄 Generate Slip Gaji Manual
                </button>
            </form>
            
            <button class="btn btn-success" onclick="showBulkEmailModal()">
                📧 Kirim Email Massal
            </button>
            
            <a href="mainpage.php" class="btn" style="background: #6c757d; color: white;">
                ← Kembali
            </a>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <h3>Filter Periode</h3>
            <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Bulan:</label>
                    <select name="bulan" style="padding: 10px; border-radius: 5px; border: 2px solid #ddd;">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == $filter_bulan ? 'selected' : '' ?>>
                                <?= getNamaBulan($i) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Tahun:</label>
                    <input type="number" name="tahun" value="<?= $filter_tahun ?>" 
                           style="padding: 10px; border-radius: 5px; border: 2px solid #ddd; width: 120px;">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
        
        <!-- Salary Table -->
        <div class="salary-table">
            <h2 style="padding: 20px; margin: 0; background: #f8f9fa;">
                Slip Gaji - <?= getNamaBulan($filter_bulan) ?> <?= $filter_tahun ?>
                (<?= count($salaries) ?> pegawai)
            </h2>
            
            <?php if (empty($salaries)): ?>
                <p style="padding: 40px; text-align: center; color: #666;">
                    Belum ada data slip gaji untuk periode ini. Klik "Generate Slip Gaji Manual" untuk membuat.
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pegawai</th>
                            <th>Role</th>
                            <th>Gaji Bersih</th>
                            <th>Hadir</th>
                            <th>Overwork</th>
                            <th>Status Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($salaries as $salary): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($salary['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($salary['role']) ?></td>
                                <td><strong>Rp <?= number_format($salary['gaji_bersih'], 0, ',', '.') ?></strong></td>
                                <td><?= $salary['jumlah_hadir'] ?> hari</td>
                                <td><?= $salary['jumlah_overwork'] ?> hari</td>
                                <td>
                                    <?php if ($salary['email_sent']): ?>
                                        <span class="badge badge-success">✓ Terkirim</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">⏳ Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;" 
                                            onclick="showEditModal(<?= htmlspecialchars(json_encode($salary)) ?>)">
                                        ✏️ Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="modal-edit" class="modal">
        <div class="modal-content">
            <h2>✏️ Edit Komponen Gaji</h2>
            <form method="POST" id="form-edit">
                <input type="hidden" name="update_komponen" value="1">
                <input type="hidden" name="riwayat_id" id="edit-id">
                
                <h4 id="edit-nama" style="color: #667eea; margin-bottom: 20px;"></h4>
                
                <div class="form-group">
                    <label>Kasbon:</label>
                    <input type="number" name="kasbon" id="edit-kasbon" step="1000" min="0">
                </div>
                
                <div class="form-group">
                    <label>Piutang Toko:</label>
                    <input type="number" name="piutang_toko" id="edit-piutang" step="1000" min="0">
                </div>
                
                <div class="form-group">
                    <label>Bonus Marketing:</label>
                    <input type="number" name="bonus_marketing" id="edit-bonus-marketing" step="1000" min="0">
                </div>
                
                <div class="form-group">
                    <label>Insentif Omset:</label>
                    <input type="number" name="insentif_omset" id="edit-insentif" step="1000" min="0">
                </div>
                
                <div class="form-group">
                    <label>Bonus Lainnya:</label>
                    <input type="number" name="bonus_lainnya" id="edit-bonus-lainnya" step="1000" min="0">
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">💾 Simpan</button>
                    <button type="button" class="btn" style="background: #6c757d; color: white;" onclick="closeModal()">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bulk Email Modal -->
    <div id="modal-bulk-email" class="modal">
        <div class="modal-content">
            <h2>📧 Kirim Email Massal</h2>
            <form method="POST">
                <input type="hidden" name="bulk_send_email" value="1">
                <input type="hidden" name="periode_bulan" value="<?= $filter_bulan ?>">
                <input type="hidden" name="periode_tahun" value="<?= $filter_tahun ?>">
                
                <p>Kirim slip gaji via email ke semua pegawai untuk periode:</p>
                <h3 style="color: #667eea;"><?= getNamaBulan($filter_bulan) ?> <?= $filter_tahun ?></h3>
                
                <p style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    ⚠️ Email hanya akan dikirim ke pegawai yang belum menerima email dan memiliki alamat email terdaftar.
                </p>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Kirim email ke semua pegawai?')">
                        📧 Kirim Sekarang
                    </button>
                    <button type="button" class="btn" style="background: #6c757d; color: white;" onclick="closeBulkEmailModal()">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showEditModal(data) {
            document.getElementById('edit-id').value = data.id;
            document.getElementById('edit-nama').textContent = data.nama_lengkap;
            document.getElementById('edit-kasbon').value = data.kasbon || 0;
            document.getElementById('edit-piutang').value = data.piutang_toko || 0;
            document.getElementById('edit-bonus-marketing').value = data.bonus_marketing || 0;
            document.getElementById('edit-insentif').value = data.insentif_omset || 0;
            document.getElementById('edit-bonus-lainnya').value = data.bonus_lainnya || 0;
            document.getElementById('modal-edit').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('modal-edit').style.display = 'none';
        }
        
        function showBulkEmailModal() {
            document.getElementById('modal-bulk-email').style.display = 'block';
        }
        
        function closeBulkEmailModal() {
            document.getElementById('modal-bulk-email').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('modal-edit');
            const emailModal = document.getElementById('modal-bulk-email');
            if (event.target == editModal) {
                closeModal();
            }
            if (event.target == emailModal) {
                closeBulkEmailModal();
            }
        }
    </script>
</body>
</html>
