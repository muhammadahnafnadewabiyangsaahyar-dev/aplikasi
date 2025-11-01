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
        <?php include 'navbar.php'; ?>
    </div>
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>]</p>
    </div>
    
    <div class="content-container"> 
        <i class="fa fa-info-circle info-icon"></i>
        <div class="info-text" style="background:#fff3cd;color:#856404;border:1px solid #ffeeba;padding:10px 12px;border-radius:4px;">
            <b>Halaman pengajuan surat izin sedang tidak tersedia sementara.</b><br>Silakan kembali lagi nanti.
        </div>
        <!-- <p class="info-text">Halaman ini diperuntukkan bagi seluruh karyawan KAORI Indonesia untuk mengajukan surat izin dengan mudah dan cepat. Silakan klik tombol di bawah untuk memulai proses pengajuan surat izin Anda.</p>
        <div class="btn-apply" id="btn-apply">Ajukan Surat Izin</div> -->
    </div>

    <div class="content-container" id="form-container" style="display: none;"> 
        <?php if (isset($_GET['error']) && (
            !empty($_POST) || // Sudah submit POST
            !empty($_REQUEST['perihal']) || !empty($_REQUEST['tanggal_izin']) || !empty($_REQUEST['tanggal_selesai']) || !empty($_REQUEST['lama_izin']) || !empty($_REQUEST['alasan_izin'])
        )): ?>
            <div class="error-message" style="color: #b00; background: #fff3f3; border: 1px solid #b00; padding: 8px; margin-bottom: 10px; border-radius: 4px;">
                <?php
                $errorMsg = 'Terjadi kesalahan.';
                if ($_GET['error'] === 'datakosong') $errorMsg = 'Semua field wajib diisi.';
                elseif ($_GET['error'] === 'ttdkosong') $errorMsg = 'Tanda tangan wajib diisi.';
                elseif ($_GET['error'] === 'tipettdditidakvalid') $errorMsg = 'Tipe file tanda tangan tidak valid.';
                elseif ($_GET['error'] === 'dekodettdditidakvalid') $errorMsg = 'Data tanda tangan tidak valid.';
                elseif ($_GET['error'] === 'gagalsimpanttd') $errorMsg = 'Gagal menyimpan file tanda tangan.';
                elseif ($_GET['error'] === 'formatttdditidakvalid') $errorMsg = 'Format data tanda tangan tidak valid.';
                elseif ($_GET['error'] === 'gagalsimpansurat') $errorMsg = 'Gagal menyimpan file surat.';
                elseif ($_GET['error'] === 'gagalinsertdb') $errorMsg = 'Gagal menyimpan data ke database.';
                echo htmlspecialchars($errorMsg);
                ?>
            </div>
        <?php endif; ?>
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
                    <img src="uploads/tanda_tangan/<?php echo htmlspecialchars($tanda_tangan_tersimpan); ?>" alt="Tanda Tangan Tersimpan" style="max-width: 150px; border: 1px solid #ccc; margin-top: 5px;">
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
    <script>
    // Otomatis hitung lama izin (hari) setelah memilih tanggal
    document.addEventListener('DOMContentLoaded', function() {
        const tglMulai = document.getElementById('tanggal_izin');
        const tglSelesai = document.getElementById('tanggal_selesai');
        const lamaIzin = document.getElementById('lama_izin');
        function hitungLamaIzin() {
            if (tglMulai.value && tglSelesai.value) {
                const start = new Date(tglMulai.value);
                const end = new Date(tglSelesai.value);
                if (!isNaN(start) && !isNaN(end) && end >= start) {
                    // +1 agar inklusif (misal: 1-1-2025 s/d 2-1-2025 = 2 hari)
                    const diff = Math.floor((end - start) / (1000*60*60*24)) + 1;
                    lamaIzin.value = diff;
                } else {
                    lamaIzin.value = '';
                }
            } else {
                lamaIzin.value = '';
            }
        }
        if (tglMulai && tglSelesai && lamaIzin) {
            tglMulai.addEventListener('change', hitungLamaIzin);
            tglSelesai.addEventListener('change', hitungLamaIzin);
        }
    });
    </script>
</footer>
</html>