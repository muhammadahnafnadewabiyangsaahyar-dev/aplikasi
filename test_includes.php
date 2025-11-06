<?php
// ========================================================
// TEST INCLUDE FILES & FUNCTIONS
// ========================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing File Includes</h1>";

// Test 1: Include absen_helper.php
echo "<h2>1. Testing absen_helper.php</h2>";
if (file_exists('absen_helper.php')) {
    echo "✅ File exists<br>";
    include_once 'absen_helper.php';
    echo "✅ File included successfully<br>";
    
    if (function_exists('hitungJarak')) {
        echo "✅ hitungJarak() function is available<br>";
        
        // Test the function
        $distance = hitungJarak(-5.1795, 119.4634, -5.1796, 119.4635);
        echo "✅ Function works! Test distance: " . $distance . " meters<br>";
    } else {
        echo "❌ hitungJarak() function NOT found after include!<br>";
        
        // Show all defined functions
        echo "<h3>All user-defined functions:</h3>";
        echo "<pre>";
        print_r(get_defined_functions()['user']);
        echo "</pre>";
    }
} else {
    echo "❌ File NOT found<br>";
}

// Test 2: Include security_helper.php
echo "<h2>2. Testing security_helper.php</h2>";
if (file_exists('security_helper.php')) {
    echo "✅ File exists<br>";
    include_once 'security_helper.php';
    echo "✅ File included successfully<br>";
} else {
    echo "❌ File NOT found<br>";
}

// Test 3: Include connect.php
echo "<h2>3. Testing connect.php</h2>";
if (file_exists('connect.php')) {
    echo "✅ File exists<br>";
    include_once 'connect.php';
    echo "✅ File included successfully<br>";
    
    if (isset($pdo)) {
        echo "✅ PDO connection available<br>";
    } else {
        echo "❌ PDO connection NOT available<br>";
    }
} else {
    echo "❌ File NOT found<br>";
}

// Test 4: Show PHP errors
echo "<h2>4. PHP Configuration</h2>";
echo "Error reporting level: " . error_reporting() . "<br>";
echo "Display errors: " . ini_get('display_errors') . "<br>";
echo "PHP version: " . phpversion() . "<br>";

?>
