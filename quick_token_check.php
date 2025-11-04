<?php
/**
 * Quick CSRF Test - Show which tokens are in session and which to use
 */
session_start();

echo "<!DOCTYPE html>
<html>
<head>
    <title>CSRF Token Status</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .token { font-family: 'Courier New', monospace; background: #f0f0f0; padding: 10px; border-radius: 4px; word-break: break-all; }
        .yes { color: #28a745; font-weight: bold; }
        .no { color: #dc3545; font-weight: bold; }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        table td { padding: 10px; border-bottom: 1px solid #ddd; }
        table td:first-child { font-weight: bold; width: 200px; }
    </style>
</head>
<body>
    <h1>🔐 CSRF Token Status Check</h1>
";

echo "<div class='box'>";
echo "<h2>Session Information</h2>";
echo "<table>";
echo "<tr><td>Session ID:</td><td>" . session_id() . "</td></tr>";
echo "<tr><td>Session Status:</td><td>" . (session_status() === PHP_SESSION_ACTIVE ? '<span class=\"yes\">ACTIVE</span>' : '<span class=\"no\">NOT ACTIVE</span>') . "</td></tr>";
echo "<tr><td>User ID:</td><td>" . ($_SESSION['user_id'] ?? '<span class=\"no\">NOT SET</span>') . "</td></tr>";
echo "<tr><td>Role:</td><td>" . ($_SESSION['role'] ?? '<span class=\"no\">NOT SET</span>') . "</td></tr>";
echo "</table>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>CSRF Tokens in Session</h2>";
echo "<table>";

// csrf_token (untuk whitelist.php)
echo "<tr><td>csrf_token</td><td>";
if (isset($_SESSION['csrf_token'])) {
    echo "<span class='yes'>✅ EXISTS</span><br>";
    echo "<div class='token'>" . htmlspecialchars($_SESSION['csrf_token']) . "</div>";
    echo "<small>Length: " . strlen($_SESSION['csrf_token']) . " chars | <strong>Used by: whitelist.php</strong></small>";
} else {
    echo "<span class='no'>❌ NOT SET</span>";
}
echo "</td></tr>";

// csrf_token_import (untuk import_csv_enhanced.php)
echo "<tr><td>csrf_token_import</td><td>";
if (isset($_SESSION['csrf_token_import'])) {
    echo "<span class='yes'>✅ EXISTS</span><br>";
    echo "<div class='token'>" . htmlspecialchars($_SESSION['csrf_token_import']) . "</div>";
    echo "<small>Length: " . strlen($_SESSION['csrf_token_import']) . " chars | <strong>Used by: import_csv_enhanced.php</strong></small>";
} else {
    echo "<span class='no'>❌ NOT SET</span>";
}
echo "</td></tr>";

// csrf_token_import_smart (untuk import_csv_smart.php)
echo "<tr><td>csrf_token_import_smart</td><td>";
if (isset($_SESSION['csrf_token_import_smart'])) {
    echo "<span class='yes'>✅ EXISTS</span><br>";
    echo "<div class='token'>" . htmlspecialchars($_SESSION['csrf_token_import_smart']) . "</div>";
    echo "<small>Length: " . strlen($_SESSION['csrf_token_import_smart']) . " chars | <strong>Used by: import_csv_smart.php</strong></small>";
} else {
    echo "<span class='no'>❌ NOT SET</span>";
}
echo "</td></tr>";

echo "</table>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>📋 Which Token to Use Where?</h2>";
echo "<table>";
echo "<tr><td>whitelist.php</td><td>Uses <code>csrf_token</code></td></tr>";
echo "<tr><td>import_csv_enhanced.php</td><td>Uses <code>csrf_token_import</code></td></tr>";
echo "<tr><td>import_csv_smart.php</td><td>Uses <code>csrf_token_import_smart</code></td></tr>";
echo "</table>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>🧪 Test Links</h2>";
echo "<p><a href='whitelist.php' style='color: #667eea; text-decoration: none; font-weight: bold;'>→ Test whitelist.php</a></p>";
echo "<p><a href='import_csv_enhanced.php' style='color: #667eea; text-decoration: none; font-weight: bold;'>→ Test import_csv_enhanced.php</a></p>";
echo "<p><a href='import_csv_smart.php' style='color: #667eea; text-decoration: none; font-weight: bold;'>→ Test import_csv_smart.php</a></p>";
echo "<p><a href='fix_csrf_tokens.php' style='color: #28a745; text-decoration: none; font-weight: bold;'>→ Regenerate All Tokens</a></p>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>🔄 Refresh Page</h2>";
echo "<p>Tokens should <strong>NOT change</strong> when you refresh this page (unless you click Regenerate).</p>";
echo "<button onclick='location.reload()' style='padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>Refresh Page</button>";
echo "</div>";

echo "</body></html>";
?>
