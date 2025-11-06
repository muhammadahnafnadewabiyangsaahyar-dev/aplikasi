<?php
/**
 * ================================================================
 * SIMPLIFIED TEST SUITE - Focus on Core Integration
 * ================================================================
 * Testing only the critical integrated features with correct schema
 * ================================================================
 */

session_start();
require_once 'connect.php';

$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Super Admin';

echo "<html><head><style>
body { font-family: Arial; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
h2 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.box { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style></head><body>";

echo "<h1>🧪 Comprehensive Integration Test Suite</h1>";
echo "<p><em>Testing Date: " . date('Y-m-d H:i:s') . "</em></p>";

// Get test user IDs
$test_users_query = $pdo->query("SELECT id, nama_lengkap, email FROM register WHERE nama_lengkap LIKE '%Test%' ORDER BY id DESC LIMIT 4");
$test_users = $test_users_query->fetchAll();

if (count($test_users) < 4) {
    echo "<div class='box error'>⚠ Not enough test users found. Please create 4 test users first.</div>";
    exit;
}

echo "<div class='box'>";
echo "<h3>📋 Test Users:</h3>";
echo "<ul>";
foreach ($test_users as $user) {
    echo "<li><strong>{$user['nama_lengkap']}</strong> (ID: {$user['id']}) - {$user['email']}</li>";
}
echo "</ul>";
echo "</div>";

$user_ids = array_column($test_users, 'id');

// ================================================================
// TEST 1: Generate Shifts for November 2025
// ================================================================
echo "<h2>TEST 1: Generate Shift Schedule</h2>";
echo "<div class='box'>";

try {
    $cabang_result = $pdo->query("SELECT id FROM cabang LIMIT 1");
    $cabang = $cabang_result->fetch();
    $cabang_id = $cabang['id'] ?? 1;
    
    $shift_count = 0;
    $bulan = 11;
    $tahun = 2025;
    
    foreach ($user_ids as $user_id) {
        for ($day = 4; $day <= 8; $day++) {
            $tanggal = sprintf('2025-11-%02d', $day);
            
            // Check existing
            $check = $pdo->prepare("SELECT id FROM shift_assignments WHERE user_id = ? AND tanggal_shift = ?");
            $check->execute([$user_id, $tanggal]);
            
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO shift_assignments 
                    (user_id, cabang_id, tanggal_shift, status_konfirmasi, created_by) 
                    VALUES (?, ?, ?, 'pending', 1)");
                $stmt->execute([$user_id, $cabang_id, $tanggal]);
                $shift_count++;
            }
        }
    }
    
    echo "<p class='success'>✓ Generated/Verified {$shift_count} shift assignments for Nov 4-8, 2025</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ================================================================
// TEST 2: Confirm Shifts
// ================================================================
echo "<h2>TEST 2: Confirm Shifts</h2>";
echo "<div class='box'>";

try {
    $confirmed = 0;
    foreach ($user_ids as $user_id) {
        $stmt = $pdo->prepare("UPDATE shift_assignments 
            SET status_konfirmasi = 'confirmed', waktu_konfirmasi = NOW() 
            WHERE user_id = ? AND tanggal_shift BETWEEN '2025-11-04' AND '2025-11-08' AND status_konfirmasi = 'pending'");
        $stmt->execute([$user_id]);
        $confirmed += $stmt->rowCount();
    }
    
    echo "<p class='success'>✓ Confirmed {$confirmed} shifts</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ================================================================
// TEST 3: Create Attendance with Variations
// ================================================================
echo "<h2>TEST 3: Create Attendance Records</h2>";
echo "<div class='box'>";

try {
    $attendance_scenarios = [
        // User 1: On time
        ['user_id' => $user_ids[0], 'date' => '2025-11-04', 'masuk' => '08:00:00', 'keluar' => '17:00:00', 'late' => 0],
        
        // User 1: Late 25 minutes
        ['user_id' => $user_ids[0], 'date' => '2025-11-05', 'masuk' => '08:25:00', 'keluar' => '17:00:00', 'late' => 25],
        
        // User 2: Overwork
        ['user_id' => $user_ids[1], 'date' => '2025-11-04', 'masuk' => '08:00:00', 'keluar' => '20:00:00', 'late' => 0],
        
        // User 3: Pulang awal
        ['user_id' => $user_ids[2], 'date' => '2025-11-04', 'masuk' => '08:00:00', 'keluar' => '15:00:00', 'late' => 0],
        
        // User 4: Normal
        ['user_id' => $user_ids[3], 'date' => '2025-11-04', 'masuk' => '08:00:00', 'keluar' => '17:00:00', 'late' => 0],
    ];
    
    $created = 0;
    foreach ($attendance_scenarios as $att) {
        $check = $pdo->prepare("SELECT id FROM absensi WHERE user_id = ? AND tanggal_absensi = ?");
        $check->execute([$att['user_id'], $att['date']]);
        
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO absensi 
                (user_id, tanggal_absensi, waktu_masuk, waktu_keluar, status_lokasi, 
                 latitude_absen, longitude_absen, menit_terlambat) 
                VALUES (?, ?, ?, ?, 'Kantor', -6.200000, 106.816666, ?)");
            $stmt->execute([
                $att['user_id'],
                $att['date'],
                $att['date'] . ' ' . $att['masuk'],
                $att['date'] . ' ' . $att['keluar'],
                $att['late']
            ]);
            $created++;
        }
    }
    
    echo "<p class='success'>✓ Created {$created} attendance records</p>";
    
    // Show summary
    echo "<h4>Attendance Summary:</h4>";
    echo "<ul>";
    echo "<li>User 1: On-time + Late (25 min)</li>";
    echo "<li>User 2: Overwork (12 hours work)</li>";
    echo "<li>User 3: Left early</li>";
    echo "<li>User 4: Normal attendance</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ================================================================
// TEST 4: Create Leave Requests
// ================================================================
echo "<h2>TEST 4: Create Leave Requests (Izin & Sakit)</h2>";
echo "<div class='box'>";

try {
    $leaves = [
        ['user_id' => $user_ids[0], 'date' => '2025-11-11', 'perihal' => 'Izin Sakit', 'alasan' => 'Demam tinggi', 'status' => 'Pending'],
        ['user_id' => $user_ids[1], 'date' => '2025-11-12', 'perihal' => 'Izin Keluarga', 'alasan' => 'Acara keluarga', 'status' => 'Diterima'],
        ['user_id' => $user_ids[2], 'date' => '2025-11-13', 'perihal' => 'Izin Sakit', 'alasan' => 'Sakit flu', 'status' => 'Diterima'],
        ['user_id' => $user_ids[3], 'date' => '2025-11-14', 'perihal' => 'Izin Pribadi', 'alasan' => 'Urusan pribadi', 'status' => 'Ditolak'],
    ];
    
    $created_leaves = 0;
    foreach ($leaves as $leave) {
        $stmt = $pdo->prepare("INSERT INTO pengajuan_izin 
            (user_id, perihal, tanggal_mulai, tanggal_selesai, lama_izin, alasan, file_surat, status) 
            VALUES (?, ?, ?, ?, 1, ?, 'test_surat.pdf', ?)");
        $stmt->execute([
            $leave['user_id'],
            $leave['perihal'],
            $leave['date'],
            $leave['date'],
            $leave['alasan'],
            $leave['status']
        ]);
        $created_leaves++;
    }
    
    echo "<p class='success'>✓ Created {$created_leaves} leave requests</p>";
    echo "<ul>";
    echo "<li>1 Pending izin</li>";
    echo "<li>2 Approved izin/sakit</li>";
    echo "<li>1 Rejected izin</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// ================================================================
// TEST 5: Check Integration - Shift + Attendance + Izin
// ================================================================
echo "<h2>TEST 5: Integration Check</h2>";
echo "<div class='box'>";

echo "<h4>A. Confirmed Shift WITHOUT Attendance (Tidak Hadir):</h4>";
$query = $pdo->prepare("SELECT sa.*, r.nama_lengkap 
    FROM shift_assignments sa 
    JOIN register r ON sa.user_id = r.id 
    LEFT JOIN absensi a ON sa.user_id = a.user_id AND sa.tanggal_shift = a.tanggal_absensi 
    WHERE sa.status_konfirmasi = 'confirmed' 
    AND a.id IS NULL 
    AND sa.user_id IN (" . implode(',', $user_ids) . ") 
    LIMIT 3");
$query->execute();
$no_attendance = $query->fetchAll();

if (count($no_attendance) > 0) {
    echo "<p class='warning'>⚠ Found " . count($no_attendance) . " confirmed shifts without attendance (Tidak Hadir)</p>";
    echo "<ul>";
    foreach ($no_attendance as $na) {
        echo "<li>{$na['nama_lengkap']} on {$na['tanggal_shift']} → Should be: <strong>Tidak Hadir + Potongan Rp 50,000</strong></li>";
    }
    echo "</ul>";
} else {
    echo "<p class='success'>✓ All confirmed shifts have attendance</p>";
}

echo "<h4>B. Attendance WITH Lateness:</h4>";
$late_query = $pdo->prepare("SELECT a.*, r.nama_lengkap 
    FROM absensi a 
    JOIN register r ON a.user_id = r.id 
    WHERE a.menit_terlambat > 0 
    AND a.user_id IN (" . implode(',', $user_ids) . ")");
$late_query->execute();
$late_records = $late_query->fetchAll();

if (count($late_records) > 0) {
    echo "<p class='success'>✓ Found " . count($late_records) . " lateness records</p>";
    echo "<ul>";
    foreach ($late_records as $late) {
        $potongan = "no deduction";
        if ($late['menit_terlambat'] >= 40) {
            $potongan = "Potongan Makan + Transport";
        } elseif ($late['menit_terlambat'] >= 20) {
            $potongan = "Potongan Makan";
        }
        echo "<li>{$late['nama_lengkap']}: {$late['menit_terlambat']} minutes late → {$potongan}</li>";
    }
    echo "</ul>";
}

echo "<h4>C. Overwork Detection:</h4>";
$overwork_query = $pdo->prepare("SELECT a.*, r.nama_lengkap,
    TIMESTAMPDIFF(HOUR, a.waktu_masuk, a.waktu_keluar) as hours_worked 
    FROM absensi a 
    JOIN register r ON a.user_id = r.id 
    WHERE TIMESTAMPDIFF(HOUR, a.waktu_masuk, a.waktu_keluar) > 8
    AND a.user_id IN (" . implode(',', $user_ids) . ")");
$overwork_query->execute();
$overwork_records = $overwork_query->fetchAll();

if (count($overwork_records) > 0) {
    echo "<p class='success'>✓ Found " . count($overwork_records) . " overwork records</p>";
    echo "<ul>";
    foreach ($overwork_records as $ow) {
        $overwork_hours = min(8, $ow['hours_worked'] - 8); // Max 8 hours overtime
        $overwork_pay = $overwork_hours * 6250;
        echo "<li>{$ow['nama_lengkap']}: Worked {$ow['hours_worked']} hours → Overwork: {$overwork_hours}h × Rp 6,250 = Rp " . number_format($overwork_pay, 0, ',', '.') . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='warning'>⚠ No overwork records found</p>";
}

echo "</div>";

// ================================================================
// SUMMARY & LINKS
// ================================================================
echo "<hr>";
echo "<h2>📊 Quick Links for Manual Verification</h2>";
echo "<div class='box'>";
echo "<ul>";
echo "<li><a href='mainpageadmin.php' target='_blank'><strong>Main Page Admin</strong> - Check overview statistics</a></li>";
echo "<li><a href='view_absensi.php?bulan=11&tahun=2025' target='_blank'><strong>View Absensi</strong> - Check attendance records for November 2025</a></li>";
echo "<li><a href='approve.php' target='_blank'><strong>Approve Surat Izin</strong> - Approve/reject leave requests</a></li>";
echo "<li><a href='slip_gaji.php' target='_blank'><strong>Slip Gaji</strong> - Generate salary slips</a></li>";
echo "<li><a href='jadwal_shift.php' target='_blank'><strong>Jadwal Shift</strong> - Check shift calendar</a></li>";
echo "</ul>";
echo "</div>";

echo "<div class='box' style='background: #e8f5e9; border-left: 4px solid #4CAF50;'>";
echo "<h3>✅ Test Data Created Successfully!</h3>";
echo "<p><strong>What to test manually:</strong></p>";
echo "<ol>";
echo "<li><strong>Approve pending izin</strong> → Check if shift status changes</li>";
echo "<li><strong>Check mainpage overview</strong> → Verify statistics are correct</li>";
echo "<li><strong>Generate slip gaji</strong> → Verify calculations (overwork, deductions, etc.)</li>";
echo "<li><strong>Check rekap absensi</strong> → Verify 'Tidak Hadir' shows correctly for confirmed shifts without attendance</li>";
echo "<li><strong>Test email sending</strong> → Try sending slip gaji via email to test addresses</li>";
echo "</ol>";
echo "</div>";

echo "<p style='margin-top:30px; color:#666;'><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
echo "</body></html>";
?>
```
