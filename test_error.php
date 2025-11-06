<?php
// TEMPORARY ERROR CHECKER - DELETE AFTER DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 System Check</h2>";

// 1. Check PHP Version
echo "<h3>1. PHP Info</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";
echo "Timezone: " . date_default_timezone_get() . "<br>";

// 2. Check Database Connection
echo "<h3>2. Database Connection</h3>";
try {
    require_once 'connect.php';
    echo "✅ Connection OK<br>";
    
    $stmt = $pdo->query("SELECT DATABASE() as db, VERSION() as v");
    $r = $stmt->fetch();
    echo "Database: <b>" . $r['db'] . "</b><br>";
    echo "MySQL: <b>" . $r['v'] . "</b><br>";
    
    // Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: <b>" . count($tables) . "</b><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 3. Check absen_helper.php
echo "<h3>3. Check Helper Files</h3>";
if (file_exists('absen_helper.php')) {
    echo "✅ absen_helper.php exists<br>";
    try {
        require_once 'absen_helper.php';
        echo "✅ absen_helper.php loaded<br>";
    } catch (Exception $e) {
        echo "❌ Error loading absen_helper.php: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ absen_helper.php NOT FOUND<br>";
}

// 4. Check Session
echo "<h3>4. Session Check</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "✅ Session active<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . ($_SESSION['username'] ?? 'N/A') . "<br>";
} else {
    echo "❌ No active session (not logged in)<br>";
}

// 5. Check required tables
echo "<h3>5. Check Required Tables</h3>";
$required_tables = ['absensi', 'registrasi', 'cabang', 'shift_assignments'];
foreach ($required_tables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "✅ Table <b>$table</b>: $count rows<br>";
    } catch (Exception $e) {
        echo "❌ Table <b>$table</b>: " . $e->getMessage() . "<br>";
    }
}

// 6. List ALL Tables
echo "<h3>6. All Tables in Database</h3>";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h3>✅ Test Complete</h3>";
echo "<p><a href='absen.php'>Go to Absen Page</a></p>";
?>
