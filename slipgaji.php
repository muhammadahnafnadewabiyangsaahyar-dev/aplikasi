<?php
session_start();
include 'connect.php';

// Fungsi bantu untuk mendapatkan nama bulan
function getNamaBulan($bulan) {
    $namaBulan = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
    return $namaBulan[(int)$bulan] ?? 'Bulan?';
}

// 1. Keamanan: Cek Login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=notloggedin');
    exit;
}
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['role'];
$home_url = ($current_user_role == 'admin') ? 'mainpageadmin.php' : 'mainpageuser.php';

$message = ''; // Untuk notifikasi sukses/gagal

// =================================================================
// --- PROSES GENERATE SLIP GAJI (HANYA ADMIN) ---
// =================================================================
if ($current_user_role == 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_gaji'])) {
    
    // Ambil data dari form
    $register_id = (int)$_POST['register_id'];
    $bulan = (int)$_POST['bulan'];
    $tahun = (int)$_POST['tahun'];

    // Validasi input dasar
    if (empty($register_id) || empty($bulan) || empty($tahun)) {
        $message = '<p class="error-msg">Error: User, Bulan, dan Tahun harus dipilih.</p>';
    } else {
        
        // Mulai Transaksi Database
        mysqli_autocommit($conn, false);
        $commit_success = true;

        // --- 1. AMBIL DATA KOMPONEN GAJI ---
        $sql_komponen = "SELECT * FROM komponen_gaji WHERE register_id = ?";
        $stmt_komponen = mysqli_prepare($conn, $sql_komponen);
        mysqli_stmt_bind_param($stmt_komponen, "i", $register_id);
        mysqli_stmt_execute($stmt_komponen);
        $result_komponen = mysqli_stmt_get_result($stmt_komponen);
        $komponen = mysqli_fetch_assoc($result_komponen);
        mysqli_stmt_close($stmt_komponen);

        if (!$komponen) {
            $message = '<p class="error-msg">Error: Komponen gaji untuk user ini tidak ditemukan.</p>';
            mysqli_rollback($conn);
        } else {
            // Asumsi hardcoded (sesuai kode asli Anda)
            $hari_kerja_asumsi = 26; 
            $bonus_lembur_per_shift = 100000;
            $potongan_absen_harian = 100000;
            $denda_telat_ringan = 5000;

            // --- 2. AMBIL DATA ABSENSI (REKAP) ---
            $sql_absensi = "SELECT menit_terlambat, status_lembur 
                            FROM absensi 
                            WHERE register_id = ? 
                            AND MONTH(tanggal_absensi) = ? 
                            AND YEAR(tanggal_absensi) = ?";
            $stmt_absensi = mysqli_prepare($conn, $sql_absensi);
            mysqli_stmt_bind_param($stmt_absensi, "iii", $register_id, $bulan, $tahun);
            mysqli_stmt_execute($stmt_absensi);
            $result_absensi = mysqli_stmt_get_result($stmt_absensi);

            // ========================================================
            // --- BLOK PERBAIKAN LOGIKA KALKULASI ---
            // ========================================================

            $total_hadir = 0;
            $total_lembur = 0;
            $total_absen = 0; // Anda mungkin perlu logika berbeda untuk menghitung ini

            // 3 Kategori Keterlambatan
            $total_telat_ringan = 0; // (<= 20 menit) -> Denda
            $total_telat_sedang = 0; // (> 20 & < 40 menit) -> Potong Transport
            $total_telat_berat  = 0; // (>= 40 menit) -> Potong Transport + Makan

            while ($row_absensi = mysqli_fetch_assoc($result_absensi)) {
                $total_hadir++;
                $menit_terlambat = (int)$row_absensi['menit_terlambat'];

                // Logika Keterlambatan (3 Kategori)
                if ($menit_terlambat > 0) {
                    if ($menit_terlambat <= 20) {
                        $total_telat_ringan++;
                    } elseif ($menit_terlambat >= 40) {
                        $total_telat_berat++;
                    } else { 
                        // Ini berarti > 20 DAN < 40
                        $total_telat_sedang++;
                    }
                }

                // Logika Lembur
                if ($row_absensi['status_lembur'] == 'Approved') {
                    $total_lembur++;
                }
            }
            mysqli_stmt_close($stmt_absensi);
            
            // NOTE: Logika $total_absen tidak ada di kode asli.
            // Anda harus menghitung $total_absen secara terpisah jika diperlukan.
            // Untuk saat ini, kita anggap $total_absen = 0 jika tidak di-supply.
            $total_absen = (int)($_POST['jumlah_absen'] ?? 0); // Ambil dari input manual jika ada


            // --- 4. KALKULASI GAJI BERDASARKAN ATURAN BISNIS BARU ---

            // Tunjangan harian
            $tunjangan_transport_harian = $komponen['tunjangan_transport'] / $hari_kerja_asumsi;
            $tunjangan_makan_harian = $komponen['tunjangan_makan'] / $hari_kerja_asumsi;
            
            // Hitung Tunjangan Aktual (setelah dipotong)
            $hari_hangus_transport = $total_telat_sedang + $total_telat_berat;
            $hari_hangus_makan = $total_telat_berat; // Hanya hangus jika >= 40 menit
            
            $tunjangan_transport_aktual = $komponen['tunjangan_transport'] - ($hari_hangus_transport * $tunjangan_transport_harian);
            $tunjangan_makan_aktual = $komponen['tunjangan_makan'] - ($hari_hangus_makan * $tunjangan_makan_harian);

            // Hitung Nilai Potongan (untuk disimpan di DB & slip)
            $potongan_telat_bawah_20 = $total_telat_ringan * $denda_telat_ringan;
            
            // Gabungkan semua potongan tunjangan ke dalam 1 variabel DB
            // (potongan_telat_atas_20) untuk kompromi DBe
            $potongan_tunjangan_transport = $hari_hangus_transport * $tunjangan_transport_harian;
            $potongan_tunjangan_makan = $hari_hangus_makan * $tunjangan_makan_harian;
            $potongan_telat_atas_20 = $potongan_tunjangan_transport + $potongan_tunjangan_makan;
            
            // Komponen Gaji Lain
            $gaji_pokok_aktual = $komponen['gaji_pokok']; // Asumsi tidak prorata
            $tunjangan_jabatan_aktual = $komponen['tunjangan_jabatan'];
            $overwork_aktual = $total_lembur * $bonus_lembur_per_shift;
            
            // Komponen Potongan Lain (dari form)
            $piutang_toko_aktual = (float)($_POST['piutang_toko'] ?? 0);
            $kasbon_aktual = (float)($_POST['kasbon'] ?? 0);
            $potongan_absen_aktual = $total_absen * $potongan_absen_harian;

            // Kalkulasi Gaji Bersih
            $gaji_bersih = ($gaji_pokok_aktual + $tunjangan_transport_aktual + $tunjangan_makan_aktual + $tunjangan_jabatan_aktual + $overwork_aktual)
                          - ($piutang_toko_aktual + $kasbon_aktual + $potongan_absen_aktual + $potongan_telat_bawah_20); // Denda ringan

            // ========================================================
            // --- AKHIR BLOK PERBAIKAN ---
            // ========================================================

            // --- 5. SIMPAN KE RIWAYAT GAJI ---
            $sql_insert = "INSERT INTO riwayat_gaji 
                           (register_id, periode_bulan, periode_tahun, gaji_pokok_aktual, 
                           tunjangan_makan, tunjangan_transportasi, tunjangan_jabatan, 
                           overwork, piutang_toko, kasbon, potongan_absen, 
                           potongan_telat_atas_20, potongan_telat_bawah_20, gaji_bersih, 
                           jumlah_hadir, jumlah_terlambat, jumlah_absen, file_slip_gaji) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            
            if ($stmt_insert) {
                $file_slip_gaji = ''; // Dulu di-generate, sekarang mungkin tidak
                $total_terlambat = $total_telat_ringan + $total_telat_sedang + $total_telat_berat;
                
                mysqli_stmt_bind_param($stmt_insert, "iiidddddddddddiiis", 
                    $register_id, $bulan, $tahun, $gaji_pokok_aktual,
                    $tunjangan_makan_aktual, $tunjangan_transport_aktual, $tunjangan_jabatan_aktual,
                    $overwork_aktual, $piutang_toko_aktual, $kasbon_aktual, $potongan_absen_aktual,
                    $potongan_telat_atas_20, $potongan_telat_bawah_20, $gaji_bersih,
                    $total_hadir, $total_terlambat, $total_absen, $file_slip_gaji
                );

                if (!mysqli_stmt_execute($stmt_insert)) {
                    $message = '<p class="error-msg">Error: Gagal menyimpan riwayat gaji. ' . mysqli_error($conn) . '</p>';
                    $commit_success = false;
                }
                mysqli_stmt_close($stmt_insert);
                
            } else {
                $message = '<p class="error-msg">Error: Gagal prepare statement insert. ' . mysqli_error($conn) . '</p>';
                $commit_success = false;
            }

            // Selesaikan Transaksi
            if ($commit_success) {
                mysqli_commit($conn);
                $message = '<p class="success-msg">Sukses! Slip gaji berhasil di-generate.</p>';
            } else {
                mysqli_rollback($conn);
            }
        }
    }
} // --- AKHIR PROSES GENERATE ---


