<?php
// ========================================================
// DEBUG SCRIPT - CEK KENAPA ABSEN GAGAL
// ========================================================
session_start();
include 'connect.php';

echo "<!DOCTYPE html><html><head><title>Debug Absen</title>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
.section { background: #2d2d30; padding: 15px; margin: 10px 0; border-left: 4px solid #007acc; }
.success { color: #4ec9b0; }
.error { color: #f48771; }
.warning { color: #dcdcaa; }
.info { color: #9cdcfe; }
h2 { color: #569cd6; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
td, th { border: 1px solid #3e3e42; padding: 8px; text-align: left; }
th { background: #252526; }
</style></head><body>";

echo "<h1>🔍 DEBUG ABSENSI SYSTEM</h1>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";

// ========================================================
// 1. CEK SESSION
// ========================================================
echo "<div class='section'>";
echo "<h2>1. SESSION CHECK</h2>";

if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✅ User logged in</p>";
    echo "<table>";
    echo "<tr><th>Key</th><th>Value</th></tr>";
    echo "<tr><td>user_id</td><td>" . $_SESSION['user_id'] . "</td></tr>";
    echo "<tr><td>username</td><td>" . ($_SESSION['username'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>role</td><td>" . ($_SESSION['role'] ?? 'N/A') . "</td></tr>";
    echo "<tr><td>id_cabang (from DB)</td><td>Will check below</td></tr>";
    echo "<tr><td>csrf_token</td><td>" . (isset($_SESSION['csrf_token']) ? 'EXISTS' : 'MISSING') . "</td></tr>";
    echo "</table>";
} else {
    echo "<p class='error'>❌ NO SESSION - User not logged in!</p>";
    echo "<p>Login terlebih dahulu di: <a href='login.php' style='color:#569cd6'>login.php</a></p>";
    die("</div></body></html>");
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

echo "</div>";

// ========================================================
// 2. CEK USER DATA DI DATABASE
// ========================================================
echo "<div class='section'>";
echo "<h2>2. USER DATA IN DATABASE</h2>";

try {
    $stmt = $pdo->prepare("SELECT * FROM register WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p class='success'>✅ User found in database</p>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($user as $key => $value) {
            if ($key !== 'password') { // Don't show password
                echo "<tr><td>$key</td><td>" . ($value ?? 'NULL') . "</td></tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ User NOT found in database!</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================================
// 3. CEK OUTLET/CABANG
// ========================================================
echo "<div class='section'>";
echo "<h2>3. OUTLET/CABANG CHECK</h2>";

if ($user_role === 'admin') {
    echo "<p class='info'>ℹ️ User is ADMIN - should skip outlet validation</p>";
    
    // Cek apakah ada Kaori HQ
    try {
        $stmt = $pdo->prepare("SELECT * FROM cabang WHERE nama_cabang LIKE '%Kaori HQ%' OR nama_cabang LIKE '%Remote%'");
        $stmt->execute();
        $kaori_hq = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($kaori_hq) {
            echo "<p class='success'>✅ Kaori HQ outlet found</p>";
            echo "<table>";
            foreach ($kaori_hq as $key => $value) {
                echo "<tr><td>$key</td><td>" . ($value ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠️ Kaori HQ outlet NOT found!</p>";
            echo "<p>Run this SQL to create:</p>";
            echo "<pre style='background:#252526;padding:10px;'>
INSERT INTO cabang (nama_cabang, nama_shift, latitude, longitude, radius_meter, jam_masuk, jam_keluar)
VALUES ('Kaori HQ - Remote Office', 'Flexible', 0, 0, 999999, '00:00:00', '23:59:59');
</pre>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='info'>ℹ️ User is REGULAR user - needs outlet validation</p>";
    
    if (isset($user['id_cabang']) && $user['id_cabang']) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM cabang WHERE id = ?");
            $stmt->execute([$user['id_cabang']]);
            $outlet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($outlet) {
                echo "<p class='success'>✅ User's outlet found</p>";
                echo "<table>";
                foreach ($outlet as $key => $value) {
                    echo "<tr><td>$key</td><td>" . ($value ?? 'NULL') . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='error'>❌ User's outlet NOT found in cabang table!</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>❌ User has NO id_cabang assigned!</p>";
    }
}

echo "</div>";

// ========================================================
// 4. CEK ABSENSI HARI INI
// ========================================================
echo "<div class='section'>";
echo "<h2>4. ABSENSI TODAY</h2>";

$today = date('Y-m-d');

try {
    $stmt = $pdo->prepare("SELECT * FROM absensi WHERE user_id = ? AND tanggal_absensi = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id, $today]);
    $absen_today = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($absen_today) {
        echo "<p class='warning'>⚠️ User already has absensi record today</p>";
        echo "<table>";
        foreach ($absen_today as $key => $value) {
            if (!in_array($key, ['foto_absen_masuk', 'foto_absen_keluar'])) {
                echo "<tr><td>$key</td><td>" . ($value ?? 'NULL') . "</td></tr>";
            }
        }
        echo "</table>";
        
        if ($absen_today['waktu_masuk'] && $absen_today['waktu_keluar']) {
            echo "<p class='info'>ℹ️ Both CHECK-IN and CHECK-OUT completed</p>";
        } elseif ($absen_today['waktu_masuk']) {
            echo "<p class='info'>ℹ️ Only CHECK-IN completed, can still CHECK-OUT</p>";
        }
    } else {
        echo "<p class='success'>✅ No absensi record today - can CHECK-IN</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ========================================================
// 5. CEK FILE HELPER
// ========================================================
echo "<div class='section'>";
echo "<h2>5. HELPER FILES CHECK</h2>";

$files_to_check = [
    'connect.php',
    'absen_helper.php',
    'security_helper.php',
    'proses_absensi.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file exists</p>";
    } else {
        echo "<p class='error'>❌ $file MISSING!</p>";
    }
}

// Cek fungsi di absen_helper.php
if (function_exists('hitungJarak')) {
    echo "<p class='success'>✅ hitungJarak() function available</p>";
} else {
    echo "<p class='error'>❌ hitungJarak() function NOT found!</p>";
}

echo "</div>";

// ========================================================
// 6. CEK PROSES_ABSENSI.PHP LOGIC
// ========================================================
echo "<div class='section'>";
echo "<h2>6. ADMIN LOGIC CHECK in proses_absensi.php</h2>";

if (file_exists('proses_absensi.php')) {
    $proses_content = file_get_contents('proses_absensi.php');
    
    // Cek apakah ada logic untuk admin
    if (strpos($proses_content, "user_role === 'admin'") !== false || 
        strpos($proses_content, '$user_role == "admin"') !== false) {
        echo "<p class='success'>✅ Admin role check found in proses_absensi.php</p>";
    } else {
        echo "<p class='warning'>⚠️ No admin role check found - might treat admin as regular user</p>";
    }
    
    // Cek apakah ada skip validation untuk admin
    if (strpos($proses_content, 'skip') !== false && strpos($proses_content, 'admin') !== false) {
        echo "<p class='success'>✅ Validation skip logic found for admin</p>";
    } else {
        echo "<p class='warning'>⚠️ No validation skip found - admin might fail location check</p>";
    }
    
    // Cek apakah ada Kaori HQ reference
    if (strpos($proses_content, 'Kaori HQ') !== false) {
        echo "<p class='success'>✅ Kaori HQ reference found</p>";
    } else {
        echo "<p class='warning'>⚠️ No Kaori HQ reference found</p>";
    }
}

echo "</div>";

// ========================================================
// 7. SIMULATION TEST
// ========================================================
echo "<div class='section'>";
echo "<h2>7. SIMULATION TEST</h2>";

echo "<p class='info'>ℹ️ Test what would happen if you try to absen:</p>";

// Simulasi check-in
if (!$absen_today || !$absen_today['waktu_masuk']) {
    echo "<p class='success'>✅ Can perform CHECK-IN</p>";
    
    if ($user_role === 'admin') {
        echo "<p class='info'>• As ADMIN: Should skip location validation</p>";
        echo "<p class='info'>• As ADMIN: Should use Kaori HQ outlet</p>";
        echo "<p class='info'>• As ADMIN: No shift/time restriction</p>";
    } else {
        echo "<p class='info'>• As USER: Need to be within outlet radius</p>";
        echo "<p class='info'>• As USER: Need to have shift assigned</p>";
    }
} else {
    echo "<p class='warning'>⚠️ Already checked in today at " . $absen_today['waktu_masuk'] . "</p>";
}

// Simulasi check-out
if ($absen_today && $absen_today['waktu_masuk'] && !$absen_today['waktu_keluar']) {
    echo "<p class='success'>✅ Can perform CHECK-OUT</p>";
} elseif ($absen_today && $absen_today['waktu_keluar']) {
    echo "<p class='warning'>⚠️ Already checked out today at " . $absen_today['waktu_keluar'] . "</p>";
}

echo "</div>";

// ========================================================
// 8. RECOMMENDATIONS
// ========================================================
echo "<div class='section'>";
echo "<h2>8. RECOMMENDATIONS</h2>";

if ($user_role === 'admin') {
    // Cek apakah Kaori HQ ada
    $stmt = $pdo->prepare("SELECT id FROM cabang WHERE nama_cabang LIKE '%Kaori HQ%' OR nama_cabang LIKE '%Remote%'");
    $stmt->execute();
    $kaori_hq = $stmt->fetch();
    
    if (!$kaori_hq) {
        echo "<p class='error'>❌ CREATE KAORI HQ OUTLET FIRST!</p>";
        echo "<pre style='background:#252526;padding:10px;'>
-- Run this SQL in phpMyAdmin:
INSERT INTO cabang (nama_cabang, nama_shift, latitude, longitude, radius_meter, jam_masuk, jam_keluar)
VALUES ('Kaori HQ - Remote Office', 'Flexible', 0, 0, 999999, '00:00:00', '23:59:59');
</pre>";
    } else {
        echo "<p class='success'>✅ Kaori HQ exists (ID: " . $kaori_hq['id'] . ")</p>";
    }
    
    // Cek apakah admin punya id_cabang ke Kaori HQ
    if (!isset($user['id_cabang']) || $user['id_cabang'] != $kaori_hq['id']) {
        echo "<p class='warning'>⚠️ UPDATE ADMIN ID_CABANG!</p>";
        echo "<pre style='background:#252526;padding:10px;'>
-- Run this SQL:
UPDATE register SET id_cabang = " . $kaori_hq['id'] . " WHERE id = $user_id;
</pre>";
    } else {
        echo "<p class='success'>✅ Admin id_cabang already correct (ID: " . $user['id_cabang'] . ")</p>";
    }
}

echo "<p class='info'>📝 To test absensi:</p>";
echo "<ol>";
echo "<li>Go to main page: <a href='mainpage.php' style='color:#569cd6'>Click here</a></li>";
echo "<li>Allow location permission when prompted</li>";
echo "<li>Click 'Absen Masuk' button</li>";
echo "<li>Check browser console (F12) for errors</li>";
echo "</ol>";

echo "</div>";

// ========================================================
// 9. QUICK FIX SQL
// ========================================================
echo "<div class='section'>";
echo "<h2>9. QUICK FIX SQL (If needed)</h2>";

echo "<p class='info'>Copy-paste these SQL queries in phpMyAdmin if needed:</p>";

echo "<h3>Create Kaori HQ (if not exists):</h3>";
echo "<pre style='background:#252526;padding:10px;'>
INSERT INTO cabang (nama_cabang, nama_shift, latitude, longitude, radius_meter, jam_masuk, jam_keluar)
VALUES ('Kaori HQ - Remote Office', 'Flexible', 0, 0, 999999, '00:00:00', '23:59:59')
ON DUPLICATE KEY UPDATE nama_cabang = 'Kaori HQ - Remote Office';
</pre>";

echo "<h3>Update ALL admins to use Kaori HQ:</h3>";
echo "<pre style='background:#252526;padding:10px;'>
UPDATE register r
SET r.id_cabang = (SELECT id FROM cabang WHERE nama_cabang LIKE '%Kaori HQ%' LIMIT 1)
WHERE r.role = 'admin';
</pre>";

echo "<h3>Check admin users:</h3>";
echo "<pre style='background:#252526;padding:10px;'>
SELECT r.id, r.username, r.role, r.id_cabang, c.nama_cabang
FROM register r
LEFT JOIN cabang c ON r.id_cabang = c.id
WHERE r.role = 'admin';
</pre>";

echo "</div>";

echo "</body></html>";
?>
