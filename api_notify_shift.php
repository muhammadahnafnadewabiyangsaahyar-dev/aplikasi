<?php
// Strict error handling for JSON API
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering immediately
ob_start();

session_start();
require_once 'connect.php';
require_once 'PHPMailer/PHPMailerAutoload.php'; // Adjust path as needed

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

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$date = $input['date'] ?? null;
$cabang_id = $input['cabang_id'] ?? null;

if (!$date || !$cabang_id) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit();
}

try {
    // Get shift assignments for this date
    $sql = "SELECT 
                sa.id,
                sa.user_id,
                sa.tanggal_shift,
                c.nama_shift,
                c.jam_masuk,
                c.jam_keluar,
                c.nama_cabang,
                r.nama_lengkap,
                r.email
            FROM shift_assignments sa
            JOIN cabang c ON sa.cabang_id = c.id
            JOIN register r ON sa.user_id = r.id
            WHERE sa.tanggal_shift = :date 
            AND sa.cabang_id = :cabang_id
            AND r.email IS NOT NULL 
            AND r.email != ''";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'date' => $date,
        'cabang_id' => $cabang_id
    ]);
    
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($assignments) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada shift atau pegawai tidak memiliki email']);
        exit();
    }
    
    // Format date for display
    $dateObj = new DateTime($date);
    $formattedDate = $dateObj->format('l, d F Y'); // e.g., Monday, 02 November 2025
    
    $sentCount = 0;
    $failedCount = 0;
    $errors = [];
    
    // Send email to each employee
    foreach ($assignments as $assignment) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Adjust as needed
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@gmail.com'; // Configure this
            $mail->Password = 'your-app-password'; // Configure this
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('your-email@gmail.com', 'Shift Management System');
            $mail->addAddress($assignment['email'], $assignment['nama_lengkap']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Notifikasi Shift - ' . $formattedDate;
            
            $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #2196F3; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                        .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                        .shift-details { background-color: white; padding: 20px; border-left: 4px solid #2196F3; margin: 20px 0; border-radius: 4px; }
                        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                        .highlight { color: #2196F3; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>📅 Notifikasi Shift</h1>
                        </div>
                        <div class='content'>
                            <p>Halo <strong>{$assignment['nama_lengkap']}</strong>,</p>
                            
                            <p>Ini adalah reminder untuk shift Anda:</p>
                            
                            <div class='shift-details'>
                                <h3 style='margin-top: 0; color: #2196F3;'>Detail Shift</h3>
                                <p><strong>📍 Cabang:</strong> {$assignment['nama_cabang']}</p>
                                <p><strong>📅 Tanggal:</strong> {$formattedDate}</p>
                                <p><strong>🕐 Shift:</strong> {$assignment['nama_shift']}</p>
                                <p><strong>⏰ Jam Kerja:</strong> {$assignment['jam_masuk']} - {$assignment['jam_keluar']}</p>
                            </div>
                            
                            <p>Harap datang tepat waktu dan siap bekerja.</p>
                            
                            <p>Terima kasih atas dedikasi Anda! 💪</p>
                        </div>
                        <div class='footer'>
                            <p>Email ini dikirim otomatis oleh Shift Management System</p>
                            <p>Jangan balas email ini</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $mail->AltBody = "Halo {$assignment['nama_lengkap']},\n\nIni adalah reminder untuk shift Anda:\n\nCabang: {$assignment['nama_cabang']}\nTanggal: {$formattedDate}\nShift: {$assignment['nama_shift']}\nJam: {$assignment['jam_masuk']} - {$assignment['jam_keluar']}\n\nHarap datang tepat waktu.\n\nTerima kasih!";
            
            $mail->send();
            $sentCount++;
            
        } catch (Exception $e) {
            $failedCount++;
            $errors[] = $assignment['nama_lengkap'] . ': ' . $mail->ErrorInfo;
        }
    }
    
    $message = "Email berhasil dikirim ke $sentCount pegawai";
    if ($failedCount > 0) {
        $message .= ", gagal: $failedCount";
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'sent' => $sentCount,
        'failed' => $failedCount,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
