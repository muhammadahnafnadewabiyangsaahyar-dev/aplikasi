<?php
require_once 'connect.php';

echo "=== VERIFIKASI FINAL - KAT AHNAF NOVEMBER 2025 ===\n\n";

// Get user ID
$query = "SELECT id FROM register WHERE nama_lengkap = 'Kata Hnaf'";
$stmt = $pdo->query($query);
$user = $stmt->fetch();
$userId = $user['id'];

echo "User ID: $userId\n\n";

// === STATISTIK LENGKAP ===
echo "=== STATISTIK NOVEMBER 2025 ===\n\n";

// Total shift (hari kerja)
$query = "SELECT COUNT(*) as total FROM shift_assignments 
          WHERE user_id = ? AND MONTH(tanggal_shift) = 11 AND YEAR(tanggal_shift) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$totalShift = $stmt->fetch()['total'];

// Hadir (status = 'Hadir' saja)
$query = "SELECT COUNT(*) as total FROM absensi 
          WHERE user_id = ? 
          AND status_kehadiran = 'Hadir'
          AND MONTH(tanggal_absensi) = 11 AND YEAR(tanggal_absensi) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$totalHadir = $stmt->fetch()['total'];

// Tepat Waktu (dari yang 'Hadir')
$query = "SELECT COUNT(*) as total FROM absensi 
          WHERE user_id = ? 
          AND status_kehadiran = 'Hadir'
          AND status_keterlambatan = 'tepat waktu'
          AND MONTH(tanggal_absensi) = 11 AND YEAR(tanggal_absensi) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$tepatWaktu = $stmt->fetch()['total'];

// Terlambat
$terlambat = $totalHadir - $tepatWaktu;

// Izin
$query = "SELECT COUNT(*) as total FROM absensi 
          WHERE user_id = ? 
          AND status_kehadiran = 'Izin'
          AND MONTH(tanggal_absensi) = 11 AND YEAR(tanggal_absensi) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$totalIzin = $stmt->fetch()['total'];

// Sakit
$query = "SELECT COUNT(*) as total FROM absensi 
          WHERE user_id = ? 
          AND status_kehadiran = 'Sakit'
          AND MONTH(tanggal_absensi) = 11 AND YEAR(tanggal_absensi) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$totalSakit = $stmt->fetch()['total'];

// Alpha
$totalPresent = $totalHadir + $totalIzin + $totalSakit;
$alpha = $totalShift - $totalPresent;

// Persentase
$persentaseKehadiran = round(($totalPresent / $totalShift) * 100, 1);

echo "📊 BREAKDOWN KEHADIRAN:\n";
echo "├─ Total Hari Kerja (Shift): $totalShift hari\n";
echo "│\n";
echo "├─ 🟢 Hadir: $totalHadir hari\n";
echo "│  ├─ Tepat Waktu: $tepatWaktu hari\n";
echo "│  └─ Terlambat: $terlambat hari\n";
echo "│\n";
echo "├─ 🟣 Izin: $totalIzin hari (disetujui)\n";
echo "├─ 🔵 Sakit: $totalSakit hari (disetujui)\n";
echo "│\n";
echo "├─ 🟢 Total Present: $totalPresent hari (Hadir + Izin + Sakit)\n";
echo "└─ 🔴 Alpha: $alpha hari (Tidak hadir tanpa keterangan)\n\n";

echo "📈 PERSENTASE KEHADIRAN:\n";
echo "$totalPresent / $totalShift × 100 = $persentaseKehadiran%\n\n";

// === DETAIL PER TANGGAL ===
echo "=== DETAIL ABSENSI PER TANGGAL ===\n\n";

$query = "SELECT tanggal_absensi, status_kehadiran, waktu_masuk, waktu_keluar, 
                 menit_terlambat, status_keterlambatan 
          FROM absensi 
          WHERE user_id = ? 
          AND MONTH(tanggal_absensi) = 11 AND YEAR(tanggal_absensi) = 2025
          ORDER BY tanggal_absensi";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);

$hadirDates = [];
$izinDates = [];
$sakitDates = [];

while ($row = $stmt->fetch()) {
    $date = $row['tanggal_absensi'];
    $status = $row['status_kehadiran'];
    
    if ($status === 'Hadir') {
        $hadirDates[] = $date;
        $telatInfo = $row['menit_terlambat'] > 0 ? " (terlambat {$row['menit_terlambat']} menit)" : " (tepat waktu)";
        echo "🟢 $date | Hadir$telatInfo\n";
    } elseif ($status === 'Izin') {
        $izinDates[] = $date;
        echo "🟣 $date | Izin\n";
    } elseif ($status === 'Sakit') {
        $sakitDates[] = $date;
        echo "🔵 $date | Sakit\n";
    }
}

echo "\n";

// === DETAIL ALPHA ===
echo "=== DETAIL HARI ALPHA ===\n\n";

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

$alphaDates = [];
while ($row = $stmt->fetch()) {
    $alphaDates[] = $row['tanggal_shift'];
    echo "🔴 " . $row['tanggal_shift'] . " (" . date('l', strtotime($row['tanggal_shift'])) . ")\n";
}

echo "\n";

// === VALIDASI ===
echo "=== VALIDASI ===\n";
$totalRecorded = count($hadirDates) + count($izinDates) + count($sakitDates) + count($alphaDates);
echo "Total shift: $totalShift\n";
echo "Total recorded: $totalRecorded (Hadir: " . count($hadirDates) . " + Izin: " . count($izinDates) . " + Sakit: " . count($sakitDates) . " + Alpha: " . count($alphaDates) . ")\n";
echo "Status: " . ($totalShift == $totalRecorded ? "✅ VALID" : "⚠️ TIDAK VALID") . "\n\n";

// === KESIMPULAN ===
echo "=== KESIMPULAN ===\n";
echo "✅ Hadir TIDAK termasuk Izin dan Sakit\n";
echo "✅ Izin dan Sakit adalah variabel terpisah\n";
echo "✅ Alpha = Total Shift - (Hadir + Izin + Sakit)\n";
echo "✅ Persentase Kehadiran = (Hadir + Izin + Sakit) / Total Shift × 100\n\n";

echo "📊 FORMULA:\n";
echo "Alpha = $totalShift - ($totalHadir + $totalIzin + $totalSakit) = $alpha ✅\n";
echo "Present = $totalHadir + $totalIzin + $totalSakit = $totalPresent ✅\n";
echo "Persentase = $totalPresent / $totalShift × 100 = $persentaseKehadiran% ✅\n";
?>
