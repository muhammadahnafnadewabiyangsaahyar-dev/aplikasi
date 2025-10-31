<?php
session_start();
include 'connect.php'; // Sertakan file koneksi database Anda
// Pastikan hanya admin yang dapat mengakses halaman ini
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php'); 
    exit;
}
$home_url = 'mainpageadmin.php';
$sql_absensi = "SELECT a.id, a.tanggal_absensi, a.waktu_masuk, a.waktu_keluar, a.status_lokasi, a.foto_absen, r.nama_lengkap 
                FROM absensi a 
                JOIN register r ON a.user_id = r.id 
                ORDER BY a.tanggal_absensi DESC, a.waktu_masuk DESC";
$stmt_absensi = $pdo->prepare($sql_absensi);
$stmt_absensi->execute();
$daftar_absensi = $stmt_absensi->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Daftar Absensi</title>
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
 <div class="main-title">Daftar Absensi</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>] [<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>]</p>
    </div>
    <div class="table-container" style="margin-top: 30px;">
        <h2 class="table-title">Daftar Absensi Pengguna</h2>
        <?php if (!empty($daftar_absensi)): ?>
            <table class="user-table"> <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Tanggal Absensi</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th>Status Lokasi</th>
                        <th>Foto Absen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_absensi as $absensi): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($absensi['id']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['tanggal_absensi']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['waktu_masuk']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['waktu_keluar']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['status_lokasi']); ?></td>
                        <td>
                            <?php if (!empty($absensi['foto_absen'])): ?>
                                <img src="<?php echo 'uploads/' . htmlspecialchars($absensi['foto_absen']); ?>" alt="Foto Absen" style="max-width: 100px; max-height: 100px;">
                            <?php else: ?>
                                Tidak ada foto
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Belum ada data absensi.</p>
        <?php endif; ?>
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