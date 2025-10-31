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
// Tidak perlu ambil daftar cabang lagi

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
        <img class="logo" src="logo.png" alt="Logo">
        <div class="nav-links">
            <a href="<?php echo $home_url; ?>" class="home">Home</a>
            <a href="approve.php" class="surat">Surat Izin</a>
            <a href="profileadmin.php" class="profile">Profile</a>
            <a href="absen.php" class="absensi">Absensi</a>
            <a href="view_user.php" class="viewusers">Daftar Pengguna</a>
            <a href="view_absensi.php" class="viewabsensi">Daftar Absensi</a>
            <a href="rekapabsen.php" class="rekapabsen">Rekap Absensi</a>
            <a href="slipgaji.php" class="slipgaji">Slip Gaji</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    <div class="main-title">Absensi Harian</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($nama_pengguna); ?></p>
    </div>

    <div class="content-container" style="text-align: center;">
        <p>Arahkan wajah Anda ke kamera. Sistem akan memverifikasi lokasi Anda secara otomatis.</p>

        <video id="camera-preview" autoplay playsinline muted></video> <canvas id="snapshot-canvas" width="640" height="480"></canvas> <p id="status-message" class="status-message">Meminta izin akses kamera dan lokasi...</p>

        <form id="absensi-form" method="POST" action="proses_absensi.php">
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="foto_absensi_base64" id="foto_absensi_base64">
            <input type="hidden" name="tipe_absen" id="tipe_absen">

            <div class="button-group">
                <button type="button" id="btn-absen-masuk" disabled>Absen Masuk</button>
                <button type="button" id="btn-absen-keluar" disabled>Absen Keluar</button>
            </div>
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
<?php mysqli_close($conn); ?>