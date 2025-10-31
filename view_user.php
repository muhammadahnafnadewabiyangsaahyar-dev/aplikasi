<?php
session_start();
include 'connect.php';

// Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

// Ambil semua data pengguna (kecuali mungkin admin super?)
// Untuk saat ini, kita ambil semua
$sql_select = "SELECT id, nama_lengkap, posisi, outlet, no_whatsapp, email, username, role 
               FROM register";
$result_select = mysqli_query($conn, $sql_select);

if (!$result_select) {
    die("Error mengambil data: " . mysqli_error($conn));
}

$home_url = 'mainpageadmin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Admin</title>
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
        <h2>Manajemen Pengguna</h2>
        <p>Di bawah ini adalah daftar semua pengguna yang terdaftar di sistem.</p>
        
        <?php if(isset($_GET['status']) && $_GET['status'] == 'delete_success'): ?>
            <p style="color: green; font-weight: bold;">Pengguna berhasil dihapus.</p>
        <?php endif; ?>
        <?php if(isset($_GET['error']) && $_GET['error'] == 'self_delete'): ?>
            <p style="color: red; font-weight: bold;">Error: Anda tidak bisa menghapus akun Anda sendiri.</p>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Posisi</th>
                        <th>Outlet</th>
                        <th>No. WhatsApp</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_select) > 0): ?>
                        <?php while ($data = mysqli_fetch_assoc($result_select)): ?>
                            <tr>
                                <td><?php echo $data['id']; ?></td>
                                <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($data['posisi']); ?></td>
                                <td><?php echo htmlspecialchars($data['outlet']); ?></td>
                                <td><?php echo htmlspecialchars($data['no_whatsapp']); ?></td>
                                <td><?php echo htmlspecialchars($data['email']); ?></td>
                                <td><?php echo htmlspecialchars($data['username']); ?></td>
                                <td><?php echo htmlspecialchars($data['role']); ?></td>
                                
                                <td class="action-buttons">
                                    <a href="edit_user.php?id=<?php echo $data['id']; ?>" class="btn-edit">Edit</a>
                                    
                                    <form action="delete_user.php" method="POST" style="display:inline;" 
                                          onsubmit="return confirm('Anda yakin ingin menghapus pengguna: <?php echo htmlspecialchars(addslashes($data['nama_lengkap'])); ?>?');">
                                          
                                        <input type="hidden" name="user_id" value="<?php echo $data['id']; ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                    </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">Tidak ada data pengguna.</td>
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
mysqli_close($conn);
?>