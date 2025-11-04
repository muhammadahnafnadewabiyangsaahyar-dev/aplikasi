<?php
session_start();
include 'connect.php'; // Sertakan file koneksi database Anda

// Define flag untuk prevent CLI code execution di helper file
define('INCLUDED_FROM_WEB', true);
include 'calculate_status_kehadiran.php'; // Helper untuk hitung status kehadiran

// Pastikan hanya admin yang dapat mengakses halaman ini
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php'); 
    exit;
}
$home_url = 'mainpageadmin.php';

// --- Ambil filter bulan & tahun dari GET, default ke bulan & tahun sekarang ---
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// --- Query absensi bulanan ---
$sql_absensi = "SELECT a.id, a.tanggal_absensi, a.waktu_masuk, a.waktu_keluar, a.status_lokasi, 
                       a.foto_absen, a.menit_terlambat, a.status_keterlambatan, a.potongan_tunjangan,
                       a.status_lembur, a.user_id, r.nama_lengkap, c.jam_keluar 
                FROM absensi a 
                JOIN register r ON a.user_id = r.id 
                LEFT JOIN cabang c ON c.id = 1
                WHERE MONTH(a.tanggal_absensi) = ? AND YEAR(a.tanggal_absensi) = ?
                ORDER BY a.tanggal_absensi DESC, a.waktu_masuk DESC";
$stmt_absensi = $pdo->prepare($sql_absensi);
$stmt_absensi->execute([$bulan, $tahun]);
$daftar_absensi = $stmt_absensi->fetchAll(PDO::FETCH_ASSOC);

// --- Hitung status kehadiran untuk setiap record (real-time calculation) ---
foreach ($daftar_absensi as &$absensi) {
    $absensi['status_kehadiran_calculated'] = hitungStatusKehadiran($absensi, $pdo);
}

// --- Ambil daftar nama unik dan tanggal unik dari $daftar_absensi
$daftar_nama = [];
$daftar_tanggal = [];
foreach ($daftar_absensi as $a) {
    if (!in_array($a['nama_lengkap'], $daftar_nama, true)) {
        $daftar_nama[] = $a['nama_lengkap'];
    }
    if (!in_array($a['tanggal_absensi'], $daftar_tanggal, true)) {
        $daftar_tanggal[] = $a['tanggal_absensi'];
    }
}
sort($daftar_nama);
sort($daftar_tanggal);

// --- Query rekap harian: seluruh user, status absen hari ini ---
$tgl_hari_ini = date('Y-m-d');
$sql_rekap = "SELECT r.id, r.nama_lengkap, a.id AS absen_id, a.waktu_masuk, a.waktu_keluar, a.status_lembur
FROM register r
LEFT JOIN absensi a ON a.id = (
    SELECT id FROM absensi 
    WHERE user_id = r.id AND tanggal_absensi = ? 
    ORDER BY waktu_keluar DESC, waktu_masuk ASC, id DESC LIMIT 1
)
ORDER BY r.nama_lengkap ASC";
$stmt_rekap = $pdo->prepare($sql_rekap);
$stmt_rekap->execute([$tgl_hari_ini]);
$rekap_harian = $stmt_rekap->fetchAll(PDO::FETCH_ASSOC);

