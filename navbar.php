<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['role']);

// --- 1. Tentukan URL Default ---
// Jika tidak login, $mainpage_url mengarah ke index.
// Jika login, akan ditimpa ke mainpage.php di bawah.
$mainpage_url = 'index.php';

// --- 2. Inisialisasi semua variabel link ---
// Ini untuk mencegah error "undefined variable"
$profile_url = null;
$surat_url = null;
$absen_url = null;
$rekapabsen_url = null;
$slipgaji_url = null;
$approvesurat_url = null; // Link ini ada di logika PHP Anda tapi tidak dipakai
$approvelembur_url = null;
$view_user_url = null;
$view_absensi_url = null;

// --- 3. Tetapkan URL jika user sudah login ---
if ($is_logged_in) {
    
    // URL Halaman Utama untuk yang sudah login
    $mainpage_url = 'mainpage.php';

    // --- PERBAIKAN: Link ini sekarang untuk SEMUA user yang login (User & Admin) ---
    $profile_url = 'profile.php';
    $surat_url = 'suratizin.php';
    $absen_url = 'absen.php';
    $rekapabsen_url = 'rekapabsen.php';
    $slipgaji_url = 'slipgaji.php';

    // --- Link yang HANYA dimiliki ADMIN ---
    if ($_SESSION['role'] == 'admin') {
        $approvesurat_url = 'approve.php'; // Link untuk approve surat izin
        $approvelembur_url = 'approve_lembur.php';
        $view_user_url = 'view_user.php';
        $view_absensi_url = 'view_absensi.php';
        $whitelist_url = 'whitelist.php'; // Tambahkan whitelist
    }
}
?>

<div class="headercontainer">
    <img class="logo" src="logo.png" alt="Logo">
    <div class="nav-links">

        <a href="<?php echo $mainpage_url; ?>" class="home">Home</a>

        <?php if ($is_logged_in): ?>
            
            <a href="<?php echo $profile_url; ?>" class="profile">Profile</a>
            <a href="<?php echo $surat_url; ?>" class="surat">Surat Izin</a>
            <a href="<?php echo $absen_url; ?>" class="absensi">Absensi</a>
            <a href="<?php echo $rekapabsen_url; ?>" class="rekapabsen">Rekap Absensi</a>
            <a href="<?php echo $slipgaji_url; ?>" class="slipgaji">Slip Gaji</a>

            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="<?php echo $approvesurat_url; ?>" class="surat">Approve Surat</a>
                <a href="<?php echo $view_user_url; ?>" class="viewusers">Daftar Pengguna</a>
                <a href="<?php echo $view_absensi_url; ?>" class="viewabsensi">Daftar Absensi</a>
                <a href="<?php echo $approvelembur_url; ?>" class="lembur">Approve Lembur</a>
                <a href="<?php echo $whitelist_url; ?>" class="whitelist">Whitelist</a>
            <?php endif; ?>

            <a href="logout.php" class="logout">Logout</a>

        <?php endif; ?>
        
    </div>
</div>