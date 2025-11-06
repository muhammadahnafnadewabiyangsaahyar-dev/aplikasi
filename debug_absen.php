<?php
// DEBUG PROSES ABSENSI
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'connect.php';

echo "<h2>🔍 Debug Proses Absensi</h2>";

// 1. Session Check
echo "<h3>1. Session Data</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User ID: " . $_SESSION['user_id'] . "<br>";
    echo "✅ Username: " . ($_SESSION['username'] ?? 'N/A') . "<br>";
    echo "✅ Role: " . ($_SESSION['role'] ?? 'N/A') . "<br>";
    echo "✅ Nama Lengkap: " . ($_SESSION['nama_lengkap'] ?? 'N/A') . "<br>";
    
    $user_id = $_SESSION['user_id'];
} else {
    echo "❌ No session - redirecting to login<br>";
    die();
}

// 2. Check User Data
echo "<h3>2. User Data from Database</h3>";
try {
    $stmt = $pdo->prepare("SELECT * FROM register WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Nama: " . ($user['nama_lengkap'] ?? 'N/A') . "<br>";
        echo "Outlet ID: " . ($user['outlet_id'] ?? 'N/A') . "<br>";
        echo "Role: " . ($user['role'] ?? 'N/A') . "<br>";
    } else {
        echo "❌ User not found in database!<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 3. Check Outlet/Cabang
echo "<h3>3. Outlet/Cabang Data</h3>";
if (isset($user['outlet_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cabang WHERE id = ?");
        $stmt->execute([$user['outlet_id']]);
        $outlet = $stmt->fetch();
        
        if ($outlet) {
            echo "✅ Outlet found<br>";
            echo "ID: " . $outlet['id'] . "<br>";
            echo "Nama: " . $outlet['nama_cabang'] . "<br>";
            echo "Latitude: " . $outlet['latitude'] . "<br>";
            echo "Longitude: " . $outlet['longitude'] . "<br>";
            echo "Radius: " . $outlet['radius_meter'] . " meter<br>";
        } else {
            echo "❌ Outlet not found!<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ User has no outlet_id assigned<br>";
}

// 4. Check Shift Assignment Today
echo "<h3>4. Shift Assignment Today</h3>";
$today = date('Y-m-d');
try {
    $stmt = $pdo->prepare("
        SELECT sa.*, c.nama_cabang, c.jam_masuk, c.jam_keluar
        FROM shift_assignments sa
        LEFT JOIN cabang c ON sa.cabang_id = c.id
        WHERE sa.user_id = ? AND sa.tanggal_shift = ?
    ");
    $stmt->execute([$user_id, $today]);
    $shift = $stmt->fetch();
    
    if ($shift) {
        echo "✅ Shift assignment found<br>";
        echo "Cabang: " . $shift['nama_cabang'] . "<br>";
        echo "Tanggal: " . $shift['tanggal_shift'] . "<br>";
        echo "Jam Masuk: " . $shift['jam_masuk'] . "<br>";
        echo "Jam Keluar: " . $shift['jam_keluar'] . "<br>";
        echo "Status: " . $shift['status_konfirmasi'] . "<br>";
    } else {
        echo "⚠️ No shift assignment for today<br>";
        echo "User will use default outlet<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 5. Check Absensi Today
echo "<h3>5. Absensi Today</h3>";
try {
    $stmt = $pdo->prepare("
        SELECT * FROM absensi 
        WHERE user_id = ? AND tanggal_absensi = ?
    ");
    $stmt->execute([$user_id, $today]);
    $absen = $stmt->fetch();
    
    if ($absen) {
        echo "✅ Absensi record found<br>";
        echo "ID: " . $absen['id'] . "<br>";
        echo "Tanggal: " . $absen['tanggal_absensi'] . "<br>";
        echo "Waktu Masuk: " . ($absen['waktu_masuk'] ?? 'Belum absen') . "<br>";
        echo "Waktu Keluar: " . ($absen['waktu_keluar'] ?? 'Belum absen') . "<br>";
        echo "Status Lokasi: " . ($absen['status_lokasi'] ?? 'N/A') . "<br>";
    } else {
        echo "ℹ️ No absensi record yet for today<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 6. Check absen_helper.php functions
echo "<h3>6. Check Helper Functions</h3>";
if (function_exists('getAbsenStatusToday')) {
    echo "✅ getAbsenStatusToday() exists<br>";
    try {
        $status = getAbsenStatusToday($pdo, $user_id);
        echo "Masuk: " . ($status['masuk'] ? 'Yes' : 'No') . "<br>";
        echo "Keluar: " . ($status['keluar'] ? 'Yes' : 'No') . "<br>";
    } catch (Exception $e) {
        echo "❌ Error calling function: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ getAbsenStatusToday() NOT FOUND<br>";
}

// 7. Simulate POST request (manual test)
echo "<h3>7. Test Form Submission</h3>";
echo "<form method='POST' action='proses_absensi.php'>";
echo "<input type='hidden' name='csrf_token' value='" . ($_SESSION['csrf_token'] ?? '') . "'>";
echo "<input type='hidden' name='latitude' value='-5.1477'>";
echo "<input type='hidden' name='longitude' value='119.4327'>";
echo "<input type='hidden' name='foto_absensi_base64' value='data:image/png;base64,iVBORw0KGgo='>";
echo "<input type='hidden' name='tipe_absen' value='masuk'>";
echo "<button type='submit'>Test Submit Absen Masuk</button>";
echo "</form>";

echo "<h3>✅ Debug Complete</h3>";
echo "<p><a href='absen.php'>Back to Absen Page</a></p>";
?>
