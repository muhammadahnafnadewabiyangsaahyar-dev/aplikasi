<?php
/**
 * Verify Kat Ahnaf Absensi Records - November 2025
 */

require_once 'connect.php';
date_default_timezone_set('Asia/Makassar');

?>
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verifikasi Absensi Kat Ahnaf - November 2025</title>
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
        .content { padding: 40px; }
        .section { 
            background: #f8f9fa; 
            border-left: 5px solid #667eea; 
            padding: 25px; 
            margin-bottom: 25px; 
            border-radius: 8px;
        }
        .section h3 { color: #667eea; margin-bottom: 15px; }
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; padding: 15px; margin: 10px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #667eea; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .badge { 
            display: inline-block; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 0.85em; 
            font-weight: 600;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔍 Verifikasi Absensi Kat Ahnaf</h1>
            <p>November 2025 - Detailed Records</p>
            <p style='font-size: 0.9em; margin-top: 10px; opacity: 0.8;'>
                <?php echo date('Y-m-d H:i:s'); ?>
            </p>
        </div>
        <div class='content'>

<?php

// Get Kat Ahnaf user
$stmt = $pdo->prepare("SELECT * FROM register WHERE email = ?");
$stmt->execute(['katahnaf@gmail.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<div class='error'><strong>❌ Error:</strong> User Kat Ahnaf tidak ditemukan!</div>";
    exit;
}

$user_id = $user['id'];

echo "<div class='info'>
    <strong>👤 User:</strong> {$user['nama_lengkap']} (ID: {$user_id})<br>
    <strong>📧 Email:</strong> {$user['email']}
</div>";

// Get shift assignments
echo "<div class='section'>
    <h3>📅 Shift Assignments - November 2025</h3>";

$stmt = $pdo->prepare("
    SELECT tanggal_shift, status_konfirmasi
    FROM shift_assignments
    WHERE user_id = ?
    AND tanggal_shift >= '2025-11-01'
    AND tanggal_shift <= '2025-11-30'
    ORDER BY tanggal_shift
");
$stmt->execute([$user_id]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<div class='info'>
    <strong>Total Shift Assignments:</strong> " . count($shifts) . " hari
</div>";

if (count($shifts) > 0) {
    echo "<table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Status Konfirmasi</th>
            </tr>
        </thead>
        <tbody>";
    
    $no = 1;
    foreach ($shifts as $shift) {
        $day_name = date('l', strtotime($shift['tanggal_shift']));
        $status_badge = $shift['status_konfirmasi'] == 'confirmed' 
            ? "<span class='badge badge-success'>✓ Confirmed</span>" 
            : "<span class='badge badge-warning'>⏳ Pending</span>";
        
        echo "<tr>
            <td>{$no}</td>
            <td>{$shift['tanggal_shift']}</td>
            <td>{$day_name}</td>
            <td>{$status_badge}</td>
        </tr>";
        $no++;
    }
    
    echo "</tbody></table>";
}

echo "</div>";

// Get absensi records
echo "<div class='section'>
    <h3>⏰ Absensi Records - November 2025</h3>";

$stmt = $pdo->prepare("
    SELECT tanggal_absensi, waktu_masuk, waktu_keluar, 
           menit_terlambat, status_kehadiran
    FROM absensi
    WHERE user_id = ?
    AND tanggal_absensi >= '2025-11-01'
    AND tanggal_absensi <= '2025-11-30'
    ORDER BY tanggal_absensi
");
$stmt->execute([$user_id]);
$absensi = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<div class='info'>
    <strong>Total Absensi Records:</strong> " . count($absensi) . " hari
</div>";

// Define special dates
$dates_lupa_absen = ['2025-11-06', '2025-11-07', '2025-11-10'];
$dates_overwork = ['2025-11-11', '2025-11-12', '2025-11-23', '2025-11-24'];
$dates_izin = ['2025-11-15'];
$dates_sakit = ['2025-11-18'];
$dates_alpha = ['2025-11-20', '2025-11-21'];

if (count($absensi) > 0) {
    echo "<table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Terlambat</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>";
    
    $no = 1;
    foreach ($absensi as $abs) {
        $day_name = date('l', strtotime($abs['tanggal_absensi']));
        $status_badge = $abs['status_kehadiran'] == 'Hadir' 
            ? "<span class='badge badge-success'>✓ Hadir</span>" 
            : "<span class='badge badge-danger'>✗ " . $abs['status_kehadiran'] . "</span>";
        
        $keterangan = '';
        if (in_array($abs['tanggal_absensi'], $dates_lupa_absen)) {
            $keterangan = "<span class='badge badge-warning'>⚠️ Lupa Absen (Input Manual)</span>";
        }
        if (in_array($abs['tanggal_absensi'], $dates_overwork)) {
            $keterangan .= " <span class='badge badge-info'>⏰ Overwork</span>";
        }
        
        $late_badge = $abs['menit_terlambat'] > 0 
            ? "<span class='badge badge-warning'>{$abs['menit_terlambat']} menit</span>" 
            : "<span class='badge badge-success'>Tepat waktu</span>";
        
        echo "<tr>
            <td>{$no}</td>
            <td>{$abs['tanggal_absensi']}</td>
            <td>{$day_name}</td>
            <td>" . date('H:i', strtotime($abs['waktu_masuk'])) . "</td>
            <td>" . date('H:i', strtotime($abs['waktu_keluar'])) . "</td>
            <td>{$late_badge}</td>
            <td>{$status_badge}</td>
            <td>{$keterangan}</td>
        </tr>";
        $no++;
    }
    
    echo "</tbody></table>";
}

echo "</div>";

// Check for missing dates
echo "<div class='section'>
    <h3>🔍 Analisis Kehadiran</h3>";

// Get all shift dates
$all_shift_dates = array_column($shifts, 'tanggal_shift');
$all_absensi_dates = array_column($absensi, 'tanggal_absensi');

// Find dates with shift but no absensi
$missing_absensi = array_diff($all_shift_dates, $all_absensi_dates);

if (count($missing_absensi) > 0) {
    echo "<div class='warning'>
        <strong>⚠️ Tanggal dengan Shift tapi Tanpa Absensi:</strong>
        <ul style='margin-top: 10px;'>";
    
    foreach ($missing_absensi as $date) {
        $day_name = date('l', strtotime($date));
        $reason = '';
        
        if (in_array($date, $dates_izin)) $reason = "📝 Izin (Approved)";
        else if (in_array($date, $dates_sakit)) $reason = "🤒 Sakit (Approved)";
        else if (in_array($date, $dates_alpha)) $reason = "❌ Alpha (Tidak Hadir)";
        else if ($date == '2025-11-08') $reason = "🔄 Reschedule ke tgl 10";
        
        echo "<li><strong>{$date}</strong> ({$day_name}) - {$reason}</li>";
    }
    
    echo "</ul>
    </div>";
} else {
    echo "<div class='success'>
        ✅ Semua tanggal shift memiliki absensi atau alasan yang jelas (izin/sakit/alpha)
    </div>";
}

// Summary
$total_hadir = count($absensi);
$total_lupa_absen = 0;
$total_overwork = 0;
$total_terlambat = 0;

foreach ($absensi as $abs) {
    if (in_array($abs['tanggal_absensi'], $dates_lupa_absen)) $total_lupa_absen++;
    if (in_array($abs['tanggal_absensi'], $dates_overwork)) $total_overwork++;
    if ($abs['menit_terlambat'] > 0) $total_terlambat++;
}

echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin-top: 30px;'>
    <h3 style='color: white; margin-bottom: 20px;'>📊 Summary Kehadiran</h3>
    <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;'>
        <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;'>
            <h4 style='color: #fff; margin-bottom: 10px;'>✅ Hadir</h4>
            <div style='font-size: 2em; font-weight: bold;'>{$total_hadir} hari</div>
            <small>Termasuk 3 hari lupa absen</small>
        </div>
        <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;'>
            <h4 style='color: #fff; margin-bottom: 10px;'>⚠️ Lupa Absen</h4>
            <div style='font-size: 2em; font-weight: bold;'>{$total_lupa_absen} hari</div>
            <small>6, 7, 10 November (sudah ditambahkan)</small>
        </div>
        <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;'>
            <h4 style='color: #fff; margin-bottom: 10px;'>⏰ Overwork</h4>
            <div style='font-size: 2em; font-weight: bold;'>{$total_overwork} hari</div>
            <small>Bonus Rp " . number_format($total_overwork * 50000, 0, ',', '.') . "</small>
        </div>
        <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;'>
            <h4 style='color: #fff; margin-bottom: 10px;'>⏱️ Terlambat</h4>
            <div style='font-size: 2em; font-weight: bold;'>{$total_terlambat} kali</div>
            <small>Dari {$total_hadir} hari hadir</small>
        </div>
    </div>
    
    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3);'>
        <h4 style='color: #fff; margin-bottom: 15px;'>📋 Rincian Lainnya:</h4>
        <ul style='list-style: none; padding: 0;'>
            <li style='padding: 5px 0;'>📝 <strong>Izin:</strong> 1 hari (15 Nov - Approved)</li>
            <li style='padding: 5px 0;'>🤒 <strong>Sakit:</strong> 1 hari (18 Nov - Approved)</li>
            <li style='padding: 5px 0;'>❌ <strong>Alpha:</strong> 2 hari (20, 21 Nov - Tidak Hadir)</li>
            <li style='padding: 5px 0;'>🔄 <strong>Reschedule:</strong> 1x (8 Nov → 10 Nov)</li>
        </ul>
    </div>
</div>";

echo "</div>";

// Get pengajuan izin
echo "<div class='section'>
    <h3>📝 Pengajuan Izin & Sakit</h3>";

$stmt = $pdo->prepare("
    SELECT perihal, tanggal_mulai, tanggal_selesai, lama_izin, 
           alasan, status, tanggal_pengajuan
    FROM pengajuan_izin
    WHERE user_id = ?
    AND tanggal_mulai >= '2025-11-01'
    AND tanggal_selesai <= '2025-11-30'
    ORDER BY tanggal_mulai
");
$stmt->execute([$user_id]);
$izin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($izin_list) > 0) {
    echo "<table>
        <thead>
            <tr>
                <th>Perihal</th>
                <th>Tanggal</th>
                <th>Lama</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Tanggal Pengajuan</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($izin_list as $izin) {
        $status_badge = $izin['status'] == 'Diterima' 
            ? "<span class='badge badge-success'>✓ Diterima</span>" 
            : "<span class='badge badge-danger'>✗ " . $izin['status'] . "</span>";
        
        echo "<tr>
            <td><strong>{$izin['perihal']}</strong></td>
            <td>{$izin['tanggal_mulai']} s/d {$izin['tanggal_selesai']}</td>
            <td>{$izin['lama_izin']} hari</td>
            <td>{$izin['alasan']}</td>
            <td>{$status_badge}</td>
            <td>" . date('Y-m-d H:i', strtotime($izin['tanggal_pengajuan'])) . "</td>
        </tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "<div class='warning'>Tidak ada pengajuan izin/sakit</div>";
}

echo "</div>";

?>

        </div>
    </div>
</body>
</html>
