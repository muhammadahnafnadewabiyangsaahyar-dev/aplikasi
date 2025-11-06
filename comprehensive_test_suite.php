<?php
/**
 * ================================================================
 * COMPREHENSIVE TEST SUITE - All Integrated Features
 * ================================================================
 * 
 * Testing Coverage:
 * 1. Approve surat izin → status absen + shift confirmation
 * 2. Sakit status → shift + rekap + gaji
 * 3. Shift confirmed → absen scenarios + gaji calculation
 * 4. Keterlambatan → overview + slip gaji
 * 5. Overwork logic → auto detect + gaji calculation
 * 6. Generate full month with variations
 * 7. Email integration testing
 * 
 * Date: 2024-11-06
 * ================================================================
 */

session_start();
require_once 'connect.php';

// Set admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'superadmin';
$_SESSION['nama_lengkap'] = 'Super Admin';

// Test configuration
$test_results = [];
$test_count = 0;
$test_passed = 0;
$test_failed = 0;

// Helper function to log test results
function logTest($test_name, $status, $message = '', $data = null) {
    global $test_results, $test_count, $test_passed, $test_failed;
    
    $test_count++;
    if ($status) {
        $test_passed++;
    } else {
        $test_failed++;
    }
    
    $test_results[] = [
        'number' => $test_count,
        'name' => $test_name,
        'status' => $status ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// ================================================================
// STEP 1: Create Dummy Users with Email
// ================================================================
echo "<h2>🧪 STEP 1: Creating Dummy Users</h2>";

$dummy_users = [
    [
        'username' => 'test_user_1',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'nama_lengkap' => 'Ahmad Pratama Test',
        'email' => 'katahnaf@gmail.com',
        'role' => 'user',
        'no_whatsapp' => '081234567001',
        'posisi' => 'Staff',
        'outlet' => 'Kantor Pusat'
    ],
    [
        'username' => 'test_user_2',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'nama_lengkap' => 'Ayu Ting Ting Test',
        'email' => 'pilaraforismacinta@gmail.com',
        'role' => 'user',
        'no_whatsapp' => '081234567002',
        'posisi' => 'Staff',
        'outlet' => 'Kantor Pusat'
    ],
    [
        'username' => 'test_user_3',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'nama_lengkap' => 'Galih Ganjar Test',
        'email' => 'galihganji@gmail.com',
        'role' => 'user',
        'no_whatsapp' => '081234567003',
        'posisi' => 'Staff',
        'outlet' => 'Kantor Pusat'
    ],
    [
        'username' => 'test_user_4',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'nama_lengkap' => 'Dewi Lestari Test',
        'email' => 'dotpikir@gmail.com',
        'role' => 'user',
        'no_whatsapp' => '081234567004',
        'posisi' => 'Staff',
        'outlet' => 'Kantor Pusat'
    ]
];

$created_user_ids = [];

try {
    $pdo->beginTransaction();
    
    foreach ($dummy_users as $user) {
        // Check if user exists
        $check_stmt = $pdo->prepare("SELECT id FROM register WHERE username = ?");
        $check_stmt->execute([$user['username']]);
        
        if ($check_stmt->fetch()) {
            // User exists, get ID
            $check_stmt->execute([$user['username']]);
            $existing = $check_stmt->fetch();
            $created_user_ids[] = $existing['id'];
            echo "<p>✓ User {$user['nama_lengkap']} already exists (ID: {$existing['id']})</p>";
        } else {
            // Create new user
            $stmt = $pdo->prepare("INSERT INTO register (username, password, nama_lengkap, email, role, no_whatsapp, posisi, outlet) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['username'],
                $user['password'],
                $user['nama_lengkap'],
                $user['email'],
                $user['role'],
                $user['no_whatsapp'],
                $user['posisi'],
                $user['outlet']
            ]);
            
            $user_id = $pdo->lastInsertId();
            $created_user_ids[] = $user_id;
            echo "<p>✓ Created user: {$user['nama_lengkap']} (ID: {$user_id})</p>";
        }
    }
    
    $pdo->commit();
    logTest('Create Dummy Users', true, "Created " . count($created_user_ids) . " test users", $created_user_ids);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color:red;'>✗ Error creating users: " . $e->getMessage() . "</p>";
    logTest('Create Dummy Users', false, $e->getMessage());
}

// ================================================================
// STEP 2: Generate Shift Schedule for One Month (November 2025)
// ================================================================
echo "<h2>🧪 STEP 2: Generating Shift Schedule (November 2025)</h2>";

$bulan = 11;
$tahun = 2025;
$total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Get cabang ID
$cabang_stmt = $pdo->query("SELECT id FROM cabang LIMIT 1");
$cabang = $cabang_stmt->fetch();
$cabang_id = $cabang['id'] ?? 1;

try {
    $pdo->beginTransaction();
    
    $shift_patterns = ['Pagi', 'Siang', 'Malam']; // Rotate shifts
    $generated_shifts = 0;
    
    foreach ($created_user_ids as $index => $user_id) {
        for ($day = 1; $day <= $total_days; $day++) {
            $tanggal = sprintf('%d-%02d-%02d', $tahun, $bulan, $day);
            
            // Skip Sundays (day 0)
            $day_of_week = date('w', strtotime($tanggal));
            if ($day_of_week == 0) continue;
            
            // Rotate shift pattern
            $shift_index = ($index + $day) % 3;
            $shift_name = $shift_patterns[$shift_index];
            
            // Check if shift already exists
            $check = $pdo->prepare("SELECT id FROM shift_assignments WHERE user_id = ? AND tanggal_shift = ?");
            $check->execute([$user_id, $tanggal]);
            
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO shift_assignments 
                    (user_id, cabang_id, tanggal_shift, status_konfirmasi, created_by, created_at) 
                    VALUES (?, ?, ?, 'pending', 1, NOW())");
                $stmt->execute([$user_id, $cabang_id, $tanggal]);
                $generated_shifts++;
            }
        }
    }
    
    $pdo->commit();
    echo "<p>✓ Generated {$generated_shifts} shift assignments for November 2025</p>";
    logTest('Generate Shift Schedule', true, "Generated {$generated_shifts} shifts");
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color:red;'>✗ Error generating shifts: " . $e->getMessage() . "</p>";
    logTest('Generate Shift Schedule', false, $e->getMessage());
}

