<?php
/**
 * API untuk mengambil data izin/sakit yang disetujui
 * Digunakan oleh kalender untuk menampilkan badge izin/sakit
 */

header('Content-Type: application/json');
session_start();
require_once 'connect.php';

// Cek authorization
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'message' => 'User not logged in']);
    exit;
}

// Ambil parameters
$user_id = $_GET['user_id'] ?? null;
$start_date = $_GET['start'] ?? null;
$end_date = $_GET['end'] ?? null;

// Validasi parameters
if (!$user_id || !$start_date || !$end_date) {
    echo json_encode([
        'error' => 'Missing parameters',
        'message' => 'Required: user_id, start, end'
    ]);
    exit;
}

// Security: Admin bisa lihat semua, user biasa hanya bisa lihat milik sendiri
$is_admin = ($_SESSION['role'] === 'admin');
if (!$is_admin && $_SESSION['user_id'] != $user_id) {
    echo json_encode([
        'error' => 'Forbidden',
        'message' => 'You can only view your own data'
    ]);
    exit;
}

try {
    // Query untuk mengambil izin/sakit yang disetujui dalam range tanggal
    $query = "SELECT 
                p.id,
                p.user_id,
                p.perihal,
                p.tanggal_mulai,
                p.tanggal_selesai,
                p.lama_izin,
                p.alasan,
                p.status,
                r.nama_lengkap,
                r.posisi
              FROM pengajuan_izin p
              JOIN register r ON p.user_id = r.id
              WHERE p.user_id = ?
              AND p.status = 'Diterima'
              AND (
                  (p.tanggal_mulai BETWEEN ? AND ?) OR
                  (p.tanggal_selesai BETWEEN ? AND ?) OR
                  (p.tanggal_mulai <= ? AND p.tanggal_selesai >= ?)
              )
              ORDER BY p.tanggal_mulai";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $user_id,
        $start_date, $end_date,
        $start_date, $end_date,
        $start_date, $end_date
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Expand data ke per-hari untuk memudahkan rendering di kalender
    $expanded_results = [];
    
    foreach ($results as $izin) {
        $start = new DateTime($izin['tanggal_mulai']);
        $end = new DateTime($izin['tanggal_selesai']);
        $end->modify('+1 day'); // Include end date
        
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);
        
        foreach ($period as $date) {
            // Skip Sunday
            if ($date->format('N') == 7) continue;
            
            $date_key = $date->format('Y-m-d');
            
            $expanded_results[] = [
                'id' => $izin['id'],
                'date' => $date_key,
                'type' => $izin['perihal'], // 'Izin' or 'Sakit'
                'reason' => $izin['alasan'],
                'user_name' => $izin['nama_lengkap'],
                'user_position' => $izin['posisi'],
                'start_date' => $izin['tanggal_mulai'],
                'end_date' => $izin['tanggal_selesai'],
                'duration' => $izin['lama_izin'],
                'status' => $izin['status']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $expanded_results,
        'count' => count($expanded_results)
    ]);

} catch (PDOException $e) {
    error_log("API Izin/Sakit Error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database error',
        'message' => 'Failed to fetch data'
    ]);
}
?>
