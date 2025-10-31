<?php
// 1. Mulai Sesi & Sertakan File yang Diperlukan
session_start();
include_once('tbs/tbs_class.php');
include_once('tbs/tbs_plugin_opentbs.php');
// Anda mungkin TIDAK memerlukan 'tbs/plugins/tbs_plugs'
include 'connect.php'; // Sertakan file koneksi database Anda

// 2. PENJAGA GERBANG: Pastikan Pengguna Sudah Login
if (!isset($_SESSION['user_id'])) {
    // Jika tidak ada ID sesi, arahkan ke login
    header('Location: index.php?error=notloggedin'); 
    exit;
}
$user_id_session = $_SESSION['user_id'];

// 3. Ambil Data dari Formulir POST (Gunakan KEY YANG BENAR)
// Gunakan null coalescing operator (??) untuk default jika data tidak ada
$perihal_form = $_POST['perihal'] ?? '';
$tanggal_mulai_form = $_POST['tanggal_izin'] ?? ''; // Key: tanggal_izin
$tanggal_selesai_form = $_POST['tanggal_selesai'] ?? '';
$lama_izin_form = $_POST['lama_izin'] ?? 0;       // Key: lama_izin
$alasan_form = $_POST['alasan_izin'] ?? '';     // Key: alasan_izin
$signature_data_base64 = $_POST['signature_data'] ?? ''; // Key: signature_data

// Validasi sederhana (opsional, bisa lebih detail)
if (empty($perihal_form) || empty($tanggal_mulai_form) || empty($tanggal_selesai_form) || empty($lama_izin_form) || empty($alasan_form) || empty($signature_data_base64)) {
    // Redirect kembali dengan pesan error jika ada data kosong
    header('Location: suratizin.php?error=datakosong'); // Sesuaikan nama file jika berbeda
    exit;
}

// 4. Proses Tanda Tangan (Base64)
$nama_file_ttd = ''; // Inisialisasi nama file
if (!empty($signature_data_base64)) {
    // Pisahkan header data (misal, "data:image/png;base64,")
    if (preg_match('/^data:image\/(\w+);base64,/', $signature_data_base64, $type)) {
        $signature_data_base64 = substr($signature_data_base64, strpos($signature_data_base64, ',') + 1);
        $type = strtolower($type[1]); // png, jpg, gif

        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
            header('Location: suratizin.php?error=tipettdditidakvalid');
            exit;
        }

        $signature_data_biner = base64_decode($signature_data_base64);

        if ($signature_data_biner === false) {
             header('Location: suratizin.php?error=dekodettdditidakvalid');
            exit;
        }

        // Buat nama file unik
        $nama_file_ttd = 'ttd_user_' . $user_id_session . '_' . time() . '.' . $type;
        $path_simpan_ttd = 'uploads/' . $nama_file_ttd;

        // Simpan file gambar
        if (!file_put_contents($path_simpan_ttd, $signature_data_biner)) {
            // Gagal menyimpan file, cek permission folder 'uploads/'
             header('Location: suratizin.php?error=gagalsimpanttd');
            exit;
        }

    } else {
         header('Location: suratizin.php?error=formatttdditidakvalid');
        exit;
    }
} else {
     header('Location: suratizin.php?error=ttdkosong');
    exit;
}

// 5. Ambil Data Pegawai Lengkap dari Database
$user_data = null; // Inisialisasi
$sql_user = "SELECT id, nama_lengkap, posisi, outlet FROM register WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id_session);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

if (!$user_data) {
    // Pengguna tidak ditemukan (meskipun ada sesi?), ini aneh
    mysqli_close($conn);
     header('Location: index.php?error=penggunatidakditemukan');
    exit;
}

// 6. Siapkan Variabel Lain untuk Template
$tanggal_hari_ini = date('j F Y'); // Format: 21 October 2025

// 7. Inisialisasi OpenTBS
$TBS = new clsTinyButStrong;
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

// 8. Muat Template Word
$template_file = 'template.docx'; // Pastikan nama file template benar
$TBS->LoadTemplate($template_file);

// 9. Gabungkan Data (MergeField) - Sesuaikan placeholder [..] dengan template Anda
$TBS->MergeField('date', $tanggal_hari_ini);
$TBS->MergeField('perihal', $perihal_form);
$TBS->MergeField('nama_panjang', $user_data['nama_lengkap']);
$TBS->MergeField('number', $user_data['id']); // Asumsi [number] adalah ID
$TBS->MergeField('cabang', $user_data['outlet']); // Asumsi [cabang] adalah outlet
$TBS->MergeField('posisi', $user_data['posisi']);
$TBS->MergeField('subjek', $alasan_form); // Asumsi [subjek] adalah alasan
$TBS->MergeField('hari', $lama_izin_form); // Asumsi [hari] adalah lama izin
$TBS->MergeField('tanggal_mulai', $tanggal_mulai_form);
$TBS->MergeField('tanggal_selesai', $tanggal_selesai_form);
$TBS->MergeField('nama_panjang2', $user_data['nama_lengkap']); // Asumsi sama

