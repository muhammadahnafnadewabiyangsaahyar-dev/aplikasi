<?php
/**
 * TEST SCRIPT: Kategori Keterlambatan
 * 
 * Script ini akan membuat test scenario untuk 3 kategori keterlambatan:
 * 1. TEPAT WAKTU - Masuk sebelum/tepat jam 08:30 (tidak ada potongan)
 * 2. TERLAMBAT < 20 MENIT - Masuk 08:31-08:49 (tidak ada potongan)
 * 3. TERLAMBAT ≥ 20 MENIT - Masuk ≥ 08:50 (ada potongan)
 */

require_once 'connect.php';

echo "========================================\n";
echo "TEST KATEGORI KETERLAMBATAN\n";
echo "========================================\n\n";

try {
    // ========================================
    // 1. GET ADMIN USER
    // ========================================
    echo "1. CHECKING ADMIN USER...\n";
    $stmt = $pdo->query("SELECT id, username, nama_lengkap FROM register WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
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
    // 2. GET JAM MASUK FROM CABANG
    // ========================================
    echo "2. CHECKING JAM MASUK SHIFT...\n";
    $stmt = $pdo->query("SELECT jam_masuk, jam_keluar FROM cabang LIMIT 1");
    $shift = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$shift) {
        echo "   ❌ ERROR: No shift data found!\n";
        exit(1);
    }
    
    echo "   Jam Masuk Shift: " . $shift['jam_masuk'] . "\n";
    echo "   Jam Keluar Shift: " . $shift['jam_keluar'] . "\n\n";
    
    $jam_masuk_shift = $shift['jam_masuk'];
    $jam_keluar_shift = $shift['jam_keluar'];
    
    // ========================================
    // 3. DELETE EXISTING ADMIN ATTENDANCE
    // ========================================
    echo "3. DELETING EXISTING ADMIN ATTENDANCE...\n";
    $stmt = $pdo->prepare("DELETE FROM absensi WHERE user_id = ?");
    $stmt->execute([$admin_user_id]);
    $deleted_count = $stmt->rowCount();
    echo "   ✅ Deleted " . $deleted_count . " attendance records\n\n";
    
    // ========================================
    // 4. CREATE TEST SCENARIOS
    // ========================================
    echo "4. CREATING TEST SCENARIOS...\n\n";
    
    $bulan_ini = date('Y-m');
    $test_scenarios = [];
    
    // ===== SCENARIO 1: TEPAT WAKTU =====
    echo "   📌 SCENARIO 1: TEPAT WAKTU\n";
    $date1 = $bulan_ini . '-01';
    $waktu_masuk1 = $date1 . ' 08:00:00'; // Tepat jam 8 pagi
    $waktu_keluar1 = $date1 . ' 17:00:00'; // Keluar jam 5 sore
    $menit_terlambat1 = 0;
    
    echo "      Tanggal: " . $date1 . "\n";
    echo "      Jam Masuk: 08:00:00 (TEPAT WAKTU)\n";
    echo "      Jam Keluar: 17:00:00\n";
    echo "      Keterlambatan: 0 menit\n";
    echo "      Potongan: Tidak ada\n\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO absensi (
            user_id, tanggal_absensi, waktu_masuk, waktu_keluar,
            status_lokasi, foto_absen, menit_terlambat, 
            status_keterlambatan, potongan_tunjangan, status_lembur
        ) VALUES (?, ?, ?, ?, 'Admin - Office', 'test_tepat_waktu.jpg', ?, 'tepat waktu', 'tidak ada', 'Not Applicable')
    ");
    $stmt->execute([$admin_user_id, $date1, $waktu_masuk1, $waktu_keluar1, $menit_terlambat1]);
    $test_scenarios[] = ['date' => $date1, 'type' => 'Tepat Waktu', 'late' => 0];
    
    // ===== SCENARIO 2: TERLAMBAT < 20 MENIT (10 menit) =====
    echo "   📌 SCENARIO 2: TERLAMBAT < 20 MENIT (10 menit)\n";
    $date2 = $bulan_ini . '-02';
    $waktu_masuk2 = $date2 . ' 08:40:00'; // Terlambat 10 menit dari 08:30
    $waktu_keluar2 = $date2 . ' 17:00:00';
    $menit_terlambat2 = 10;
    
    echo "      Tanggal: " . $date2 . "\n";
    echo "      Jam Masuk: 08:40:00 (Terlambat 10 menit)\n";
    echo "      Jam Keluar: 17:00:00\n";
    echo "      Keterlambatan: 10 menit\n";
    echo "      Potongan: Tidak ada (< 20 menit)\n\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO absensi (
            user_id, tanggal_absensi, waktu_masuk, waktu_keluar,
            status_lokasi, foto_absen, menit_terlambat, 
            status_keterlambatan, potongan_tunjangan, status_lembur
        ) VALUES (?, ?, ?, ?, 'Admin - Office', 'test_terlambat_10.jpg', ?, 'terlambat', 'tidak ada', 'Not Applicable')
    ");
    $stmt->execute([$admin_user_id, $date2, $waktu_masuk2, $waktu_keluar2, $menit_terlambat2]);
    $test_scenarios[] = ['date' => $date2, 'type' => 'Terlambat < 20 menit', 'late' => 10];
    
    // ===== SCENARIO 3: TERLAMBAT < 20 MENIT (15 menit) =====
    echo "   📌 SCENARIO 3: TERLAMBAT < 20 MENIT (15 menit)\n";
    $date3 = $bulan_ini . '-03';
    $waktu_masuk3 = $date3 . ' 08:45:00'; // Terlambat 15 menit
    $waktu_keluar3 = $date3 . ' 17:00:00';
    $menit_terlambat3 = 15;
    
    echo "      Tanggal: " . $date3 . "\n";
    echo "      Jam Masuk: 08:45:00 (Terlambat 15 menit)\n";
    echo "      Jam Keluar: 17:00:00\n";
    echo "      Keterlambatan: 15 menit\n";
    echo "      Potongan: Tidak ada (< 20 menit)\n\n";
    
    $stmt->execute([$admin_user_id, $date3, $waktu_masuk3, $waktu_keluar3, $menit_terlambat3]);
    $test_scenarios[] = ['date' => $date3, 'type' => 'Terlambat < 20 menit', 'late' => 15];
    
    // ===== SCENARIO 4: TERLAMBAT ≥ 20 MENIT (20 menit - batas) =====
    echo "   📌 SCENARIO 4: TERLAMBAT ≥ 20 MENIT (tepat 20 menit)\n";
    $date4 = $bulan_ini . '-04';
    $waktu_masuk4 = $date4 . ' 08:50:00'; // Terlambat tepat 20 menit
    $waktu_keluar4 = $date4 . ' 17:00:00';
    $menit_terlambat4 = 20;
    
    echo "      Tanggal: " . $date4 . "\n";
    echo "      Jam Masuk: 08:50:00 (Terlambat 20 menit)\n";
    echo "      Jam Keluar: 17:00:00\n";
    echo "      Keterlambatan: 20 menit\n";
    echo "      Potongan: ADA POTONGAN (≥ 20 menit)\n\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO absensi (
            user_id, tanggal_absensi, waktu_masuk, waktu_keluar,
            status_lokasi, foto_absen, menit_terlambat, 
            status_keterlambatan, potongan_tunjangan, status_lembur
        ) VALUES (?, ?, ?, ?, 'Admin - Office', 'test_terlambat_20.jpg', ?, 'terlambat', 'ada potongan', 'Not Applicable')
    ");
    $stmt->execute([$admin_user_id, $date4, $waktu_masuk4, $waktu_keluar4, $menit_terlambat4]);
    $test_scenarios[] = ['date' => $date4, 'type' => 'Terlambat ≥ 20 menit', 'late' => 20];
    
    // ===== SCENARIO 5: TERLAMBAT ≥ 20 MENIT (30 menit) =====
    echo "   📌 SCENARIO 5: TERLAMBAT ≥ 20 MENIT (30 menit)\n";
    $date5 = $bulan_ini . '-05';
    $waktu_masuk5 = $date5 . ' 09:00:00'; // Terlambat 30 menit
    $waktu_keluar5 = $date5 . ' 17:00:00';
    $menit_terlambat5 = 30;
    
    echo "      Tanggal: " . $date5 . "\n";
    echo "      Jam Masuk: 09:00:00 (Terlambat 30 menit)\n";
    echo "      Jam Keluar: 17:00:00\n";
    echo "      Keterlambatan: 30 menit\n";
    echo "      Potongan: ADA POTONGAN (≥ 20 menit)\n\n";
    
    $stmt->execute([$admin_user_id, $date5, $waktu_masuk5, $waktu_keluar5, $menit_terlambat5]);
    $test_scenarios[] = ['date' => $date5, 'type' => 'Terlambat ≥ 20 menit', 'late' => 30];
    
    // ===== SCENARIO 6: TERLAMBAT ≥ 20 MENIT (60 menit) =====
    echo "   📌 SCENARIO 6: TERLAMBAT ≥ 20 MENIT (60 menit)\n";
    $date6 = $bulan_ini . '-06';
    $waktu_masuk6 = $date6 . ' 09:30:00'; // Terlambat 1 jam
    $waktu_keluar6 = $date6 . ' 17:00:00';
    $menit_terlambat6 = 60;
    
    echo "      Tanggal: " . $date6 . "\n";
    echo "      Jam Masuk: 09:30:00 (Terlambat 60 menit)\n";
    echo "      Jam Keluar: 17:00:00\n";
    echo "      Keterlambatan: 60 menit\n";
    echo "      Potongan: ADA POTONGAN (≥ 20 menit)\n\n";
    
    $stmt->execute([$admin_user_id, $date6, $waktu_masuk6, $waktu_keluar6, $menit_terlambat6]);
    $test_scenarios[] = ['date' => $date6, 'type' => 'Terlambat ≥ 20 menit', 'late' => 60];
    
    echo "   ✅ Created 6 test scenarios!\n\n";
    
    // ========================================
    // 5. VERIFY TEST SCENARIOS
    // ========================================
    echo "5. VERIFYING TEST SCENARIOS...\n";
    echo "   " . str_repeat("=", 100) . "\n";
    printf("   %-12s | %-10s | %-10s | %-15s | %-20s | %-20s\n", 
           "TANGGAL", "JAM MASUK", "TERLAMBAT", "STATUS", "KATEGORI", "POTONGAN");
    echo "   " . str_repeat("=", 100) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            tanggal_absensi,
            TIME(waktu_masuk) as jam_masuk,
            TIME(waktu_keluar) as jam_keluar,
            menit_terlambat,
            status_keterlambatan,
            potongan_tunjangan
        FROM absensi
        WHERE user_id = ?
        ORDER BY tanggal_absensi ASC
    ");
    $stmt->execute([$admin_user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $icon = $row['status_keterlambatan'] == 'tepat waktu' ? '✅' : '⚠️';
        $kategori = '';
        $potongan_icon = $row['potongan_tunjangan'] == 'tidak ada' ? '✅ Tidak Ada' : '❌ Ada';
        
        if ($row['menit_terlambat'] == 0) {
            $kategori = '✅ Tepat Waktu';
        } elseif ($row['menit_terlambat'] < 20) {
            $kategori = '⚠️ < 20 menit';
        } else {
            $kategori = '❌ ≥ 20 menit';
        }
        
        printf("   %-12s | %-10s | %-10s | %-15s | %-20s | %-20s\n",
               $row['tanggal_absensi'],
               $row['jam_masuk'],
               $row['menit_terlambat'] . ' menit',
               $icon . ' ' . $row['status_keterlambatan'],
               $kategori,
               $potongan_icon
        );
    }
    echo "   " . str_repeat("=", 100) . "\n\n";
    
    // ========================================
    // 6. STATISTICS SUMMARY
    // ========================================
    echo "6. STATISTICS SUMMARY...\n";
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status_keterlambatan = 'tepat waktu' THEN 1 ELSE 0 END) as tepat_waktu,
            SUM(CASE WHEN menit_terlambat > 0 AND menit_terlambat < 20 THEN 1 ELSE 0 END) as terlambat_ringan,
            SUM(CASE WHEN menit_terlambat >= 20 THEN 1 ELSE 0 END) as terlambat_berat,
            SUM(CASE WHEN potongan_tunjangan = 'ada potongan' THEN 1 ELSE 0 END) as total_potongan,
            AVG(menit_terlambat) as rata_terlambat
        FROM absensi
        WHERE user_id = ?
    ");
    $stmt->execute([$admin_user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   Total Absensi: " . $stats['total'] . " hari\n";
    echo "   ✅ Tepat Waktu: " . $stats['tepat_waktu'] . " hari (tidak ada potongan)\n";
    echo "   ⚠️ Terlambat < 20 menit: " . $stats['terlambat_ringan'] . " hari (tidak ada potongan)\n";
    echo "   ❌ Terlambat ≥ 20 menit: " . $stats['terlambat_berat'] . " hari (ADA POTONGAN)\n";
    echo "   📊 Total Hari dengan Potongan: " . $stats['total_potongan'] . " hari\n";
    echo "   📈 Rata-rata Keterlambatan: " . round($stats['rata_terlambat'], 1) . " menit\n\n";
    
    // ========================================
    // 7. EXPECTED RESULTS
    // ========================================
    echo "========================================\n";
    echo "✅ SETUP COMPLETE!\n";
    echo "========================================\n\n";
    echo "NEXT STEPS:\n";
    echo "1. Login sebagai admin di browser\n";
    echo "2. Buka mainpage.php - Lihat statistik:\n";
    echo "   - Total Kehadiran: 6 hari\n";
    echo "   - Tepat Waktu: 1 hari\n";
    echo "   - Terlambat: 5 hari\n";
    echo "   - Rata-rata Keterlambatan: ~27 menit\n\n";
    echo "3. Buka rekapabsen.php - Lihat detail per hari:\n";
    echo "   - Status keterlambatan untuk setiap hari\n";
    echo "   - Potongan tunjangan (ada/tidak ada)\n\n";
    echo "4. Buka view_absensi.php (admin) - Lihat rekap:\n";
    echo "   - Filter by bulan/tahun\n";
    echo "   - Export ke CSV\n\n";
    
    echo "========================================\n";
    echo "KATEGORI KETERLAMBATAN:\n";
    echo "========================================\n";
    echo "✅ TEPAT WAKTU (0 menit)\n";
    echo "   - Tidak ada potongan\n";
    echo "   - Status: 'tepat waktu'\n\n";
    echo "⚠️ TERLAMBAT < 20 MENIT (1-19 menit)\n";
    echo "   - TIDAK ADA POTONGAN\n";
    echo "   - Status: 'terlambat'\n";
    echo "   - Potongan: 'tidak ada'\n\n";
    echo "❌ TERLAMBAT ≥ 20 MENIT (20+ menit)\n";
    echo "   - ADA POTONGAN\n";
    echo "   - Status: 'terlambat'\n";
    echo "   - Potongan: 'ada potongan'\n\n";
    
    echo "========================================\n";
    echo "CLEANUP:\n";
    echo "========================================\n";
    echo "php -r \"require 'connect.php'; \$pdo->exec('DELETE FROM absensi WHERE user_id = " . $admin_user_id . "'); echo 'Cleaned!';\" \n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
