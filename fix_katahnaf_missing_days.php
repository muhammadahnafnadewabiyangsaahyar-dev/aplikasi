<?php
require_once 'connect.php';

echo "=== FIX KAT AHNAF - MISSING DAYS ===\n\n";

// Get user and cabang ID
$query = "SELECT id FROM register WHERE nama_lengkap = 'Kata Hnaf'";
$stmt = $pdo->query($query);
$user = $stmt->fetch();
$userId = $user['id'];

$query = "SELECT id FROM cabang WHERE nama_shift = 'pagi' LIMIT 1";
$stmt = $pdo->query($query);
$cabang = $stmt->fetch();
$pagiCabangId = $cabang['id'];

$query = "SELECT id FROM cabang WHERE nama_shift = 'siang' LIMIT 1";
$stmt = $pdo->query($query);
$cabang = $stmt->fetch();
$siangCabangId = $cabang['id'];

echo "User ID: $userId\n";
echo "Pagi Cabang ID: $pagiCabangId\n";
echo "Siang Cabang ID: $siangCabangId\n\n";

// === STEP 1: Tambahkan 5 shift yang hilang ===
echo "=== STEP 1: TAMBAH 5 SHIFT YANG HILANG ===\n";

$missingDays = [
    ['date' => '2025-11-01', 'cabang' => $pagiCabangId, 'note' => null],
    ['date' => '2025-11-03', 'cabang' => $pagiCabangId, 'note' => null],
    ['date' => '2025-11-04', 'cabang' => $pagiCabangId, 'note' => null],
    ['date' => '2025-11-05', 'cabang' => $pagiCabangId, 'note' => null],
    ['date' => '2025-11-17', 'cabang' => $pagiCabangId, 'note' => null]
];

$insertShift = "INSERT INTO shift_assignments 
                (user_id, cabang_id, tanggal_shift, status_konfirmasi, catatan_pegawai, created_by) 
                VALUES (?, ?, ?, 'confirmed', ?, ?)";
$stmtShift = $pdo->prepare($insertShift);

foreach ($missingDays as $day) {
    $stmtShift->execute([$userId, $day['cabang'], $day['date'], $day['note'], $userId]);
    echo "✅ Shift ditambahkan: " . $day['date'] . " (Pagi)\n";
}

echo "\n✅ 5 shift berhasil ditambahkan!\n\n";

// === STEP 2: Verifikasi ulang ===
echo "=== STEP 2: VERIFIKASI ULANG ===\n";

// Hitung shift
$query = "SELECT COUNT(*) as total FROM shift_assignments 
          WHERE user_id = ? AND MONTH(tanggal_shift) = 11 AND YEAR(tanggal_shift) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$shiftCount = $stmt->fetch()['total'];

// Hitung absensi
$query = "SELECT COUNT(*) as total FROM absensi 
          WHERE user_id = ? AND MONTH(tanggal_absensi) = 11 AND YEAR(tanggal_absensi) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$absensiCount = $stmt->fetch()['total'];

// Hitung alpha
$alphaCount = $shiftCount - $absensiCount;

echo "Total shift November: $shiftCount\n";
echo "Total absensi November: $absensiCount\n";
echo "Total alpha (seharusnya): $alphaCount\n\n";

echo "=== PERHITUNGAN KEHADIRAN ===\n";
echo "Total hari kerja: 25 hari (Senin-Sabtu)\n";
echo "Total shift dibuat: $shiftCount hari\n";
echo "Total hadir (absensi): $absensiCount hari\n";
echo "Total alpha: $alphaCount hari\n\n";

// Detail alpha
echo "=== DETAIL HARI ALPHA ===\n";
$query = "SELECT sa.tanggal_shift 
          FROM shift_assignments sa
          LEFT JOIN absensi a ON sa.user_id = a.user_id AND sa.tanggal_shift = a.tanggal_absensi
          WHERE sa.user_id = ? 
          AND MONTH(sa.tanggal_shift) = 11 
          AND YEAR(sa.tanggal_shift) = 2025
          AND a.id IS NULL
          ORDER BY sa.tanggal_shift";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);

$alphas = [];
while ($row = $stmt->fetch()) {
    $alphas[] = $row['tanggal_shift'];
    echo "🔴 " . $row['tanggal_shift'] . " (" . date('l', strtotime($row['tanggal_shift'])) . ")\n";
}

echo "\nTotal: " . count($alphas) . " hari alpha\n\n";

// Cek apakah ada izin/sakit untuk hari alpha
echo "=== CEK IZIN/SAKIT UNTUK HARI ALPHA ===\n";
foreach ($alphas as $alphaDate) {
    $query = "SELECT perihal, status FROM pengajuan_izin 
              WHERE user_id = ? 
              AND ? BETWEEN tanggal_mulai AND tanggal_selesai";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId, $alphaDate]);
    $izin = $stmt->fetch();
    
    if ($izin) {
        echo "📋 " . $alphaDate . " → " . $izin['perihal'] . " (" . $izin['status'] . ")\n";
    }
}

echo "\n=== SELESAI ===\n";
echo "Data Kat Ahnaf November 2025 sudah dilengkapi!\n";
echo "Silakan refresh halaman absensi untuk melihat perubahan.\n\n";

echo "📊 RINGKASAN FINAL:\n";
echo "- Screenshot Overview menunjukkan: 16 hadir, 10 alpha\n";
echo "- Database sekarang: $absensiCount hadir, $alphaCount alpha\n";
echo "- Status: " . ($alphaCount == 10 ? "✅ SUDAH SESUAI" : "⚠️  PERLU DICEK LAGI") . "\n";
?>
