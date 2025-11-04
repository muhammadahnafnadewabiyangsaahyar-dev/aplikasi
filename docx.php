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

// 4. Ambil Data Pegawai Lengkap dari Database (PDO)
$user_data = null;
$sql_user = "SELECT * FROM register WHERE id = ?";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute([$user_id_session]);
$user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    header('Location: index.php?error=penggunatidakditemukan');
    exit;
}

// 5. Validasi field wajib
if (empty($perihal_form) || empty($tanggal_mulai_form) || empty($tanggal_selesai_form) || empty($lama_izin_form) || empty($alasan_form)) {
    header('Location: suratizin.php?error=datakosong');
    exit;
}

// 6. Validasi tanda tangan: wajib sudah ada di profil
if (empty($user_data['tanda_tangan_file'])) {
    header('Location: suratizin.php?error=ttdkosong');
    exit;
}

// 7. Siapkan Variabel Lain untuk Template
$tanggal_hari_ini = date('j F Y');
$tanda_tangan_dir = 'uploads/tanda_tangan/';
$nama_file_ttd_final = $user_data['tanda_tangan_file'];
$path_ttd_untuk_word = $tanda_tangan_dir . $nama_file_ttd_final;

// 8. Inisialisasi OpenTBS
$TBS = new clsTinyButStrong;
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

// 9. Muat Template Word
$template_file = 'template.docx';
$TBS->LoadTemplate($template_file);

// 10. Gabungkan Data (MergeField)
$TBS->MergeField('date', $tanggal_hari_ini);
$TBS->MergeField('perihal', $perihal_form);
$TBS->MergeField('nama_panjang', $user_data['nama_lengkap']);
$TBS->MergeField('number', $user_data['id']);
$TBS->MergeField('cabang', $user_data['outlet']);
$TBS->MergeField('posisi', $user_data['posisi']);
$TBS->MergeField('subjek', $alasan_form);
$TBS->MergeField('hari', $lama_izin_form);
$TBS->MergeField('tanggal_mulai', $tanggal_mulai_form);
$TBS->MergeField('tanggal_izin', $tanggal_mulai_form);
$TBS->MergeField('tanggal_selesai', $tanggal_selesai_form);
$TBS->MergeField('nama_panjang2', $user_data['nama_lengkap']);

// 11. Masukkan Gambar Tanda Tangan
if (file_exists($path_ttd_untuk_word)) {
    $TBS->PlugIn(OPENTBS_CHANGE_PICTURE, 'ttd', $path_ttd_untuk_word);
} else {
    $TBS->MergeField('ttd', '(Tanda Tangan Gagal Dimuat)');
}

// 12. Simpan File Word ke Server (Folder 'surat_izin/')
$nama_file_surat = 'surat_izin_user_' . $user_id_session . '_' . time() . '.docx';
$folder_surat_izin = 'uploads/surat_izin/';
if (!is_dir($folder_surat_izin)) {
    mkdir($folder_surat_izin, 0777, true);
}
$path_simpan_surat = $folder_surat_izin . $nama_file_surat;
// Jangan hapus file lama, biarkan file baru selalu unik
$TBS->Show(OPENTBS_FILE, $path_simpan_surat);

if (!file_exists($path_simpan_surat)) {
    header('Location: suratizin.php?error=gagalsimpansurat');
    exit;
}

// 13. Simpan Data Pengajuan ke Database
$sql_insert = "INSERT INTO pengajuan_izin (user_id, perihal, tanggal_mulai, tanggal_selesai, lama_izin, alasan, file_surat, tanda_tangan_file, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmt_insert = $pdo->prepare($sql_insert);
$stmt_insert->execute([
    $user_id_session,
    $perihal_form,
    $tanggal_mulai_form,
    $tanggal_selesai_form,
    $lama_izin_form,
    $alasan_form,
    $nama_file_surat,
    $nama_file_ttd_final
]);

if ($stmt_insert) {
    // 14. Kirim Email Notifikasi ke HR dan Kepala Toko
    require_once __DIR__ . '/email_helper.php';
    
    $pengajuan_id = $pdo->lastInsertId();
    
    $izin_data = [
        'id' => $pengajuan_id,
        'tanggal_mulai' => $tanggal_mulai_form,
        'tanggal_selesai' => $tanggal_selesai_form,
        'durasi_hari' => $lama_izin_form,
        'alasan' => $alasan_form,
        'jenis_izin' => $perihal_form
    ];
    
    // Kirim email notification
    $email_sent = sendEmailIzinBaru($izin_data, $user_data, $pdo);
    
    if ($email_sent) {
        error_log("✅ Email notifikasi izin #{$pengajuan_id} berhasil dikirim");
    } else {
        error_log("⚠️ Email notifikasi izin #{$pengajuan_id} gagal dikirim (izin tetap tersimpan)");
    }
    
    header('Location: mainpage.php?status=sukses');
    exit;
} else {
    if (file_exists($path_simpan_surat)) unlink($path_simpan_surat);
    header('Location: suratizin.php?error=gagalinsertdb');
    exit;
}
?>