// --- Ekspor CSV jika diminta ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $bulan_csv = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
    $tahun_csv = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="riwayat_absensi_' . $bulan_csv . '_' . $tahun_csv . '.csv"');
    $output = fopen('php://output', 'w');
    // Header kolom
    fputcsv($output, ['ID', 'Nama Lengkap', 'Tanggal Absensi', 'Waktu Masuk', 'Waktu Keluar', 
                      'Status Lokasi', 'Foto Absen', 'Menit Terlambat', 'Status Keterlambatan', 'Potongan Tunjangan', 'Status Kehadiran']);
    foreach ($daftar_absensi as $absensi) {
        fputcsv($output, [
            $absensi['id'],
            $absensi['nama_lengkap'],
            $absensi['tanggal_absensi'],
            $absensi['waktu_masuk'],
            $absensi['waktu_keluar'],
            $absensi['status_lokasi'],
            $absensi['foto_absen'],
            $absensi['menit_terlambat'] ?? 0,
            $absensi['status_keterlambatan'] ?? 'tepat waktu',
            $absensi['potongan_tunjangan'] ?? 'tidak ada',
            $absensi['status_kehadiran_calculated'] ?? 'Belum Absen Keluar'
        ]);
    }
    fclose($output);
    exit;
}
// --- Ekspor CSV per user jika diminta ---
if (isset($_GET['export']) && $_GET['export'] === 'csv_user' && isset($_GET['nama'])) {
    $nama_user = $_GET['nama'];
    $bulan_csv = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
    $tahun_csv = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
    $sql_user = "SELECT a.id, a.tanggal_absensi, a.waktu_masuk, a.waktu_keluar, a.status_lokasi, 
                        a.foto_absen, a.menit_terlambat, a.status_keterlambatan, a.potongan_tunjangan,
                        a.user_id, r.nama_lengkap 
                FROM absensi a 
                JOIN register r ON a.user_id = r.id 
                WHERE r.nama_lengkap = ? AND MONTH(a.tanggal_absensi) = ? AND YEAR(a.tanggal_absensi) = ?
                ORDER BY a.tanggal_absensi DESC, a.waktu_masuk DESC";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([$nama_user, $bulan_csv, $tahun_csv]);
    $absensi_user = $stmt_user->fetchAll(PDO::FETCH_ASSOC);
    
    // Hitung status kehadiran untuk setiap record
    foreach ($absensi_user as &$abs) {
        $abs['status_kehadiran_calculated'] = hitungStatusKehadiran($abs, $pdo);
    }
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="absensi_' . urlencode($nama_user) . '_' . $bulan_csv . '_' . $tahun_csv . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Nama Lengkap', 'Tanggal Absensi', 'Waktu Masuk', 'Waktu Keluar', 
                      'Status Lokasi', 'Foto Absen', 'Menit Terlambat', 'Status Keterlambatan', 'Potongan Tunjangan', 'Status Kehadiran']);
    foreach ($absensi_user as $absensi) {
        fputcsv($output, [
            $absensi['id'],
            $absensi['nama_lengkap'],
            $absensi['tanggal_absensi'],
            $absensi['waktu_masuk'],
            $absensi['waktu_keluar'],
            $absensi['status_lokasi'],
            $absensi['foto_absen'],
            $absensi['menit_terlambat'] ?? 0,
            $absensi['status_keterlambatan'] ?? 'tepat waktu',
            $absensi['potongan_tunjangan'] ?? 'tidak ada',
            $absensi['status_kehadiran_calculated'] ?? 'Belum Absen Keluar',
            $absensi['potongan_tunjangan'] ?? 'tidak ada'
        ]);
    }
    fclose($output);
    exit;
}

