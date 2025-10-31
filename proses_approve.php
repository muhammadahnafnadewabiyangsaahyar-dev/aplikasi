<?php
session_start();
header('Content-Type: application/json'); // Respons selalu JSON

require 'vendor/autoload.php';

// --- Keamanan ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak (Bukan Admin).']);
    exit;
}

// --- Muat Dependensi & Koneksi ---
include 'connect.php'; 
// !!! PENTING: Jika menggunakan Composer, sertakan autoload PHPMailer !!!
// Jika download manual, sesuaikan path include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Validasi Input ---
if (!isset($_POST['pengajuan_id']) || !isset($_POST['action']) || !is_numeric($_POST['pengajuan_id'])) {
    echo json_encode(['success' => false, 'message' => 'Data permintaan tidak valid.']);
    exit;
}

$pengajuan_id = (int)$_POST['pengajuan_id'];
$action = $_POST['action'];
$new_status = '';
$email_subject = '';
$email_body_base = ''; // Pesan dasar untuk email
$wa_message_base = ''; // Pesan dasar untuk WA

// --- Tentukan Status Baru & Pesan Notifikasi ---
// !!! PENTING: Sesuaikan 'Approved'/'Rejected' dengan 'Disetujui'/'Ditolak' jika Anda menggunakan itu di query Riwayat !!!
if ($action == 'approve') {
    $new_status = 'Approved'; 
    $email_subject = "Pengajuan Izin Anda Disetujui (ID: #{$pengajuan_id})";
    $email_body_base = "Pengajuan izin Anda dengan ID #{$pengajuan_id} telah disetujui oleh admin.";
    $wa_message_base = "Info KAORI: Pengajuan izin Anda ID #{$pengajuan_id} telah disetujui.";
} elseif ($action == 'reject') {
    $new_status = 'Rejected';
    $email_subject = "Pengajuan Izin Anda Ditolak (ID: #{$pengajuan_id})";
    $email_body_base = "Mohon maaf, pengajuan izin Anda dengan ID #{$pengajuan_id} ditolak.";
    // Anda bisa tambahkan input alasan penolakan jika mau
    $wa_message_base = "Info KAORI: Mohon maaf, pengajuan izin Anda ID #{$pengajuan_id} ditolak.";
} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
    exit;
}

// === UPDATE DATABASE ===
$conn->begin_transaction(); // Mulai transaksi

try {
    $sql_update = "UPDATE pengajuan_izin SET status = ? WHERE id = ? AND status = 'Pending'"; // Update hanya jika masih pending
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "si", $new_status, $pengajuan_id);

    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception("Gagal mengupdate status: " . mysqli_error($conn));
    }
    
    // Cek apakah ada baris yang terpengaruh (mencegah update ganda)
    if (mysqli_stmt_affected_rows($stmt_update) == 0) {
         throw new Exception("Pengajuan tidak ditemukan atau sudah diproses.");
    }
    mysqli_stmt_close($stmt_update);

    // === AMBIL INFO USER UNTUK NOTIFIKASI ===
    $sql_user_info = "SELECT r.email, r.no_whatsapp, r.nama_lengkap 
                      FROM pengajuan_izin p 
                      JOIN register r ON p.user_id = r.id 
                      WHERE p.id = ?";
    $stmt_user_info = mysqli_prepare($conn, $sql_user_info);
    mysqli_stmt_bind_param($stmt_user_info, "i", $pengajuan_id);
    mysqli_stmt_execute($stmt_user_info);
    $result_user_info = mysqli_stmt_get_result($stmt_user_info);
    $user_info = mysqli_fetch_assoc($result_user_info);
    mysqli_stmt_close($stmt_user_info);

    if (!$user_info) {
        throw new Exception("Gagal mengambil info user untuk notifikasi.");
    }
        
    $user_email = $user_info['email'];
    $user_wa = $user_info['no_whatsapp']; 
    $user_nama = $user_info['nama_lengkap'];
    
    // Tambahkan sapaan ke pesan
    $email_body = "Halo " . htmlspecialchars($user_nama) . ",<br><br>" . $email_body_base;
    $wa_message = "Halo " . $user_nama . ", " . $wa_message_base;

    // --- PROTOTYPE WHATSAPP: Buat Link Click-to-Chat ---
    // Pastikan nomor WA dalam format internasional tanpa '+' atau spasi (misal: 62812...)
    //$wa_number_clean = preg_replace('/[^0-9]/', '', $user_wa); 
    // Jika tidak dimulai dengan 62, tambahkan (asumsi nomor Indonesia)
    //if (substr($wa_number_clean, 0, 2) !== '62') {
         //if ($wa_number_clean[0] === '0') {
             //$wa_number_clean = '62' . substr($wa_number_clean, 1);
         //} else {
             //$wa_number_clean = '62' . $wa_number_clean; // Tambahkan asumsi
         //}
    //}
    //$wa_encoded_message = urlencode($wa_message);
    //$wa_link = "https://wa.me/{$wa_number_clean}?text={$wa_encoded_message}";
    
    // Tambahkan link WA ke email
    $email_body .= "<br><br>Untuk informasi lebih lanjut, silakan hubungi HR atau balas Email ini.";

    // === KIRIM NOTIFIKASI EMAIL (Gunakan PHPMailer) ===
    // !!! ANDA HARUS MENGINSTAL PHPMailer dan MENGISI DETAIL SMTP !!!
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi Server SMTP (contoh Gmail, perlu Allow Less Secure Apps atau App Password)
        // $mail->SMTPDebug = 2; // Aktifkan untuk debugging detail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Ganti dengan host SMTP Anda
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kaori.aplikasi.notif@gmail.com'; // Ganti email Anda
        $mail->Password   = 'imjq nmeq vyig umgn'; // Ganti password aplikasi / password biasa
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Gunakan SMTPS untuk Gmail
        $mail->Port       = 465; // Port SMTPS Gmail

        // Pengirim & Penerima
        $mail->setFrom('kaori.aplikasi.notif@gmail.com', 'Sistem Notifikasi KAORI'); // Ganti email pengirim
        $mail->addAddress($user_email, $user_nama); // Penerima

        // Konten Email
        $mail->isHTML(true); // Set email format ke HTML
        $mail->Subject = $email_subject;
        $mail->Body    = $email_body;
        $mail->AltBody = strip_tags($email_body); // Versi teks biasa

        $mail->send();
        // Email berhasil terkirim
        
    } catch (Exception $e) {
        // Gagal kirim email, log error tapi jangan hentikan proses utama
        error_log("PHPMailer Error [ID: {$pengajuan_id}]: " . $mail->ErrorInfo);
        // Anda bisa memilih untuk melempar exception di sini jika email WAJIB terkirim
        // throw new Exception("Gagal mengirim email notifikasi."); 
    }
    

    // Jika semua berhasil sampai sini
    $conn->commit(); // Konfirmasi semua perubahan database
    echo json_encode(['success' => true, 'message' => 'Status berhasil diubah dan notifikasi dijadwalkan.']);

} catch (Exception $e) {
    $conn->rollback(); // Batalkan perubahan database jika ada error
    error_log("Proses Approve Error [ID: {$pengajuan_id}]: " . $e->getMessage()); // Catat error di log server
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>