<?php
/**
 * TEST SCRIPT: Lupa Absen Pulang Feature
 * 
 * Script ini akan:
 * 1. Cek waktu database
 * 2. Hapus data absen admin
 * 3. Buat test scenario: Kemarin absen masuk tanpa keluar
 */

require_once 'connect.php';

echo "========================================\n";
echo "TEST LUPA ABSEN PULANG FEATURE\n";
echo "========================================\n\n";

try {
    // ========================================
    // 1. CHECK CURRENT DATABASE TIME
    // ========================================
    echo "1. CHECKING DATABASE TIME...\n";
    $stmt = $pdo->query("SELECT NOW() as db_time, CURDATE() as db_date, CURTIME() as db_time_only");
    $time = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Current Database Time: " . $time['db_time'] . "\n";
    echo "   Current Date: " . $time['db_date'] . "\n";
    echo "   Current Time: " . $time['db_time_only'] . "\n\n";
    
    // ========================================
    // 2. CHECK ADMIN USER
    // ========================================
    echo "2. CHECKING ADMIN USER...\n";
    $stmt = $pdo->query("SELECT id, username, nama_lengkap, role FROM register WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "   ❌ ERROR: No admin user found!\n";
        exit(1);
    }
    
    echo "   Admin User ID: " . $admin['id'] . "\n";
    echo "   Username: " . $admin['username'] . "\n";
    echo "   Nama: " . $admin['nama_lengkap'] . "\n\n";
    
    $admin_user_id = $admin['id'];
    
    // ========================================
    // 3. SHOW CURRENT ADMIN ATTENDANCE
    // ========================================
    echo "3. CURRENT ADMIN ATTENDANCE DATA...\n";
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.tanggal_absensi,
            TIME(a.waktu_masuk) as jam_masuk,
            TIME(a.waktu_keluar) as jam_keluar,
            CASE 
                WHEN a.waktu_keluar IS NULL AND a.tanggal_absensi < CURDATE() THEN 'Lupa Absen Pulang'
                WHEN a.waktu_keluar IS NULL THEN 'Belum Absen Keluar'
                ELSE 'Complete'
            END as status_deteksi
        FROM absensi a
        WHERE a.user_id = ?
        ORDER BY a.tanggal_absensi DESC
        LIMIT 10
    ");
    $stmt->execute([$admin_user_id]);
    $current_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($current_attendance)) {
        echo "   No attendance records found.\n\n";
    } else {
        foreach ($current_attendance as $att) {
            echo "   - " . $att['tanggal_absensi'] . " | Masuk: " . ($att['jam_masuk'] ?? '-') . " | Keluar: " . ($att['jam_keluar'] ?? '-') . " | Status: " . $att['status_deteksi'] . "\n";
        }
        echo "\n";
    }
    
    // ========================================
    // 4. DELETE ADMIN ATTENDANCE
    // ========================================
    echo "4. DELETING ADMIN ATTENDANCE DATA...\n";
    $stmt = $pdo->prepare("DELETE FROM absensi WHERE user_id = ?");
    $stmt->execute([$admin_user_id]);
    $deleted_count = $stmt->rowCount();
    echo "   ✅ Deleted " . $deleted_count . " attendance records\n\n";
    
    // ========================================
    // 5. CREATE TEST SCENARIO
    // ========================================
    echo "5. CREATING TEST SCENARIO...\n";
    
    // Tanggal kemarin
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $waktu_masuk = $yesterday . ' 08:00:00';
    
    echo "   Creating attendance record:\n";
    echo "   - Date: " . $yesterday . " (yesterday)\n";
    echo "   - Clock In: 08:00:00\n";
    echo "   - Clock Out: NULL (FORGOT!)\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO absensi (
            user_id,
            tanggal_absensi,
            waktu_masuk,
            waktu_keluar,
            status_lokasi,
            foto_absen,
            menit_terlambat,
            status_keterlambatan,
            potongan_tunjangan,
            status_lembur
        ) VALUES (?, ?, ?, NULL, ?, ?, 0, 'tepat waktu', 'tidak ada', 'Not Applicable')
    ");
    
    $stmt->execute([
        $admin_user_id,
        $yesterday,
        $waktu_masuk,
        'Admin - Office',
        'test_absen.jpg'
    ]);
    
    echo "   ✅ Test scenario created!\n\n";
    
    // ========================================
    // 6. VERIFY TEST SCENARIO
    // ========================================
    echo "6. VERIFYING TEST SCENARIO...\n";
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.tanggal_absensi,
            TIME(a.waktu_masuk) as jam_masuk,
            TIME(a.waktu_keluar) as jam_keluar,
            DATEDIFF(CURDATE(), a.tanggal_absensi) as hari_lalu,
            CASE 
                WHEN a.waktu_keluar IS NULL AND a.tanggal_absensi < CURDATE() THEN 'Lupa Absen Pulang'
                WHEN a.waktu_keluar IS NULL THEN 'Belum Absen Keluar'
                ELSE 'Complete'
            END as status_deteksi
        FROM absensi a
        WHERE a.user_id = ?
        ORDER BY a.tanggal_absensi DESC
    ");
    $stmt->execute([$admin_user_id]);
    $verify = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($verify as $att) {
        $icon = $att['status_deteksi'] == 'Lupa Absen Pulang' ? '🔴' : ($att['status_deteksi'] == 'Belum Absen Keluar' ? '⚠️' : '✅');
        echo "   " . $icon . " " . $att['tanggal_absensi'] . " | Masuk: " . ($att['jam_masuk'] ?? '-') . " | Keluar: " . ($att['jam_keluar'] ?? '-') . " | Status: " . $att['status_deteksi'] . " (" . $att['hari_lalu'] . " hari lalu)\n";
    }
    echo "\n";
    
    // ========================================
    // 7. EXPECTED RESULTS
    // ========================================
    echo "========================================\n";
    echo "✅ SETUP COMPLETE!\n";
    echo "========================================\n\n";
    echo "NEXT STEPS:\n";
    echo "1. Login sebagai admin di browser\n";
    echo "2. Buka mainpage.php\n";
    echo "3. Anda seharusnya melihat:\n";
    echo "   - ⚠️ Warning banner: 'Anda Lupa Absen Pulang! (1 hari)'\n";
    echo "   - 📊 Stat card: 'Lupa Absen Pulang: 1'\n";
    echo "   - 📝 Detail: '" . $yesterday . " - Masuk: 08:00 → Keluar: - [Lupa Absen Pulang]'\n\n";
    echo "4. Coba absen hari ini untuk test fitur absen normal\n";
    echo "5. Buka rekapabsen.php untuk lihat status 'Lupa Absen Pulang'\n\n";
    
    echo "========================================\n";
    echo "TESTING TIPS:\n";
    echo "========================================\n";
    echo "- Untuk test 'Belum Absen Keluar': Absen hari ini tanpa keluar\n";
    echo "- Untuk test 'Lupa Absen Pulang': Data sudah ada (kemarin tanpa keluar)\n";
    echo "- Untuk cleanup: Run DELETE FROM absensi WHERE user_id = " . $admin_user_id . "\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
