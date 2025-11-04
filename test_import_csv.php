<!DOCTYPE html>
<html>
<head>
    <title>Test Import CSV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .container {
            border: 2px solid #ddd;
            padding: 20px;
            border-radius: 8px;
        }
        h1 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
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
        .info {
            background-color: #e7f3fe;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin: 20px 0;
        }
        .success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 12px;
            margin: 20px 0;
        }
        .error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 12px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Import CSV - Whitelist Pegawai</h1>
        
        <div class="info">
            <strong>ℹ️ Informasi:</strong><br>
            - Script ini untuk test import CSV tanpa perlu login<br>
            - Format CSV: <code>No;Nama Lengkap;Posisi</code><br>
            - Role akan auto-detect berdasarkan posisi<br>
            - Delimiter: titik koma (;)
        </div>

        <?php
        session_start();
        include 'connect.php';

        // Generate CSRF token jika belum ada
        if (!isset($_SESSION['csrf_token_test'])) {
            $_SESSION['csrf_token_test'] = bin2hex(random_bytes(32));
        }

        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
            // Validasi CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token_test']) {
                $error = 'Invalid CSRF token. Please refresh and try again.';
            } else {
                $file = $_FILES['import_file'] ?? null;
                
                if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Error uploading file: ' . ($file['error'] ?? 'No file selected');
                } else {
                    $filename = $file['tmp_name'];
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    
                    if (!in_array($extension, ['csv', 'txt'])) {
                        $error = 'Only CSV or TXT files are allowed.';
                    } else {
                        try {
                            // Fungsi auto-detect role
                            function getRoleByPosisi($posisi) {
                                $posisi_lower = strtolower(trim($posisi));
                                $admin_positions = ['hr', 'finance', 'marketing', 'scm', 'akuntan', 'owner', 'superadmin'];
                                return in_array($posisi_lower, $admin_positions) ? 'admin' : 'user';
                            }
                            
                            $handle = fopen($filename, "r");
                            if ($handle) {
                                $imported = 0;
                                $skipped = 0;
                                $details = [];
                                $rowNum = 0;
                                
                                while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                                    $rowNum++;
                                    
                                    // Skip header
                                    if ($rowNum === 1 && (stripos($row[1] ?? '', 'nama') !== false)) {
                                        $details[] = "Row $rowNum: Header - skipped";
                                        continue;
                                    }
                                    
                                    $nama = trim($row[1] ?? '');
                                    $posisi = trim($row[2] ?? '');
                                    
                                    if ($nama === '') {
                                        $skipped++;
                                        $details[] = "Row $rowNum: Empty name - skipped";
                                        continue;
                                    }
                                    
                                    // Auto-detect role
                                    $role = getRoleByPosisi($posisi);
                                    
                                    // Cek duplikat
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pegawai_whitelist WHERE nama_lengkap = ?");
                                    $stmt->execute([$nama]);
                                    
                                    if ($stmt->fetchColumn() > 0) {
                                        $skipped++;
                                        $details[] = "Row $rowNum: '$nama' already exists - skipped";
                                        continue;
                                    }
                                    
                                    // Insert
                                    $stmt = $pdo->prepare("INSERT INTO pegawai_whitelist (nama_lengkap, posisi, status_registrasi, role) VALUES (?, ?, 'pending', ?)");
                                    $stmt->execute([$nama, $posisi, $role]);
                                    
                                    $imported++;
                                    $details[] = "Row $rowNum: '$nama' ($posisi) imported as role='$role' ✅";
                                }
                                
                                fclose($handle);
                                
                                $success = "<strong>Import Complete!</strong><br>";
                                $success .= "✅ Imported: $imported<br>";
                                $success .= "⏭️ Skipped: $skipped<br><br>";
                                $success .= "<strong>Details:</strong><br>" . implode("<br>", $details);
                            } else {
                                $error = 'Failed to read file.';
                            }
                        } catch (Exception $e) {
                            $error = 'Import failed: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
        ?>

        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><strong>❌ Error:</strong><br><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token_test'] ?>">
            
            <div class="form-group">
                <label for="import_file">📁 Select CSV File:</label>
                <input type="file" name="import_file" id="import_file" accept=".csv,.txt" required>
            </div>
            
            <div class="form-group">
                <button type="submit" name="import" value="1">🚀 Import CSV</button>
            </div>
        </form>

        <div class="info">
            <strong>📝 Sample CSV Format:</strong>
            <pre>No;Nama Lengkap;Posisi
1;Ahmad Rifai;Barista
2;Budi Santoso;HR
3;Siti Nurhaliza;Kitchen</pre>
            
            <strong>🤖 Auto-Role Detection:</strong><br>
            - <strong>Admin:</strong> HR, Finance, Marketing, SCM, Akuntan, Owner, Superadmin<br>
            - <strong>User:</strong> Semua posisi lainnya (Barista, Kitchen, Server, dll)
        </div>

        <div style="margin-top: 20px;">
            <a href="whitelist.php" style="color: #2196F3;">← Back to Whitelist</a> |
            <a href="template_import_basic.csv" download style="color: #2196F3;">📥 Download Template</a>
        </div>
    </div>
</body>
</html>
