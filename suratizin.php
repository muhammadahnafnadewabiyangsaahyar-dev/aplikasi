<?php
session_start();

// 1. PENJAGA GERBANG: Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) { // Lebih baik cek user_id
    header('Location: index.php?error=notloggedin'); 
    exit;
}
$user_id = $_SESSION['user_id'];

// 2. Muat Koneksi & Ambil Data User (HANYA YANG PERLU UNTUK HALAMAN INI)
include 'connect.php';

// Ambil HANYA kolom tanda tangan untuk logika if/else
$tanda_tangan_tersimpan = null; // Default
$sql_user_ttd = "SELECT tanda_tangan_file FROM register WHERE id = ?";
$stmt_user_ttd = $pdo->prepare($sql_user_ttd);
$stmt_user_ttd->execute([$user_id]);
$user_ttd_data = $stmt_user_ttd->fetch(PDO::FETCH_ASSOC);
if ($user_ttd_data) {
    $tanda_tangan_tersimpan = $user_ttd_data['tanda_tangan_file'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Ajukan Surat Izin</title> 
</head>
<body>
    <div class="headercontainer">
        <img class="logo" src="logo.png" alt="Logo">
        <?php include 'navbar.php'; ?>
    </div>
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>]</p>
    </div>
    
    <div class="content-container"> 
        <i class="fa fa-info-circle info-icon"></i>
        <p class="info-text">Halaman ini diperuntukkan bagi seluruh karyawan KAORI Indonesia untuk mengajukan surat izin dengan mudah dan cepat. Silakan klik tombol di bawah untuk memulai proses pengajuan surat izin Anda.</p>
        <div class="btn-apply" id="btn-apply">Ajukan Surat Izin</div>
    </div>

    <div class="content-container" id="form-container" style="display: none;"> 
        <p class="fill-info">Silakan lengkapi formulir pengajuan surat izin di bawah ini:</p>
        <form method="POST" action="docx.php" class="form-surat-izin">
            <div class="input-group">
                <label for="perihal">Perihal:</label>
                <input type="text" id="perihal" name="perihal" required>
            </div>
            <div class="input-group">
                <label for="tanggal_izin">Tanggal Mulai Izin:</label>
                <input type="date" id="tanggal_izin" name="tanggal_izin" required>
            </div>
            <div class="input-group">
                <label for="tanggal_selesai">Tanggal Selesai Izin:</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" required>
            </div>
            <div class="input-group">
                <label for="lama_izin">Lama Izin (dalam hari):</label>
                <input type="number" id="lama_izin" name="lama_izin" min="1" required>
            </div>
            <div class="input-group">
                <label for="alasan_izin">Alasan Izin:</label>
                <textarea id="alasan_izin" name="alasan_izin" rows="4" required></textarea>
            </div>

            <?php 
            // Cek apakah variabel $tanda_tangan_tersimpan KOSONG
            if (empty($tanda_tangan_tersimpan)): 
            ?>
                <div class="input-group">
                    <label for="signature">Tanda Tangan:</label>
                    <canvas id="signature-pad" class="signature-pad" width="400" height="200" style="border: 1px solid #000;"></canvas>
                    <button type="button" id="clear-signature">Hapus</button>
                    <input type="hidden" name="signature_data" id="signature-data">
                </div>
            <?php else: ?>
                <div class="input-group">
                    <label>Tanda Tangan:</label>
                    <p>Tanda tangan sudah tersimpan di profil Anda.</p> 
                    <img src="uploads/<?php echo htmlspecialchars($tanda_tangan_tersimpan); ?>" alt="Tanda Tangan Tersimpan" style="max-width: 150px; border: 1px solid #ccc; margin-top: 5px;">
                    <br>
                    <button type="button" id="edit-signature-btn">Ubah Tanda Tangan</button>
                    <div class="edit-signature-container" id="edit-signature-container" style="display: none; margin-top: 15px;">
                        <form action="update_signature.php" method="POST" id="edit-signature-form">
                            <label for="edit-signature">Gambar Tanda Tangan Baru:</label><br>
                            <canvas id="edit-signature-pad" class="edit-signature-pad" width="400" height="200" style="border: 1px solid #000;"></canvas><br>
                            <button type="button" id="clear-new-signature">Hapus</button>
                            <input type="hidden" name="edit_signature_data" id="edit-signature-data">
                            <br><br>
                            <button type="submit">Simpan Tanda Tangan Baru</button>
                            <button type="button" id="cancel-edit-signature">Batal</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?> 
            <div class="input-group">
                <button type="submit" class="btn-apply">Ajukan Surat Izin</button>
            </div>
        </form>
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
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.10/dist/signature_pad.umd.min.js"></script> 
    <script src="script_izin.js"></script>
    <script src="script_ubah_ttd.js"></script>
</footer>
</html>