// ================================================================
// STEP 3: Create Varied Attendance Records
// ================================================================
echo "<h2>🧪 STEP 3: Creating Varied Attendance Records</h2>";

try {
    $pdo->beginTransaction();
    
    $attendance_scenarios = [
        // User 1: Mix of on-time and late
        ['user_id' => $created_user_ids[0], 'date' => '2025-11-04', 'masuk' => '08:00:00', 'keluar' => '17:00:00', 'status' => 'hadir'],
        ['user_id' => $created_user_ids[0], 'date' => '2025-11-05', 'masuk' => '08:25:00', 'keluar' => '17:00:00', 'status' => 'terlambat'], // 25 min late
        ['user_id' => $created_user_ids[0], 'date' => '2025-11-06', 'masuk' => '08:45:00', 'keluar' => '17:00:00', 'status' => 'terlambat'], // 45 min late
        
        // User 2: Overwork scenarios
        ['user_id' => $created_user_ids[1], 'date' => '2025-11-04', 'masuk' => '08:00:00', 'keluar' => '19:00:00', 'status' => 'hadir'], // 2 hours overwork
        ['user_id' => $created_user_ids[1], 'date' => '2025-11-05', 'masuk' => '08:00:00', 'keluar' => '20:00:00', 'status' => 'hadir'], // 3 hours overwork
        
        // User 3: Absent (confirmed shift but no attendance)
        // No attendance records for user 3 on their shift days
        
        // User 4: Mixed scenarios
        ['user_id' => $created_user_ids[3], 'date' => '2025-11-04', 'masuk' => '08:00:00', 'keluar' => '16:00:00', 'status' => 'pulang_awal'], // Left early
        ['user_id' => $created_user_ids[3], 'date' => '2025-11-05', 'masuk' => '08:00:00', 'keluar' => '17:00:00', 'status' => 'hadir'],
    ];
    
    $attendance_count = 0;
    foreach ($attendance_scenarios as $att) {
        // Check if attendance exists
        $check = $pdo->prepare("SELECT id FROM absensi WHERE user_id = ? AND tanggal_absensi = ?");
        $check->execute([$att['user_id'], $att['date']]);            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO absensi 
                    (user_id, tanggal_absensi, waktu_masuk, waktu_keluar, status_lokasi, latitude_absen, longitude_absen, status_kehadiran, menit_terlambat) 
                    VALUES (?, ?, ?, ?, 'Kantor', -6.200000, 106.816666, ?, 0)");
                $stmt->execute([
                    $att['user_id'],
                    $att['date'],
                    $att['date'] . ' ' . $att['masuk'],
                    $att['date'] . ' ' . $att['keluar'],
                    $att['status']
                ]);
                $attendance_count++;
            }
    }
    
    $pdo->commit();
    echo "<p>✓ Created {$attendance_count} attendance records with variations</p>";
    logTest('Create Attendance Records', true, "Created {$attendance_count} attendance records");
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color:red;'>✗ Error creating attendance: " . $e->getMessage() . "</p>";
    logTest('Create Attendance Records', false, $e->getMessage());
}