// =================================================================
// --- AMBIL DATA UNTUK TAMPILAN (FORM & RIWAYAT) ---
// =================================================================

// Ambil daftar user untuk dropdown (hanya admin)
$daftar_user = [];
if ($current_user_role == 'admin') {
    $sql_user = "SELECT id, nama_lengkap FROM register ORDER BY nama_lengkap ASC";
    $stmt_user = $pdo->query($sql_user);
    $daftar_user = $stmt_user->fetchAll(PDO::FETCH_ASSOC);
}

// Ambil data riwayat gaji untuk ditampilkan
$user_id_to_view = $current_user_id;
if ($current_user_role == 'admin' && isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id_to_view = (int)$_GET['user_id'];
}

$sql_riwayat = "SELECT rg.*, r.nama_lengkap FROM riwayat_gaji rg JOIN register r ON rg.user_id = r.id WHERE rg.user_id = ? ORDER BY rg.periode_tahun DESC, rg.periode_bulan DESC";
$stmt_riwayat = $pdo->prepare($sql_riwayat);
$stmt_riwayat->execute([$user_id_to_view]);
$riwayat_gaji = $stmt_riwayat->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <div class="headercontainer">
        <img class="logo" src="logo.png" alt="Logo">
        <div class="nav-links">
            <a href="<?php echo $home_url; ?>" class="home">Home</a>
            <?php if ($current_user_role == 'admin'): ?>
                <a href="approve.php" class="surat">Surat Izin</a>
                <a href="view_user.php" class="viewusers">Daftar Pengguna</a>
                <a href="rekapabsen.php" class="rekapabsen">Rekap Absensi</a>
            <?php else: ?>
                <a href="suratizin.php" class="surat">Surat Izin</a>
                <a href="rekapabsen.php" class="rekap">Rekap Absen</a>
            <?php endif; ?>
            <a href="slipgaji.php" class="slipgaji">Slip Gaji</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?php echo htmlspecialchars($current_user_role); ?>]</p>
    </div>

    <?php if ($current_user_role == 'admin'): ?>
    <div class="content-container">
        <h2>Generate Slip Gaji Karyawan</h2>
        <p>Pilih karyawan dan periode untuk menghitung dan menyimpan slip gaji.</p>
        
        <?php echo $message; // Tampilkan notifikasi sukses/gagal ?>
        
        <div class="form-gaji-container">
            <form action="slipgaji.php" method="POST">
                <input type="hidden" name="generate_gaji" value="1">
                <div class="input-group">
                    <label for="register_id">Karyawan:</label>
                    <select id="register_id" name="register_id" required>
                        <option value="">-- Pilih Karyawan --</option>
                        <?php foreach ($daftar_user as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['nama_lengkap']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="bulan">Bulan:</label>
                    <select id="bulan" name="bulan" required>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($i == date('m')) ? 'selected' : ''; ?>><?php echo getNamaBulan($i); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="tahun">Tahun:</label>
                    <input type="number" id="tahun" name="tahun" value="<?php echo date('Y'); ?>" required>
                </div>
                
                <p style="font-size: 0.9em; color: #555;">Potongan Tambahan (Opsional):</p>
                <div class="input-group">
                    <label for="piutang_toko">Piutang Toko:</label>
                    <input type="number" id="piutang_toko" name="piutang_toko" value="0" step="1000">
                </div>
                <div class="input-group">
                    <label for="kasbon">Kasbon:</label>
                    <input type="number" id="kasbon" name="kasbon" value="0" step="1000">
                </div>
                 <div class="input-group">
                    <label for="jumlah_absen">Jumlah Absen (Manual):</label>
                    <input type="number" id="jumlah_absen" name="jumlah_absen" value="0" step="1">
                </div>
                
                <div class="input-group" style="width: 100%; margin-top: 20px;">
                    <button type="submit" class="btn-apply">Generate & Simpan Gaji</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>


    <div class="content-container">
        <h2>Riwayat Slip Gaji</h2>
        
        <?php if ($current_user_role == 'admin'): ?>
            <p>Pilih karyawan untuk melihat riwayat slip gaji:</p>
            <form action="slipgaji.php" method="GET">
                <select name="user_id" onchange="this.form.submit()">
                    <option value="">-- Lihat Riwayat User Lain --</option>
                    <?php foreach ($daftar_user as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $user_id_to_view) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <hr style="margin: 20px 0;">
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <?php if ($current_user_role == 'admin'): ?>
                            <th>Nama Karyawan</th>
                        <?php endif; ?>
                        <th>Periode</th>
                        <th>Gaji Bersih (THP)</th>
                        <th>Hadir</th>
                        <th>Telat</th>
                        <th>Absen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riwayat_gaji)): ?>
                        <tr>
                            <td colspan="<?php echo ($current_user_role == 'admin') ? '7' : '6'; ?>" style="text-align: center;">Tidak ada riwayat gaji.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($riwayat_gaji as $riwayat): ?>
                            <tr>
                                <?php if ($current_user_role == 'admin'): ?>
                                    <td><?php echo htmlspecialchars($riwayat['nama_lengkap']); ?></td>
                                <?php endif; ?>
                                <td><?php echo getNamaBulan($riwayat['periode_bulan']) . ' ' . $riwayat['periode_tahun']; ?></td>
                                <td>Rp <?php echo number_format($riwayat['gaji_bersih'], 0, ',', '.'); ?></td>
                                <td><?php echo $riwayat['jumlah_hadir']; ?></td>
                                <td><?php echo $riwayat['jumlah_terlambat']; ?></td>
                                <td><?php echo $riwayat['jumlah_absen']; ?></td>
                                <td>
                                    <a href="generate_slip.php?id=<?php echo $riwayat['id']; ?>" class="btn-apply" style="font-size: 0.9em; padding: 5px 10px; text-decoration: none;">
                                        Download (.docx)
                                    </a>
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