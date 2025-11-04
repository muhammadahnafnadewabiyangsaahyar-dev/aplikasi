<?php
/**
 * Helper Script: Calculate Status Kehadiran
 * 
 * Script ini digunakan untuk menghitung status kehadiran berdasarkan jam keluar vs jam shift
 * 
 * LOGIKA:
 * - ADMIN: Status "Hadir" jika kerja >= 8 jam, "Tidak Hadir" jika < 8 jam atau belum absen keluar
 * - USER: Status berdasarkan jam keluar vs jam shift cabang
 *   - "Hadir" jika waktu_keluar >= jam_keluar_shift
 *   - "Tidak Hadir" jika waktu_keluar < jam_keluar_shift
 *   - "Tidak Hadir" jika belum absen keluar (waktu_keluar NULL)
 * 
 * Script ini bisa dipanggil:
 * 1. Via cron job (setiap hari untuk update status kehadiran)
 * 2. Di dalam view_absensi.php atau rekapabsen.php (real-time calculation)
 */

// NOTE: Tidak perlu require_once 'connect.php' di sini
// karena file ini di-include setelah connect.php sudah dimuat di file utama

/**
 * Hitung status kehadiran untuk satu record absensi
 * 
 * @param array $absensi_record - Record absensi dengan field: waktu_masuk, waktu_keluar, user_id, tanggal_absensi
 * @param PDO $pdo - Database connection
 * @return string - Status kehadiran: "Hadir", "Tidak Hadir", "Belum Absen Keluar", "Lupa Absen Pulang"
 */
function hitungStatusKehadiran($absensi_record, $pdo) {
    // === DETEKSI LUPA ABSEN PULANG ===
    // Jika belum absen keluar, cek apakah sudah melewati 23:59 hari itu
    if (empty($absensi_record['waktu_keluar'])) {
        $tanggal_absensi = $absensi_record['tanggal_absensi'];
        $today = date('Y-m-d');
        
        // Jika tanggal absensi < hari ini (sudah melewati 23:59), berarti lupa absen pulang
        if ($tanggal_absensi < $today) {
            return 'Lupa Absen Pulang';
        }
        
        // Jika masih hari ini, status masih "Belum Absen Keluar"
        return 'Belum Absen Keluar';
    }
    
    // Ambil role user dari tabel register
    $stmt_user = $pdo->prepare("SELECT role FROM register WHERE id = ?");
    $stmt_user->execute([$absensi_record['user_id']]);
    $user = $stmt_user->fetch();
    $is_admin = ($user && $user['role'] === 'admin');
    
    if ($is_admin) {
        // ========================================================
        // LOGIKA ADMIN: Minimal 8 jam kerja untuk status "Hadir"
        // ========================================================
        $waktu_masuk = strtotime($absensi_record['waktu_masuk']);
        $waktu_keluar = strtotime($absensi_record['waktu_keluar']);
        $durasi_kerja_detik = $waktu_keluar - $waktu_masuk;
        $durasi_kerja_jam = $durasi_kerja_detik / 3600;
        
        // Minimal 8 jam kerja
        if ($durasi_kerja_jam >= 8) {
            return 'Hadir';
        } else {
            return 'Tidak Hadir';
        }
        
    } else {
        // ========================================================
        // LOGIKA USER: Berdasarkan jam keluar vs jam shift
        // ========================================================
        
        // Ambil jam shift dari cabang (gunakan shift pertama sebagai default jika tidak ada cabang_id di absensi)
        $stmt_shift = $pdo->prepare("SELECT jam_keluar FROM cabang LIMIT 1");
        $stmt_shift->execute();
        $shift = $stmt_shift->fetch();
        
        if (!$shift) {
            // Fallback jika tidak ada data shift
            return 'Data Shift Tidak Ditemukan';
        }
        
        $jam_keluar_shift = $shift['jam_keluar'];
        
        // Bandingkan waktu keluar dengan jam shift
        $waktu_keluar = date('H:i:s', strtotime($absensi_record['waktu_keluar']));
        
        if ($waktu_keluar >= $jam_keluar_shift) {
            return 'Hadir';
        } else {
            return 'Tidak Hadir';
        }
    }
}

/**
 * Update status kehadiran untuk semua absensi di database
 * Gunakan ini untuk batch update (contoh: via cron job)
 * 
 * @param PDO $pdo - Database connection
 * @param string $tanggal - Tanggal untuk update (format: Y-m-d). Default: hari ini
 * @return array - Hasil update dengan count success/failed
 */
function updateAllStatusKehadiran($pdo, $tanggal = null) {
    if ($tanggal === null) {
        $tanggal = date('Y-m-d');
    }
    
    // Ambil semua absensi untuk tanggal tersebut
    $stmt = $pdo->prepare("SELECT id, user_id, waktu_masuk, waktu_keluar, tanggal_absensi FROM absensi WHERE DATE(tanggal_absensi) = ?");
    $stmt->execute([$tanggal]);
    $absensi_list = $stmt->fetchAll();
    
    $success_count = 0;
    $failed_count = 0;
    
    foreach ($absensi_list as $absensi) {
        $status_kehadiran = hitungStatusKehadiran($absensi, $pdo);
        
        // Update ke database (HANYA jika ada kolom status_kehadiran)
        // Jika belum ada kolom, skip update
        try {
            $stmt_update = $pdo->prepare("UPDATE absensi SET status_kehadiran = ? WHERE id = ?");
            $stmt_update->execute([$status_kehadiran, $absensi['id']]);
            $success_count++;
        } catch (PDOException $e) {
            // Kolom status_kehadiran mungkin belum ada
            $failed_count++;
        }
    }
    
    return [
        'success' => $success_count,
        'failed' => $failed_count,
        'tanggal' => $tanggal
    ];
}

// ========================================================
// CLI Execution (jika script dipanggil langsung via command line)
// Gunakan __FILE__ dan get_included_files() untuk deteksi
// ========================================================
if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    // Script dipanggil langsung via CLI, bukan di-include
    // Load connect.php hanya untuk CLI mode
    if (!isset($pdo)) {
        require_once 'connect.php';
    }
    
    // Ambil tanggal dari argument atau gunakan hari ini
    $tanggal = $argv[1] ?? date('Y-m-d');
    
    echo "Updating status kehadiran untuk tanggal: $tanggal\n";
    
    $result = updateAllStatusKehadiran($pdo, $tanggal);
    
    echo "Success: {$result['success']}, Failed: {$result['failed']}\n";
    echo "Done!\n";
}
// NOTE: Closing tag dihilangkan untuk mencegah whitespace output (PSR standard)