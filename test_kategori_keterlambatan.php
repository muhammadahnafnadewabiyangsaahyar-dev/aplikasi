<?php
/**
 * TEST SCRIPT: Kategori Keterlambatan
 * 
 * Script ini akan test 3 kategori keterlambatan:
 * 1. < 20 menit     → Tidak ada potongan
 * 2. 20-40 menit    → Potong tunjangan makan
 * 3. > 40 menit     → Potong tunjangan makan + transport
 * 
 * Plus test:
 * 4. Tepat waktu    → Tidak ada potongan
 * 5. Lupa absen pulang → Test kombinasi dengan keterlambatan
 */

require_once 'connect.php';

echo "========================================\n";
echo "TEST KATEGORI KETERLAMBATAN\n";
echo "========================================\n\n";

try {
    // ========================================
    // 1. CHECK DATABASE TIME & JAM MASUK SHIFT
    // ========================================
    echo "1. CHECKING DATABASE TIME & SHIFT...\n";
    $stmt = $pdo->query("SELECT NOW() as db_time, CURDATE() as db_date");
    $time = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Current Database Time: " . $time['db_time'] . "\n";
    echo "   Current Date: " . $time['db_date'] . "\n";
    
    // Get jam masuk shift dari cabang
    $stmt = $pdo->query("SELECT jam_masuk, jam_keluar FROM cabang LIMIT 1");
    $shift = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Jam Masuk Shift: " . $shift['jam_masuk'] . "\n";
    echo "   Jam Keluar Shift: " . $shift['jam_keluar'] . "\n\n";
    
    $jam_masuk_shift = $shift['jam_masuk']; // Misal: 08:00:00
    
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
    // 3. DELETE OLD TEST DATA
    // ========================================
    echo "3. CLEANING OLD TEST DATA...\n";
    $stmt = $pdo->prepare("DELETE FROM absensi WHERE user_id = ?");
    $stmt->execute([$admin_user_id]);
    $deleted_count = $stmt->rowCount();
    echo "   ✅ Deleted " . $deleted_count . " old records\n\n";
    
    // ========================================
    // 4. CREATE TEST SCENARIOS
    // ========================================
    echo "4. CREATING TEST SCENARIOS...\n";
    echo "   (Creating 8 test cases for different tardiness levels)\n\n";
    
    // Base date: 7 hari ke belakang sampai kemarin
    $test_cases = [];
    
    // Hitung jam masuk shift dalam menit dari midnight
    $shift_time = strtotime("1970-01-01 " . $jam_masuk_shift);
    $shift_minutes = (int)date('H', $shift_time) * 60 + (int)date('i', $shift_time);
    
    // TEST CASE 1: Tepat waktu
    $test_cases[] = [
        'date' => date('Y-m-d', strtotime('-8 days')),
        'delay_minutes' => 0,
        'description' => 'Tepat Waktu',
        'expected_status' => 'tepat waktu',
        'expected_potongan' => 'tidak ada'
    ];
    
    // TEST CASE 2: Terlambat 5 menit (< 20)
    $test_cases[] = [
        'date' => date('Y-m-d', strtotime('-7 days')),
        'delay_minutes' => 5,
        'description' => 'Terlambat 5 menit',
        'expected_status' => 'terlambat',
        'expected_potongan' => 'tidak ada'
    ];
    
    // TEST CASE 3: Terlambat 15 menit (< 20)
    $test_cases[] = [
        'date' => date('Y-m-d', strtotime('-6 days')),
        'delay_minutes' => 15,
        'description' => 'Terlambat 15 menit',
        'expected_status' => 'terlambat',
        'expected_potongan' => 'tidak ada'
    ];
    
    // TEST CASE 4: Terlambat 20 menit (batas bawah kategori 2)
    $test_cases[] = [
        'date' => date('Y-m-d', strtotime('-5 days')),
        'delay_minutes' => 20,
        'description' => 'Terlambat 20 menit',
        'expected_status' => 'terlambat',
        'expected_potongan' => 'tunjangan makan'
    ];
    
    // TEST CASE 5: Terlambat 30 menit (20-39)
    $test_cases[] = [
        'date' => date('Y-m-d', strtotime('-4 days')),
        'delay_minutes' => 30,
        'description' => 'Terlambat 30 menit',
        'expected_status' => 'terlambat',
        'expected_potongan' => 'tunjangan makan'
    ];
    
    // TEST CASE 6: Terlambat 39 menit (batas atas kategori 2)
    $test_cases[] = [
        'date' => date('Y-m-d', strtotime('-3 days')),
        'delay_minutes' => 39,
        'description' => 'Terlambat 39 menit (max kategori makan saja)',
        'expected_status' => 'terlambat',
        'expected_potongan' => 'tunjangan makan'
    ];
    
    // TEST CASE 7: Terlambat 40 menit (>= 40, batas bawah kategori 3)
    $test_cases[] = [
        'date' => date('Y-m-d', strtotime('-2 days')),
        'delay_minutes' => 40,
        'description' => 'Terlambat 40 menit (min kategori makan+transport)',
        'expected_status' => 'terlambat',
        'expected_potongan' => 'tunjangan makan dan transport'
    ];
    
    // TEST CASE 8: Terlambat 60 menit (> 40) - kemarin, lupa absen pulang
    $test_cases[] = [
        'date' => date('Y-m-d', strtotime('-1 days')),
        'delay_minutes' => 60,
        'description' => 'Terlambat 60 menit (1 jam) + Lupa Absen Pulang',
        'expected_status' => 'terlambat',
        'expected_potongan' => 'tunjangan makan dan transport',
        'no_clock_out' => true  // Test kombinasi: terlambat + lupa absen pulang
    ];
    
    // Insert test data
    foreach ($test_cases as $index => $test) {
        $case_num = $index + 1;
        echo "   Case $case_num: {$test['description']}\n";
        
        // Hitung waktu masuk
        $clock_in_minutes = $shift_minutes + $test['delay_minutes'];
        $clock_in_hour = floor($clock_in_minutes / 60);
        $clock_in_min = $clock_in_minutes % 60;
        $waktu_masuk = $test['date'] . ' ' . sprintf('%02d:%02d:00', $clock_in_hour, $clock_in_min);
        
        // Waktu keluar (8 jam kemudian, atau NULL jika test lupa absen)
        $waktu_keluar = null;
        if (!isset($test['no_clock_out'])) {
            $clock_out_time = strtotime($waktu_masuk) + (8 * 3600); // +8 jam
            $waktu_keluar = date('Y-m-d H:i:s', $clock_out_time);
        }
        
        echo "      - Tanggal: {$test['date']}\n";
        echo "      - Masuk: " . date('H:i:s', strtotime($waktu_masuk)) . " (Terlambat: {$test['delay_minutes']} menit)\n";
        echo "      - Keluar: " . ($waktu_keluar ? date('H:i:s', strtotime($waktu_keluar)) : 'NULL (Lupa absen pulang)') . "\n";
        echo "      - Expected Status: {$test['expected_status']}\n";
        echo "      - Expected Potongan: {$test['expected_potongan']}\n";
        
        // Insert ke database
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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $admin_user_id,
            $test['date'],
            $waktu_masuk,
            $waktu_keluar,
            'Admin - Office',
            'test_absen_' . $case_num . '.jpg',
            $test['delay_minutes'],
            $test['expected_status'],
            $test['expected_potongan'],
            'Not Applicable'
        ]);
        
        echo "      ✅ Created\n\n";
    }
    
    // ========================================
    // 5. VERIFY ALL TEST CASES
    // ========================================
    echo "========================================\n";
    echo "5. VERIFYING TEST RESULTS...\n";
    echo "========================================\n\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.tanggal_absensi,
            TIME(a.waktu_masuk) as jam_masuk,
            TIME(a.waktu_keluar) as jam_keluar,
            a.menit_terlambat,
            a.status_keterlambatan,
            a.potongan_tunjangan,
            CASE 
                WHEN a.waktu_keluar IS NULL AND a.tanggal_absensi < CURDATE() THEN 'Lupa Absen Pulang'
                WHEN a.waktu_keluar IS NULL THEN 'Belum Absen Keluar'
                ELSE 'Complete'
            END as status_kehadiran
        FROM absensi a
        WHERE a.user_id = ?
        ORDER BY a.tanggal_absensi ASC, a.waktu_masuk ASC
    ");
    $stmt->execute([$admin_user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "┌─────────────┬──────────┬──────────┬──────────┬─────────────────┬──────────────────────────────────┬─────────────────────┐\n";
    echo "│ Tanggal     │ Masuk    │ Keluar   │ Terlambat│ Status Terlambat│ Potongan Tunjangan               │ Status Kehadiran    │\n";
    echo "├─────────────┼──────────┼──────────┼──────────┼─────────────────┼──────────────────────────────────┼─────────────────────┤\n";
    
    foreach ($results as $row) {
        $icon_status = $row['status_keterlambatan'] == 'tepat waktu' ? '✅' : '⚠️';
        $icon_kehadiran = $row['status_kehadiran'] == 'Complete' ? '✅' : '🔴';
        
        printf(
            "│ %s │ %s │ %s │ %3d mnt  │ %s %-13s │ %-32s │ %s %-17s │\n",
            $row['tanggal_absensi'],
            $row['jam_masuk'] ?? '-',
            $row['jam_keluar'] ?? 'NULL',
            $row['menit_terlambat'],
            $icon_status,
            $row['status_keterlambatan'],
            $row['potongan_tunjangan'],
            $icon_kehadiran,
            $row['status_kehadiran']
        );
    }
    
    echo "└─────────────┴──────────┴──────────┴──────────┴─────────────────┴──────────────────────────────────┴─────────────────────┘\n\n";
    
    // ========================================
    // 6. SUMMARY
    // ========================================
    echo "========================================\n";
    echo "📊 SUMMARY\n";
    echo "========================================\n\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status_keterlambatan = 'tepat waktu' THEN 1 ELSE 0 END) as tepat_waktu,
            SUM(CASE WHEN menit_terlambat > 0 AND menit_terlambat < 20 THEN 1 ELSE 0 END) as telat_ringan,
            SUM(CASE WHEN menit_terlambat >= 20 AND menit_terlambat < 40 THEN 1 ELSE 0 END) as telat_sedang,
            SUM(CASE WHEN menit_terlambat >= 40 THEN 1 ELSE 0 END) as telat_berat,
            SUM(CASE WHEN waktu_keluar IS NULL AND tanggal_absensi < CURDATE() THEN 1 ELSE 0 END) as lupa_absen_pulang,
            AVG(menit_terlambat) as rata_telat
        FROM absensi
        WHERE user_id = ?
    ");
    $stmt->execute([$admin_user_id]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total Absensi: " . $summary['total'] . "\n";
    echo "├─ ✅ Tepat Waktu: " . $summary['tepat_waktu'] . " (Tidak ada potongan)\n";
    echo "├─ ⚠️ Terlambat < 20 menit: " . $summary['telat_ringan'] . " (Tidak ada potongan)\n";
    echo "├─ ⚠️ Terlambat 20-39 menit: " . $summary['telat_sedang'] . " (Potong tunjangan makan)\n";
    echo "├─ 🔴 Terlambat >= 40 menit: " . $summary['telat_berat'] . " (Potong tunjangan makan + transport)\n";
    echo "└─ 🔴 Lupa Absen Pulang: " . $summary['lupa_absen_pulang'] . " (Dihitung hadir dengan catatan)\n\n";
    echo "Rata-rata Keterlambatan: " . round($summary['rata_telat'], 1) . " menit\n\n";
    
    // ========================================
    // 7. TESTING INSTRUCTIONS
    // ========================================
    echo "========================================\n";
    echo "✅ SETUP COMPLETE!\n";
    echo "========================================\n\n";
    echo "NEXT STEPS:\n";
    echo "1. Login sebagai admin (superadmin) di browser\n";
    echo "2. Buka mainpage.php untuk lihat overview statistik\n";
    echo "3. Buka rekapabsen.php untuk lihat detail per tanggal\n";
    echo "4. Buka view_absensi.php (jika admin) untuk lihat data semua user\n\n";
    
    echo "EXPECTED DASHBOARD STATS:\n";
    echo "├─ Total Kehadiran: " . ($summary['total'] - $summary['lupa_absen_pulang']) . " (yang complete)\n";
    echo "├─ Tepat Waktu: " . $summary['tepat_waktu'] . "\n";
    echo "├─ Terlambat: " . ($summary['telat_ringan'] + $summary['telat_sedang'] + $summary['telat_berat']) . "\n";
    echo "└─ Lupa Absen Pulang: " . $summary['lupa_absen_pulang'] . "\n\n";
    
    echo "========================================\n";
    echo "KATEGORI KETERLAMBATAN:\n";
    echo "========================================\n";
    echo "1. Tepat Waktu         → Tidak ada potongan\n";
    echo "2. < 20 menit          → Tidak ada potongan\n";
    echo "3. 20-39 menit         → Potong tunjangan makan\n";
    echo "4. >= 40 menit         → Potong tunjangan makan + transport\n\n";
    
    echo "========================================\n";
    echo "CLEANUP (Optional):\n";
    echo "========================================\n";
    echo "To remove all test data:\n";
    echo "php -r \"require 'connect.php'; \\$pdo->exec('DELETE FROM absensi WHERE user_id = $admin_user_id'); echo 'Cleaned!';\";\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
