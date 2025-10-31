<?php
session_start();

// 1. PENJAGA GERBANG: Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=notloggedin');
    exit;
}
$user_id = $_SESSION['user_id'];
$nama_pengguna = $_SESSION['nama_lengkap'] ?? $_SESSION['username']; // Ambil nama untuk sapaan

// 2. Muat Koneksi (jika diperlukan untuk data lain di halaman ini, misal header dinamis)
include 'connect.php'; 
include 'absen_helper.php';

// Cek status absen hari ini
$absen_status = getAbsenStatusToday($pdo, $user_id);
$tipe_default = 'masuk';
$label_default = 'Absen Masuk';
if ($absen_status['masuk'] && !$absen_status['keluar']) {
    $tipe_default = 'keluar';
    $label_default = 'Absen Keluar';
} elseif ($absen_status['masuk'] && $absen_status['keluar']) {
    $tipe_default = 'done';
    $label_default = 'Absensi Selesai';
}

$home_url = ($_SESSION['role'] ?? '') === 'admin' ? 'mainpageadmin.php' : 'mainpageuser.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Harian</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <div class="headercontainer">
        <?php include 'navbar.php'; ?>
    </div>
    <div class="main-title">Absensi Harian</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($nama_pengguna); ?></p>
    </div>

    <div class="content-container" style="text-align: center;">
        <p>Arahkan wajah Anda ke kamera. Sistem akan memverifikasi lokasi Anda secara otomatis.</p>

<video id="kamera-preview" autoplay playsinline muted style="border: 1px solid #ccc;"></video>
<canvas id="kamera-canvas" width="640" height="480" style="display: none; border: 1px solid #ccc;"></canvas>

<p id="status-lokasi" class="status-message" style="color: orange;">Meminta izin akses lokasi...</p>

        <form id="form-absensi" method="POST" action="proses_absensi.php">
            <input type="hidden" name="latitude" id="input-latitude">
            <input type="hidden" name="longitude" id="input-longitude">
            <input type="hidden" name="foto_absensi_base64" id="input-foto-base64">
            <input type="hidden" name="tipe_absen" id="input-tipe-absen">

            <?php if ($tipe_default !== 'done'): ?>
                <button type="button" id="btn-absen" data-tipe="<?php echo $tipe_default; ?>" disabled><?php echo $label_default; ?></button>
            <?php else: ?>
                <button type="button" id="btn-absen" disabled><?php echo $label_default; ?></button>
            <?php endif; ?>
        </form>
         <?php if(isset($_GET['error'])): ?>
            <p class="error-message">Error: <?php echo htmlspecialchars($_GET['error']); ?> 
            <?php if(isset($_GET['msg'])) echo '- ' . htmlspecialchars($_GET['msg']); ?>
            <?php if(isset($_GET['code'])) echo '(Code: ' . htmlspecialchars($_GET['code']) . ')'; ?>
            </p>
        <?php elseif(isset($_GET['status'])): ?>
             <p class="success-message">Status: <?php echo htmlspecialchars($_GET['status']); ?></p>
        <?php endif; ?>
    </div>

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
</body>
<script src="script_absen.js" defer></script>
</html>