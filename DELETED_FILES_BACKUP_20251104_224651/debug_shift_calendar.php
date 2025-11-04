<?php
session_start();
require_once 'connect.php';

// Check session
echo "<h1>Debug Shift Calendar</h1>";
echo "<h2>Session Check:</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "Nama: " . ($_SESSION['nama_lengkap'] ?? 'NOT SET') . "\n";
echo "</pre>";

// Check if admin
$isAdmin = isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
echo "<h3>Is Admin: " . ($isAdmin ? '✅ YES' : '❌ NO') . "</h3>";

if (!$isAdmin) {
    echo "<p style='color: red;'>You must login as admin to access shift calendar!</p>";
    echo "<a href='login.php'>Go to Login</a>";
} else {
    echo "<h2>Database Check:</h2>";
    
    // Count cabang
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cabang");
    $result = $stmt->fetch();
    echo "<p>Total Cabang: " . $result['total'] . "</p>";
    
    // Get cabang list
    echo "<h3>Cabang List:</h3>";
    $stmt = $pdo->query("SELECT id, nama_cabang, nama_shift, jam_masuk, jam_keluar FROM cabang ORDER BY nama_cabang LIMIT 10");
    $cabangList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nama Cabang</th><th>Shift</th><th>Jam Masuk</th><th>Jam Keluar</th></tr>";
    foreach ($cabangList as $cabang) {
        echo "<tr>";
        echo "<td>" . $cabang['id'] . "</td>";
        echo "<td>" . $cabang['nama_cabang'] . "</td>";
        echo "<td>" . $cabang['nama_shift'] . "</td>";
        echo "<td>" . $cabang['jam_masuk'] . "</td>";
        echo "<td>" . $cabang['jam_keluar'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test API
    echo "<h2>API Test:</h2>";
    echo "<p>Testing API endpoint (should work if you're logged in as admin):</p>";
    echo "<a href='api_shift_calendar.php?action=get_cabang' target='_blank'>Test Get Cabang API</a>";
    
    // Count users
    echo "<h3>User Check:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM register WHERE role = 'user'");
    $result = $stmt->fetch();
    echo "<p>Total Users: " . $result['total'] . "</p>";
    
    // Count shift assignments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM shift_assignments");
    $result = $stmt->fetch();
    echo "<p>Total Shift Assignments: " . $result['total'] . "</p>";
    
    echo "<h3>Actions:</h3>";
    echo "<ul>";
    echo "<li><a href='shift_calendar.php'>Go to Shift Calendar</a></li>";
    echo "<li><a href='mainpage.php'>Go to Main Page</a></li>";
    echo "</ul>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Shift Calendar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        table { border-collapse: collapse; margin: 10px 0; background: white; }
        th { background: #667eea; color: white; }
        pre { background: white; padding: 15px; border-radius: 5px; }
        a { color: #667eea; text-decoration: none; padding: 5px 10px; background: white; border-radius: 3px; display: inline-block; margin: 5px; }
        a:hover { background: #667eea; color: white; }
    </style>
</head>
<body>
</body>
</html>
