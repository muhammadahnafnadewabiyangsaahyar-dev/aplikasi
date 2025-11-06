<?php
require_once 'connect.php';

echo "=== FIX STATUS KEHADIRAN UNTUK IZIN & SAKIT ===\n\n";

// Get user ID
$query = "SELECT id FROM register WHERE nama_lengkap = 'Kata Hnaf'";
$stmt = $pdo->query($query);
$user = $stmt->fetch();
$userId = $user['id'];

echo "User ID: $userId\n\n";

// === STEP 1: Ambil semua pengajuan izin/sakit yang disetujui bulan ini ===
echo "=== STEP 1: CEK IZIN/SAKIT YANG DISETUJUI ===\n";

$query = "SELECT id, perihal, tanggal_mulai, tanggal_selesai, status, alasan 
          FROM pengajuan_izin 
          WHERE user_id = ? 
          AND status = 'Diterima'
          AND ((MONTH(tanggal_mulai) = 11 AND YEAR(tanggal_mulai) = 2025)
               OR (MONTH(tanggal_selesai) = 11 AND YEAR(tanggal_selesai) = 2025))
          ORDER BY tanggal_mulai";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$approvedLeaves = $stmt->fetchAll();

if (empty($approvedLeaves)) {
    echo "❌ Tidak ada izin/sakit yang disetujui di bulan November 2025.\n";
    exit;
}

echo "✅ Ditemukan " . count($approvedLeaves) . " pengajuan izin/sakit yang disetujui:\n\n";

foreach ($approvedLeaves as $leave) {
    echo "📋 " . $leave['perihal'] . " | " . $leave['tanggal_mulai'] . " s/d " . $leave['tanggal_selesai'] . "\n";
    echo "   Alasan: " . $leave['alasan'] . "\n\n";
}

// === STEP 2: Untuk setiap hari dalam range izin/sakit, cek apakah ada absensi ===
echo "=== STEP 2: UPDATE STATUS KEHADIRAN ===\n\n";

$totalAdded = 0;
$totalUpdated = 0;
$totalSkipped = 0;

foreach ($approvedLeaves as $leave) {
    $perihal = $leave['perihal'];
    $start = new DateTime($leave['tanggal_mulai']);
    $end = new DateTime($leave['tanggal_selesai']);
    $end = $end->modify('+1 day'); // Include end date
    
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($start, $interval, $end);
    
    foreach ($dateRange as $date) {
        $tanggal = $date->format('Y-m-d');
        $dayName = $date->format('l');
        
        // Skip Sunday
        if ($dayName === 'Sunday') {
            echo "⏭️  " . $tanggal . " (Sunday) - SKIP (bukan hari kerja)\n";
            $totalSkipped++;
            continue;
        }
        
        // Cek apakah sudah ada record absensi untuk tanggal ini
        $checkQuery = "SELECT id, status_kehadiran FROM absensi 
                       WHERE user_id = ? AND tanggal_absensi = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$userId, $tanggal]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Sudah ada record, cek apakah perlu update
            if ($existing['status_kehadiran'] !== $perihal) {
                // Update status kehadiran
                $updateQuery = "UPDATE absensi 
                               SET status_kehadiran = ?, 
                                   status_keterlambatan = ?, 
                                   menit_terlambat = 0,
                                   waktu_masuk = NULL,
                                   waktu_keluar = NULL
                               WHERE id = ?";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([$perihal, $perihal, $existing['id']]);
                
                echo "🔄 " . $tanggal . " (" . $dayName . ") - UPDATE dari '" . $existing['status_kehadiran'] . "' ke '" . $perihal . "'\n";
                $totalUpdated++;
            } else {
                echo "✅ " . $tanggal . " (" . $dayName . ") - SUDAH BENAR ('" . $perihal . "')\n";
                $totalSkipped++;
            }
        } else {
            // Belum ada record, insert baru
            $insertQuery = "INSERT INTO absensi 
                           (user_id, tanggal_absensi, waktu_masuk, waktu_keluar, 
                            status_kehadiran, menit_terlambat, status_keterlambatan) 
                           VALUES (?, ?, NULL, NULL, ?, 0, ?)";
            $insertStmt = $pdo->prepare($insertQuery);
            $insertStmt->execute([$userId, $tanggal, $perihal, $perihal]);
            
            echo "➕ " . $tanggal . " (" . $dayName . ") - TAMBAH record baru ('" . $perihal . "')\n";
            $totalAdded++;
        }
    }
    echo "\n";
}

// === STEP 3: Verifikasi hasil ===
echo "=== STEP 3: VERIFIKASI HASIL ===\n\n";

// Hitung ulang statistik
$query = "SELECT 
            COUNT(DISTINCT CASE WHEN status_kehadiran = 'Hadir' THEN tanggal_absensi END) as hadir,
            COUNT(DISTINCT CASE WHEN status_kehadiran = 'Izin' THEN tanggal_absensi END) as izin,
            COUNT(DISTINCT CASE WHEN status_kehadiran = 'Sakit' THEN tanggal_absensi END) as sakit
          FROM absensi 
          WHERE user_id = ? 
          AND MONTH(tanggal_absensi) = 11 
          AND YEAR(tanggal_absensi) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$result = $stmt->fetch();

// Hitung shift yang ada
$query = "SELECT COUNT(*) as total FROM shift_assignments 
          WHERE user_id = ? AND MONTH(tanggal_shift) = 11 AND YEAR(tanggal_shift) = 2025";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$totalShift = $stmt->fetch()['total'];

$totalKehadiran = $result['hadir'] + $result['izin'] + $result['sakit'];
$alpha = $totalShift - $totalKehadiran;

echo "📊 STATISTIK NOVEMBER 2025:\n";
echo "Total shift: " . $totalShift . " hari\n";
echo "Hadir: " . $result['hadir'] . " hari\n";
echo "Izin: " . $result['izin'] . " hari\n";
echo "Sakit: " . $result['sakit'] . " hari\n";
echo "Total kehadiran (Hadir + Izin + Sakit): " . $totalKehadiran . " hari\n";
echo "Alpha: " . $alpha . " hari\n\n";

echo "=== RINGKASAN PERUBAHAN ===\n";
echo "✅ Record ditambahkan: " . $totalAdded . "\n";
echo "🔄 Record diupdate: " . $totalUpdated . "\n";
echo "⏭️  Record diskip: " . $totalSkipped . "\n\n";

echo "=== SELESAI ===\n";
echo "Silakan refresh halaman mainpage.php untuk melihat perubahan!\n";
?>
