<?php
session_start();
// Muat koneksi DB (Ini sekarang membuat variabel $pdo)
include 'connect.php'; 

// Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

// ========================================================
// --- BLOK PERBAIKAN: Gunakan PDO untuk mengambil data ---
// ========================================================
$daftar_izin = []; // Inisialisasi array

try {
    // Ambil data izin yang masih 'Pending'
    $sql_select = "SELECT p.id, r.nama_lengkap, p.perihal, p.tanggal_mulai, p.tanggal_selesai, p.lama_izin, p.alasan, p.file_surat, p.tanda_tangan_file 
                   FROM pengajuan_izin p
                   JOIN register r ON p.user_id = r.id
                   WHERE p.status = 'Pending'
                   ORDER BY p.tanggal_pengajuan ASC";

    // 1. Gunakan $pdo->query() untuk eksekusi
    $stmt = $pdo->query($sql_select);

    // 2. Ambil semua data sekaligus ke dalam array
    $daftar_izin = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Tangani error jika query gagal
    die("Error mengambil data: " . $e->getMessage());
}
// ========================================================

$home_url = 'mainpageadmin.php'; // Admin pasti ke mainpageadmin
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Surat Izin - Admin</title>
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
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>]</p>
    </div>

    <div class="content-container">
        <h2>Persetujuan Surat Izin</h2>
        <p>Di bawah ini adalah daftar pengajuan surat izin yang menunggu persetujuan.</p>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Perihal</th>
                        <th>Tanggal Izin</th>
                        <th>Lama (Hari)</th>
                        <th>Alasan</th>
                        <th>File Surat (DOCX)</th>
                        <th>Tanda Tangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_izin)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Tidak ada pengajuan surat izin yang menunggu persetujuan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_izin as $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($data['perihal']); ?></td>
                                <td><?php echo htmlspecialchars($data['tanggal_mulai'] . ' s/d ' . $data['tanggal_selesai']); ?></td>
                                <td><?php echo htmlspecialchars($data['lama_izin']); ?></td>
                                <td style="max-width: 200px; white-space: pre-wrap;"><?php echo htmlspecialchars($data['alasan']); ?></td>
                                <td>
                                    <a href="surat_izin/<?php echo htmlspecialchars($data['file_surat']); ?>" class="link-surat" download>
                                        Download Surat
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($data['tanda_tangan_file'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($data['tanda_tangan_file']); ?>" alt="TTD" style="max-width: 100px; border: 1px solid #ccc;">
                                    <?php else: ?>
                                        (Tidak ada TTD)
                                    <?php endif; ?>
                                </td>
                                
                                <td class="action-buttons">
                                    <form action="proses_approve.php" method="POST" style="display: block;">
                                        <input type="hidden" name="izin_id" value="<?php echo $data['id']; ?>">
                                        <input type="hidden" name="status" value="Diterima">
                                        <button type="submit" class="btn-approve">Setujui</button>
                                    </form>
                                    <form action="proses_approve.php" method="POST" style="display: block; margin-top: 5px;">
                                        <input type="hidden" name="izin_id" value="<?php echo $data['id']; ?>">
                                        <input type="hidden" name="status" value="Ditolak">
                                        <button type="submit" class="btn-reject">Tolak</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
            </table>
        </div>
    </div>

</body>
<footer>
    <div class="footer-container">
        <p class="footer-text">© 2024 KAORI Indonesia. All rights reserved.</p>
    </div>
</footer>
</html>
<?php
// 4. Hapus mysqli_close($conn); karena PDO tidak membutuhkannya
?>