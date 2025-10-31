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
        header('Location: approve_lembur.php?error=datakosong');
        exit;
    }
    
    $absen_id = (int)$_POST['absen_id'];
    $action = $_POST['action']; // 'approve' atau 'reject'
    $new_status_lembur = '';
    $pesan_sukses = '';

    // Tentukan status baru berdasarkan aksi
    if ($action == 'approve') {
        $new_status_lembur = 'Approved'; // (Pastikan 'Approved' ada di ENUM Anda)
        $pesan_sukses = 'lembur_disetujui';
    } elseif ($action == 'reject') {
        $new_status_lembur = 'Rejected'; // (Pastikan 'Rejected' ada di ENUM Anda)
        $pesan_sukses = 'lembur_ditolak';
    } else {
        header('Location: approve_lembur.php?error=aksitidakvalid');
        exit;
    }

    // 4. Update status lembur di database
    // Update hanya jika statusnya masih 'Pending' untuk mencegah aksi ganda
    $sql_update = "UPDATE absensi SET status_lembur = ? WHERE id = ? AND status_lembur = 'Pending'";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    
    if ($stmt_update) {
        mysqli_stmt_bind_param($stmt_update, "si", $new_status_lembur, $absen_id);
        
        if (mysqli_stmt_execute($stmt_update)) {
            // Cek apakah ada baris yang benar-benar terupdate
            if (mysqli_stmt_affected_rows($stmt_update) > 0) {
                // Berhasil update
                $pesan_status = 'status=' . $pesan_sukses;
            } else {
                // Tidak ada baris yang terupdate (mungkin sudah diproses orang lain)
                $pesan_status = 'error=sudahdiproses';
            }
        } else {
            // Gagal eksekusi
            $pesan_status = 'error=dbupdate&msg=' . urlencode(mysqli_stmt_error($stmt_update));
        }
        mysqli_stmt_close($stmt_update);
    } else {
        // Gagal prepare
        $pesan_status = 'error=dbprepare&msg=' . urlencode(mysqli_error($conn));
    }
    
    mysqli_close($conn);
    // Arahkan kembali ke halaman approve lembur dengan pesan status
    header('Location: approve_lembur.php?' . $pesan_status);
    exit;

} else {
    // Jika bukan metode POST
    header('Location: approve_lembur.php?error=invalidmethod');
    exit;
}
?>