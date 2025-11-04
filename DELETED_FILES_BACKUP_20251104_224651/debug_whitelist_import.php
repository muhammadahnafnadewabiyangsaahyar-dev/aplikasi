<?php
/**
 * Whitelist Import Debugger
 * Specifically debug the whitelist.php import issue
 */
ob_start();

// Endpoint AJAX (sama seperti whitelist.php)
if (isset($_GET['check']) && isset($_GET['nama'])) {
    include 'connect.php';
    $nama = trim($_GET['nama']);
    $stmt = $pdo->prepare("SELECT nama_lengkap, posisi, status_registrasi FROM pegawai_whitelist WHERE nama_lengkap = ?");
    $stmt->execute([$nama]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['status_registrasi'] !== 'terdaftar') {
        ob_end_clean();
        echo json_encode([
            'found' => true,
            'nama_lengkap' => $row['nama_lengkap'],
            'posisi' => $row['posisi'],
            'status' => $row['status_registrasi']
        ]);
    } else {
        ob_end_clean();
        echo json_encode(['found' => false]);
    }
    exit;
}

session_start();
include 'connect.php';

// AUTH check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

// Generate CSRF token jika belum ada
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    error_log("🆕 csrf_token GENERATED in debug_whitelist: " . substr($_SESSION['csrf_token'], 0, 20) . '...');
} else {
    error_log("♻️ csrf_token EXISTS in debug_whitelist: " . substr($_SESSION['csrf_token'], 0, 20) . '... (reusing)');
}

$test_result = null;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_import'])) {
    error_log("=== DEBUG WHITELIST POST RECEIVED ===");
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST keys: " . implode(', ', array_keys($_POST)));
    error_log("POST csrf_token: " . (isset($_POST['csrf_token']) ? substr($_POST['csrf_token'], 0, 20) . '... (length: ' . strlen($_POST['csrf_token']) . ')' : 'NOT SET'));
    error_log("SESSION csrf_token: " . (isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 20) . '... (length: ' . strlen($_SESSION['csrf_token']) . ')' : 'NOT SET'));
    
    $test_result = [
        'csrf_posted' => isset($_POST['csrf_token']),
        'csrf_session' => isset($_SESSION['csrf_token']),
        'csrf_match' => isset($_POST['csrf_token']) && isset($_SESSION['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'],
        'file_uploaded' => isset($_FILES['test_file']) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK,
        'posted_token' => isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'NULL',
        'session_token' => isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : 'NULL'
    ];
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log("❌ CSRF VALIDATION FAILED!");
        error_log("Reason: " . (!isset($_POST['csrf_token']) ? 'Token not posted' : 'Token mismatch'));
        $test_result['error'] = 'CSRF validation failed';
    } else {
        error_log("✅ CSRF VALIDATION PASSED!");
        $test_result['success'] = 'CSRF validation successful';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Whitelist Import Debugger</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .box {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .box h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .token-display {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            word-break: break-all;
            font-size: 12px;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #721c24;
        }
        .info {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        button {
            background: #dc3545;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #c82333;
        }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
        }
        .compare-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .compare-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .compare-table td:first-child {
            font-weight: bold;
            width: 200px;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🐛 Whitelist Import Debugger</h1>
        <p>Debugging the specific issue with whitelist.php CSV import</p>
    </div>

    <?php if ($test_result): ?>
        <div class="box">
            <h2>📊 Test Results</h2>
            <?php if (isset($test_result['success'])): ?>
                <div class="success">
                    <strong>✅ SUCCESS!</strong><br>
                    <?= htmlspecialchars($test_result['success']) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($test_result['error'])): ?>
                <div class="error">
                    <strong>❌ FAILED!</strong><br>
                    <?= htmlspecialchars($test_result['error']) ?>
                </div>
            <?php endif; ?>
            
            <table class="compare-table">
                <tr>
                    <td>CSRF Posted</td>
                    <td><?= $test_result['csrf_posted'] ? '✅ YES' : '❌ NO' ?></td>
                </tr>
                <tr>
                    <td>CSRF in Session</td>
                    <td><?= $test_result['csrf_session'] ? '✅ YES' : '❌ NO' ?></td>
                </tr>
                <tr>
                    <td>Tokens Match</td>
                    <td><?= $test_result['csrf_match'] ? '✅ YES' : '❌ NO' ?></td>
                </tr>
                <tr>
                    <td>File Uploaded</td>
                    <td><?= $test_result['file_uploaded'] ? '✅ YES' : '❌ NO' ?></td>
                </tr>
            </table>
            
            <div class="info">
                <strong>Token Comparison:</strong><br>
                <strong>Posted Token:</strong>
                <div class="token-display"><?= htmlspecialchars($test_result['posted_token']) ?></div>
                <strong>Session Token:</strong>
                <div class="token-display"><?= htmlspecialchars($test_result['session_token']) ?></div>
                <strong>Match:</strong> <?= $test_result['posted_token'] === $test_result['session_token'] ? '✅ YES' : '❌ NO - TOKENS ARE DIFFERENT!' ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="box">
        <h2>Current Session State</h2>
        <table class="compare-table">
            <tr>
                <td>Session ID</td>
                <td><?= session_id() ?></td>
            </tr>
            <tr>
                <td>User ID</td>
                <td><?= $_SESSION['user_id'] ?? 'NOT SET' ?></td>
            </tr>
            <tr>
                <td>Role</td>
                <td><?= $_SESSION['role'] ?? 'NOT SET' ?></td>
            </tr>
            <tr>
                <td>csrf_token</td>
                <td>
                    <?php if (isset($_SESSION['csrf_token'])): ?>
                        ✅ EXISTS (<?= strlen($_SESSION['csrf_token']) ?> chars)<br>
                        <div class="token-display"><?= htmlspecialchars($_SESSION['csrf_token']) ?></div>
                    <?php else: ?>
                        ❌ NOT SET
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <h2>Test Import Form (Same as whitelist.php)</h2>
        <div class="info">
            <strong>This form mimics the whitelist.php import form exactly.</strong><br>
            If this works but whitelist.php doesn't, there's something different about the actual whitelist.php file.
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: bold;">Select CSV/TXT file:</label>
                <input type="file" name="test_file" accept=".csv,.txt">
            </div>
            
            <button type="submit" name="test_import" value="1">🧪 Test Import (Same as whitelist.php)</button>
        </form>
    </div>

    <div class="box">
        <h2>🔍 Diagnosis</h2>
        <div class="info">
            <strong>If this test PASSES but whitelist.php FAILS:</strong>
            <ul>
                <li>There might be JavaScript interfering in whitelist.php</li>
                <li>The form structure might be different</li>
                <li>There might be multiple forms causing confusion</li>
                <li>Session might be reset somehow in whitelist.php</li>
            </ul>
        </div>
        <div class="info">
            <strong>Next Steps if test passes:</strong>
            <ol>
                <li>Open whitelist.php in browser</li>
                <li>View Page Source (Ctrl+U)</li>
                <li>Search for <code>csrf_token</code></li>
                <li>Compare the token value with the one shown above</li>
                <li>If different → token is being regenerated somewhere!</li>
            </ol>
        </div>
    </div>

    <div class="box">
        <h2>🔗 Quick Links</h2>
        <p><a href="whitelist.php" style="color: #dc3545; font-weight: bold;">→ Go to actual whitelist.php</a></p>
        <p><a href="quick_token_check.php" style="color: #667eea; font-weight: bold;">→ Check all tokens</a></p>
        <p><a href="debug_csrf.php" style="color: #28a745; font-weight: bold;">→ Full CSRF debug</a></p>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>
