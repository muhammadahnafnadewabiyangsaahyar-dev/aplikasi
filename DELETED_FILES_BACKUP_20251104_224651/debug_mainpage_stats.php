<?php
/**
 * DEBUG SCRIPT: Check Mainpage Statistics
 */

require_once 'connect.php';

echo "========================================\n";
echo "DEBUG MAINPAGE STATISTICS\n";
echo "========================================\n\n";

// Simulasi session (ganti dengan user_id yang ingin dicek)
$user_id = 1; // Superadmin
$bulan_ini = date('Y-m');

try {
    echo "User ID: $user_id\n";
    echo "Bulan: $bulan_ini\n\n";
    
    // ========================================
    // 1. TOTAL KEHADIRAN (SEMUA YANG ADA WAKTU_MASUK)
    // ========================================
    echo "1. TOTAL KEHADIRAN (query mainpage.php):\n";
    $sql_hadir = "SELECT COUNT(DISTINCT tanggal_absensi) as total 
                  FROM absensi 
                  WHERE user_id = ? 
                  AND waktu_masuk IS NOT NULL
                  AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql_hadir);
    $stmt->execute([$user_id, $bulan_ini]);
    $total_hadir = $stmt->fetchColumn();
    echo "   Result: $total_hadir hari\n\n";
    
    // ========================================
    // 2. BREAKDOWN DETAIL
    // ========================================
    echo "2. BREAKDOWN DETAIL:\n";
    $sql_detail = "SELECT 
                    tanggal_absensi,
                    TIME(waktu_masuk) as jam_masuk,
                    TIME(waktu_keluar) as jam_keluar,
                    menit_terlambat,
                    status_keterlambatan,
                    potongan_tunjangan,
                    CASE 
                        WHEN waktu_keluar IS NULL AND tanggal_absensi < CURDATE() THEN 'Lupa Absen Pulang'
                        WHEN waktu_keluar IS NULL THEN 'Belum Absen Keluar'
                        ELSE 'Complete'
                    END as status_kehadiran
                   FROM absensi 
                   WHERE user_id = ? 
                   AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?
                   ORDER BY tanggal_absensi ASC";
    $stmt = $pdo->prepare($sql_detail);
    $stmt->execute([$user_id, $bulan_ini]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($details as $d) {
        $icon = $d['status_kehadiran'] == 'Complete' ? '✅' : '🔴';
        printf(
            "   %s %s | Masuk: %s | Keluar: %s | Telat: %d mnt | Potongan: %s | Status: %s\n",
            $icon,
            $d['tanggal_absensi'],
            $d['jam_masuk'] ?? '-',
            $d['jam_keluar'] ?? 'NULL',
            $d['menit_terlambat'],
            $d['potongan_tunjangan'],
            $d['status_kehadiran']
        );
    }
    echo "\n";
    
    // ========================================
    // 3. TEPAT WAKTU
    // ========================================
    echo "3. TEPAT WAKTU:\n";
    $sql_tepat = "SELECT COUNT(DISTINCT tanggal_absensi) as total 
                  FROM absensi 
                  WHERE user_id = ? 
                  AND status_keterlambatan = 'tepat waktu'
                  AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql_tepat);
    $stmt->execute([$user_id, $bulan_ini]);
    $tepat_waktu = $stmt->fetchColumn();
    echo "   Result: $tepat_waktu hari\n\n";
    
    // ========================================
    // 4. LUPA ABSEN PULANG
    // ========================================
    echo "4. LUPA ABSEN PULANG:\n";
    $sql_lupa = "SELECT COUNT(*) as total
                 FROM absensi 
                 WHERE user_id = ? 
                 AND waktu_masuk IS NOT NULL 
                 AND waktu_keluar IS NULL
                 AND tanggal_absensi < CURDATE()
                 AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql_lupa);
    $stmt->execute([$user_id, $bulan_ini]);
    $lupa_absen_pulang = $stmt->fetchColumn();
    echo "   Result: $lupa_absen_pulang hari\n\n";
    
    // ========================================
    // 5. SUMMARY STATS
    // ========================================
    echo "========================================\n";
    echo "📊 SUMMARY (Expected di Mainpage):\n";
    echo "========================================\n";
    echo "Total Kehadiran: $total_hadir\n";
    echo "├─ Tepat Waktu: $tepat_waktu\n";
    echo "├─ Terlambat: " . ($total_hadir - $tepat_waktu) . "\n";
    echo "└─ Lupa Absen Pulang: $lupa_absen_pulang\n\n";
    
    // ========================================
    // 6. VERIFICATION
    // ========================================
    echo "========================================\n";
    echo "✅ VERIFICATION:\n";
    echo "========================================\n";
    
    // Hitung manual
    $complete = 0;
    $lupa = 0;
    foreach ($details as $d) {
        if ($d['status_kehadiran'] == 'Complete') {
            $complete++;
        } elseif ($d['status_kehadiran'] == 'Lupa Absen Pulang') {
            $lupa++;
        }
    }
    
    echo "Manual Count:\n";
    echo "├─ Complete: $complete\n";
    echo "├─ Lupa Absen Pulang: $lupa\n";
    echo "└─ Total: " . ($complete + $lupa) . "\n\n";
    
    if (($complete + $lupa) == $total_hadir) {
        echo "✅ Query BENAR! Total kehadiran = Complete + Lupa Absen Pulang\n";
    } else {
        echo "❌ Query SALAH! Mohon review logika\n";
    }
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
