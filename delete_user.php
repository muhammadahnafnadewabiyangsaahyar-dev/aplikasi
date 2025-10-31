<?php
session_start();
include 'connect.php';

// ========================================================
// --- BLOK PERBAIKAN: Keamanan CSRF & Validasi ---
// ========================================================

// 1. Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    // Redirect jika bukan admin
    header('Location: index.php?error=unauthorized');
    exit;
}

// 2. Keamanan CSRF: Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect jika metode salah (misal: akses via GET)
    header('Location: view_user.php?error=invalid_method');
    exit;
}

// 3. Ambil data dari POST dan validasi
if (!isset($_POST['user_id']) || !filter_var($_POST['user_id'], FILTER_VALIDATE_INT)) {
    // Redirect jika ID tidak valid
    header('Location: view_user.php?error=invalid_id');
    exit;
}
$user_id_to_delete = (int)$_POST['user_id'];
$admin_id = (int)$_SESSION['user_id'];

// 4. Bonus Keamanan Logika: Jangan biarkan admin menghapus dirinya sendiri
if ($user_id_to_delete == $admin_id) {
    header("Location: view_user.php?error=self_delete");
    exit;
}

// 5. Lakukan penghapusan menggunakan prepared statement (Kode asli Anda sudah aman di sini)
$sql_delete = "DELETE FROM register WHERE id = ?";
$stmt_delete = mysqli_prepare($conn, $sql_delete);

if ($stmt_delete) {
    mysqli_stmt_bind_param($stmt_delete, "i", $user_id_to_delete);
    mysqli_stmt_execute($stmt_delete);
    
    // Cek apakah ada baris yang terhapus
    if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
        header("Location: view_user.php?status=delete_success");
    } else {
        header("Location: view_user.php?error=user_not_found");
    }
    mysqli_stmt_close($stmt_delete);
    
} else {
    // Gagal prepare statement
    header("Location: view_user.php?error=db_error");
}

mysqli_close($conn);
exit;

// ========================================================
// --- AKHIR BLOK PERBAIKAN ---
// ========================================================
?>