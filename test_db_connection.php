<?php
/**
 * Test Database Connection & Login Query
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Database Connection Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// Test 1: Include connect.php
echo "<h2>Test 1: Include connect.php</h2>";
try {
    require_once 'connect.php';
    echo "<p class='success'>✓ connect.php included successfully</p>";
    echo "<p>PDO Object: " . (isset($pdo) ? 'EXISTS' : 'NOT FOUND') . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error including connect.php: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Test basic query
echo "<h2>Test 2: Test Basic Query</h2>";
try {
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p class='success'>✓ Basic query successful: " . $result['test'] . "</p>";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Query failed: " . $e->getMessage() . "</p>";
}

// Test 3: Check register table
echo "<h2>Test 3: Check 'register' Table</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'register'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "<p class='success'>✓ Table 'register' exists</p>";
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM register");
        $count = $stmt->fetch();
        echo "<p>Total records: " . $count['count'] . "</p>";
        
        // Show structure
        $stmt = $pdo->query("DESCRIBE register");
        $columns = $stmt->fetchAll();
        echo "<p>Table structure:</p><pre>";
        print_r($columns);
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ Table 'register' NOT FOUND</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error checking table: " . $e->getMessage() . "</p>";
}

// Test 4: Check users table
echo "<h2>Test 4: Check 'users' Table</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "<p class='success'>✓ Table 'users' exists</p>";
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch();
        echo "<p>Total records: " . $count['count'] . "</p>";
        
        // Show structure
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "<p>Table structure:</p><pre>";
        print_r($columns);
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ Table 'users' NOT FOUND</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error checking table: " . $e->getMessage() . "</p>";
}

// Test 5: Test the actual login query
echo "<h2>Test 5: Test Login Query (with test username)</h2>";
echo "<form method='post' style='background:#f0f0f0;padding:15px;border-radius:5px;max-width:400px;'>";
echo "<p><label>Test Username: <input type='text' name='test_username' value='superadmin' style='padding:5px;width:200px;'></label></p>";
echo "<p><button type='submit' style='padding:10px 20px;background:#4CAF50;color:white;border:none;border-radius:4px;cursor:pointer;'>Test Query</button></p>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_username'])) {
    $test_username = $_POST['test_username'];
    
    echo "<h3>Testing with username: <code>{$test_username}</code></h3>";
    
    try {
        // This is the exact query from login.php
        $sql = "SELECT r.*, u.id_cabang 
                FROM register r 
                LEFT JOIN users u ON r.id = u.id 
                WHERE r.username = ?";
        
        echo "<p>Query:</p><pre>" . htmlspecialchars($sql) . "</pre>";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$test_username]);
        $row = $stmt->fetch();
        
        if ($row) {
            echo "<p class='success'>✓ User found!</p>";
            echo "<pre>";
            print_r($row);
            echo "</pre>";
            
            // Check password field
            if (isset($row['password'])) {
                echo "<p class='success'>✓ Password field exists</p>";
                echo "<p>Password hash: " . substr($row['password'], 0, 20) . "...</p>";
            } else {
                echo "<p class='error'>✗ Password field NOT FOUND</p>";
            }
        } else {
            echo "<p class='error'>✗ User NOT found</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Query error: " . $e->getMessage() . "</p>";
        echo "<pre>Error details: " . print_r($e, true) . "</pre>";
    }
}

// Test 6: List all usernames
echo "<h2>Test 6: All Usernames in 'register' Table</h2>";
try {
    $stmt = $pdo->query("SELECT id, username, role, nama_lengkap FROM register LIMIT 10");
    $users = $stmt->fetchAll();
    
    if ($users) {
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
        echo "<tr style='background:#4CAF50;color:white;'><th>ID</th><th>Username</th><th>Role</th><th>Nama Lengkap</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['nama_lengkap'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>No users found</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Login</a></p>";
?>
