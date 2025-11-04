<?php
/**
 * DEBUG CSRF TOKEN ISSUES
 * Use this file to diagnose CSRF token problems
 */

session_start();

// Auto-generate missing tokens on page load
$tokens_generated = [];
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $tokens_generated[] = 'csrf_token';
}
if (!isset($_SESSION['csrf_token_import'])) {
    $_SESSION['csrf_token_import'] = bin2hex(random_bytes(32));
    $tokens_generated[] = 'csrf_token_import';
}
if (!isset($_SESSION['csrf_token_import_smart'])) {
    $_SESSION['csrf_token_import_smart'] = bin2hex(random_bytes(32));
    $tokens_generated[] = 'csrf_token_import_smart';
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSRF Token Debug</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border: 2px solid #333;
            border-radius: 5px;
        }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre {
            background: #f0f0f0;
            padding: 10px;
            overflow-x: auto;
        }
        h2 {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>🔍 CSRF Token Debug Tool</h1>
    
    <?php if (!empty($tokens_generated)): ?>
        <div style="background: #d4edda; border: 2px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <strong>✅ Auto-Generated Missing Tokens:</strong>
            <ul>
                <?php foreach ($tokens_generated as $token): ?>
                    <li><code><?= $token ?></code></li>
                <?php endforeach; ?>
            </ul>
            <p style="margin-bottom: 0;">These tokens were missing and have been automatically generated. You can now use the import features.</p>
        </div>
    <?php endif; ?>
    
    <div class="debug-section">
        <h2>1. Session Information</h2>
        <p><strong>Session ID:</strong> <?= session_id() ?></p>
        <p><strong>Session Status:</strong> 
            <?php
            $status = session_status();
            if ($status === PHP_SESSION_ACTIVE) {
                echo '<span class="ok">✅ ACTIVE</span>';
            } else {
                echo '<span class="error">❌ NOT ACTIVE (Status: ' . $status . ')</span>';
            }
            ?>
        </p>
        <p><strong>Session Save Path:</strong> <?= session_save_path() ?></p>
        <p><strong>Session Name:</strong> <?= session_name() ?></p>
    </div>
    
    <div class="debug-section">
        <h2>2. CSRF Tokens in Session</h2>
        <?php
        $tokens = [
            'csrf_token' => isset($_SESSION['csrf_token']),
            'csrf_token_import' => isset($_SESSION['csrf_token_import']),
            'csrf_token_import_smart' => isset($_SESSION['csrf_token_import_smart'])
        ];
        ?>
        
        <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
            <tr>
                <th>Token Name</th>
                <th>Exists</th>
                <th>Length</th>
                <th>First 20 Chars</th>
            </tr>
            <?php foreach ($tokens as $name => $exists): ?>
            <tr>
                <td><code><?= $name ?></code></td>
                <td><?= $exists ? '<span class="ok">✅ YES</span>' : '<span class="error">❌ NO</span>' ?></td>
                <td><?= $exists ? strlen($_SESSION[$name]) : 'N/A' ?></td>
                <td><?= $exists ? htmlspecialchars(substr($_SESSION[$name], 0, 20)) . '...' : 'N/A' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="debug-section">
        <h2>3. User Session Data</h2>
        <p><strong>User ID:</strong> <?= isset($_SESSION['user_id']) ? '<span class="ok">' . $_SESSION['user_id'] . '</span>' : '<span class="error">NOT SET</span>' ?></p>
        <p><strong>Role:</strong> <?= isset($_SESSION['role']) ? '<span class="ok">' . $_SESSION['role'] . '</span>' : '<span class="error">NOT SET</span>' ?></p>
        <p><strong>Username:</strong> <?= isset($_SESSION['username']) ? $_SESSION['username'] : '<span class="error">NOT SET</span>' ?></p>
    </div>
    
    <div class="debug-section">
        <h2>4. All Session Variables</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <div class="debug-section">
        <h2>5. POST Data (if any)</h2>
        <?php if (!empty($_POST)): ?>
            <pre><?php print_r($_POST); ?></pre>
        <?php else: ?>
            <p class="warning">⚠️ No POST data</p>
        <?php endif; ?>
    </div>
    
    <div class="debug-section">
        <h2>6. Server Info</h2>
        <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
        <p><strong>Server Software:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></p>
        <p><strong>Request Method:</strong> <?= $_SERVER['REQUEST_METHOD'] ?></p>
        <p><strong>Request URI:</strong> <?= $_SERVER['REQUEST_URI'] ?></p>
    </div>
    
    <div class="debug-section">
        <h2>7. Test Forms</h2>
        
        <h3>Test 1: csrf_token (whitelist.php style)</h3>
        <form method="post" action="?test=1" style="background: #e7f3fe; padding: 15px; margin: 10px 0;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <button type="submit" name="test1">Test Submit (csrf_token)</button>
        </form>
        
        <h3>Test 2: csrf_token_import (import_csv_enhanced.php style)</h3>
        <form method="post" action="?test=2" style="background: #d4edda; padding: 15px; margin: 10px 0;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token_import'] ?? '' ?>">
            <button type="submit" name="test2">Test Submit (csrf_token_import)</button>
        </form>
        
        <?php
        if (isset($_GET['test'])) {
            echo '<div style="background: #fff3cd; padding: 15px; margin: 10px 0;">';
            echo '<strong>Test Result for Test ' . $_GET['test'] . ':</strong><br>';
            echo 'Posted csrf_token: ' . ($_POST['csrf_token'] ?? 'NOT SET') . '<br>';
            
            if ($_GET['test'] == 1) {
                $expected = $_SESSION['csrf_token'] ?? '';
                echo 'Expected token: ' . substr($expected, 0, 20) . '...<br>';
                echo 'Match: ' . (($_POST['csrf_token'] ?? '') === $expected ? '<span class="ok">✅ YES</span>' : '<span class="error">❌ NO</span>');
            } else {
                $expected = $_SESSION['csrf_token_import'] ?? '';
                echo 'Expected token: ' . substr($expected, 0, 20) . '...<br>';
                echo 'Match: ' . (($_POST['csrf_token'] ?? '') === $expected ? '<span class="ok">✅ YES</span>' : '<span class="error">❌ NO</span>');
            }
            echo '</div>';
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h2>8. Diagnostic Summary</h2>
        <?php
        $issues = [];
        
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $issues[] = '❌ Session is not active';
        }
        
        if (!isset($_SESSION['user_id'])) {
            $issues[] = '❌ User not logged in (user_id missing)';
        }
        
        if (!isset($_SESSION['role'])) {
            $issues[] = '❌ User role not set';
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $issues[] = '⚠️ csrf_token not set (needed for whitelist.php)';
        }
        
        if (!isset($_SESSION['csrf_token_import'])) {
            $issues[] = '⚠️ csrf_token_import not set (needed for import_csv_enhanced.php)';
        }
        
        if (empty($issues)) {
            echo '<p class="ok">✅ All checks passed! No issues detected.</p>';
        } else {
            echo '<p class="error">Issues detected:</p>';
            echo '<ul>';
            foreach ($issues as $issue) {
                echo '<li>' . $issue . '</li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h2>9. Quick Actions</h2>
        <p>
            <a href="?action=generate_tokens" style="padding: 10px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">Generate All Tokens</a>
            <a href="?action=clear_session" style="padding: 10px; background: #f44336; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Clear Session</a>
            <a href="debug_csrf.php" style="padding: 10px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Refresh</a>
        </p>
        
        <?php
        if (isset($_GET['action'])) {
            if ($_GET['action'] === 'generate_tokens') {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['csrf_token_import'] = bin2hex(random_bytes(32));
                $_SESSION['csrf_token_import_smart'] = bin2hex(random_bytes(32));
                echo '<p class="ok">✅ All tokens generated! <a href="debug_csrf.php">Refresh to see</a></p>';
            } elseif ($_GET['action'] === 'clear_session') {
                session_destroy();
                echo '<p class="warning">⚠️ Session cleared! <a href="debug_csrf.php">Refresh</a></p>';
            }
        }
        ?>
    </div>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="whitelist.php">← Back to Whitelist</a> | 
        <a href="import_csv_enhanced.php">Go to Import Enhanced</a> | 
        <a href="import_csv_smart.php">Go to Import Smart</a>
    </div>
</body>
</html>
