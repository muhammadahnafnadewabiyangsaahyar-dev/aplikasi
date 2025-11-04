<?php
session_start();
include 'connect.php';

echo "========================================\n";
echo "SESSION & DATA CHECK\n";
echo "========================================\n\n";

// 1. Check session
echo "1. SESSION INFO:\n";
if (isset($_SESSION['username'])) {
    echo "   ✅ User logged in\n";
    echo "   Username: " . $_SESSION['username'] . "\n";
    echo "   User ID: " . $_SESSION['user_id'] . "\n";
    echo "   Role: " . ($_SESSION['role'] ?? 'user') . "\n";
} else {
    echo "   ❌ Not logged in\n";
    echo "   Please login first!\n";
    exit;
}

$user_id = $_SESSION['user_id'];
$bulan_ini = date('Y-m');

echo "\n2. LUPA ABSEN PULANG DATA:\n";
$sql_lupa = "SELECT 
                id,
                tanggal_absensi,
                TIME(waktu_masuk) as jam_masuk,
                DATEDIFF(CURDATE(), tanggal_absensi) as hari_lalu
            FROM absensi 
            WHERE user_id = ? 
            AND waktu_masuk IS NOT NULL 
            AND waktu_keluar IS NULL
            AND tanggal_absensi < CURDATE()
            ORDER BY tanggal_absensi DESC 
            LIMIT 10";
$stmt = $pdo->prepare($sql_lupa);
$stmt->execute([$user_id]);
$lupa_absen_pulang = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($lupa_absen_pulang)) {
    echo "   ❌ No 'lupa absen pulang' data found\n";
    echo "   Run: php test_lupa_absen_pulang.php\n";
} else {
    echo "   ✅ Found " . count($lupa_absen_pulang) . " records\n";
    foreach ($lupa_absen_pulang as $lupa) {
        echo "   - " . $lupa['tanggal_absensi'] . " | Masuk: " . $lupa['jam_masuk'] . " | Lupa sejak " . $lupa['hari_lalu'] . " hari lalu\n";
    }
}

echo "\n3. STATISTICS:\n";
// Total hadir
$sql_hadir = "SELECT COUNT(DISTINCT tanggal_absensi) as total 
              FROM absensi 
              WHERE user_id = ? 
              AND waktu_masuk IS NOT NULL
              AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
$stmt = $pdo->prepare($sql_hadir);
$stmt->execute([$user_id, $bulan_ini]);
$total_hadir = $stmt->fetchColumn();

echo "   Total Hadir (bulan ini): $total_hadir\n";
echo "   Lupa Absen Pulang: " . count($lupa_absen_pulang) . "\n";

echo "\n========================================\n";
echo "✅ CHECK COMPLETE!\n";
echo "========================================\n";
echo "\nNEXT STEPS:\n";
echo "1. If not logged in, go to: http://localhost/aplikasi/\n";
echo "2. Login as: superadmin / (your password)\n";
echo "3. Go to: http://localhost/aplikasi/mainpage.php\n";
echo "4. You should see the warning banner and stat card\n";
echo "========================================\n";
