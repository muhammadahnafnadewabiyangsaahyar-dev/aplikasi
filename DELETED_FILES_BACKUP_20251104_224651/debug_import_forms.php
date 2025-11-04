<?php
/**
 * Import Form Debugger - Side-by-Side Comparison
 * This tool helps debug why one form works and another doesn't
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$test_result = null;

// Handle test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_form'])) {
    $test_result = [
        'timestamp' => date('Y-m-d H:i:s'),
        'csrf_posted' => isset($_POST['csrf_token']),
        'csrf_session' => isset($_SESSION['csrf_token']),
        'csrf_match' => isset($_POST['csrf_token']) && isset($_SESSION['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'],
        'file_uploaded' => isset($_FILES['test_file']) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK,
        'post_data' => $_POST,
        'files_data' => $_FILES
    ];
    
    error_log("=== IMPORT FORM DEBUGGER TEST ===");
    error_log("CSRF Posted: " . ($test_result['csrf_posted'] ? 'YES' : 'NO'));
    error_log("CSRF Session: " . ($test_result['csrf_session'] ? 'YES' : 'NO'));
    error_log("CSRF Match: " . ($test_result['csrf_match'] ? 'YES' : 'NO'));
    error_log("File Uploaded: " . ($test_result['file_uploaded'] ? 'YES' : 'NO'));
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Form Debugger</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 1024px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
        .panel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .panel h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
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
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
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
            width: 100%;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .code-block {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .status-good {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-bad {
            color: #f44336;
            font-weight: bold;
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
        .console-output {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            margin: 10px 0;
        }
        .console-output .log {
            color: #4CAF50;
        }
        .console-output .warn {
            color: #ff9800;
        }
        .console-output .error {
            color: #f44336;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            backdrop-filter: blur(10px);
            transition: background 0.3s;
        }
        .back-link:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔬 Import Form Debugger</h1>
            <p>Side-by-side comparison to debug form submission issues</p>
        </div>

        <?php if ($test_result): ?>
            <div class="panel" style="grid-column: 1 / -1;">
                <h2>📊 Test Results</h2>
                <?php if ($test_result['csrf_match'] && $test_result['file_uploaded']): ?>
                    <div class="success-box">
                        <strong>✅ TEST PASSED!</strong>
                        <p>CSRF token validation successful and file uploaded correctly.</p>
                    </div>
                <?php else: ?>
                    <div class="error-box">
                        <strong>❌ TEST FAILED</strong>
                        <ul style="margin-top: 10px;">
                            <?php if (!$test_result['csrf_posted']): ?>
                                <li>CSRF token was NOT included in POST data</li>
                            <?php endif; ?>
                            <?php if (!$test_result['csrf_session']): ?>
                                <li>CSRF token is NOT in session</li>
                            <?php endif; ?>
                            <?php if ($test_result['csrf_posted'] && !$test_result['csrf_match']): ?>
                                <li>CSRF tokens DO NOT match (posted vs session)</li>
                            <?php endif; ?>
                            <?php if (!$test_result['file_uploaded']): ?>
                                <li>File was NOT uploaded successfully</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="info-box">
                    <strong>Detailed Results:</strong>
                    <div class="code-block">
                        Timestamp: <?= $test_result['timestamp'] ?><br>
                        CSRF Posted: <span class="<?= $test_result['csrf_posted'] ? 'status-good' : 'status-bad' ?>"><?= $test_result['csrf_posted'] ? 'YES' : 'NO' ?></span><br>
                        CSRF Session: <span class="<?= $test_result['csrf_session'] ? 'status-good' : 'status-bad' ?>"><?= $test_result['csrf_session'] ? 'YES' : 'NO' ?></span><br>
                        CSRF Match: <span class="<?= $test_result['csrf_match'] ? 'status-good' : 'status-bad' ?>"><?= $test_result['csrf_match'] ? 'YES' : 'NO' ?></span><br>
                        File Uploaded: <span class="<?= $test_result['file_uploaded'] ? 'status-good' : 'status-bad' ?>"><?= $test_result['file_uploaded'] ? 'YES' : 'NO' ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid">
            <!-- Form Method 1: Standard HTML Form -->
            <div class="panel">
                <h2>Method 1: Standard HTML Form</h2>
                <p style="margin-bottom: 20px; color: #666;">Uses standard HTML form submission (like whitelist.php)</p>
                
                <form method="POST" enctype="multipart/form-data" id="form1">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="test_form" value="1">
                    
                    <div class="form-group">
                        <label for="test_file1">Select CSV/TXT file:</label>
                        <input type="file" name="test_file" id="test_file1" accept=".csv,.txt">
                    </div>
                    
                    <button type="submit" id="btn1">🧪 Test Method 1</button>
                </form>
                
                <div class="info-box" style="margin-top: 20px;">
                    <strong>What this tests:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <li>Standard form POST</li>
                        <li>CSRF token as hidden input</li>
                        <li>File upload with multipart/form-data</li>
                    </ul>
                </div>
            </div>

            <!-- Form Method 2: With JavaScript Console Logging -->
            <div class="panel">
                <h2>Method 2: With Debug Logging</h2>
                <p style="margin-bottom: 20px; color: #666;">Same as Method 1, but with console logging</p>
                
                <form method="POST" enctype="multipart/form-data" id="form2">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="test_form" value="2">
                    
                    <div class="form-group">
                        <label for="test_file2">Select CSV/TXT file:</label>
                        <input type="file" name="test_file" id="test_file2" accept=".csv,.txt">
                    </div>
                    
                    <button type="submit" id="btn2">🧪 Test Method 2</button>
                </form>
                
                <div class="info-box" style="margin-top: 20px;">
                    <strong>Open Browser Console (F12) to see:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <li>Form submission event</li>
                        <li>CSRF token presence check</li>
                        <li>All FormData being sent</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Console Output Preview -->
        <div class="panel">
            <h2>📺 Expected Console Output</h2>
            <p style="margin-bottom: 15px;">When you submit Method 2, you should see output like this in your browser console (F12):</p>
            <div class="console-output">
                <div class="log">Form submitting: POST</div>
                <div class="log">CSRF token present: 9f3a7b2c1d4e5f6a7b8c...</div>
                <div class="log">Form data being sent:</div>
                <div class="log">  csrf_token: 9f3a7b2c1d4e5f6a7b8c...</div>
                <div class="log">  test_form: 2</div>
                <div class="log">  test_file: [File object]</div>
            </div>
            <p style="margin-top: 15px; color: #666;"><strong>If CSRF token is missing:</strong> You'll see a warning in orange.</p>
        </div>

        <!-- Current State Info -->
        <div class="panel">
            <h2>🔍 Current Session State</h2>
            <div class="code-block">
                <strong>Session ID:</strong> <?= session_id() ?><br>
                <strong>User:</strong> <?= isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) : 'Not logged in' ?><br>
                <strong>Role:</strong> <?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'N/A' ?><br>
                <strong>CSRF Token:</strong> <?= isset($_SESSION['csrf_token']) ? '<span class="status-good">PRESENT</span> (Length: ' . strlen($_SESSION['csrf_token']) . ')' : '<span class="status-bad">MISSING</span>' ?>
            </div>
            
            <?php if (isset($_SESSION['csrf_token'])): ?>
                <details>
                    <summary style="cursor: pointer; padding: 10px; background: #f5f5f5; border-radius: 4px; margin-top: 10px;">
                        Click to view full CSRF token
                    </summary>
                    <div class="code-block" style="word-break: break-all;">
                        <?= htmlspecialchars($_SESSION['csrf_token']) ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>

        <a href="whitelist.php" class="back-link">← Back to Whitelist</a>
        <a href="diagnostic_import.php" class="back-link">🔍 Full Diagnostic Tool</a>
        <a href="debug_csrf.php" class="back-link">🔧 Debug CSRF</a>
    </div>

    <script>
        // Enhanced logging for Form 2
        document.getElementById('form2').addEventListener('submit', function(e) {
            console.log('%c=== FORM 2 DEBUG START ===', 'color: #2196F3; font-weight: bold; font-size: 14px;');
            console.log('Form submitting:', this.getAttribute('method'));
            
            // Check for CSRF token
            var csrfInput = this.querySelector('input[name="csrf_token"]');
            if (csrfInput) {
                console.log('%cCSRF token present: ' + csrfInput.value.substring(0, 20) + '...', 'color: #4CAF50;');
                console.log('Token length:', csrfInput.value.length);
            } else {
                console.warn('%cWARNING: CSRF token NOT found in form!', 'color: #ff9800; font-weight: bold;');
            }
            
            // Log all form data
            var formData = new FormData(this);
            console.log('Form data being sent:');
            for (var pair of formData.entries()) {
                if (pair[0] === 'csrf_token') {
                    console.log('  %c' + pair[0] + ': ' + pair[1].substring(0, 20) + '...', 'color: #4CAF50;');
                } else if (pair[0] === 'test_file') {
                    console.log('  ' + pair[0] + ': [File: ' + (pair[1].name || 'no file') + ']');
                } else {
                    console.log('  ' + pair[0] + ': ' + pair[1]);
                }
            }
            
            console.log('%c=== FORM 2 DEBUG END ===', 'color: #2196F3; font-weight: bold; font-size: 14px;');
            
            // Disable button to prevent double submit
            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = '⏳ Processing...';
        });
        
        // Simple double-submit prevention for Form 1
        document.getElementById('form1').addEventListener('submit', function() {
            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = '⏳ Processing...';
        });
    </script>
</body>
</html>
