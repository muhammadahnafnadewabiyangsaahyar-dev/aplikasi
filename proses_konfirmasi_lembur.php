<?php
session_start();
include 'connect.php';

// 1. Keamanan: Pastikan admin yang login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika bukan admin, tendang
    header('Location: index.php?error=unauthorized'); 
    exit;
}

// 2. Proses hanya jika metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 3. Validasi Input
    if (!isset($_POST['absen_id']) || !filter_var($_POST['absen_id'], FILTER_VALIDATE_INT) || !isset($_POST['action'])) {
        header('Location: approve_lembur.php?error=invalid_input');
        exit;
    }
    $absen_id = (int)$_POST['absen_id'];
    $action = $_POST['action']; // 'approve' atau 'reject'
    $new_status_lembur = '';
    $pesan_sukses = '';

    // Tentukan status baru berdasarkan aksi
    if ($action == 'approve') {
        $new_status_lembur = 'Approved';
        $pesan_sukses = 'lembur_disetujui';
    } elseif ($action == 'reject') {
        $new_status_lembur = 'Rejected';
        $pesan_sukses = 'lembur_ditolak';
    } else {
        header('Location: approve_lembur.php?error=invalid_action');
        exit;
    }

    // 4. Update status lembur di database (hanya jika status masih Pending)
    $sql_update = "UPDATE absensi SET status_lembur = ? WHERE id = ? AND status_lembur = 'Pending'";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$new_status_lembur, $absen_id]);

    // Arahkan kembali ke halaman approve lembur dengan pesan status
    header('Location: approve_lembur.php?status=' . $pesan_sukses);
    exit;

} else {
    header('Location: approve_lembur.php?error=invalid_method');
    exit;
}
?>