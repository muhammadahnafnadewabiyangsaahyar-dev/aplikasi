<?php
require_once 'connect.php';

echo "=== VERIFIKASI DATA KAT AHNAF - NOVEMBER 2025 ===\n\n";

// Get user ID
$query = "SELECT id FROM register WHERE nama_lengkap = 'Kata Hnaf'";
$stmt = $pdo->query($query);
$user = $stmt->fetch();
$userId = $user['id'];

echo "User ID: $userId\n\n";

// === STEP 1: Cek semua hari kerja November 2025 ===
echo "=== STEP 1: CEK HARI KERJA NOVEMBER 2025 ===\n";
$daysInNovember = cal_days_in_month(CAL_GREGORIAN, 11, 2025);
$workDays = [];
$workDayCount = 0;

for ($day = 1; $day <= $daysInNovember; $day++) {
    $date = "2025-11-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    $dayOfWeek = date('N', strtotime($date)); // 1 (Monday) - 7 (Sunday)
    $dayName = date('l', strtotime($date));
    
    // Skip Sunday (day 7)
    if ($dayOfWeek != 7) {
        $workDays[] = [
            'date' => $date,
            'day' => $day,
            'dayName' => $dayName
        ];
        $workDayCount++;
    }
}

echo "Total hari kerja (Senin-Sabtu): $workDayCount hari\n\n";

// === STEP 2: Cek shift assignments ===
echo "=== STEP 2: CEK SHIFT ASSIGNMENTS ===\n";
$query = "SELECT sa.tanggal_shift, c.nama_shift, c.jam_masuk, c.jam_keluar, 
                 sa.status_konfirmasi, sa.catatan_pegawai as catatan 
          FROM shift_assignments sa
          LEFT JOIN cabang c ON sa.cabang_id = c.id
          WHERE sa.user_id = ? 
          AND MONTH(sa.tanggal_shift) = 11 
          AND YEAR(sa.tanggal_shift) = 2025
          ORDER BY sa.tanggal_shift";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);

$shiftDates = [];
$shiftCount = 0;
while ($row = $stmt->fetch()) {
    $shiftDates[] = $row['tanggal_shift'];
    $shiftCount++;
    $shiftInfo = $row['nama_shift'] . " (" . substr($row['jam_masuk'], 0, 5) . "-" . substr($row['jam_keluar'], 0, 5) . ")";
    echo "- " . $row['tanggal_shift'] . " | " . $shiftInfo . " | " . $row['status_konfirmasi'] . " | " . ($row['catatan'] ?? '-') . "\n";
}

echo "\nTotal shift: $shiftCount\n\n";

// === STEP 3: Cek hari yang TIDAK ada shift ===
echo "=== STEP 3: HARI KERJA TANPA SHIFT ===\n";
$missingShiftDays = [];
foreach ($workDays as $workDay) {
    if (!in_array($workDay['date'], $shiftDates)) {
        $missingShiftDays[] = $workDay;
        echo "❌ " . $workDay['date'] . " (" . $workDay['dayName'] . ") - TIDAK ADA SHIFT\n";
    }
}

echo "\nTotal hari kerja tanpa shift: " . count($missingShiftDays) . " hari\n\n";

// === STEP 4: Cek absensi ===
echo "=== STEP 4: CEK ABSENSI ===\n";
$query = "SELECT tanggal_absensi as tanggal, waktu_masuk, waktu_keluar, status_kehadiran as status, 
                 menit_terlambat as terlambat, status_keterlambatan as keterangan 
          FROM absensi 
          WHERE user_id = ? 
          AND MONTH(tanggal_absensi) = 11 
          AND YEAR(tanggal_absensi) = 2025
          ORDER BY tanggal_absensi";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);

$absensiDates = [];
$absensiCount = 0;
while ($row = $stmt->fetch()) {
    $absensiDates[] = $row['tanggal'];
    $absensiCount++;
    echo "- " . $row['tanggal'] . " | " . $row['status'] . " | Terlambat: " . $row['terlambat'] . " menit | " . ($row['keterangan'] ?? '-') . "\n";
}

echo "\nTotal absensi: $absensiCount\n\n";

// === STEP 5: Analisis Alpha ===
echo "=== STEP 5: ANALISIS ALPHA ===\n";
$alphaDays = [];

foreach ($workDays as $workDay) {
    // Hari kerja yang punya shift tapi tidak ada absensi
    if (in_array($workDay['date'], $shiftDates) && !in_array($workDay['date'], $absensiDates)) {
        $alphaDays[] = $workDay;
        echo "🔴 ALPHA: " . $workDay['date'] . " (" . $workDay['dayName'] . ") - Ada shift, tapi tidak absen\n";
    }
}

echo "\nTotal Alpha seharusnya: " . count($alphaDays) . " hari\n\n";

// === STEP 6: Cek izin & sakit ===
echo "=== STEP 6: CEK IZIN & SAKIT ===\n";
$query = "SELECT tanggal_mulai, tanggal_selesai, perihal, alasan, status 
          FROM pengajuan_izin 
          WHERE user_id = ? 
          AND ((MONTH(tanggal_mulai) = 11 AND YEAR(tanggal_mulai) = 2025)
               OR (MONTH(tanggal_selesai) = 11 AND YEAR(tanggal_selesai) = 2025))
          ORDER BY tanggal_mulai";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);

$izinCount = 0;
while ($row = $stmt->fetch()) {
    $izinCount++;
    echo "- " . $row['perihal'] . " | " . $row['tanggal_mulai'] . " s/d " . $row['tanggal_selesai'] . " | " . $row['status'] . " | " . $row['alasan'] . "\n";
}

echo "\nTotal izin/sakit: $izinCount\n\n";

// === SUMMARY ===
echo "=== SUMMARY & REKOMENDASI ===\n";
echo "Total hari kerja November 2025: $workDayCount hari\n";
echo "Total shift dibuat: $shiftCount hari\n";
echo "Total absensi: $absensiCount hari\n";
echo "Hari kerja tanpa shift: " . count($missingShiftDays) . " hari\n";
echo "Alpha (seharusnya): " . count($alphaDays) . " hari\n";
echo "Izin/Sakit: $izinCount pengajuan\n\n";

if (count($missingShiftDays) > 0) {
    echo "⚠️  MASALAH: Ada " . count($missingShiftDays) . " hari kerja yang tidak memiliki shift assignment.\n";
    echo "   Hari-hari tersebut akan dianggap ALPHA oleh sistem.\n\n";
    
    echo "💡 SOLUSI: Buat shift assignment untuk hari-hari berikut:\n";
    foreach ($missingShiftDays as $day) {
        echo "   - " . $day['date'] . " (" . $day['dayName'] . ")\n";
    }
    echo "\n";
}

$expectedAlpha = $workDayCount - $absensiCount;
echo "Perhitungan Alpha:\n";
echo "  $workDayCount (hari kerja) - $absensiCount (hadir) = $expectedAlpha Alpha\n\n";
?>
