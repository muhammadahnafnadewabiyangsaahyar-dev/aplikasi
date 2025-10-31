<?php
session_start(); 

// Penjaga Gerbang: Pastikan login & admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php'); 
    exit;
}

// Muat koneksi DB
include 'connect.php';

// Ambil semua data pengguna dari tabel 'register'
// Pilih kolom yang ingin Anda tampilkan
$sql_users = "SELECT id, nama_lengkap, username, email, role, time_created FROM register ORDER BY time_created DESC"; 
$result_users = mysqli_query($conn, $sql_users);

// Siapkan array untuk menampung data (opsional, bisa loop langsung)
$daftar_pengguna = [];
if ($result_users && mysqli_num_rows($result_users) > 0) {
    while ($row = mysqli_fetch_assoc($result_users)) {
        $daftar_pengguna[] = $row;
    }
} else if (!$result_users) {
    // Handle error jika query gagal
    echo "Error mengambil data pengguna: " . mysqli_error($conn);
}

// Jangan lupa tutup koneksi jika sudah tidak dipakai lagi di halaman ini
// mysqli_close($conn); // Tutup di akhir halaman jika masih perlu DB

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
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>] [<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>]</p>
    </div>
    <div class="content-container">
        <p class="content-text">Ini adalah halaman utama untuk admin KAORI Indonesia. Gunakan navigasi di atas untuk mengakses fitur-fitur yang tersedia.</p>
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
</html>