// Ambil daftar nama unik dari $rekap_harian
$daftar_nama_harian = [];
foreach ($rekap_harian as $row) {
    if (!in_array($row['nama_lengkap'], $daftar_nama_harian, true)) {
        $daftar_nama_harian[] = $row['nama_lengkap'];
    }
}
sort($daftar_nama_harian);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Daftar Absensi</title>
</head>
<body>
    <div class="headercontainer">
        <?php include 'navbar.php'; ?>
    </div>
 <div class="main-title">Daftar Absensi</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>] [<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>]</p>
    </div>
    <div class="table-container" style="margin-top: 30px;">
        <h2 class="table-title">Riwayat Absensi Bulanan</h2>
        <form method="get" style="margin-bottom:20px; display:inline-block;">
            <label>Bulan:
                <select name="bulan">
                    <?php for ($b=1; $b<=12; $b++): ?>
                        <option value="<?php echo $b; ?>" <?php if ($bulan == $b) echo 'selected'; ?>><?php echo date('F', mktime(0,0,0,$b,1)); ?></option>
                    <?php endfor; ?>
                </select>
            </label>
            <label>Tahun:
                <select name="tahun">
                    <?php for ($t = date('Y')-3; $t <= date('Y')+1; $t++): ?>
                        <option value="<?php echo $t; ?>" <?php if ($tahun == $t) echo 'selected'; ?>><?php echo $t; ?></option>
                    <?php endfor; ?>
                </select>
            </label>
            <button type="submit">Filter</button>
        </form>
        <a href="?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>&export=csv" class="btn-export" style="margin-left:20px;">Download CSV</a>
        <!-- Filter kolom untuk tabel 1 -->
        <div style="margin-bottom:10px;">
            <form method="get" style="display:inline;">
                <label>Filter Nama:
                    <select name="nama" id="filterNama1" onchange="this.form.submit()">
                        <option value="">-- Semua --</option>
                        <?php foreach ($daftar_nama as $nama): ?>
                            <option value="<?php echo htmlspecialchars($nama); ?>" <?php if(isset($_GET['nama']) && $_GET['nama'] === $nama) echo 'selected'; ?>><?php echo htmlspecialchars($nama); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <input type="hidden" name="bulan" value="<?php echo $bulan; ?>">
                <input type="hidden" name="tahun" value="<?php echo $tahun; ?>">
            </form>
            <form method="get" style="display:inline; margin-left:10px;">
                <input type="hidden" name="bulan" value="<?php echo $bulan; ?>">
                <input type="hidden" name="tahun" value="<?php echo $tahun; ?>">
                <?php if (!empty($_GET['nama'])): ?>
                    <input type="hidden" name="nama" value="<?php echo htmlspecialchars($_GET['nama']); ?>">
                    <button type="submit" name="export" value="csv_user" class="btn-export">Download CSV Nama Ini</button>
                <?php endif; ?>
            </form>
            <label style="margin-left:20px;">Filter Tanggal:
                <select id="filterTanggal1" onchange="filterTableDropdown('filterTanggal1','.user-table',2)">
                    <option value="">-- Semua --</option>
                    <?php foreach ($daftar_tanggal as $tgl): ?>
                        <option value="<?php echo htmlspecialchars($tgl); ?>"><?php echo htmlspecialchars($tgl); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <?php if (!empty($daftar_absensi)): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Tanggal Absensi</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th>Status Lokasi</th>
                        <th>Foto Absen</th>
                        <th>Status Keterlambatan</th>
                        <th>Potongan Tunjangan</th>
                        <th>Status Kehadiran</th>
                        <th>Status Lembur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_absensi as $absensi): ?>
                    <?php if (empty($_GET['nama']) || $absensi['nama_lengkap'] === $_GET['nama']): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($absensi['id']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['tanggal_absensi']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['waktu_masuk']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['waktu_keluar']); ?></td>
                        <td><?php echo htmlspecialchars($absensi['status_lokasi']); ?></td>
                        <td>
                            <?php if (!empty($absensi['foto_absen'])): ?>
                                <?php
                                $foto = htmlspecialchars($absensi['foto_absen']);
                                // FIX: Semua foto sekarang di uploads/absensi/
                                $path_foto = 'uploads/absensi/' . $foto;
                                ?>
                                <img src="<?php echo $path_foto; ?>" alt="Foto Absen Masuk" style="max-width: 100px; max-height: 100px;">
                            <?php else: ?>
                                Tidak ada foto
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            // Tampilkan status keterlambatan dengan warna
                            $menit = $absensi['menit_terlambat'] ?? 0;
                            $status_ket = $absensi['status_keterlambatan'] ?? 'tepat waktu';
                            
                            if ($status_ket == 'di luar shift') {
                                // Absen di luar range shift (terlalu awal/terlalu terlambat)
                                echo '<span style="color: purple; font-weight: bold;">⚠ DI LUAR SHIFT</span><br>';
                                echo '<small style="color: gray;">(Absen ' . abs($menit) . ' menit dari shift - perlu review)</small>';
                            } elseif ($menit == 0 || $status_ket == 'tepat waktu') {
                                echo '<span style="color: green; font-weight: bold;">✓ Tepat Waktu</span>';
                            } elseif ($menit > 0 && $menit < 20) {
                                echo '<span style="color: orange; font-weight: bold;">⚠ Terlambat ' . $menit . ' menit</span>';
                            } elseif ($menit >= 20 && $menit < 40) {
                                echo '<span style="color: #FF6B35; font-weight: bold;">⚠ Terlambat ' . $menit . ' menit</span>';
                            } else {
                                echo '<span style="color: red; font-weight: bold;">✗ Terlambat ' . $menit . ' menit</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            // Tampilkan potongan tunjangan
                            $potongan = $absensi['potongan_tunjangan'] ?? 'tidak ada';
                            if ($potongan == 'tidak ada') {
                                echo '<span style="color: green;">-</span>';
                            } elseif ($potongan == 'tunjangan makan') {
                                echo '<span style="color: #FF6B35; font-weight: bold;">🍽️ Makan</span>';
                            } else {
                                echo '<span style="color: red; font-weight: bold;">🍽️ Makan + 🚗 Transport</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            // STATUS KEHADIRAN - Gunakan fungsi helper untuk konsistensi
                            $status_kehadiran = $absensi['status_kehadiran_calculated'];
                            
                            // Ambil info admin untuk styling
                            $stmt_role = $pdo->prepare("SELECT role FROM register WHERE id = ?");
                            $stmt_role->execute([$absensi['user_id']]);
                            $user_role_info = $stmt_role->fetch();
                            $is_admin_user = ($user_role_info && $user_role_info['role'] === 'admin');
                            
                            if ($status_kehadiran == 'Hadir') {
                                if ($is_admin_user) {
                                    // Admin: Tampilkan info durasi kerja dalam format jam menit
                                    $waktu_masuk = strtotime($absensi['waktu_masuk']);
                                    $waktu_keluar = strtotime($absensi['waktu_keluar']);
                                    $durasi_detik = $waktu_keluar - $waktu_masuk;
                                    $durasi_jam = floor($durasi_detik / 3600);
                                    $durasi_menit = floor(($durasi_detik % 3600) / 60);
                                    
                                    $format_durasi = '';
                                    if ($durasi_jam > 0) {
                                        $format_durasi .= $durasi_jam . ' jam';
                                    }
                                    if ($durasi_menit > 0) {
                                        $format_durasi .= ($durasi_jam > 0 ? ' ' : '') . $durasi_menit . ' menit';
                                    }
                                    if (empty($format_durasi)) {
                                        $format_durasi = '0 menit';
                                    }
                                    
                                    echo '<span style="color: green; font-weight: bold;">✓ Hadir (Admin)</span><br>';
                                    echo '<small style="color: gray;">(Kerja: ' . $format_durasi . ')</small>';
                                } else {
                                    echo '<span style="color: green; font-weight: bold;">✓ Hadir</span>';
                                }
                            } elseif ($status_kehadiran == 'Tidak Hadir') {
                                if ($is_admin_user) {
                                    // Admin: Tampilkan info durasi kerja dalam format jam menit
                                    $waktu_masuk = strtotime($absensi['waktu_masuk']);
                                    $waktu_keluar = strtotime($absensi['waktu_keluar']);
                                    $durasi_detik = $waktu_keluar - $waktu_masuk;
                                    $durasi_jam = floor($durasi_detik / 3600);
                                    $durasi_menit = floor(($durasi_detik % 3600) / 60);
                                    
                                    $format_durasi = '';
                                    if ($durasi_jam > 0) {
                                        $format_durasi .= $durasi_jam . ' jam';
                                    }
                                    if ($durasi_menit > 0) {
                                        $format_durasi .= ($durasi_jam > 0 ? ' ' : '') . $durasi_menit . ' menit';
                                    }
                                    if (empty($format_durasi)) {
                                        $format_durasi = '0 menit';
                                    }
                                    
                                    echo '<span style="color: red; font-weight: bold;">❌ Tidak Hadir (Admin)</span><br>';
                                    echo '<small style="color: red;">(Kerja: ' . $format_durasi . ' - Minimal 8 jam)</small>';
                                } else {
                                    $jam_keluar_shift = $absensi['jam_keluar'] ?? null;
                                    $waktu_keluar_user = $absensi['waktu_keluar'] ?? null;
                                    if (!empty($waktu_keluar_user) && !empty($jam_keluar_shift)) {
                                        $jam_keluar_only = date('H:i:s', strtotime($waktu_keluar_user));
                                        $selisih_detik = strtotime($jam_keluar_shift) - strtotime($jam_keluar_only);
                                        $selisih_jam = floor($selisih_detik / 3600);
                                        $selisih_menit = floor(($selisih_detik % 3600) / 60);
                                        
                                        $format_selisih = '';
                                        if ($selisih_jam > 0) {
                                            $format_selisih .= $selisih_jam . ' jam';
                                        }
                                        if ($selisih_menit > 0) {
                                            $format_selisih .= ($selisih_jam > 0 ? ' ' : '') . $selisih_menit . ' menit';
                                        }
                                        if (empty($format_selisih)) {
                                            $format_selisih = '0 menit';
                                        }
                                        
                                        echo '<span style="color: red; font-weight: bold;">❌ Tidak Hadir</span><br>';
                                        echo '<small style="color: red;">(Pulang ' . $format_selisih . ' lebih awal)</small>';
                                    } else {
                                        echo '<span style="color: red; font-weight: bold;">❌ Tidak Hadir</span>';
                                    }
                                }
                            } elseif ($status_kehadiran == 'Belum Absen Keluar') {
                                echo '<span style="color: orange; font-weight: bold;">⚠ Belum Keluar</span>';
                            } elseif ($status_kehadiran == 'Lupa Absen Pulang') {
                                echo '<span style="color: #ff6b6b; font-weight: bold;"><i class="fa fa-user-clock"></i> Lupa Absen Pulang</span><br>';
                                echo '<small style="color: #ff6b6b;">(Dihitung hadir dengan catatan)</small>';
                            } else {
                                // Fallback untuk status lain
                                echo '<span style="color: gray;">' . htmlspecialchars($status_kehadiran) . '</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo isset($absensi['status_lembur']) ? htmlspecialchars($absensi['status_lembur']) : '-'; ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Belum ada data absensi untuk bulan dan tahun ini.</p>
        <?php endif; ?>
    </div>

    <div class="table-container" style="margin-top: 40px;">
        <h2 class="table-title">Rekap Absensi Harian (<?php echo date('d-m-Y'); ?>)</h2>
        <!-- Filter kolom untuk tabel 2 -->
        <div style="margin-bottom:10px;">
            <label>Filter Nama:
                <select id="filterNama2" onchange="filterTableDropdown('filterNama2','.user-table:last-of-type',0)">
                    <option value="">-- Semua --</option>
                    <?php foreach ($daftar_nama_harian as $nama): ?>
                        <option value="<?php echo htmlspecialchars($nama); ?>"><?php echo htmlspecialchars($nama); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Status Absen Hari Ini</th>
                    <th>Waktu Masuk</th>
                    <th>Waktu Keluar</th>
                    <th>Status Overwork</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekap_harian as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td>
                        <?php if (!is_null($row['absen_id'])): ?>
                            <span style="color:green;font-weight:bold;">Sudah Absen</span>
                        <?php else: ?>
                            <span style="color:red;font-weight:bold;">Belum Absen</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row['waktu_masuk'] ? htmlspecialchars(date('H:i', strtotime($row['waktu_masuk']))) : '-'; ?></td>
                    <td><?php echo $row['waktu_keluar'] ? htmlspecialchars(date('H:i', strtotime($row['waktu_keluar']))) : '-'; ?></td>
                    <td>
                        <?php
                        if (!is_null($row['absen_id'])) {
                            if ($row['status_lembur'] === 'Pending' || $row['status_lembur'] === 'Approved') {
                                echo '<span style="color:orange;font-weight:bold;">Overwork</span>';
                            } else {
                                echo '-';
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    // Filter kolom untuk dua tabel
    function filterTable(inputId, tableClass, colIdx) {
        var input = document.getElementById(inputId);
        var filter = input.value.toLowerCase();
        var table = document.querySelector(tableClass);
        var trs = table.getElementsByTagName('tr');
        for (var i = 1; i < trs.length; i++) { // Mulai dari 1 agar header tidak ikut
            var tds = trs[i].getElementsByTagName('td');
            if (tds[colIdx]) {
                var txt = tds[colIdx].textContent || tds[colIdx].innerText;
                trs[i].style.display = txt.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
            }
        }
    }

    function filterTableDropdown(selectId, tableClass, colIdx) {
        var select = document.getElementById(selectId);
        var filter = select.value.toLowerCase();
        var table = document.querySelector(tableClass);
        var trs = table.getElementsByTagName('tr');
        for (var i = 1; i < trs.length; i++) {
            var tds = trs[i].getElementsByTagName('td');
            if (tds[colIdx]) {
                var txt = tds[colIdx].textContent || tds[colIdx].innerText;
                trs[i].style.display = (!filter || txt.toLowerCase() === filter) ? '' : 'none';
            }
        }
    }
    </script>
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
</footer>
</html>