<?php
session_start();
include 'connect.php'; // Pastikan nama file koneksi Anda benar

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=notloggedin');
    exit;
}
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$nama_pengguna = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];

// 2. Siapkan variabel untuk hasil query
$daftar_absensi = [];
$sql = ""; // Inisialisasi string SQL

// 3. Tentukan Kueri Berdasarkan Peran
if ($user_role == 'admin') {
    // Admin: Ambil semua data absensi + nama pengguna
    $sql = "SELECT a.*, r.nama_lengkap FROM absensi a JOIN register r ON a.user_id = r.id ORDER BY a.tanggal_absensi DESC, a.waktu_masuk DESC";
    $stmt = $pdo->query($sql);
    $daftar_absensi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // User Biasa: Ambil hanya data absensi milik sendiri (Gunakan Prepared Statement)
    $sql = "SELECT * FROM absensi WHERE user_id = ? ORDER BY tanggal_absensi DESC, waktu_masuk DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $daftar_absensi = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Tidak perlu tutup $pdo di sini
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Rekap Absensi</title>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($nama_pengguna); ?> [<?php echo htmlspecialchars($user_role); ?>]</p>
    </div>
    <div class="content-container">
        <h2>Rekap Absensi</h2>
        <table class="rekap-table">
            <thead>
                <tr>
                    <?php if ($user_role == 'admin'): ?>
                        <th>Nama Pengguna</th>
                    <?php endif; ?>
                    <th>Tanggal Absensi</th>
                    <th>Waktu Masuk</th>
                    <th>Waktu Keluar</th>
                    <th>Status Lokasi</th>
                    <th>Foto Absen</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_absensi)): ?>
                    <tr>
                        <td colspan="<?php echo ($user_role == 'admin') ? '6' : '5'; ?>">Tidak ada data absensi.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_absensi as $absen): ?>
                        <tr>
                            <?php if ($user_role == 'admin'): ?>
                                <td><?php echo htmlspecialchars($absen['nama_lengkap']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($absen['tanggal_absensi']); ?></td>
                            <td><?php echo htmlspecialchars($absen['waktu_masuk']); ?></td>
                            <td><?php echo htmlspecialchars($absen['waktu_keluar'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($absen['status_lokasi']); ?></td>
                            <td>
                                <?php if (!empty($absen['foto_absen'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($absen['foto_absen']); ?>" alt="Foto Absen" class="foto-absen">
                                <?php endif; ?>
                                <td>
                                    <?php if (!empty($absen['foto_absen'])): 
                                    $foto = htmlspecialchars($absen['foto_absen']);
                                    if (strpos($foto, 'absen_keluar_') === 0) {
                                        $path_foto = 'uploads/absen_keluar/' . $foto;
                                    } else {
                                        $path_foto = 'uploads/absen_masuk/' . $foto;
                                    }
                                    if (file_exists($path_foto)): // Cek apakah file benar-benar ada
                                    ?>
                                        <img src="<?php echo $path_foto; ?>" alt="Foto Absen" class="foto-absen" style="max-width: 50px; height: auto;"> <?php else: ?>
                                        (File tidak ditemukan)
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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