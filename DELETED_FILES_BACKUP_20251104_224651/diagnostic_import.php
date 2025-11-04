<?php
/**
 * CSRF Import Diagnostic Tool
 * This page simulates the import form submission to diagnose CSRF issues
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$diagnostic_results = [];
$post_received = false;

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_received = true;
    
    $diagnostic_results[] = [
        'title' => 'POST Request Received',
        'status' => 'info',
        'details' => 'Request method: ' . $_SERVER['REQUEST_METHOD']
    ];
    
    // Check CSRF token in POST
    if (isset($_POST['csrf_token'])) {
        $diagnostic_results[] = [
            'title' => 'CSRF Token in POST',
            'status' => 'success',
            'details' => 'Token present: ' . substr($_POST['csrf_token'], 0, 20) . '... (length: ' . strlen($_POST['csrf_token']) . ')'
        ];
    } else {
        $diagnostic_results[] = [
            'title' => 'CSRF Token in POST',
            'status' => 'error',
            'details' => 'Token NOT present in POST data'
        ];
    }
    
    // Check CSRF token in SESSION
    if (isset($_SESSION['csrf_token'])) {
        $diagnostic_results[] = [
            'title' => 'CSRF Token in SESSION',
            'status' => 'success',
            'details' => 'Token present: ' . substr($_SESSION['csrf_token'], 0, 20) . '... (length: ' . strlen($_SESSION['csrf_token']) . ')'
        ];
    } else {
        $diagnostic_results[] = [
            'title' => 'CSRF Token in SESSION',
            'status' => 'error',
            'details' => 'Token NOT present in session'
        ];
    }
    
    // Validate CSRF token
    if (isset($_POST['csrf_token']) && isset($_SESSION['csrf_token'])) {
        if ($_POST['csrf_token'] === $_SESSION['csrf_token']) {
            $diagnostic_results[] = [
                'title' => 'CSRF Token Validation',
                'status' => 'success',
                'details' => '✓ Tokens match! CSRF validation would PASS'
            ];
        } else {
            $diagnostic_results[] = [
                'title' => 'CSRF Token Validation',
                'status' => 'error',
                'details' => '✗ Tokens DO NOT match! CSRF validation would FAIL'
            ];
        }
    } else {
        $diagnostic_results[] = [
            'title' => 'CSRF Token Validation',
            'status' => 'warning',
            'details' => 'Cannot validate - one or both tokens missing'
        ];
    }
    
    // Check for file upload
    if (isset($_FILES['import_file'])) {
        $file = $_FILES['import_file'];
        $diagnostic_results[] = [
            'title' => 'File Upload',
            'status' => 'info',
            'details' => 'File: ' . $file['name'] . ' | Size: ' . $file['size'] . ' bytes | Error: ' . $file['error']
        ];
    } else {
        $diagnostic_results[] = [
            'title' => 'File Upload',
            'status' => 'warning',
            'details' => 'No file uploaded'
        ];
    }
    
    // Log everything
    error_log("=== DIAGNOSTIC TEST ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("SESSION data: " . print_r($_SESSION, true));
    error_log("FILES data: " . print_r($_FILES, true));
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Import Diagnostic</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin: 0 0 10px 0;
        }
        .card {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .success-box {
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .error-box {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        input[type="file"] {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            width: 100%;
            max-width: 400px;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        button:active {
            transform: translateY(0);
        }
        .token-display {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            word-break: break-all;
            font-size: 14px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .back-link:hover {
            background: #5a6268;
        }
        .diagnostic-item {
            margin: 10px 0;
        }
        .diagnostic-item strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 CSRF Import Diagnostic Tool</h1>
        <p>Test and diagnose CSRF token issues during CSV import</p>
    </div>

    <?php if ($post_received): ?>
        <div class="card">
            <h2>📊 Diagnostic Results</h2>
            <?php foreach ($diagnostic_results as $result): ?>
                <div class="<?= $result['status'] ?>-box diagnostic-item">
                    <strong><?= htmlspecialchars($result['title']) ?></strong>
                    <div><?= htmlspecialchars($result['details']) ?></div>
                </div>
            <?php endforeach; ?>
            
            <div class="info-box" style="margin-top: 20px;">
                <strong>Raw Data Dump:</strong>
                <details>
                    <summary>Click to view POST data</summary>
                    <pre><?= htmlspecialchars(print_r($_POST, true)) ?></pre>
                </details>
                <details>
                    <summary>Click to view FILES data</summary>
                    <pre><?= htmlspecialchars(print_r($_FILES, true)) ?></pre>
                </details>
                <details>
                    <summary>Click to view SESSION data</summary>
                    <pre><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
                </details>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Current Session State</h2>
        <div class="info-box">
            <strong>Session ID:</strong> <?= session_id() ?>
        </div>
        <div class="info-box">
            <strong>CSRF Token in Session:</strong>
            <?php if (isset($_SESSION['csrf_token'])): ?>
                <div class="token-display"><?= htmlspecialchars($_SESSION['csrf_token']) ?></div>
                <small>Length: <?= strlen($_SESSION['csrf_token']) ?> characters</small>
            <?php else: ?>
                <span style="color: red;">NOT SET</span>
            <?php endif; ?>
        </div>
        <div class="info-box">
            <strong>User Info:</strong>
            <?php if (isset($_SESSION['user_id'])): ?>
                User ID: <?= htmlspecialchars($_SESSION['user_id']) ?> | 
                Role: <?= htmlspecialchars($_SESSION['role']) ?> | 
                Name: <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?>
            <?php else: ?>
                <span style="color: red;">Not logged in</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h2>Test Import Form</h2>
        <p>Use this form to test CSV import with CSRF validation:</p>
        
        <form method="POST" enctype="multipart/form-data" id="testForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="form-group">
                <label for="import_file">Select CSV/TXT file:</label>
                <input type="file" name="import_file" id="import_file" accept=".csv,.txt">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="showToken" style="width: auto; margin-right: 10px;">
                    Show CSRF token being sent
                </label>
            </div>
            
            <div id="tokenPreview" style="display: none;" class="info-box">
                <strong>Token that will be sent:</strong>
                <div class="token-display"><?= htmlspecialchars($_SESSION['csrf_token']) ?></div>
            </div>
            
            <button type="submit" name="test_import" value="1">
                🧪 Test Import Submission
            </button>
        </form>
        
        <div class="warning-box" style="margin-top: 20px;">
            <strong>⚠️ Note:</strong> This is a diagnostic tool. It will validate the CSRF token and show results, 
            but will NOT actually import data into the database.
        </div>
    </div>

    <div class="card">
        <h2>🛠️ Troubleshooting Tips</h2>
        <ul>
            <li><strong>If CSRF token is missing from POST:</strong> Check if the hidden input field is present in the form HTML</li>
            <li><strong>If CSRF token is missing from SESSION:</strong> Session might be expiring or not being maintained correctly</li>
            <li><strong>If tokens don't match:</strong> Token might be regenerating between page load and form submission</li>
            <li><strong>If file upload is empty:</strong> Check form enctype="multipart/form-data" attribute</li>
        </ul>
    </div>

    <a href="whitelist.php" class="back-link">← Back to Whitelist</a>
    <a href="debug_csrf.php" class="back-link" style="background: #28a745;">🔧 Debug CSRF Tool</a>

    <script>
        document.getElementById('showToken').addEventListener('change', function() {
            document.getElementById('tokenPreview').style.display = this.checked ? 'block' : 'none';
        });
        
        // Prevent double submission
        document.getElementById('testForm').addEventListener('submit', function() {
            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = '⏳ Processing...';
        });
    </script>
</body>
</html>
