<?php
session_start(); 

// Penjaga Gerbang: Pastikan login & admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php'); 
    exit;
}

// Muat koneksi DB (Ini sekarang membuat variabel $pdo)
include 'connect.php';

// ========================================================
// --- BLOK PERBAIKAN: Gunakan PDO untuk mengambil data ---
// ========================================================
$daftar_pengguna = []; // Inisialisasi array

try {
    // Ambil semua data pengguna dari tabel 'register'
    $sql_users = "SELECT id, nama_lengkap, username, email, role, time_created FROM register ORDER BY time_created DESC"; 
    
    // 1. Gunakan $pdo->query() untuk eksekusi
    $stmt = $pdo->query($sql_users);
    
    // 2. Ambil semua data sekaligus ke dalam array
    $daftar_pengguna = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle error jika query gagal
    echo "Error mengambil data pengguna: " . $e->getMessage();
}
// =Note: $daftar_pengguna sekarang sudah berisi data, siap untuk di-loop di HTML
// ========================================================

// Variabel $home_url (sebelumnya tidak terdefinisi, ditambahkan)
$home_url = ($_SESSION['role'] == 'admin') ? 'mainpageadmin.php' : 'mainpageuser.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Admin Dashboard - Teman KAORI</title>
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
        
        <h2 style="margin-top: 30px;">Daftar Pengguna Terdaftar</h2>
        <div style="overflow-x: auto;">
            <table class="rekap-table"> <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_pengguna)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Belum ada pengguna yang terdaftar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_pengguna as $pengguna): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pengguna['id']); ?></td>
                                <td><?php echo htmlspecialchars($pengguna['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($pengguna['username']); ?></td>
                                <td><?php echo htmlspecialchars($pengguna['email']); ?></td>
                                <td><?php echo htmlspecialchars($pengguna['role']); ?></td>
                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($pengguna['time_created']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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