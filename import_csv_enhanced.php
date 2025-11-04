<?php
/**
 * Enhanced Import CSV dengan Anti-Duplicate + Multiple Modes
 * Features:
 * - SKIP mode: Skip existing (safe)
 * - UPDATE mode: Update existing data
 * - REPORT mode: Detailed import report
 */

session_start();

// DEBUG: Log session status
error_log("=== IMPORT CSV ENHANCED DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Session status: " . session_status());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("csrf_token_import before: " . (isset($_SESSION['csrf_token_import']) ? 'EXISTS' : 'NOT SET'));

include 'connect.php';
include 'functions_role.php'; // Central role function

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

// Generate CSRF token ONLY if not exists (don't regenerate!)
if (!isset($_SESSION['csrf_token_import']) || empty($_SESSION['csrf_token_import'])) {
    $_SESSION['csrf_token_import'] = bin2hex(random_bytes(32));
    error_log("csrf_token_import GENERATED: " . substr($_SESSION['csrf_token_import'], 0, 20) . '...');
} else {
    error_log("csrf_token_import EXISTS: " . substr($_SESSION['csrf_token_import'], 0, 20) . '... (reusing)');
}

$success = '';
$error = '';
$importReport = null;
$debug_info = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    // DEBUG: Log all relevant information
    error_log("=== POST REQUEST RECEIVED ===");
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST keys: " . implode(', ', array_keys($_POST)));
    error_log("FILES keys: " . (isset($_FILES) ? implode(', ', array_keys($_FILES)) : 'NONE'));
    
    $debug_info['post_data'] = [
        'import_button' => isset($_POST['import']) ? 'YES' : 'NO',
        'csrf_token_posted' => isset($_POST['csrf_token']) ? 'YES (length: ' . strlen($_POST['csrf_token']) . ')' : 'NO',
        'import_mode' => $_POST['import_mode'] ?? 'NOT SET',
        'file_uploaded' => isset($_FILES['import_file']) ? 'YES' : 'NO'
    ];
    
    $debug_info['session_data'] = [
        'csrf_token_exists' => isset($_SESSION['csrf_token_import']) ? 'YES (length: ' . strlen($_SESSION['csrf_token_import']) . ')' : 'NO',
        'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
        'role' => $_SESSION['role'] ?? 'NOT SET'
    ];
    
    error_log("POST csrf_token: " . (isset($_POST['csrf_token']) ? substr($_POST['csrf_token'], 0, 20) . '... (length: ' . strlen($_POST['csrf_token']) . ')' : 'NOT SET'));
    error_log("SESSION csrf_token_import: " . (isset($_SESSION['csrf_token_import']) ? substr($_SESSION['csrf_token_import'], 0, 20) . '... (length: ' . strlen($_SESSION['csrf_token_import']) . ')' : 'NOT SET'));
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token_import']) {
        error_log("❌ CSRF VALIDATION FAILED!");
        error_log("Reason: " . (!isset($_POST['csrf_token']) ? 'Token not posted' : 'Token mismatch'));
        
        $debug_info['csrf_error'] = [
            'posted_token' => isset($_POST['csrf_token']) ? substr($_POST['csrf_token'], 0, 20) . '...' : 'NULL',
            'session_token' => isset($_SESSION['csrf_token_import']) ? substr($_SESSION['csrf_token_import'], 0, 20) . '...' : 'NULL',
            'tokens_match' => (isset($_POST['csrf_token']) && isset($_SESSION['csrf_token_import']) && $_POST['csrf_token'] === $_SESSION['csrf_token_import']) ? 'YES' : 'NO'
        ];
        $error = 'Invalid CSRF token. Please refresh the page and try again.';
    } else {
        error_log("✅ CSRF VALIDATION PASSED!");
        
        $file = $_FILES['import_file'] ?? null;
        $import_mode = $_POST['import_mode'] ?? 'skip';
    
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $filename = $file['tmp_name'];
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (!in_array($extension, ['csv', 'txt'])) {
            $error = 'Hanya file CSV atau TXT yang diperbolehkan.';
        } else {
            try {
                error_log("Opening CSV file: $filename");
                $handle = fopen($filename, "r");
                if ($handle) {
                    error_log("CSV file opened successfully");
                    $report = [
                        'imported' => 0,
                        'updated' => 0,
                        'skipped' => 0,
                        'errors' => 0,
                        'details' => []
                    ];
                    
                    $rowNum = 0;
                    
                    while (($row = fgetcsv($handle, 2000, ";")) !== false) {
                        $rowNum++;
                        
                        // Skip header
                        if ($rowNum === 1 && (stripos($row[1] ?? '', 'nama') !== false)) {
                            $report['details'][] = [
                                'row' => $rowNum,
                                'status' => 'header',
                                'message' => 'Header row - skipped'
                            ];
                            continue;
                        }
                        
                        // Parse data - support extended format with salary components
                        $nama = trim($row[1] ?? '');
                        $posisi = trim($row[2] ?? '');
                        
                        // Komponen gaji (optional columns 3-10)
                        $gaji_pokok = isset($row[3]) && $row[3] !== '' ? floatval($row[3]) : null;
                        $tunjangan_transport = isset($row[4]) && $row[4] !== '' ? floatval($row[4]) : null;
                        $tunjangan_makan = isset($row[5]) && $row[5] !== '' ? floatval($row[5]) : null;
                        $overwork = isset($row[6]) && $row[6] !== '' ? floatval($row[6]) : null;
                        $tunjangan_jabatan = isset($row[7]) && $row[7] !== '' ? floatval($row[7]) : null;
                        $bonus_kehadiran = isset($row[8]) && $row[8] !== '' ? floatval($row[8]) : null;
                        $bonus_marketing = isset($row[9]) && $row[9] !== '' ? floatval($row[9]) : null;
                        $insentif_omset = isset($row[10]) && $row[10] !== '' ? floatval($row[10]) : null;
                        
                        // Validate
                        if ($nama === '') {
                            $report['skipped']++;
                            $report['details'][] = [
                                'row' => $rowNum,
                                'status' => 'error',
                                'message' => 'Empty name - skipped'
                            ];
                            continue;
                        }
                        
                        // Auto-detect role dari database
                        $role = getRoleByPosisiFromDB($pdo, $posisi);
                        
                        // Check existing
                        $stmt = $pdo->prepare("SELECT * FROM pegawai_whitelist WHERE nama_lengkap = ?");
                        $stmt->execute([$nama]);
                        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existing) {
                            // Data already exists
                            
                            if ($import_mode === 'skip') {
                                // SKIP MODE: Don't import duplicate
                                $report['skipped']++;
                                $report['details'][] = [
                                    'row' => $rowNum,
                                    'nama' => $nama,
                                    'status' => 'skipped',
                                    'message' => "Already exists (Posisi: {$existing['posisi']}, Role: {$existing['role']})",
                                    'action' => 'SKIP'
                                ];
                                
                            } elseif ($import_mode === 'update') {
                                // UPDATE MODE: Update existing data + komponen gaji
                                try {
                                    $old_posisi = $existing['posisi'];
                                    $old_role = $existing['role'];
                                    
                                    // Update pegawai_whitelist dengan data gaji
                                    $stmt = $pdo->prepare("
                                        UPDATE pegawai_whitelist SET 
                                            posisi = ?, 
                                            role = ?,
                                            gaji_pokok = ?,
                                            tunjangan_transport = ?,
                                            tunjangan_makan = ?,
                                            overwork = ?,
                                            tunjangan_jabatan = ?,
                                            bonus_kehadiran = ?,
                                            bonus_marketing = ?,
                                            insentif_omset = ?
                                        WHERE nama_lengkap = ?
                                    ");
                                    $stmt->execute([
                                        $posisi, $role,
                                        $gaji_pokok ?? 0,
                                        $tunjangan_transport ?? 0,
                                        $tunjangan_makan ?? 0,
                                        $overwork ?? 0,
                                        $tunjangan_jabatan ?? 0,
                                        $bonus_kehadiran ?? 0,
                                        $bonus_marketing ?? 0,
                                        $insentif_omset ?? 0,
                                        $nama
                                    ]);
                                    
                                    $report['updated']++;
                                    $hasGajiData = $gaji_pokok !== null || $tunjangan_transport !== null || $tunjangan_makan !== null;
                                    $gajiMsg = $hasGajiData ? " + Gaji updated" : "";
                                    $report['details'][] = [
                                        'row' => $rowNum,
                                        'nama' => $nama,
                                        'status' => 'updated',
                                        'message' => "Updated: Posisi ($old_posisi → $posisi), Role ($old_role → $role)$gajiMsg",
                                        'action' => 'UPDATE'
                                    ];
                                } catch (Exception $e) {
                                    $report['errors']++;
                                    $report['details'][] = [
                                        'row' => $rowNum,
                                        'nama' => $nama,
                                        'status' => 'error',
                                        'message' => 'Update failed: ' . $e->getMessage(),
                                        'action' => 'ERROR'
                                    ];
                                }
                            }
                            
                        } else {
                            // New data - INSERT
                            try {
                                // Insert ke pegawai_whitelist dengan data gaji
                                $stmt = $pdo->prepare("
                                    INSERT INTO pegawai_whitelist (
                                        nama_lengkap, posisi, status_registrasi, role,
                                        gaji_pokok, tunjangan_transport, tunjangan_makan, overwork,
                                        tunjangan_jabatan, bonus_kehadiran, bonus_marketing, insentif_omset
                                    ) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $nama, $posisi, $role,
                                    $gaji_pokok ?? 0, 
                                    $tunjangan_transport ?? 0, 
                                    $tunjangan_makan ?? 0, 
                                    $overwork ?? 0,
                                    $tunjangan_jabatan ?? 0, 
                                    $bonus_kehadiran ?? 0, 
                                    $bonus_marketing ?? 0, 
                                    $insentif_omset ?? 0
                                ]);
                                
                                $report['imported']++;
                                $hasGajiData = $gaji_pokok !== null || $tunjangan_transport !== null || $tunjangan_makan !== null;
                                $gajiMsg = $hasGajiData ? " + Gaji data saved" : "";
                                
                                $report['details'][] = [
                                    'row' => $rowNum,
                                    'nama' => $nama,
                                    'status' => 'imported',
                                    'message' => "New entry: $nama ($posisi) as role='$role'$gajiMsg",
                                    'action' => 'INSERT'
                                ];
                            } catch (PDOException $e) {
                                $report['errors']++;
                                $report['details'][] = [
                                    'row' => $rowNum,
                                    'nama' => $nama,
                                    'status' => 'error',
                                    'message' => 'Database error: ' . $e->getMessage(),
                                    'action' => 'ERROR'
                                ];
                            }
                        }
                    }
                    
                    fclose($handle);
                    $importReport = $report;
                    
                    // Success message
                    $success = "Import complete! Imported: {$report['imported']}, Updated: {$report['updated']}, Skipped: {$report['skipped']}, Errors: {$report['errors']}";
                }
            } catch (Exception $e) {
                $error = 'Import failed: ' . $e->getMessage();
            }
        }
        } else {
            $error = 'No file uploaded or upload error.';
        }
    } // Close CSRF validation else block
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enhanced Import CSV - Anti-Duplicate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .container {
            border: 2px solid #ddd;
            padding: 20px;
            border-radius: 8px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .info {
            background-color: #e7f3fe;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin: 15px 0;
        }
        .success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 12px;
            margin: 15px 0;
        }
        .error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 12px;
            margin: 15px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input[type="file"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            max-width: 400px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .report-table th {
            background-color: #4CAF50;
            color: white;
        }
        .status-imported { background-color: #d4edda; }
        .status-updated { background-color: #fff3cd; }
        .status-skipped { background-color: #f8d7da; }
        .status-error { background-color: #f8d7da; font-weight: bold; }
        .status-header { background-color: #e7e7e7; }
        .mode-description {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .debug-box {
            background-color: #f0f0f0;
            border: 2px solid #333;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
        }
        .debug-box h3 {
            margin-top: 0;
            color: #333;
        }
        .debug-box pre {
            background: #fff;
            padding: 10px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Enhanced Import CSV - Anti-Duplicate</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <strong>✅ <?= $success ?></strong>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error">
                <strong>❌ <?= htmlspecialchars($error) ?></strong>
            </div>
            
            <!-- DEBUG INFO when error occurs -->
            <?php if (!empty($debug_info)): ?>
                <div class="debug-box">
                    <h3>🔍 DEBUG INFORMATION:</h3>
                    <pre><?= print_r($debug_info, true) ?></pre>
                    
                    <h4>Troubleshooting Steps:</h4>
                    <ol>
                        <li>Check if CSRF token is in the form (view page source)</li>
                        <li>Verify session is active (check user_id and role)</li>
                        <li>Try refreshing the page to regenerate token</li>
                        <li>Clear browser cache and cookies</li>
                        <li>Check if session.save_path is writable</li>
                    </ol>
                    
                    <p><strong>Session Token (first 20 chars):</strong> <?= isset($_SESSION['csrf_token_import']) ? substr($_SESSION['csrf_token_import'], 0, 20) . '...' : 'NOT SET' ?></p>
                    <p><strong>Posted Token (first 20 chars):</strong> <?= isset($_POST['csrf_token']) ? substr($_POST['csrf_token'], 0, 20) . '...' : 'NOT SET' ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="info">
            <strong>ℹ️ Import Features:</strong><br>
            - ✅ Cek duplikasi berdasarkan <strong>Nama Lengkap</strong><br>
            - ✅ UNIQUE constraint di database<br>
            - ✅ Pilih mode import: SKIP atau UPDATE<br>
            - ✅ <strong>Support import komponen gaji langsung!</strong> (Tidak perlu akun dulu)<br>
            - ✅ Detailed import report<br><br>
            
            <strong>📄 Format CSV:</strong><br>
            <code>No; Nama Lengkap; Posisi; Gaji Pokok; Tunjangan Transport; Tunjangan Makan; Overwork; Tunjangan Jabatan; Bonus Kehadiran; Bonus Marketing; Insentif Omset</code><br><br>
            
            <strong>Example:</strong><br>
            <code>1;John Doe;Manager;5000000;500000;300000;0;1000000;500000;0;0</code><br>
            <code>2;Jane Smith;Staff;3000000;300000;200000;0;0;300000;0;0</code><br><br>
            
            <strong>✨ NEW Features:</strong><br>
            - ✅ Data gaji langsung tersimpan di whitelist<br>
            - ✅ Tidak perlu menunggu pegawai register<br>
            - ✅ Saat pegawai register nanti, data gaji akan otomatis tersinkronisasi
        </div>
        
        <!-- DEBUG: Show CSRF Token Status -->
        <div class="debug-box" style="background-color: #e7f3fe;">
            <strong>🔐 CSRF Token Status:</strong><br>
            Session Token: <?= isset($_SESSION['csrf_token_import']) ? '✅ Active (' . strlen($_SESSION['csrf_token_import']) . ' chars)' : '❌ Not Set' ?><br>
            Form Token: <span id="form-token-status">Checking...</span>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const tokenInput = document.querySelector('input[name="csrf_token"]');
                    const status = document.getElementById('form-token-status');
                    if (tokenInput && tokenInput.value) {
                        status.innerHTML = '✅ Present in form (' + tokenInput.value.length + ' chars)';
                        status.style.color = 'green';
                    } else {
                        status.innerHTML = '❌ Missing from form!';
                        status.style.color = 'red';
                    }
                });
            </script>
        </div>
        
        <form method="post" enctype="multipart/form-data" onsubmit="return debugFormSubmit(this);">
            <!-- CSRF Token for import -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token_import'] ?>">
            
            <div class="form-group">
                <label for="import_mode">🎯 Import Mode:</label>
                <select name="import_mode" id="import_mode" onchange="updateModeDescription()">
                    <option value="skip">SKIP - Skip existing data (Safe, Default)</option>
                    <option value="update">UPDATE - Update existing data (Advanced)</option>
                </select>
            </div>
            
            <div class="mode-description" id="mode-description">
                <strong>📌 SKIP MODE (Recommended):</strong><br>
                - Jika nama sudah ada → <strong>SKIP</strong>, tidak diimport<br>
                - Aman, tidak akan overwrite data existing<br>
                - Cocok untuk: Import pegawai baru
            </div>
            
            <div class="form-group">
                <label for="import_file">📁 Select CSV File:</label>
                <input type="file" name="import_file" id="import_file" accept=".csv,.txt" required>
            </div>
            
            <div class="form-group">
                <button type="submit" name="import" value="1">🚀 Import CSV</button>
            </div>
        </form>
        
        <?php if ($importReport): ?>
            <h2>📊 Import Report</h2>
            <div class="success">
                <strong>Summary:</strong><br>
                ✅ Imported (New): <?= $importReport['imported'] ?><br>
                🔄 Updated: <?= $importReport['updated'] ?><br>
                ⏭️ Skipped: <?= $importReport['skipped'] ?><br>
                ❌ Errors: <?= $importReport['errors'] ?>
            </div>
            
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($importReport['details'] as $detail): ?>
                        <tr class="status-<?= $detail['status'] ?>">
                            <td><?= $detail['row'] ?></td>
                            <td><?= htmlspecialchars($detail['nama'] ?? '-') ?></td>
                            <td><strong><?= strtoupper($detail['status']) ?></strong></td>
                            <td><?= htmlspecialchars($detail['message']) ?></td>
                            <td><?= $detail['action'] ?? '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <a href="whitelist.php" style="color: #2196F3;">← Back to Whitelist</a> |
            <a href="template_import_with_gaji.csv" download style="color: #2196F3;">📥 Download Template (With Gaji)</a> |
            <a href="import_whitelist.php" style="color: #2196F3;">� Change Import Method</a>
        </div>
        
        <!-- DEBUG INFO BOX (Always show in debug mode) -->
        <div class="debug-box">
            <h3>🔍 Debug Information</h3>
            <pre><?php print_r($debug_info); ?></pre>
        </div>
    </div>
    
    <script>
        function debugFormSubmit(form) {
            console.log('=== FORM SUBMIT DEBUG ===');
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            console.log('Form enctype:', form.enctype);
            
            const csrfToken = form.querySelector('input[name="csrf_token"]');
            console.log('CSRF Token Element:', csrfToken);
            console.log('CSRF Token Value:', csrfToken ? csrfToken.value : 'NOT FOUND');
            console.log('CSRF Token Length:', csrfToken ? csrfToken.value.length : 0);
            
            const importButton = form.querySelector('button[name="import"]');
            console.log('Import Button:', importButton);
            console.log('Import Button Value:', importButton ? importButton.value : 'NOT FOUND');
            
            const file = form.querySelector('input[name="import_file"]');
            console.log('File Input:', file);
            console.log('File Selected:', file && file.files.length > 0 ? file.files[0].name : 'NO FILE');
            
            // Show alert with debug info
            const debugMsg = `
DEBUG INFO:
- CSRF Token: ${csrfToken ? '✅ Present (' + csrfToken.value.length + ' chars)' : '❌ Missing'}
- Import Button: ${importButton ? '✅ Present' : '❌ Missing'}
- File: ${file && file.files.length > 0 ? '✅ ' + file.files[0].name : '❌ No file selected'}

Check browser console for detailed logs.
            `;
            
            if (!csrfToken || !csrfToken.value) {
                alert('ERROR: CSRF Token is missing from form!\n\n' + debugMsg);
                return false;
            }
            
            if (!file || file.files.length === 0) {
                alert('Please select a CSV file to upload.');
                return false;
            }
            
            console.log('Form validation passed. Submitting...');
            return true;
        }
        
        function updateModeDescription() {
            const mode = document.getElementById('import_mode').value;
            const desc = document.getElementById('mode-description');
            
            if (mode === 'skip') {
                desc.innerHTML = `
                    <strong>📌 SKIP MODE (Recommended):</strong><br>
                    - Jika nama sudah ada → <strong>SKIP</strong>, tidak diimport<br>
                    - Aman, tidak akan overwrite data existing<br>
                    - Cocok untuk: Import pegawai baru
                `;
                desc.style.borderColor = '#28a745';
                desc.style.backgroundColor = '#d4edda';
            } else if (mode === 'update') {
                desc.innerHTML = `
                    <strong>⚠️ UPDATE MODE (Advanced):</strong><br>
                    - Jika nama sudah ada → <strong>UPDATE</strong> posisi dan role<br>
                    - Will overwrite existing data!<br>
                    - Cocok untuk: Bulk update pegawai existing
                `;
                desc.style.borderColor = '#ffc107';
                desc.style.backgroundColor = '#fff3cd';
            }
        }
    </script>
</body>
</html>
