<?php
/**
 * Mode 3: Smart Import CSV dengan Intelligent Conflict Resolution
 * Features:
 * - 100% match → Overwrite
 * - Partial match → User choose (old/new/skip)
 * - No match → Insert new
 * - Database-based role detection
 */

session_start();

// DEBUG: Log session status
error_log("=== IMPORT CSV SMART DEBUG ===");
error_log("Session ID: " . session_id());
error_log("csrf_token_import_smart before: " . (isset($_SESSION['csrf_token_import_smart']) ? 'EXISTS' : 'NOT SET'));

include 'connect.php';
include 'functions_role.php';

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

// Generate CSRF token ONLY if not exists (don't regenerate!)
if (!isset($_SESSION['csrf_token_import_smart']) || empty($_SESSION['csrf_token_import_smart'])) {
    $_SESSION['csrf_token_import_smart'] = bin2hex(random_bytes(32));
    error_log("csrf_token_import_smart GENERATED: " . substr($_SESSION['csrf_token_import_smart'], 0, 20) . '...');
} else {
    error_log("csrf_token_import_smart EXISTS: " . substr($_SESSION['csrf_token_import_smart'], 0, 20) . '... (reusing)');
}

$step = $_GET['step'] ?? 'upload';
$conflicts = [];
$success = '';
$error = '';

// Step 1: Upload & Analyze
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['analyze'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token_import_smart']) {
        $error = 'Invalid CSRF token. Please refresh the page and try again.';
    } else {
    $file = $_FILES['import_file'] ?? null;
    
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $filename = $file['tmp_name'];
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (!in_array($extension, ['csv', 'txt'])) {
            $error = 'Hanya file CSV atau TXT yang diperbolehkan.';
        } else {
            // Save file temporarily
            $temp_file = 'temp_import_' . session_id() . '.csv';
            move_uploaded_file($filename, $temp_file);
            $_SESSION['temp_import_file'] = $temp_file;
            
            // Analyze file for conflicts
            $handle = fopen($temp_file, "r");
            if ($handle) {
                $rowNum = 0;
                $conflicts = [];
                
                while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                    $rowNum++;
                    
                    // Skip header
                    if ($rowNum === 1 && (stripos($row[1] ?? '', 'nama') !== false)) {
                        continue;
                    }
                    
                    $nama = trim($row[1] ?? '');
                    $posisi = trim($row[2] ?? '');
                    
                    // Parse salary data (optional columns 3-10)
                    $gaji_pokok = isset($row[3]) && $row[3] !== '' ? floatval($row[3]) : null;
                    $tunjangan_transport = isset($row[4]) && $row[4] !== '' ? floatval($row[4]) : null;
                    $tunjangan_makan = isset($row[5]) && $row[5] !== '' ? floatval($row[5]) : null;
                    $overwork = isset($row[6]) && $row[6] !== '' ? floatval($row[6]) : null;
                    $tunjangan_jabatan = isset($row[7]) && $row[7] !== '' ? floatval($row[7]) : null;
                    $bonus_kehadiran = isset($row[8]) && $row[8] !== '' ? floatval($row[8]) : null;
                    $bonus_marketing = isset($row[9]) && $row[9] !== '' ? floatval($row[9]) : null;
                    $insentif_omset = isset($row[10]) && $row[10] !== '' ? floatval($row[10]) : null;
                    
                    if ($nama === '') continue;
                    
                    // Auto-detect role from database
                    $role_new = getRoleByPosisiFromDB($pdo, $posisi);
                    
                    // Check existing
                    $stmt = $pdo->prepare("SELECT * FROM pegawai_whitelist WHERE nama_lengkap = ?");
                    $stmt->execute([$nama]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existing) {
                        // Compare data
                        $role_old = getRoleByPosisiFromDB($pdo, $existing['posisi']);
                        
                        $match_posisi = ($existing['posisi'] === $posisi);
                        $match_role = ($role_old === $role_new);
                        
                        if ($match_posisi && $match_role) {
                            // 100% match
                            $conflicts[] = [
                                'row' => $rowNum,
                                'nama' => $nama,
                                'type' => '100_match',
                                'old_posisi' => $existing['posisi'],
                                'new_posisi' => $posisi,
                                'old_role' => $role_old,
                                'new_role' => $role_new,
                                'action' => 'overwrite', // Auto: overwrite
                                'color' => 'green',
                                'salary' => [
                                    'gaji_pokok' => $gaji_pokok,
                                    'tunjangan_transport' => $tunjangan_transport,
                                    'tunjangan_makan' => $tunjangan_makan,
                                    'overwork' => $overwork,
                                    'tunjangan_jabatan' => $tunjangan_jabatan,
                                    'bonus_kehadiran' => $bonus_kehadiran,
                                    'bonus_marketing' => $bonus_marketing,
                                    'insentif_omset' => $insentif_omset
                                ]
                            ];
                        } else {
                            // Conflict detected
                            $conflicts[] = [
                                'row' => $rowNum,
                                'nama' => $nama,
                                'type' => 'conflict',
                                'old_posisi' => $existing['posisi'],
                                'new_posisi' => $posisi,
                                'old_role' => $role_old,
                                'new_role' => $role_new,
                                'action' => 'pending', // User must choose
                                'color' => 'yellow',
                                'salary' => [
                                    'gaji_pokok' => $gaji_pokok,
                                    'tunjangan_transport' => $tunjangan_transport,
                                    'tunjangan_makan' => $tunjangan_makan,
                                    'overwork' => $overwork,
                                    'tunjangan_jabatan' => $tunjangan_jabatan,
                                    'bonus_kehadiran' => $bonus_kehadiran,
                                    'bonus_marketing' => $bonus_marketing,
                                    'insentif_omset' => $insentif_omset
                                ]
                            ];
                        }
                    } else {
                        // New entry
                        $conflicts[] = [
                            'row' => $rowNum,
                            'nama' => $nama,
                            'type' => 'new',
                            'old_posisi' => null,
                            'new_posisi' => $posisi,
                            'old_role' => null,
                            'new_role' => $role_new,
                            'action' => 'insert', // Auto: insert
                            'color' => 'blue',
                            'salary' => [
                                'gaji_pokok' => $gaji_pokok,
                                'tunjangan_transport' => $tunjangan_transport,
                                'tunjangan_makan' => $tunjangan_makan,
                                'overwork' => $overwork,
                                'tunjangan_jabatan' => $tunjangan_jabatan,
                                'bonus_kehadiran' => $bonus_kehadiran,
                                'bonus_marketing' => $bonus_marketing,
                                'insentif_omset' => $insentif_omset
                            ]
                        ];
                    }
                }
                
                fclose($handle);
                $_SESSION['import_conflicts'] = $conflicts;
                $step = 'review';
            }
        }
    } else {
        $error = 'No file uploaded or upload error.';
    }
    } // Close CSRF validation
}

