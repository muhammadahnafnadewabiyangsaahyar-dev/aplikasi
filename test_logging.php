<?php
// Test script to verify error_log() is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Logging Test</h1>";

// Test 1: error_log()
error_log("=== TEST ERROR LOG ===");
error_log("This is a test message from test_logging.php");
error_log("Timestamp: " . date('Y-m-d H:i:s'));
echo "<p>✓ error_log() called - check Apache error_log</p>";

// Test 2: Check error log location
$error_log_path = ini_get('error_log');
echo "<p>Error log path: <strong>" . ($error_log_path ?: 'default (stderr/Apache error_log)') . "</strong></p>";

// Test 3: Check if we can write to a custom log file
$custom_log = __DIR__ . '/test_app.log';
error_log("TEST: " . date('Y-m-d H:i:s') . " - Custom log test\n", 3, $custom_log);
echo "<p>Custom log test written to: <code>$custom_log</code></p>";

// Test 4: Display PHP info related to logging
echo "<h2>PHP Logging Configuration</h2>";
echo "<pre>";
echo "log_errors: " . ini_get('log_errors') . "\n";
echo "error_log: " . ini_get('error_log') . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . error_reporting() . "\n";
echo "</pre>";

// Test 5: Session test
session_start();
echo "<h2>Session Test</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<p><a href='whitelist.php'>← Back to Whitelist</a></p>";