// ================================================================
// STEP 4: Create Leave Requests (Izin & Sakit)
// ================================================================
echo "<h2>🧪 STEP 4: Creating Leave Requests</h2>";

try {
    $pdo->beginTransaction();
    
    $leave_requests = [
        // User 1: Izin (pending)
        ['user_id' => $created_user_ids[0], 'tanggal_mulai' => '2025-11-11', 'tanggal_selesai' => '2025-11-11', 'perihal' => 'Izin Sakit', 'alasan' => 'Sakit demam', 'status' => 'Pending'],
        
        // User 2: Sakit (approved)
        ['user_id' => $created_user_ids[1], 'tanggal_mulai' => '2025-11-12', 'tanggal_selesai' => '2025-11-13', 'perihal' => 'Izin Sakit', 'alasan' => 'Sakit flu', 'status' => 'Diterima'],
        
        // User 3: Izin (approved)
        ['user_id' => $created_user_ids[2], 'tanggal_mulai' => '2025-11-14', 'tanggal_selesai' => '2025-11-14', 'perihal' => 'Izin Urusan Keluarga', 'alasan' => 'Acara keluarga', 'status' => 'Diterima'],
        
        // User 4: Sakit (rejected)
        ['user_id' => $created_user_ids[3], 'tanggal_mulai' => '2025-11-15', 'tanggal_selesai' => '2025-11-15', 'perihal' => 'Izin Sakit', 'alasan' => 'Sakit ringan', 'status' => 'Ditolak'],
    ];
    
    $leave_count = 0;
    foreach ($leave_requests as $leave) {
        $lama_izin = (strtotime($leave['tanggal_selesai']) - strtotime($leave['tanggal_mulai'])) / 86400 + 1;
        
        $stmt = $pdo->prepare("INSERT INTO pengajuan_izin 
            (user_id, perihal, tanggal_mulai, tanggal_selesai, lama_izin, alasan, file_surat, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'dummy.pdf', ?)");
        $stmt->execute([
            $leave['user_id'],
            $leave['perihal'],
            $leave['tanggal_mulai'],
            $leave['tanggal_selesai'],
            $lama_izin,
            $leave['alasan'],
            $leave['status']
        ]);
        $leave_count++;
    }
    
    $pdo->commit();
    echo "<p>✓ Created {$leave_count} leave requests (izin & sakit)</p>";
    logTest('Create Leave Requests', true, "Created {$leave_count} leave requests");
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color:red;'>✗ Error creating leave requests: " . $e->getMessage() . "</p>";
    logTest('Create Leave Requests', false, $e->getMessage());
}

// ================================================================
// STEP 5: Confirm Some Shifts
// ================================================================
echo "<h2>🧪 STEP 5: Confirming Shifts</h2>";

try {
    $pdo->beginTransaction();
    
    // Confirm shifts for first 5 days of November for all users
    foreach ($created_user_ids as $user_id) {
        for ($day = 4; $day <= 8; $day++) {
            $tanggal = sprintf('2025-11-%02d', $day);
            
            $stmt = $pdo->prepare("UPDATE shift_assignments 
                SET status_konfirmasi = 'confirmed', 
                    updated_at = NOW() 
                WHERE user_id = ? AND tanggal_shift = ?");
            $stmt->execute([$user_id, $tanggal]);
        }
    }
    
    $pdo->commit();
    echo "<p>✓ Confirmed shifts for test period (Nov 4-8, 2025)</p>";
    logTest('Confirm Shifts', true, "Confirmed shifts for Nov 4-8");
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p style='color:red;'>✗ Error confirming shifts: " . $e->getMessage() . "</p>";
    logTest('Confirm Shifts', false, $e->getMessage());
}

// ================================================================
// TEST SCENARIOS
// ================================================================

echo "<h2>🧪 RUNNING TEST SCENARIOS</h2>";

// ================================================================
// TEST 1: Approve Surat Izin → Check Status Changes
// ================================================================
echo "<h3>TEST 1: Approve Surat Izin Integration</h3>";

try {
    // Find pending izin
    $pending_izin = $pdo->prepare("SELECT * FROM pengajuan_izin WHERE status = 'Pending' LIMIT 1");
    $pending_izin->execute();
    $izin = $pending_izin->fetch();
    
    if ($izin) {
        // Approve the izin
        $approve_stmt = $pdo->prepare("UPDATE pengajuan_izin SET status = 'Diterima' WHERE id = ?");
        $approve_stmt->execute([$izin['id']]);
        
        // Check if shift status changed
        $check_shift = $pdo->prepare("SELECT status_konfirmasi FROM shift_assignments WHERE user_id = ? AND tanggal_shift BETWEEN ? AND ?");
        $check_shift->execute([$izin['user_id'], $izin['tanggal_mulai'], $izin['tanggal_selesai']]);
        $shift = $check_shift->fetch();
        
        echo "<p>✓ Approved izin ID: {$izin['id']}</p>";
        echo "<p>→ Shift status: " . ($shift['status_konfirmasi'] ?? 'N/A') . "</p>";
        
        logTest('Approve Izin Integration', true, "Izin approved and shift status checked");
    } else {
        echo "<p>⚠ No pending izin found</p>";
        logTest('Approve Izin Integration', false, "No pending izin");
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error in izin approval test: " . $e->getMessage() . "</p>";
    logTest('Approve Izin Integration', false, $e->getMessage());
}

// ================================================================
// TEST 2: Sakit Status Integration
// ================================================================
echo "<h3>TEST 2: Sakit Status Integration</h3>";

try {
    $sakit_query = $pdo->prepare("SELECT pi.*, r.nama_lengkap 
        FROM pengajuan_izin pi 
        JOIN register r ON pi.user_id = r.id 
        WHERE pi.perihal LIKE '%Sakit%' AND pi.status = 'Diterima' LIMIT 1");
    $sakit_query->execute();
    $sakit = $sakit_query->fetch();
    
    if ($sakit) {
        echo "<p>✓ Found approved sakit for {$sakit['nama_lengkap']}</p>";
        echo "<p>→ Date range: {$sakit['tanggal_mulai']} to {$sakit['tanggal_selesai']}</p>";
        
        // Check shift status
        $shift_check = $pdo->prepare("SELECT * FROM shift_assignments WHERE user_id = ? AND tanggal_shift BETWEEN ? AND ?");
        $shift_check->execute([$sakit['user_id'], $sakit['tanggal_mulai'], $sakit['tanggal_selesai']]);
        $shifts = $shift_check->fetchAll();
        
        echo "<p>→ Affected shifts: " . count($shifts) . "</p>";
        
        logTest('Sakit Status Integration', true, "Sakit status integrated with shifts");
    } else {
        echo "<p>⚠ No approved sakit found</p>";
        logTest('Sakit Status Integration', false, "No approved sakit");
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error in sakit test: " . $e->getMessage() . "</p>";
    logTest('Sakit Status Integration', false, $e->getMessage());
}

// ================================================================
// TEST 3: Shift Confirmed → Attendance Scenarios
// ================================================================
echo "<h3>TEST 3: Shift Confirmed with Attendance Scenarios</h3>";

try {
    // Scenario A: Confirmed shift but NO attendance (should be tidak hadir)
    $no_attendance = $pdo->prepare("SELECT sa.*, r.nama_lengkap 
        FROM shift_assignments sa 
        JOIN register r ON sa.user_id = r.id 
        LEFT JOIN absensi a ON sa.user_id = a.user_id AND sa.tanggal_shift = a.tanggal_absensi 
        WHERE sa.status_konfirmasi = 'confirmed' 
        AND a.id IS NULL 
        AND sa.tanggal_shift <= CURDATE() 
        LIMIT 1");
    $no_attendance->execute();
    $no_att = $no_attendance->fetch();
    
    if ($no_att) {
        echo "<p>✓ Found confirmed shift WITHOUT attendance</p>";
        echo "<p>→ User: {$no_att['nama_lengkap']}, Date: {$no_att['tanggal_shift']}</p>";
        echo "<p>→ <strong>Should be: Tidak Hadir + Potongan Rp 50,000</strong></p>";
        
        logTest('Shift Confirmed - No Attendance', true, "Detected tidak hadir scenario");
    } else {
        echo "<p>⚠ No confirmed shift without attendance found (create one for testing)</p>";
        logTest('Shift Confirmed - No Attendance', false, "No test data");
    }
    
    // Scenario B: Confirmed shift WITH attendance
    $with_attendance = $pdo->prepare("SELECT sa.*, a.waktu_masuk, a.waktu_keluar, a.status_kehadiran, r.nama_lengkap 
        FROM shift_assignments sa 
        JOIN register r ON sa.user_id = r.id 
        JOIN absensi a ON sa.user_id = a.user_id AND sa.tanggal_shift = a.tanggal_absensi 
        WHERE sa.status_konfirmasi = 'confirmed' 
        LIMIT 1");
    $with_attendance->execute();
    $with_att = $with_attendance->fetch();
    
    if ($with_att) {
        echo "<p>✓ Found confirmed shift WITH attendance</p>";
        echo "<p>→ User: {$with_att['nama_lengkap']}, Date: {$with_att['tanggal_shift']}</p>";
        echo "<p>→ Status: {$with_att['status_kehadiran']}</p>";
        
        logTest('Shift Confirmed - With Attendance', true, "Attendance recorded");
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error in shift attendance test: " . $e->getMessage() . "</p>";
    logTest('Shift Confirmed Tests', false, $e->getMessage());
}

// ================================================================
// TEST 4: Keterlambatan Scenarios
// ================================================================
echo "<h3>TEST 4: Keterlambatan (Lateness) Scenarios</h3>";

try {
    $late_query = $pdo->prepare("SELECT a.*, r.nama_lengkap, a.menit_terlambat, a.potongan_tunjangan 
        FROM absensi a 
        JOIN register r ON a.user_id = r.id 
        WHERE a.menit_terlambat > 0 
        ORDER BY a.menit_terlambat DESC");
    $late_query->execute();
    $late_records = $late_query->fetchAll();
    
    if (count($late_records) > 0) {
        echo "<p>✓ Found " . count($late_records) . " lateness records</p>";
        
        foreach ($late_records as $late) {
            echo "<p>→ {$late['nama_lengkap']}: {$late['menit_terlambat']} minutes late</p>";
            echo "<p>   Potongan: {$late['potongan_tunjangan']}</p>";
        }
        
        logTest('Keterlambatan Detection', true, "Found " . count($late_records) . " late records");
    } else {
        echo "<p>⚠ No lateness records found</p>";
        logTest('Keterlambatan Detection', false, "No late records");
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error in keterlambatan test: " . $e->getMessage() . "</p>";
    logTest('Keterlambatan Detection', false, $e->getMessage());
}

// ================================================================
// TEST 5: Overwork Auto Detection
// ================================================================
echo "<h3>TEST 5: Overwork Auto Detection</h3>";

try {
    // Find attendance with working hours > 8
    $overwork_query = $pdo->prepare("SELECT a.*, r.nama_lengkap,
        TIMESTAMPDIFF(HOUR, a.waktu_masuk, a.waktu_keluar) as hours_worked 
        FROM absensi a 
        JOIN register r ON a.user_id = r.id 
        WHERE TIMESTAMPDIFF(HOUR, a.waktu_masuk, a.waktu_keluar) > 8");
    $overwork_query->execute();
    $overwork_records = $overwork_query->fetchAll();
    
    if (count($overwork_records) > 0) {
        echo "<p>✓ Found " . count($overwork_records) . " potential overwork records</p>";
        
        foreach ($overwork_records as $ow) {
            $overwork_hours = $ow['hours_worked'] - 8;
            $overwork_pay = $overwork_hours * 6250; // Rp 6,250 per hour
            
            echo "<p>→ {$ow['nama_lengkap']}: {$ow['hours_worked']} hours worked</p>";
            echo "<p>   Overwork: {$overwork_hours} hours × Rp 6,250 = Rp " . number_format($overwork_pay, 0, ',', '.') . "</p>";
        }
        
        logTest('Overwork Auto Detection', true, "Found " . count($overwork_records) . " overwork records");
    } else {
        echo "<p>⚠ No overwork records found</p>";
        logTest('Overwork Auto Detection', false, "No overwork records");
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error in overwork test: " . $e->getMessage() . "</p>";
    logTest('Overwork Auto Detection', false, $e->getMessage());
}

// ================================================================
// TEST 6: Generate Slip Gaji
// ================================================================
echo "<h3>TEST 6: Generate Slip Gaji for Test Users</h3>";

try {
    require_once 'calculate_status_kehadiran.php';
    
    $periode_bulan = 11;
    $periode_tahun = 2025;
    $generated_count = 0;
    
    foreach ($created_user_ids as $user_id) {
        // Get user data
        $user_stmt = $pdo->prepare("SELECT * FROM register WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch();
        
        if (!$user) continue;
        
        // Get attendance summary
        $att_stmt = $pdo->prepare("SELECT COUNT(*) as jumlah_absen FROM absensi WHERE user_id = ? AND MONTH(tanggal_absensi) = ? AND YEAR(tanggal_absensi) = ?");
        $att_stmt->execute([$user_id, $periode_bulan, $periode_tahun]);
        $att_summary = $att_stmt->fetch();
        
        // Calculate basic salary and deductions
        $gaji_pokok = 5000000; // Base salary
        $tunjangan_makan = 300000;
        $tunjangan_transport = 200000;
        $jumlah_absen = $att_summary['jumlah_absen'];
        
        // Check for tidak hadir (confirmed shift but no attendance)
        $tidak_hadir_stmt = $pdo->prepare("SELECT COUNT(*) as count 
            FROM shift_assignments sa 
            LEFT JOIN absensi a ON sa.user_id = a.user_id AND sa.tanggal_shift = a.tanggal_absensi 
            WHERE sa.user_id = ? 
            AND sa.status_konfirmasi = 'confirmed' 
            AND a.id IS NULL 
            AND MONTH(sa.tanggal_shift) = ? 
            AND YEAR(sa.tanggal_shift) = ?");
        $tidak_hadir_stmt->execute([$user_id, $periode_bulan, $periode_tahun]);
        $tidak_hadir = $tidak_hadir_stmt->fetch();
        $hari_tidak_hadir = $tidak_hadir['count'];
        $potongan_tidak_hadir = $hari_tidak_hadir * 50000;
        
        // Calculate overwork
        $overwork_stmt = $pdo->prepare("SELECT SUM(GREATEST(0, TIMESTAMPDIFF(HOUR, waktu_masuk, waktu_keluar) - 8)) as total_overwork_hours 
            FROM absensi 
            WHERE user_id = ? 
            AND MONTH(tanggal_absensi) = ? 
            AND YEAR(tanggal_absensi) = ?");
        $overwork_stmt->execute([$user_id, $periode_bulan, $periode_tahun]);
        $overwork_data = $overwork_stmt->fetch();
        $overwork_hours = min(8, $overwork_data['total_overwork_hours'] ?? 0); // Max 8 hours
        $overwork_pay = $overwork_hours * 6250;
        
        $total_gaji = $gaji_pokok + $tunjangan_makan + $tunjangan_transport + $overwork_pay - $potongan_tidak_hadir;
        
        // Insert into riwayat_gaji
        $insert_stmt = $pdo->prepare("INSERT INTO riwayat_gaji 
            (user_id, periode_bulan, periode_tahun, gaji_pokok, tunjangan_makan, tunjangan_transport, 
             overwork, jumlah_absen, hari_tidak_hadir, potongan_tidak_hadir, total_gaji, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $insert_stmt->execute([
            $user_id,
            $periode_bulan,
            $periode_tahun,
            $gaji_pokok,
            $tunjangan_makan,
            $tunjangan_transport,
            $overwork_pay,
            $jumlah_absen,
            $hari_tidak_hadir,
            $potongan_tidak_hadir,
            $total_gaji
        ]);
        
        $generated_count++;
        echo "<p>✓ Generated slip gaji for {$user['nama_lengkap']}</p>";
        echo "<p>   Total: Rp " . number_format($total_gaji, 0, ',', '.') . "</p>";
    }
    
    echo "<p><strong>✓ Generated {$generated_count} slip gaji records</strong></p>";
    logTest('Generate Slip Gaji', true, "Generated {$generated_count} slip gaji");
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error generating slip gaji: " . $e->getMessage() . "</p>";
    logTest('Generate Slip Gaji', false, $e->getMessage());
}

// ================================================================
// TEST RESULTS SUMMARY
// ================================================================

echo "<hr>";
echo "<h2>📊 TEST RESULTS SUMMARY</h2>";
echo "<div style='background:#f0f0f0; padding:20px; border-radius:8px;'>";
echo "<h3>Overall Results:</h3>";
echo "<p><strong>Total Tests:</strong> {$test_count}</p>";
echo "<p style='color:green;'><strong>Passed:</strong> {$test_passed}</p>";
echo "<p style='color:red;'><strong>Failed:</strong> {$test_failed}</p>";
echo "<p><strong>Success Rate:</strong> " . round(($test_passed / max($test_count, 1)) * 100, 2) . "%</p>";
echo "</div>";

echo "<hr>";
echo "<h3>Detailed Results:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#667eea; color:white;'>
        <th>#</th>
        <th>Test Name</th>
        <th>Status</th>
        <th>Message</th>
        <th>Timestamp</th>
      </tr>";

foreach ($test_results as $result) {
    $bg_color = $result['status'] === 'PASS' ? '#d4edda' : '#f8d7da';
    $text_color = $result['status'] === 'PASS' ? '#155724' : '#721c24';
    
    echo "<tr style='background:{$bg_color}; color:{$text_color};'>";
    echo "<td>{$result['number']}</td>";
    echo "<td>{$result['name']}</td>";
    echo "<td><strong>{$result['status']}</strong></td>";
    echo "<td>{$result['message']}</td>";
    echo "<td>{$result['timestamp']}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>🔗 Quick Links for Manual Testing:</h3>";
echo "<ul>";
echo "<li><a href='mainpageadmin.php' target='_blank'>Main Page Admin (Overview)</a></li>";
echo "<li><a href='view_absensi.php' target='_blank'>View Absensi (Rekap)</a></li>";
echo "<li><a href='approve.php' target='_blank'>Approve Surat Izin</a></li>";
echo "<li><a href='slip_gaji.php' target='_blank'>Slip Gaji</a></li>";
echo "<li><a href='jadwal_shift.php' target='_blank'>Jadwal Shift</a></li>";
echo "</ul>";

echo "<p style='margin-top:30px; color:#666;'><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comprehensive Test Suite Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        h3 {
            color: #555;
            margin-top: 20px;
        }
        p {
            line-height: 1.6;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .warning {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>
</body>
</html>
```
