<?php
/**
 * QUICK FIX: Force Generate All CSRF Tokens
 * Run this file to immediately generate all required CSRF tokens
 */

session_start();

// Force generate all tokens
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['csrf_token_import'] = bin2hex(random_bytes(32));
$_SESSION['csrf_token_import_smart'] = bin2hex(random_bytes(32));

?>
<!DOCTYPE html>
<html>
<head>
    <title>Token Fixed!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .success-box {
            background: white;
            color: #333;
            padding: 40px;
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .checkmark {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        h1 {
            color: #28a745;
            margin: 0;
        }
        .token-list {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .token-item {
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-left: 4px solid #28a745;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <div class="checkmark">✅</div>
        <h1>Tokens Fixed!</h1>
        <p>All CSRF tokens have been successfully generated.</p>
        
        <div class="token-list">
            <div class="token-item">
                <strong>csrf_token:</strong><br>
                <code><?= substr($_SESSION['csrf_token'], 0, 30) ?>...</code>
                (<?= strlen($_SESSION['csrf_token']) ?> chars)
            </div>
            <div class="token-item">
                <strong>csrf_token_import:</strong><br>
                <code><?= substr($_SESSION['csrf_token_import'], 0, 30) ?>...</code>
                (<?= strlen($_SESSION['csrf_token_import']) ?> chars)
            </div>
            <div class="token-item">
                <strong>csrf_token_import_smart:</strong><br>
                <code><?= substr($_SESSION['csrf_token_import_smart'], 0, 30) ?>...</code>
                (<?= strlen($_SESSION['csrf_token_import_smart']) ?> chars)
            </div>
        </div>
        
        <p><strong>Session ID:</strong> <?= session_id() ?></p>
        <p><strong>User ID:</strong> <?= $_SESSION['user_id'] ?? 'Not set' ?></p>
        <p><strong>Role:</strong> <?= $_SESSION['role'] ?? 'Not set' ?></p>
        
        <hr style="margin: 30px 0;">
        
        <h2>✅ What's Fixed:</h2>
        <ul style="text-align: left; display: inline-block;">
            <li>✅ All CSRF tokens generated</li>
            <li>✅ Session is active</li>
            <li>✅ Tokens are 64 characters long</li>
            <li>✅ Ready for import!</li>
        </ul>
        
        <hr style="margin: 30px 0;">
        
        <h2>🚀 Next Steps:</h2>
        <div>
            <a href="import_csv_enhanced.php" class="btn">Go to Import Enhanced</a>
            <a href="import_csv_smart.php" class="btn">Go to Import Smart</a>
        </div>
        <div style="margin-top: 20px;">
            <a href="debug_csrf.php" class="btn" style="background: #6c757d;">View Debug Info</a>
            <a href="whitelist.php" class="btn" style="background: #6c757d;">Back to Whitelist</a>
        </div>
        
        <hr style="margin: 30px 0;">
        
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
            <strong>💡 Tip:</strong> If you still get "Invalid request" error after this:
            <ol style="text-align: left; margin: 10px 0 0 20px;">
                <li>Clear your browser cache (Cmd+Shift+R on Mac)</li>
                <li>Try in incognito/private mode</li>
                <li>Check browser console for JavaScript errors (F12)</li>
                <li>Check PHP error log for server-side errors</li>
            </ol>
        </div>
    </div>
    
    <div style="margin-top: 30px; color: white;">
        <p><small>Generated at: <?= date('Y-m-d H:i:s') ?></small></p>
        <p><small><a href="fix_csrf_tokens.php" style="color: white;">Run again</a> | <a href="TROUBLESHOOTING_CSRF.md" style="color: white;">Troubleshooting Guide</a></small></p>
    </div>
</body>
</html>
