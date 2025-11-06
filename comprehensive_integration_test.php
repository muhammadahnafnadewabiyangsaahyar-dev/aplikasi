<?php
/**
 * COMPREHENSIVE INTEGRATION TEST SUITE
 * 
 * Test semua fitur yang saling berkaitan:
 * 1. Approve surat izin → update status absen & shift → rekap & penggajian
 * 2. Sakit → status shift & absensi → rekap & penggajian
 * 3. Shift confirmed → absen/tidak absen → rekap & penggajian
 * 4. Keterlambatan → mainpage overview & slip gaji
 * 5. Overwork otomatis → rekap & slip gaji
 * 6. Generate shift 1 bulan → variasi absen → izin/sakit → slip gaji
 * 7. Test email untuk slip gaji
 */

// Output HTML header
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Integration Test Suite - KAORI System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            line-height: 1.6;
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 40px; 
            text-align: center; 
        }
        .header h1 { 
            font-size: 2.5em; 
            margin-bottom: 10px; 
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .header p { 
            font-size: 1.2em; 
            opacity: 0.9; 
        }
        .test-content { 
            padding: 40px; 
        }
        .test-section { 
            background: #f8f9fa; 
            border-left: 5px solid #667eea; 
            padding: 25px; 
            margin-bottom: 30px; 
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section-header { 
            color: #667eea; 
            font-size: 1.5em; 
            margin-bottom: 15px; 
            font-weight: 600;
        }
        .log-container { 
            background: white; 
            padding: 20px; 
            border-radius: 5px; 
            font-family: 'Courier New', monospace; 
            font-size: 13px;
        }
        .log-entry { 
            padding: 8px 12px; 
            margin: 5px 0; 
            border-radius: 4px; 
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .log-timestamp { 
            color: #6c757d; 
            font-weight: 500;
        }
        .log-level { 
            font-weight: bold; 
            padding: 2px 8px; 
            border-radius: 3px; 
            font-size: 11px;
        }
        .log-info .log-level { background: #d1ecf1; color: #0c5460; }
        .log-success .log-level { background: #d4edda; color: #155724; }
        .log-error .log-level { background: #f8d7da; color: #721c24; }
        .log-message { 
            flex: 1;
        }
        .test-result { 
            padding: 12px 20px; 
            margin: 8px 0; 
            border-radius: 5px; 
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .test-pass { 
            background: #d4edda; 
            border-left: 4px solid #28a745; 
            color: #155724; 
        }
        .test-pass::before {
            content: "✓";
            background: #28a745;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .test-fail { 
            background: #f8d7da; 
            border-left: 4px solid #dc3545; 
            color: #721c24; 
        }
        .test-fail::before {
            content: "✗";
            background: #dc3545;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .summary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 40px; 
            margin-top: 40px; 
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .summary h2 { 
            font-size: 2em; 
            margin-bottom: 20px; 
            text-align: center;
        }
        .summary-stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin: 30px 0;
        }
        .stat-card { 
            background: rgba(255,255,255,0.15); 
            padding: 25px; 
            border-radius: 10px; 
            text-align: center;
            backdrop-filter: blur(10px);
        }
        .stat-value { 
            font-size: 3em; 
            font-weight: bold; 
            margin: 10px 0;
        }
        .stat-label { 
            font-size: 1.1em; 
            opacity: 0.9;
        }
        .manual-steps { 
            background: white; 
            color: #333; 
            padding: 30px; 
            border-radius: 10px; 
            margin-top: 30px;
        }
        .manual-steps h3 { 
            color: #667eea; 
            margin-bottom: 15px; 
            font-size: 1.5em;
        }
        .manual-steps ol { 
            margin-left: 20px; 
        }
        .manual-steps li { 
            padding: 8px 0; 
            font-size: 1.1em;
        }
        .test-users { 
            background: rgba(255,255,255,0.1); 
            padding: 20px; 
            border-radius: 8px; 
            margin-top: 20px;
        }
        .test-users h4 { 
            margin-bottom: 10px; 
            font-size: 1.3em;
        }
        .test-users ul { 
            list-style: none; 
        }
        .test-users li { 
            padding: 8px 0; 
            font-size: 1.1em;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .footer { 
            background: #2c3e50; 
            color: white; 
            padding: 30px; 
            text-align: center; 
            font-size: 0.9em;
        }
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Comprehensive Integration Test Suite</h1>
            <p>Testing all interconnected features of KAORI System</p>
            <p style="font-size: 0.9em; margin-top: 10px; opacity: 0.8;">
                Started at: <?php echo date('Y-m-d H:i:s'); ?>
            </p>
        </div>
        <div class="test-content">
<?php

require_once 'connect.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set timezone
date_default_timezone_set('Asia/Makassar');

// Test configuration
$test_emails = [
    'katahnaf@gmail.com',
    'pilaraforismacinta@gmail.com',
    'galihganji@gmail.com',
    'dotpikir@gmail.com'
];

$test_results = [];
$test_counter = 0;

/**
 * Helper functions
 */
function test_log($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_class = strtolower($type);
    echo "<div class='log-entry log-{$log_class}'>
        <span class='log-timestamp'>{$timestamp}</span>
        <span class='log-level'>{$type}</span>
        <span class='log-message'>{$message}</span>
    </div>";
    flush();
}

function test_section($title) {
    global $test_counter;
    $test_counter++;
    if ($test_counter > 1) {
        echo "</div></div>"; // Close previous section
    }
    echo "<div class='test-section'>
        <h2 class='test-section-header'>Test #{$test_counter}: {$title}</h2>
        <div class='log-container'>";
    flush();
}

function test_result($test_name, $passed, $message = '') {
    global $test_results;
    $class = $passed ? 'test-pass' : 'test-fail';
    
    echo "<div class='test-result {$class}'>
        <strong>{$test_name}</strong>";
    if ($message) {
        echo " <span style='opacity: 0.8;'>→ {$message}</span>";
    }
    echo "</div>";
    
    $test_results[] = [
        'test' => $test_name,
        'passed' => $passed,
        'message' => $message
    ];
    flush();
}

function cleanup_test_data($pdo) {
    test_log("Cleaning up previous test data...", "INFO");
    
    try {
        $pdo->beginTransaction();
        
        // Delete test users and related data (cascade)
        $stmt = $pdo->prepare("
            DELETE FROM register 
            WHERE email IN (?, ?, ?, ?) 
            OR username LIKE 'test_user_%'
        ");
        $stmt->execute([
            'katahnaf@gmail.com',
            'pilaraforismacinta@gmail.com',
            'galihganji@gmail.com',
            'dotpikir@gmail.com'
        ]);
        
        $pdo->commit();
        test_log("Cleanup completed successfully", "SUCCESS");
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        test_log("Cleanup failed: " . $e->getMessage(), "ERROR");
        return false;
    }
}

function create_test_user($pdo, $email, $name, $username, $outlet = 'Cabang A') {
    test_log("Creating test user: {$name} ({$email})", "INFO");
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO register (
                username, password, nama_lengkap, email, no_whatsapp,
                role, posisi, outlet, gaji_pokok, tunjangan_transport, 
                tunjangan_makan, tunjangan_jabatan
            ) VALUES (?, ?, ?, ?, ?, 'user', 'Staff', ?, 5000000, 500000, 500000, 0)
        ");
        
        $password = password_hash('Test123!', PASSWORD_DEFAULT);
        $phone = '08' . rand(1000000000, 9999999999);
        
        $stmt->execute([
            $username,
            $password,
            $name,
            $email,
            $phone,
            $outlet
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // Create komponen gaji for this user
        $stmt_komponen = $pdo->prepare("
            INSERT INTO komponen_gaji (
                register_id, jabatan, gaji_pokok, tunjangan_makan, 
                tunjangan_transport, tunjangan_jabatan, overwork
            ) VALUES (?, 'Staff', 5000000, 500000, 500000, 0, 50000)
        ");
        $stmt_komponen->execute([$user_id]);
        
        test_log("User created successfully with ID: {$user_id}", "SUCCESS");
        return $user_id;
        
    } catch (Exception $e) {
        test_log("Failed to create user: " . $e->getMessage(), "ERROR");
        return false;
    }
}

function create_shift_assignment($pdo, $user_id, $date, $shift_type = 'Pagi', $status = 'pending') {
    try {
        // Get default cabang_id (use first cabang or 1)
        $stmt_cabang = $pdo->query("SELECT id FROM cabang LIMIT 1");
        $cabang = $stmt_cabang->fetch();
        $cabang_id = $cabang ? $cabang['id'] : 1;
        
        $stmt = $pdo->prepare("
            INSERT INTO shift_assignments (
                user_id, cabang_id, tanggal_shift, status_konfirmasi, created_by
            ) VALUES (?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([$user_id, $cabang_id, $date, $status]);
        return $pdo->lastInsertId();
        
    } catch (Exception $e) {
        test_log("Failed to create shift: " . $e->getMessage(), "ERROR");
        return false;
    }
}

function create_attendance($pdo, $user_id, $date, $time_in, $time_out = null, $late_minutes = 0) {
    try {
        // Get cabang_id
        $stmt_cabang = $pdo->query("SELECT id FROM cabang LIMIT 1");
        $cabang = $stmt_cabang->fetch();
        $cabang_id = $cabang ? $cabang['id'] : 1;
        
        $stmt = $pdo->prepare("
            INSERT INTO absensi (
                user_id, cabang_id, tanggal_absensi, waktu_masuk, waktu_keluar,
                menit_terlambat, status_kehadiran
            ) VALUES (?, ?, ?, ?, ?, ?, 'Hadir')
        ");
        
        $stmt->execute([
            $user_id,
            $cabang_id,
            $date,
            $date . ' ' . $time_in,
            $time_out ? ($date . ' ' . $time_out) : null,
            $late_minutes
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (Exception $e) {
        test_log("Failed to create attendance: " . $e->getMessage(), "ERROR");
        return false;
    }
}

function create_leave_request($pdo, $user_id, $start_date, $end_date, $type = 'izin', $status = 'Pending') {
    try {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $days = $start->diff($end)->days + 1;
        
        $perihal = ($type == 'sakit') ? 'Sakit' : 'Izin';
        $alasan = "Test {$type} request";
        
        // Create a dummy file for file_surat
        $dummy_file = 'test_' . uniqid() . '.txt';
        
        $stmt = $pdo->prepare("
            INSERT INTO pengajuan_izin (
                user_id, perihal, tanggal_mulai, tanggal_selesai,
                lama_izin, alasan, file_surat, status, tanggal_pengajuan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $perihal,
            $start_date,
            $end_date,
            $days,
            $alasan,
            $dummy_file,
            $status
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (Exception $e) {
        test_log("Failed to create leave request: " . $e->getMessage(), "ERROR");
        return false;
    }
}

function generate_monthly_shifts($pdo, $user_id, $year, $month) {
    test_log("Generating shifts for user {$user_id} for {$year}-{$month}", "INFO");
    
    $shifts_created = 0;
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $shift_types = ['Pagi', 'Siang', 'Malam'];
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $dow = date('w', strtotime($date)); // 0 = Sunday
        
        // Skip Sundays
        if ($dow == 0) continue;
        
        // Random shift type
        $shift_type = $shift_types[array_rand($shift_types)];
        
        if (create_shift_assignment($pdo, $user_id, $date, $shift_type, 'pending')) {
            $shifts_created++;
        }
    }
    
    test_log("Created {$shifts_created} shifts for the month", "SUCCESS");
    return $shifts_created;
}

function send_test_email($to_email, $subject, $body) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kaori.aplikasi.notif@gmail.com';
        $mail->Password = 'imjq nmeq vyig umgn';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        $mail->setFrom('kaori.aplikasi.notif@gmail.com', 'KAORI Test System');
        $mail->addAddress($to_email);
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        test_log("Email failed: " . $e->getMessage(), "ERROR");
        return false;
    }
}

/**
 * TEST 1: Approve Izin → Status Absen & Shift
 */
function test_approve_izin($pdo, $user_id, $test_date) {
    test_section("APPROVE IZIN → STATUS ABSEN & SHIFT");
    
    // Create shift for the date
    $shift_id = create_shift_assignment($pdo, $user_id, $test_date, 'Pagi', 'confirmed');
    test_result("Create shift for izin test", $shift_id !== false);
    
    // Create leave request
    $leave_id = create_leave_request($pdo, $user_id, $test_date, $test_date, 'izin', 'Pending');
    test_result("Create izin request", $leave_id !== false);
    
    // Approve the leave
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE pengajuan_izin SET status = 'Diterima' WHERE id = ?");
        $stmt->execute([$leave_id]);
        
        $pdo->commit();
        test_result("Approve izin", true);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        test_result("Approve izin", false, $e->getMessage());
    }
    
    // Verify shift status (should still be confirmed, but with izin on that date)
    $stmt = $pdo->prepare("SELECT status_konfirmasi FROM shift_assignments WHERE id = ?");
    $stmt->execute([$shift_id]);
    $shift = $stmt->fetch();
    test_result("Shift status unchanged", $shift['status_konfirmasi'] == 'confirmed');
    
    // Check if leave is approved
    $stmt = $pdo->prepare("SELECT status FROM pengajuan_izin WHERE id = ?");
    $stmt->execute([$leave_id]);
    $leave = $stmt->fetch();
    test_result("Leave status = Diterima", $leave['status'] == 'Diterima');
    
    return ['shift_id' => $shift_id, 'leave_id' => $leave_id];
}

/**
 * TEST 2: Sakit → Status Shift & Absensi
 */
function test_sakit_status($pdo, $user_id, $test_date) {
    test_section("SAKIT → STATUS SHIFT & ABSENSI");
    
    // Create shift
    $shift_id = create_shift_assignment($pdo, $user_id, $test_date, 'Pagi', 'confirmed');
    test_result("Create shift for sakit test", $shift_id !== false);
    
    // Create sakit request (already approved)
    $leave_id = create_leave_request($pdo, $user_id, $test_date, $test_date, 'sakit', 'Diterima');
    test_result("Create approved sakit request", $leave_id !== false);
    
    // Check if there's no attendance
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM absensi 
        WHERE user_id = ? AND tanggal_absensi = ?
    ");
    $stmt->execute([$user_id, $test_date]);
    $result = $stmt->fetch();
    test_result("No attendance record for sakit day", $result['count'] == 0);
    
    return ['shift_id' => $shift_id, 'leave_id' => $leave_id];
}

/**
 * TEST 3a: Shift Confirmed → Tidak Absen (Potongan 50.000)
 */
function test_shift_confirmed_no_attendance($pdo, $user_id, $test_date) {
    test_section("SHIFT CONFIRMED → TIDAK ABSEN (Potongan 50k)");
    
    // Create confirmed shift
    $shift_id = create_shift_assignment($pdo, $user_id, $test_date, 'Pagi', 'confirmed');
    test_result("Create confirmed shift", $shift_id !== false);
    
    // Don't create attendance - simulating tidak hadir
    test_log("No attendance record created (simulating tidak hadir)", "INFO");
    
    // Verify shift is confirmed
    $stmt = $pdo->prepare("SELECT status_konfirmasi FROM shift_assignments WHERE id = ?");
    $stmt->execute([$shift_id]);
    $shift = $stmt->fetch();
    test_result("Shift is confirmed", $shift['status_konfirmasi'] == 'confirmed');
    
    // Check if there's no attendance
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM absensi 
        WHERE user_id = ? AND tanggal_absensi = ?
    ");
    $stmt->execute([$user_id, $test_date]);
    $result = $stmt->fetch();
    test_result("No attendance record exists", $result['count'] == 0);
    
    test_log("Expected: Potongan 50.000 in slip gaji", "INFO");
    
    return ['shift_id' => $shift_id];
}

/**
 * TEST 3b: Shift Confirmed → Hadir
 */
function test_shift_confirmed_with_attendance($pdo, $user_id, $test_date) {
    test_section("SHIFT CONFIRMED → HADIR");
    
    // Create confirmed shift
    $shift_id = create_shift_assignment($pdo, $user_id, $test_date, 'Pagi', 'confirmed');
    test_result("Create confirmed shift", $shift_id !== false);
    
    // Create attendance (on time)
    $attendance_id = create_attendance($pdo, $user_id, $test_date, '08:00:00', '16:00:00', 0);
    test_result("Create attendance record", $attendance_id !== false);
    
    // Verify attendance exists
    $stmt = $pdo->prepare("
        SELECT status_kehadiran, menit_terlambat 
        FROM absensi 
        WHERE id = ?
    ");
    $stmt->execute([$attendance_id]);
    $attendance = $stmt->fetch();
    test_result("Attendance status = Hadir", $attendance['status_kehadiran'] == 'Hadir');
    test_result("No late minutes", $attendance['menit_terlambat'] == 0);
    
    return ['shift_id' => $shift_id, 'attendance_id' => $attendance_id];
}

/**
 * TEST 4: Keterlambatan Variations
 */
function test_lateness_variations($pdo, $user_id, $base_date) {
    test_section("KETERLAMBATAN VARIATIONS");
    
    $test_cases = [
        ['minutes' => 10, 'category' => 'Terlambat < 20 menit'],
        ['minutes' => 25, 'category' => 'Terlambat > 20 menit'],
        ['minutes' => 45, 'category' => 'Terlambat > 20 menit'],
    ];
    
    $results = [];
    
    foreach ($test_cases as $index => $case) {
        $test_date = date('Y-m-d', strtotime($base_date . ' +' . $index . ' days'));
        
        // Create shift
        $shift_id = create_shift_assignment($pdo, $user_id, $test_date, 'Pagi', 'confirmed');
        
        // Calculate late time
        $late_time = date('H:i:s', strtotime('08:00:00 +' . $case['minutes'] . ' minutes'));
        
        // Create attendance
        $attendance_id = create_attendance($pdo, $user_id, $test_date, $late_time, '16:00:00', $case['minutes']);
        
        test_result(
            "Keterlambatan {$case['minutes']} menit", 
            $attendance_id !== false,
            $case['category']
        );
        
        $results[] = [
            'shift_id' => $shift_id,
            'attendance_id' => $attendance_id,
            'minutes' => $case['minutes']
        ];
    }
    
    return $results;
}

/**
 * TEST 5: Overwork Otomatis (Bukan Jadwal Shift)
 */
function test_overwork_auto($pdo, $user_id, $test_date) {
    test_section("OVERWORK OTOMATIS (Bukan Jadwal Shift)");
    
    // Don't create shift - simulating work on non-shift day
    test_log("No shift assignment (simulating overwork)", "INFO");
    
    // Create attendance (8+ hours)
    $attendance_id = create_attendance($pdo, $user_id, $test_date, '09:00:00', '17:30:00', 0);
    test_result("Create overwork attendance", $attendance_id !== false);
    
    // Verify no shift exists
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM shift_assignments 
        WHERE user_id = ? AND tanggal_shift = ?
    ");
    $stmt->execute([$user_id, $test_date]);
    $result = $stmt->fetch();
    test_result("No shift assignment", $result['count'] == 0);
    
    // Verify attendance exists
    $stmt = $pdo->prepare("
        SELECT status_kehadiran, menit_terlambat, waktu_masuk, waktu_keluar 
        FROM absensi 
        WHERE id = ?
    ");
    $stmt->execute([$attendance_id]);
    $attendance = $stmt->fetch();
    
    $masuk = new DateTime($attendance['waktu_masuk']);
    $keluar = new DateTime($attendance['waktu_keluar']);
    $hours = $masuk->diff($keluar)->h;
    
    test_result("Work hours >= 8", $hours >= 8, "Worked {$hours} hours");
    test_log("Expected: Overwork payment 50.000 in slip gaji", "INFO");
    
    // Test overwork with lateness
    $test_date_late = date('Y-m-d', strtotime($test_date . ' +1 day'));
    $attendance_id_late = create_attendance($pdo, $user_id, $test_date_late, '09:30:00', '18:00:00', 30);
    test_result("Create overwork with 30min late", $attendance_id_late !== false);
    test_log("Expected: Overwork 50.000 - potongan (30min / 60 * 6.250)", "INFO");
    
    return [
        'attendance_id' => $attendance_id,
        'attendance_id_late' => $attendance_id_late
    ];
}

/**
 * TEST 6: Generate Slip Gaji
 */
function test_generate_slip_gaji($pdo, $user_id, $month, $year) {
    test_section("GENERATE SLIP GAJI");
    
    // Run the auto generate script with full PHP path
    test_log("Running auto_generate_slipgaji.php...", "INFO");
    
    // Try different PHP paths
    $php_paths = [
        '/usr/bin/php',
        '/usr/local/bin/php',
        '/opt/homebrew/bin/php',
        'php' // fallback
    ];
    
    $php_executable = 'php';
    foreach ($php_paths as $path) {
        if (file_exists($path) && is_executable($path)) {
            $php_executable = $path;
            break;
        }
    }
    
    $output = [];
    $return_var = 0;
    $command = $php_executable . ' ' . __DIR__ . '/auto_generate_slipgaji.php 2>&1';
    exec($command, $output, $return_var);
    
    test_result("Execute slip gaji script", $return_var === 0, implode("\n", $output));
    
    // Verify slip gaji created
    $stmt = $pdo->prepare("
        SELECT * FROM riwayat_gaji 
        WHERE register_id = ? 
        AND periode_bulan = ? 
        AND periode_tahun = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $month, $year]);
    $slip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($slip) {
        test_result("Slip gaji created", true, "ID: {$slip['id']}");
        test_log("Gaji Pokok Aktual: Rp " . number_format($slip['gaji_pokok_aktual'], 0, ',', '.'), "INFO");
        test_log("Total Pendapatan: Rp " . number_format($slip['total_pendapatan'], 0, ',', '.'), "INFO");
        test_log("Total Potongan: Rp " . number_format($slip['total_potongan'], 0, ',', '.'), "INFO");
        test_log("Gaji Bersih: Rp " . number_format($slip['gaji_bersih'], 0, ',', '.'), "INFO");
        test_log("Overwork: Rp " . number_format($slip['overwork'], 0, ',', '.'), "INFO");
        test_log("Potongan Tidak Hadir: Rp " . number_format($slip['potongan_tidak_hadir'], 0, ',', '.'), "INFO");
        test_log("Potongan Telat > 20: Rp " . number_format($slip['potongan_telat_atas_20'], 0, ',', '.'), "INFO");
        test_log("Potongan Telat < 20: Rp " . number_format($slip['potongan_telat_bawah_20'], 0, ',', '.'), "INFO");
    } else {
        test_result("Slip gaji created", false, "No record found");
    }
    
    return $slip;
}

/**
 * TEST 7: Send Email Test
 */
function test_send_email($pdo, $slip_gaji_id, $email) {
    test_section("SEND EMAIL TEST");
    
    // Get slip gaji details
    $stmt = $pdo->prepare("
        SELECT rg.*, r.nama_lengkap, r.email
        FROM riwayat_gaji rg
        JOIN register r ON rg.register_id = r.id
        WHERE rg.id = ?
    ");
    $stmt->execute([$slip_gaji_id]);
    $slip = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$slip) {
        test_result("Get slip gaji data", false, "Slip not found");
        return false;
    }
    
    test_result("Get slip gaji data", true);
    
    // Prepare email
    $bulan_nama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $subject = "Slip Gaji - " . $bulan_nama[$slip['periode_bulan']] . " " . $slip['periode_tahun'];
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
            .salary-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .salary-table th, .salary-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            .salary-table th { background: #f8f9fa; font-weight: bold; }
            .total-row { background: #e8f5e9; font-weight: bold; font-size: 18px; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🧾 Slip Gaji</h1>
                <p>{$bulan_nama[$slip['periode_bulan']]} {$slip['periode_tahun']}</p>
            </div>
            
            <div class='content'>
                <h2>Halo, {$slip['nama_lengkap']}!</h2>
                <p>Berikut adalah rincian gaji Anda untuk periode <strong>{$bulan_nama[$slip['periode_bulan']]} {$slip['periode_tahun']}</strong>:</p>
                
                <table class='salary-table'>
                    <tr>
                        <th colspan='2' style='background: #667eea; color: white;'>PENDAPATAN</th>
                    </tr>
                    <tr>
                        <td>Gaji Pokok</td>
                        <td style='text-align: right;'>Rp " . number_format($slip['gaji_pokok_aktual'], 0, ',', '.') . "</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Makan</td>
                        <td style='text-align: right;'>Rp " . number_format($slip['tunjangan_makan'], 0, ',', '.') . "</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Transportasi</td>
                        <td style='text-align: right;'>Rp " . number_format($slip['tunjangan_transportasi'], 0, ',', '.') . "</td>
                    </tr>
                    <tr>
                        <td>Overwork</td>
                        <td style='text-align: right;'>Rp " . number_format($slip['overwork'], 0, ',', '.') . "</td>
                    </tr>
                    <tr style='background: #e3f2fd;'>
                        <td><strong>Total Pendapatan</strong></td>
                        <td style='text-align: right;'><strong>Rp " . number_format($slip['total_pendapatan'], 0, ',', '.') . "</strong></td>
                    </tr>
                    
                    <tr>
                        <th colspan='2' style='background: #f44336; color: white;'>POTONGAN</th>
                    </tr>
                    <tr>
                        <td>Tidak Hadir</td>
                        <td style='text-align: right;'>Rp " . number_format($slip['potongan_tidak_hadir'], 0, ',', '.') . "</td>
                    </tr>
                    <tr>
                        <td>Keterlambatan > 20 menit</td>
                        <td style='text-align: right;'>Rp " . number_format($slip['potongan_telat_atas_20'], 0, ',', '.') . "</td>
                    </tr>
                    <tr>
                        <td>Keterlambatan < 20 menit</td>
                        <td style='text-align: right;'>Rp " . number_format($slip['potongan_telat_bawah_20'], 0, ',', '.') . "</td>
                    </tr>
                    <tr style='background: #ffebee;'>
                        <td><strong>Total Potongan</strong></td>
                        <td style='text-align: right;'><strong>Rp " . number_format($slip['total_potongan'], 0, ',', '.') . "</strong></td>
                    </tr>
                    
                    <tr class='total-row'>
                        <td>GAJI BERSIH</td>
                        <td style='text-align: right;'>Rp " . number_format($slip['gaji_bersih'], 0, ',', '.') . "</td>
                    </tr>
                </table>
                
                <p style='color: #666; font-size: 14px;'>Jika ada pertanyaan, silakan hubungi HR Department.</p>
            </div>
            
            <div class='footer'>
                <p>© 2024 KAORI Indonesia. All rights reserved.</p>
                <p>Email ini dikirim otomatis, mohon tidak membalas.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email
    $email_sent = send_test_email($email, $subject, $body);
    test_result("Send email to {$email}", $email_sent);
    
    if ($email_sent) {
        // Update email_sent flag
        $stmt = $pdo->prepare("UPDATE riwayat_gaji SET email_sent = 1, email_sent_at = NOW() WHERE id = ?");
        $stmt->execute([$slip_gaji_id]);
        test_log("Email sent flag updated in database", "SUCCESS");
    }
    
    return $email_sent;
}

/**
 * MAIN TEST EXECUTION
 */
function run_all_tests() {
    global $pdo, $test_emails, $test_users;
    
    $start_time = microtime(true);
    
    // Clean up previous test data
    cleanup_test_data($pdo);
    
    // Create test users
    $test_users = [];
    $names = ['Kata Hnaf', 'Pilar Aforisma', 'Galih Ganji', 'Dot Pikir'];
    $usernames = ['test_user_1', 'test_user_2', 'test_user_3', 'test_user_4'];
    
    test_section("CREATE TEST USERS");
    foreach ($test_emails as $index => $email) {
        $user_id = create_test_user($pdo, $email, $names[$index], $usernames[$index]);
        if ($user_id) {
            $test_users[] = [
                'id' => $user_id,
                'email' => $email,
                'name' => $names[$index]
            ];
        }
    }
    
    test_result("All test users created", count($test_users) == 4);
    
    // Use November 2025 for testing
    $test_month = 11;
    $test_year = 2025;
    $base_date = '2025-11-10'; // Start from Nov 10
    
    // TEST 1: Approve Izin (User 1)
    $user1 = $test_users[0];
    $izin_results = test_approve_izin($pdo, $user1['id'], '2025-11-11');
    
    // TEST 2: Sakit (User 1)
    $sakit_results = test_sakit_status($pdo, $user1['id'], '2025-11-12');
    
    // TEST 3a: Shift Confirmed - No Attendance (User 2)
    $user2 = $test_users[1];
    $no_attend_results = test_shift_confirmed_no_attendance($pdo, $user2['id'], '2025-11-11');
    
    // TEST 3b: Shift Confirmed - With Attendance (User 2)
    $attend_results = test_shift_confirmed_with_attendance($pdo, $user2['id'], '2025-11-12');
    
    // TEST 4: Lateness Variations (User 3)
    $user3 = $test_users[2];
    $late_results = test_lateness_variations($pdo, $user3['id'], '2025-11-11');
    
    // TEST 5: Overwork Auto (User 3)
    $overwork_results = test_overwork_auto($pdo, $user3['id'], '2025-11-15');
    
    // TEST 6: Generate Monthly Shifts and Varied Attendance (User 4)
    $user4 = $test_users[3];
    test_section("GENERATE MONTHLY SHIFTS & VARIED ATTENDANCE");
    $shifts_created = generate_monthly_shifts($pdo, $user4['id'], $test_year, $test_month);
    test_result("Monthly shifts generated", $shifts_created > 0, "{$shifts_created} shifts created");
    
    // Create varied attendance for User 4
    $attendance_scenarios = [
        ['date' => '2025-11-04', 'in' => '08:00:00', 'out' => '16:00:00', 'late' => 0, 'desc' => 'On time'],
        ['date' => '2025-11-05', 'in' => '08:15:00', 'out' => '16:00:00', 'late' => 15, 'desc' => 'Late 15 min'],
        ['date' => '2025-11-06', 'in' => '08:30:00', 'out' => '16:00:00', 'late' => 30, 'desc' => 'Late 30 min'],
        ['date' => '2025-11-07', 'in' => '08:00:00', 'out' => '16:30:00', 'late' => 0, 'desc' => 'Overtime'],
        // Nov 8 - no attendance (tidak hadir)
    ];
    
    foreach ($attendance_scenarios as $scenario) {
        $att_id = create_attendance($pdo, $user4['id'], $scenario['date'], $scenario['in'], $scenario['out'], $scenario['late']);
        test_result("Attendance: {$scenario['desc']}", $att_id !== false, $scenario['date']);
    }
    
    // Create izin for User 4
    $leave_id = create_leave_request($pdo, $user4['id'], '2025-11-13', '2025-11-14', 'izin', 'Diterima');
    test_result("Create approved izin for User 4", $leave_id !== false, "2 days");
    
    // Create sakit for User 4
    $sakit_id = create_leave_request($pdo, $user4['id'], '2025-11-18', '2025-11-19', 'sakit', 'Diterima');
    test_result("Create approved sakit for User 4", $sakit_id !== false, "2 days");
    
    // TEST 7: Generate Slip Gaji for all users
    test_section("GENERATE SLIP GAJI FOR ALL USERS");
    $slips = [];
    foreach ($test_users as $user) {
        $slip = test_generate_slip_gaji($pdo, $user['id'], $test_month, $test_year);
        if ($slip) {
            $slips[] = ['user' => $user, 'slip' => $slip];
        }
    }
    
    // TEST 8: Send Email Tests
    test_section("SEND EMAIL TESTS");
    foreach ($slips as $slip_data) {
        if (isset($slip_data['slip']['id'])) {
            test_send_email($pdo, $slip_data['slip']['id'], $slip_data['user']['email']);
            sleep(2); // Delay to avoid rate limiting
        }
    }
    
    // Summary
    $end_time = microtime(true);
    $duration = round($end_time - $start_time, 2);
    
    // Close last test section
    echo "</div></div>";
    
    global $test_results;
    $total = count($test_results);
    $passed = count(array_filter($test_results, fn($r) => $r['passed']));
    $failed = $total - $passed;
    $pass_rate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;
    
    ?>
    <div class="summary">
        <h2>📊 TEST SUMMARY</h2>
        
        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-label">Total Tests</div>
                <div class="stat-value"><?php echo $total; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">✓ Passed</div>
                <div class="stat-value" style="color: #4CAF50;"><?php echo $passed; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">✗ Failed</div>
                <div class="stat-value" style="color: <?php echo $failed > 0 ? '#f44336' : '#4CAF50'; ?>;">
                    <?php echo $failed; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pass Rate</div>
                <div class="stat-value"><?php echo $pass_rate; ?>%</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Duration</div>
                <div class="stat-value" style="font-size: 2em;"><?php echo $duration; ?>s</div>
            </div>
        </div>
        
        <?php if ($failed > 0): ?>
        <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 10px; margin-top: 20px;">
            <h3 style="color: #ffeb3b; margin-bottom: 15px;">⚠️ Failed Tests:</h3>
            <ul style="list-style: none;">
                <?php foreach ($test_results as $result): ?>
                    <?php if (!$result['passed']): ?>
                        <li style="padding: 10px; background: rgba(255,255,255,0.1); margin: 5px 0; border-radius: 5px;">
                            <strong>✗ <?php echo htmlspecialchars($result['test']); ?></strong>
                            <?php if ($result['message']): ?>
                                <br><span style="opacity: 0.9; font-size: 0.95em;">
                                    → <?php echo htmlspecialchars($result['message']); ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="manual-steps">
            <h3>📋 Manual Verification Steps</h3>
            <ol>
                <li>Check <strong>mainpageadmin.php</strong> for overview statistics</li>
                <li>Check <strong>view_absensi.php</strong> for attendance records</li>
                <li>Check <strong>jadwal_shift.php</strong> for shift assignments</li>
                <li>Check <strong>approve.php</strong> for leave requests</li>
                <li>Check <strong>slip_gaji_management.php</strong> for generated salary slips</li>
                <li>Check email inboxes for salary slip emails</li>
            </ol>
        </div>
        
        <div class="test-users">
            <h4>👥 Test Users Created:</h4>
            <ul>
                <?php 
                global $test_users;
                if (isset($test_users)) {
                    foreach ($test_users as $user): 
                ?>
                    <li>
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong> 
                        (<?php echo htmlspecialchars($user['email']); ?>) 
                        - ID: <?php echo $user['id']; ?>
                    </li>
                <?php 
                    endforeach;
                }
                ?>
            </ul>
            <p style="margin-top: 15px; opacity: 0.9;">
                Password for all test users: <code style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 3px;">Test123!</code>
            </p>
        </div>
    </div>
    
    </div> <!-- Close test-content -->
    
    <div class="footer">
        <p><strong>KAORI Integration Test Suite v1.0</strong></p>
        <p>Generated on <?php echo date('Y-m-d H:i:s'); ?></p>
        <p style="margin-top: 10px; opacity: 0.7;">
            © 2024 KAORI Indonesia. All rights reserved.
        </p>
    </div>
    
    </div> <!-- Close container -->
</body>
</html>
    <?php
}

// RUN TESTS
try {
    run_all_tests();
} catch (Exception $e) {
    echo "<div class='log-entry log-error'>
        <span class='log-timestamp'>" . date('Y-m-d H:i:s') . "</span>
        <span class='log-level'>FATAL ERROR</span>
        <span class='log-message'>" . htmlspecialchars($e->getMessage()) . "</span>
    </div>";
    echo "<div class='log-entry log-error'>
        <span class='log-timestamp'>" . date('Y-m-d H:i:s') . "</span>
        <span class='log-level'>STACK TRACE</span>
        <span class='log-message'><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></span>
    </div>";
    echo "</div></div></div></div></body></html>";
}
