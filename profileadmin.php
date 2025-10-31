<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}


include 'connect.php';

$user_id = $_SESSION['user_id']; 

$sql = "SELECT nama_lengkap, posisi, outlet, email, foto_profil FROM register WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id); 
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
mysqli_close($conn);

if (empty($user['foto_profil'])) {
    $path_foto = 'user.png'; 
} else {
    $path_foto = 'uploads/' . htmlspecialchars($user['foto_profil']);
}

if ($_SESSION['role'] == 'admin') {
    $home_url = 'mainpageadmin.php';
} else {
    $home_url = 'mainpageuser.php';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="styleprofile.css">
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
</div>
    <div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
        <img src="<?php echo $path_foto; ?>" alt="User Profile Picture" class="profile-picture">
          <form action="upload_foto.php" method="POST" enctype="multipart/form-data">
            <label for="foto">Ganti Foto Profil:</label>
            <input type="file" name="foto_profil" id="foto" required>
            <button type="submit">Upload</button>
            </form>
        </div>
        <div class="profile-info-box profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
            <div class="profile-detail">Jabatan: <?php echo htmlspecialchars($user['posisi']); ?></div>
            <div class="profile-detail">Outlet: <?php echo htmlspecialchars($user['outlet']); ?></div>
            <div class="profile-email">Email: <?php echo htmlspecialchars($user['email']); ?></div>
        </div>
    </div>
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