// Step 2: Process with decisions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token_import_smart']) {
        $error = 'Invalid CSRF token. Please refresh the page and try again.';
        $step = 'review';
    } else {
    $conflicts = $_SESSION['import_conflicts'] ?? [];
    $decisions = $_POST['decision'] ?? [];
    
    $report = [
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
        'details' => []
    ];
    
    foreach ($conflicts as $index => $conflict) {
        $nama = $conflict['nama'];
        $posisi = $conflict['new_posisi'];
        $role = $conflict['new_role'];
        $action = $decisions[$index] ?? $conflict['action'];
        
        // Extract salary data
        $salary = $conflict['salary'] ?? [];
        $gaji_pokok = $salary['gaji_pokok'] ?? 0;
        $tunjangan_transport = $salary['tunjangan_transport'] ?? 0;
        $tunjangan_makan = $salary['tunjangan_makan'] ?? 0;
        $overwork = $salary['overwork'] ?? 0;
        $tunjangan_jabatan = $salary['tunjangan_jabatan'] ?? 0;
        $bonus_kehadiran = $salary['bonus_kehadiran'] ?? 0;
        $bonus_marketing = $salary['bonus_marketing'] ?? 0;
        $insentif_omset = $salary['insentif_omset'] ?? 0;
        
        try {
            if ($action === 'insert' || $action === 'overwrite' || $action === 'use_new') {
                // Check if exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM pegawai_whitelist WHERE nama_lengkap = ?");
                $stmt->execute([$nama]);
                
                if ($stmt->fetchColumn() > 0) {
                    // UPDATE with salary data
                    $stmt = $pdo->prepare("UPDATE pegawai_whitelist SET 
                                          posisi = ?, role = ?,
                                          gaji_pokok = ?, tunjangan_transport = ?, tunjangan_makan = ?,
                                          overwork = ?, tunjangan_jabatan = ?, bonus_kehadiran = ?,
                                          bonus_marketing = ?, insentif_omset = ?
                                          WHERE nama_lengkap = ?");
                    $stmt->execute([$posisi, $role, $gaji_pokok, $tunjangan_transport, $tunjangan_makan,
                                   $overwork, $tunjangan_jabatan, $bonus_kehadiran, 
                                   $bonus_marketing, $insentif_omset, $nama]);
                    $report['updated']++;
                    $report['details'][] = "Row {$conflict['row']}: '$nama' UPDATED (with salary data)";
                } else {
                    // INSERT with salary data
                    $stmt = $pdo->prepare("INSERT INTO pegawai_whitelist 
                                          (nama_lengkap, posisi, status_registrasi, role,
                                           gaji_pokok, tunjangan_transport, tunjangan_makan, overwork,
                                           tunjangan_jabatan, bonus_kehadiran, bonus_marketing, insentif_omset) 
                                          VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nama, $posisi, $role, $gaji_pokok, $tunjangan_transport, $tunjangan_makan,
                                   $overwork, $tunjangan_jabatan, $bonus_kehadiran,
                                   $bonus_marketing, $insentif_omset]);
                    $report['inserted']++;
                    $report['details'][] = "Row {$conflict['row']}: '$nama' INSERTED (with salary data)";
                }
            } elseif ($action === 'use_old') {
                // Keep old data - no action
                $report['skipped']++;
                $report['details'][] = "Row {$conflict['row']}: '$nama' KEPT OLD DATA";
            } elseif ($action === 'skip') {
                // Skip completely
                $report['skipped']++;
                $report['details'][] = "Row {$conflict['row']}: '$nama' SKIPPED";
            }
        } catch (PDOException $e) {
            $report['details'][] = "Row {$conflict['row']}: ERROR - " . $e->getMessage();
        }
    }
    
    // Cleanup
    if (isset($_SESSION['temp_import_file']) && file_exists($_SESSION['temp_import_file'])) {
        unlink($_SESSION['temp_import_file']);
    }
    unset($_SESSION['temp_import_file']);
    unset($_SESSION['import_conflicts']);
    
    $_SESSION['import_report'] = $report;
    header('Location: ?step=complete');
    exit;
    } // Close CSRF validation
}

