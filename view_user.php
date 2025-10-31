<?php
session_start();
include 'connect.php';

// Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

// Ambil semua data pengguna
$sql_select = "SELECT id, nama_lengkap, posisi, outlet, no_whatsapp, email, username, role FROM register";
$stmt_select = $pdo->prepare($sql_select);
$stmt_select->execute();
$result_select = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

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
        <?php include 'navbar.php'; ?>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result_select as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($user['posisi']); ?></td>
                        <td><?php echo htmlspecialchars($user['outlet']); ?></td>
                        <td><?php echo htmlspecialchars($user['no_whatsapp']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                    </tr>
                    <?php endforeach; ?>
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