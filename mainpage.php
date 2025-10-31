<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
include 'connect.php';

$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$daftar_pengguna = [];
if ($is_admin) {
    try {
        $sql_users = "SELECT id, nama_lengkap, username, email, role, time_created FROM register ORDER BY time_created DESC";
        $stmt = $pdo->query($sql_users);
        $daftar_pengguna = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_pengguna = "Error mengambil data pengguna: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Document</title>
</head>
<body>
    <div class="headercontainer">
        <img class="logo" src="logo.png" alt="Logo">
        <?php include 'navbar.php'; ?>
    </div>
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>][<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>]</p>
    </div>
    <div class="content-container">
        <p class="content-text">Ini adalah halaman utama KAORI Indonesia. Gunakan navigasi di atas untuk mengakses fitur-fitur yang tersedia.</p>
    </div>
</body>
<footer>
    <div class="footer-container">
        <p class="footer-text">© 2024 KAORI Indonesia. All rights reserved.</p>
        <p class="footer-text">Follow us on:</p>
        <div class="social-icons">
            <i class="fa fa-brands fa-facebook-f footer-link"></i>
            <i class="fa fa-brands fa-twitter footer-link"></i>
            <i class="fa fa-brands fa-instagram footer-link"></i>
        </div>
    </div>
</footer>
</html>