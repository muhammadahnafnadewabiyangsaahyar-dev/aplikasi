<?php
session_start();
include 'connect.php'; // Menggunakan file connect.php baru (PDO)

// 1. Hanya proses jika method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 2. Validasi input
    if (empty($username) || empty($password)) {
        // Alihkan kembali dengan error, jangan gunakan die()
        header('Location: index.php?error=emptyfields');
        exit();
    }

    try {
        // 3. Cek apakah username ada di database
        $sql = "SELECT * FROM register WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $row = $stmt->fetch(); // Gunakan fetch() karena username itu unik

        // 4. Verifikasi User dan Password
        if ($row && password_verify($password, $row['password'])) {
            // Login berhasil
            
            // --- PERBAIKAN KEAMANAN: Session Fixation ---
            // Regenerasi ID session setelah login berhasil
            session_regenerate_id(true);
            // ---------------------------------------------

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];

            // 5. Redirect berdasarkan role
            if ($_SESSION['role'] == 'admin') {
                header("Location: mainpageadmin.php");
            } else {
                header("Location: mainpageuser.php");
            }
            exit();

        } elseif ($row) {
            // Username ditemukan, tapi password salah
            header('Location: index.php?error=invalidpassword');
            exit();
        } else {
            // Username tidak ditemukan
            header('Location: index.php?error=usernotfound');
            exit();
        }

    } catch (PDOException $e) {
        // Tangani error database
        error_log("Login Gagal: " . $e->getMessage());
        header('Location: index.php?error=dberror');
        exit();
    }

} else {
    // Jika ada yang mencoba mengakses login.php secara langsung
    header('Location: index.php');
    exit();
}
?>