// 10. Masukkan Gambar Tanda Tangan
// Pastikan placeholder di Word (Alt Text -> Title) adalah 'ttd'
$path_ttd_untuk_word = 'uploads/' . $nama_file_ttd;
if (file_exists($path_ttd_untuk_word)) {
    $TBS->PlugIn(OPENTBS_CHANGE_PICTURE, 'ttd', $path_ttd_untuk_word);
} else {
    // Handle jika file ttd tidak ada (meskipun seharusnya sudah disimpan)
    // Mungkin beri placeholder teks saja
    $TBS->MergeField('ttd', '(Tanda Tangan Gagal Dimuat)'); 
}
// ... (Ambil data user ke $user_data) ...

$nama_file_ttd_final = ''; // Variabel untuk nama file TTD yang akan disimpan ke pengajuan_izin

// Cek apakah TTD sudah ada di profil user
if (empty($user_data['tanda_tangan_file'])) {
    // --- BELUM ADA TTD: Proses yang baru di-submit ---
    $signature_data_base64 = $_POST['signature_data'] ?? '';
    if (empty($signature_data_base64)) {
         header('Location: suratizin.php?error=ttdkosong'); // TTD wajib jika belum ada
         exit;
    }

    // ... (Logika pisahkan header, base64_decode ke $signature_data_biner) ...

    // Buat nama file unik (mungkin tanpa timestamp sekarang?)
    $nama_file_ttd_baru = 'ttd_user_' . $user_id_session . '.' . $type; // Misal: ttd_user_2.png
    $path_simpan_ttd = 'uploads/' . $nama_file_ttd_baru;

    // Simpan file gambar
    if (!file_put_contents($path_simpan_ttd, $signature_data_biner)) {
        header('Location: suratizin.php?error=gagalsimpanttd');
        exit;
    }

    // !!! UPDATE tabel register !!!
    $sql_update_ttd = "UPDATE register SET tanda_tangan_file = ? WHERE id = ?";
    $stmt_update_ttd = mysqli_prepare($conn, $sql_update_ttd);
    mysqli_stmt_bind_param($stmt_update_ttd, "si", $nama_file_ttd_baru, $user_id_session);
    mysqli_stmt_execute($stmt_update_ttd);
    mysqli_stmt_close($stmt_update_ttd);
    // (Tambahkan error handling jika perlu)

    $nama_file_ttd_final = $nama_file_ttd_baru; // Gunakan nama file baru ini

} else {
    // --- SUDAH ADA TTD: Gunakan yang lama ---
    $nama_file_ttd_final = $user_data['tanda_tangan_file']; 
    // Kita tidak perlu memproses $_POST['signature_data'] karena canvas disembunyikan
}

// ... (Lanjutkan ke MergeField, PlugIn, Simpan Word, INSERT ke pengajuan_izin) ...

// Saat INSERT ke pengajuan_izin, gunakan $nama_file_ttd_final
// mysqli_stmt_bind_param($stmt_insert, "isssisss", ..., $nama_file_surat, $nama_file_ttd_final); 

// Saat PlugIn gambar ke Word, gunakan path lengkapnya
$path_ttd_untuk_word = 'uploads/' . $nama_file_ttd_final;
if (file_exists($path_ttd_untuk_word)) {
    $TBS->PlugIn(OPENTBS_CHANGE_PICTURE, 'ttd', $path_ttd_untuk_word);
} 
// ...

// 11. Simpan File Word ke Server (Folder 'surat_izin/')
$nama_file_surat = 'surat_izin_user_' . $user_id_session . '_' . time() . '.docx';
$path_simpan_surat = 'surat_izin/' . $nama_file_surat;
$TBS->Show(OPENTBS_FILE, $path_simpan_surat); // Simpan ke file

// Cek apakah file berhasil disimpan (opsional tapi bagus)
if (!file_exists($path_simpan_surat)) {
     header('Location: suratizin.php?error=gagalsimpansurat');
     mysqli_close($conn);
    exit;
}

// 12. Simpan Data Pengajuan ke Database `pengajuan_izin`
$sql_insert = "INSERT INTO pengajuan_izin 
                (user_id, perihal, tanggal_mulai, tanggal_selesai, lama_izin, alasan, file_surat, tanda_tangan_file, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')"; // Status default Pending
$stmt_insert = mysqli_prepare($conn, $sql_insert);
// Tipe data: i (integer), s (string), s, s, i, s, s, s
mysqli_stmt_bind_param($stmt_insert, "isssisss", 
    $user_id_session, 
    $perihal_form,
    $tanggal_mulai_form,
    $tanggal_selesai_form,
    $lama_izin_form,
    $alasan_form,
    $nama_file_surat,       // Simpan nama file saja
    $nama_file_ttd          // Simpan nama file saja
);

// 13. Eksekusi INSERT dan Redirect
if (mysqli_stmt_execute($stmt_insert)) {
    // Jika INSERT berhasil, arahkan ke dashboard user dengan pesan sukses
    mysqli_stmt_close($stmt_insert);
    mysqli_close($conn);
    header('Location: mainpageuser.php?status=sukses'); // Ganti jika nama file beda
    exit;
} else {
    // Jika INSERT gagal
    $error_msg = mysqli_error($conn);
    mysqli_stmt_close($stmt_insert);
    mysqli_close($conn);
    // Hapus file yang sudah terlanjur disimpan jika insert gagal (opsional)
    if (file_exists($path_simpan_ttd)) unlink($path_simpan_ttd);
    if (file_exists($path_simpan_surat)) unlink($path_simpan_surat);
     header('Location: suratizin.php?error=gagalinsertdb&msg=' . urlencode($error_msg));
    exit;
}

?>