// Load conflicts for review
if ($step === 'review') {
    $conflicts = $_SESSION['import_conflicts'] ?? [];
}

// Load report for complete
if ($step === 'complete') {
    $report = $_SESSION['import_report'] ?? null;
    unset($_SESSION['import_report']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smart Import CSV - Mode 3</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .step.active {
            background: #4CAF50;
            color: white;
        }
        .step.completed {
            background: #2196F3;
            color: white;
        }
        .info-box {
            background: #e7f3fe;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .error-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .conflict-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .conflict-table th {
            background: #4CAF50;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .conflict-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .conflict-table tr:hover {
            background: #f5f5f5;
        }
        .type-100_match { background-color: #d4edda; }
        .type-conflict { background-color: #fff3cd; }
        .type-new { background-color: #d1ecf1; }
        
        select.decision {
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
            max-width: 200px;
        }
        select.decision:focus {
            border-color: #4CAF50;
            outline: none;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
        }
        button:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="file"] {
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 6px;
            width: 100%;
            cursor: pointer;
        }
        .legend {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .legend-item {
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }
        .summary-stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            flex: 1;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 32px;
        }
        .stat-card p {
            margin: 0;
            color: #666;
        }
        .stat-insert { background: #d1ecf1; border-left: 4px solid #17a2b8; }
        .stat-update { background: #fff3cd; border-left: 4px solid #ffc107; }
        .stat-skip { background: #f8d7da; border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧠 Smart Import CSV - Mode 3: Intelligent Conflict Resolution</h1>
        
        <div class="step-indicator">
            <div class="step <?= $step === 'upload' ? 'active' : ($step !== 'upload' ? 'completed' : '') ?>">
                1️⃣ Upload & Analyze
            </div>
            <div class="step <?= $step === 'review' ? 'active' : ($step === 'complete' ? 'completed' : '') ?>">
                2️⃣ Review & Decide
            </div>
            <div class="step <?= $step === 'complete' ? 'active' : '' ?>">
                3️⃣ Import Complete
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="error-box">
                <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($step === 'upload'): ?>
            <div class="info-box">
                <strong>💡 Smart Import Logic:</strong><br>
                • <strong>100% Match</strong> (Nama + Posisi sama) → <span style="color: green;">✅ Auto OVERWRITE</span><br>
                • <strong>Conflict</strong> (Nama sama, Posisi beda) → <span style="color: orange;">⚠️ Anda pilih: Keep old / Use new / Skip</span><br>
                • <strong>New Entry</strong> (Nama baru) → <span style="color: blue;">➕ Auto INSERT</span><br>
                • <strong>Role Detection</strong> → Otomatis dari database <code>posisi_jabatan</code>
            </div>
            
            <form method="post" enctype="multipart/form-data">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token_import_smart'] ?>">
                
                <div class="form-group">
                    <label for="import_file">📁 Select CSV File:</label>
                    <input type="file" name="import_file" id="import_file" accept=".csv,.txt" required>
                </div>
                
                <button type="submit" name="analyze">🔍 Analyze File</button>
            </form>
            
            <div class="info-box" style="margin-top: 30px;">
                <strong>📝 CSV Format:</strong>
                <pre style="background: #f9f9f9; padding: 10px; border-radius: 4px;">No;Nama Lengkap;Posisi
1;Ahmad Rifai;Barista
2;Siti Nurhaliza;HR</pre>
            </div>
            
        <?php elseif ($step === 'review'): ?>
            <div class="info-box">
                <strong>📊 Analysis Result:</strong> <?= count($conflicts) ?> rows detected
            </div>
            
            <div class="legend">
                <div class="legend-item type-100_match">✅ 100% Match (Auto Overwrite)</div>
                <div class="legend-item type-conflict">⚠️ Conflict (Your Decision Needed)</div>
                <div class="legend-item type-new">➕ New Entry (Auto Insert)</div>
            </div>
            
            <?php
            $count_100 = count(array_filter($conflicts, fn($c) => $c['type'] === '100_match'));
            $count_conflict = count(array_filter($conflicts, fn($c) => $c['type'] === 'conflict'));
            $count_new = count(array_filter($conflicts, fn($c) => $c['type'] === 'new'));
            ?>
            
            <div class="summary-stats">
                <div class="stat-card stat-update">
                    <h3><?= $count_100 ?></h3>
                    <p>100% Match</p>
                </div>
                <div class="stat-card" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                    <h3><?= $count_conflict ?></h3>
                    <p>Conflicts</p>
                </div>
                <div class="stat-card stat-insert">
                    <h3><?= $count_new ?></h3>
                    <p>New Entries</p>
                </div>
            </div>
            
            <form method="post">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token_import_smart'] ?>">
                
                <table class="conflict-table">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Nama</th>
                            <th>Type</th>
                            <th>Old Data</th>
                            <th>New Data</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conflicts as $index => $conflict): ?>
                            <tr class="type-<?= $conflict['type'] ?>">
                                <td><?= $conflict['row'] ?></td>
                                <td><strong><?= htmlspecialchars($conflict['nama']) ?></strong></td>
                                <td>
                                    <?php if ($conflict['type'] === '100_match'): ?>
                                        ✅ 100% Match
                                    <?php elseif ($conflict['type'] === 'conflict'): ?>
                                        ⚠️ Conflict
                                    <?php else: ?>
                                        ➕ New
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($conflict['old_posisi']): ?>
                                        Posisi: <?= htmlspecialchars($conflict['old_posisi']) ?><br>
                                        Role: <?= htmlspecialchars($conflict['old_role']) ?>
                                    <?php else: ?>
                                        <em>-</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    Posisi: <?= htmlspecialchars($conflict['new_posisi']) ?><br>
                                    Role: <?= htmlspecialchars($conflict['new_role']) ?>
                                </td>
                                <td>
                                    <?php if ($conflict['type'] === 'conflict'): ?>
                                        <select name="decision[<?= $index ?>]" class="decision" required>
                                            <option value="use_new">Use NEW data</option>
                                            <option value="use_old">Keep OLD data</option>
                                            <option value="skip">Skip (No change)</option>
                                        </select>
                                    <?php elseif ($conflict['type'] === '100_match'): ?>
                                        <input type="hidden" name="decision[<?= $index ?>]" value="overwrite">
                                        <strong style="color: green;">✅ Overwrite</strong>
                                    <?php else: ?>
                                        <input type="hidden" name="decision[<?= $index ?>]" value="insert">
                                        <strong style="color: blue;">➕ Insert</strong>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" name="process">🚀 Process Import</button>
                </div>
            </form>
            
        <?php elseif ($step === 'complete' && $report): ?>
            <div class="success-box">
                <h2>✅ Import Complete!</h2>
            </div>
            
            <div class="summary-stats">
                <div class="stat-card stat-insert">
                    <h3><?= $report['inserted'] ?></h3>
                    <p>Inserted</p>
                </div>
                <div class="stat-card stat-update">
                    <h3><?= $report['updated'] ?></h3>
                    <p>Updated</p>
                </div>
                <div class="stat-card stat-skip">
                    <h3><?= $report['skipped'] ?></h3>
                    <p>Skipped</p>
                </div>
            </div>
            
            <div class="info-box">
                <strong>📋 Details:</strong><br>
                <?php foreach ($report['details'] as $detail): ?>
                    • <?= htmlspecialchars($detail) ?><br>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 30px; text-align: center;">
                <a href="whitelist.php" style="display: inline-block; background: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    ← Back to Whitelist
                </a>
                <a href="?step=upload" style="display: inline-block; background: #2196F3; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-left: 10px;">
                    🔄 Import Another File
                </a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <a href="whitelist.php" style="color: #2196F3; text-decoration: none;">← Back to Whitelist</a> |
            <a href="template_import_basic.csv" download style="color: #2196F3; text-decoration: none;">📥 Download Template</a> |
            <a href="import_csv_enhanced.php" style="color: #2196F3; text-decoration: none;">🔄 Mode 2 (Simple)</a>
        </div>
    </div>
</body>
</html>
