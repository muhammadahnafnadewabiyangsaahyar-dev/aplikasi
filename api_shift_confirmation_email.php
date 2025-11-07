<?php
// DEBUG: Tampilkan semua error di browser
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start output buffering immediately
ob_start();

session_start();
require_once 'connect.php';
require 'vendor/autoload.php';

// Clear any previous output
ob_end_clean();

// Start fresh output buffer
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Disable any potential output from error handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$shift_id = $_POST['shift_id'] ?? null;
$status = $_POST['status'] ?? null; // 'confirmed' or 'declined'
$catatan = $_POST['catatan'] ?? '';
$decline_reason = $_POST['decline_reason'] ?? null; // 'sakit', 'izin', or 'reschedule'

// Validation
if (!$shift_id || !$status) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit();
}

if (!in_array($status, ['confirmed', 'declined'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Status tidak valid']);
    exit();
}

// If declined, decline_reason is required
if ($status === 'declined' && !$decline_reason) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Alasan penolakan harus dipilih']);
    exit();
}

if ($decline_reason && !in_array($decline_reason, ['sakit', 'izin', 'reschedule'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Alasan penolakan tidak valid']);
    exit();
}

// Get shift details and verify ownership
$sql_verify = "SELECT sa.*, c.nama_cabang, c.nama_shift, c.jam_masuk, c.jam_keluar,
               r.nama_lengkap, r.email
               FROM shift_assignments sa
               JOIN cabang c ON sa.cabang_id = c.id
               JOIN register r ON sa.user_id = r.id
               WHERE sa.id = ? AND sa.user_id = ?";
$stmt_verify = $pdo->prepare($sql_verify);
$stmt_verify->execute([$shift_id, $user_id]);
$shift = $stmt_verify->fetch(PDO::FETCH_ASSOC);

if (!$shift) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Shift tidak ditemukan atau bukan milik Anda']);
    exit();
}

// Prevent changes if already confirmed/declined
if ($shift['status_konfirmasi'] !== 'pending') {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Shift sudah dikonfirmasi sebelumnya dan tidak dapat diubah']);
    exit();
}

// Update shift status
$sql_update = "UPDATE shift_assignments 
               SET status_konfirmasi = ?, 
                   waktu_konfirmasi = NOW(), 
                   catatan_pegawai = ?,
                   decline_reason = ?,
                   updated_at = NOW()
               WHERE id = ?";
$stmt_update = $pdo->prepare($sql_update);

if (!$stmt_update->execute([$status, $catatan, $decline_reason, $shift_id])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Gagal update status']);
    exit();
}

// Get HR and store manager emails
$sql_recipients = "SELECT DISTINCT r.email, r.nama_lengkap, r.role
                   FROM register r
                   WHERE r.role IN ('hr', 'kepala_toko') 
                   AND r.email IS NOT NULL 
                   AND r.email != ''";
$stmt_recipients = $pdo->prepare($sql_recipients);
$stmt_recipients->execute();
$recipients = $stmt_recipients->fetchAll(PDO::FETCH_ASSOC);

// Prepare email content
$employeeName = $shift['nama_lengkap'];
$shiftDate = date('d F Y', strtotime($shift['tanggal_shift']));
$dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w', strtotime($shift['tanggal_shift']))];
$outletName = $shift['nama_cabang'];
$shiftType = $shift['nama_shift'];
$shiftTime = substr($shift['jam_masuk'], 0, 5) . ' - ' . substr($shift['jam_keluar'], 0, 5);

// Build subject and message based on status
if ($status === 'confirmed') {
    $subject = '✅ Konfirmasi Shift - ' . $employeeName;
    $statusText = '<span style="color: #4caf50; font-weight: bold;">DIKONFIRMASI</span>';
    $messageTitle = 'Shift Telah Dikonfirmasi';
    $additionalInfo = '';
} else {
    // Declined
    $reasonLabels = [
        'sakit' => 'Sakit',
        'izin' => 'Izin',
        'reschedule' => 'Meminta Reschedule'
    ];
    $reasonLabel = $reasonLabels[$decline_reason] ?? $decline_reason;
    
    $subject = '❌ Penolakan Shift - ' . $employeeName . ' (' . $reasonLabel . ')';
    $statusText = '<span style="color: #f44336; font-weight: bold;">DITOLAK</span>';
    $messageTitle = 'Shift Ditolak';
    $additionalInfo = '
        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <strong>Alasan Penolakan:</strong> ' . $reasonLabel . '
            ' . ($catatan ? '<br><strong>Catatan:</strong> ' . htmlspecialchars($catatan) : '') . '
        </div>
    ';
}

$emailBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background-color: #ffffff; padding: 30px; }
        .shift-card { background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .shift-card table { width: 100%; border-collapse: collapse; }
        .shift-card td { padding: 8px 0; }
        .shift-card td:first-child { font-weight: bold; width: 40%; color: #666; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; }
        .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-size: 14px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>📋 " . $messageTitle . "</h1>
        </div>
        <div class='content'>
            <p>Halo,</p>
            <p>Informasi konfirmasi shift dari pegawai:</p>
            
            <div class='shift-card'>
                <table>
                    <tr>
                        <td>Pegawai</td>
                        <td><strong>" . htmlspecialchars($employeeName) . "</strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal Shift</td>
                        <td><strong>" . $shiftDate . " (" . $dayName . ")</strong></td>
                    </tr>
                    <tr>
                        <td>Lokasi</td>
                        <td>" . htmlspecialchars($outletName) . "</td>
                    </tr>
                    <tr>
                        <td>Jenis Shift</td>
                        <td>" . htmlspecialchars($shiftType) . "</td>
                    </tr>
                    <tr>
                        <td>Jam Kerja</td>
                        <td>" . $shiftTime . "</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>" . $statusText . "</td>
                    </tr>
                </table>
            </div>
            
            " . $additionalInfo . "
            
            <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;'>
                Email ini dikirim secara otomatis oleh sistem. Silakan login ke sistem untuk melihat detail lebih lanjut.
            </p>
        </div>
        <div class='footer'>
            <p>&copy; " . date('Y') . " Shift Management System. All rights reserved.</p>
            <p>Email ini dikirim ke HR dan Kepala Toko</p>
        </div>
    </div>
</body>
</html>
";

// Send email to all recipients
$emailSuccess = 0;
$emailFailed = 0;

foreach ($recipients as $recipient) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kaori.aplikasi.notif@gmail.com';
        $mail->Password = 'imjq nmeq vyig umgn'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Recipients
        $mail->setFrom('kaori.aplikasi.notif@gmail.com', 'Shift Management System');
        $mail->addAddress($recipient['email'], $recipient['nama_lengkap']);
        
        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $emailBody;
        
        $mail->send();
        $emailSuccess++;
        
    } catch (Exception $e) {
        $emailFailed++;
        // Show error in JSON response for debugging
        $lastError = $mail->ErrorInfo . ' | Exception: ' . $e->getMessage();
    }
}

// Prepare success message
$message = $status === 'confirmed' 
    ? 'Shift berhasil dikonfirmasi' 
    : 'Shift berhasil ditolak dengan alasan: ' . $reasonLabels[$decline_reason];

if ($emailSuccess > 0) {
    $message .= ". Email notifikasi telah dikirim ke HR dan Kepala Toko ($emailSuccess berhasil)";
}

if ($emailFailed > 0 && isset($lastError)) {
    $message .= "<br><strong>Error detail:</strong> " . htmlspecialchars($lastError);
}

// Clear buffer before final output
ob_end_clean();
echo json_encode([
    'status' => 'success', 
    'message' => $message,
    'emailSent' => $emailSuccess,
    'emailFailed' => $emailFailed
]);
exit();
?>
