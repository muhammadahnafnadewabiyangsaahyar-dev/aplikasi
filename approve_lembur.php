<?php
session_start();
include 'connect.php';

// 1. Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

// ========================================================
// --- BLOK PERBAIKAN: Gunakan POST untuk Keamanan CSRF ---
// ========================================================
// Proses aksi (approve/reject) HANYA jika metodenya POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['action']) && isset($_POST['absen_id'])) {
        $action = $_POST['action'];
        $absen_id = (int)$_POST['absen_id'];

        $new_status = '';
        if ($action == 'approve') {
            $new_status = 'Approved';
        } elseif ($action == 'reject') {
            $new_status = 'Rejected';
        }

        // Pastikan status valid dan gunakan prepared statement
        if (!empty($new_status)) {
            $sql_update = "UPDATE absensi SET status_lembur = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            
            if ($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, "si", $new_status, $absen_id);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
            }
            
            // Redirect kembali ke halaman ini (PRG Pattern)
            // untuk mencegah re-submit form jika user me-refresh halaman
            header("Location: approve_lembur.php?status=success");
            exit;
        }
    }
}
// ========================================================
// --- AKHIR BLOK PERBAIKAN ---
// ========================================================


// Ambil data lembur yang masih 'Pending' untuk ditampilkan
// (JOIN dengan tabel register untuk mendapatkan nama_lengkap)
$sql_select = "SELECT a.id, r.nama_lengkap, a.tanggal_absensi, a.waktu_masuk, a.waktu_keluar 
               FROM absensi a 
               JOIN register r ON a.user_id = r.id 
               WHERE a.status_lembur = 'Pending'
               ORDER BY a.tanggal_absensi DESC";

$result_select = mysqli_query($conn, $sql_select);

if (!$result_select) {
    die("Error mengambil data: " . mysqli_error($conn));
}

$home_url = ($_SESSION['role'] == 'admin') ? 'mainpageadmin.php' : 'mainpageuser.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Lembur - Admin</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <div class="headercontainer">
        <img class="logo" src="logo.png" alt="Logo">
        <div class="nav-links">
            <a href="<?php echo $home_url; ?>" class="home">Home</a>
            <a href="approve.php" class="surat">Surat Izin</a>
            <a href="approve_lembur.php" class="lembur">Approve Lembur</a>
            <a href="view_user.php" class="viewusers">Daftar Pengguna</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>]</p>
    </div>

    <div class="content-container">
        <h2>Persetujuan Lembur (Overwork)</h2>
        <p>Di bawah ini adalah daftar pengajuan lembur yang menunggu persetujuan Anda.</p>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Tanggal Absen</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_select) > 0): ?>
                        <?php while ($data = mysqli_fetch_assoc($result_select)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($data['tanggal_absensi']); ?></td>
                                <td><?php echo htmlspecialchars(date('H:i:s', strtotime($data['waktu_masuk']))); ?></td>
                                <td><?php echo htmlspecialchars(date('H:i:s', strtotime($data['waktu_keluar']))); ?></td>
                                
                                <td class="action-buttons">
                                    <form action="approve_lembur.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="absen_id" value="<?php echo $data['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-approve">Approve</button>
                                    </form>
                                    <form action="approve_lembur.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="absen_id" value="<?php echo $data['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-reject">Reject</button>
                                    </form>
                                </td>
                                </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Tidak ada pengajuan lembur yang menunggu persetujuan.</td>
                        </tr>
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
// Tutup koneksi di akhir
mysqli_close($conn);
?>