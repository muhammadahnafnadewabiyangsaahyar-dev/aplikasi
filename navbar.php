<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<?php

//dinamisasi navbar
$home_url = 'index.php';
$profile_url = 'profile.php';
$surat_url = 'suratizin.php';
$absen_url = 'absen.php';
$rekapabsen_url = 'rekapabsen.php';
$slipgaji_url = 'slipgaji.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        // Jika ADMIN
        $mainpage_url = 'mainpage.php';
        $profile_url = 'profile.php';
        $approvesurat_url = 'approve.php';
        $approvelembur_url = 'approve_lembur.php';
        $view_user_url = 'view_user.php';
        $view_absensi_url = 'view_absensi.php';
    } else {
        // Jika USER BIASA
        $mainpage_url = 'mainpage.php';
        $profile_url = 'profile.php';
        $surat_url = 'suratizin.php';
        $absen_url = 'absen.php';
        $rekapabsen_url = 'rekapabsen.php';
        $slipgaji_url = 'slipgaji.php';
    }
}
?>

<div class="headercontainer">
    <img class="logo" src="logo.png" alt="Logo">
    <div class="nav-links">

        <a href="<?php echo $mainpage_url; ?>" class="home">Home</a>
        <a href="<?php echo $surat_url; ?>" class="surat">Surat Izin</a>
        <a href="<?php echo $profile_url; ?>" class="profile">Profile</a>
        

        <a href="absen.php" class="absensi">Absensi</a>
        <a href="rekapabsen.php" class="rekapabsen">Rekap Absensi</a>
        <a href="slipgaji.php" class="slipgaji">Slip Gaji</a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <a href="view_user.php" class="viewusers">Daftar Pengguna</a>
            <a href="view_absensi.php" class="viewabsensi">Daftar Absensi</a>
            <a href="approve_lembur.php" class="lembur">Approve Lembur</a>
        <?php endif; ?>

        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>