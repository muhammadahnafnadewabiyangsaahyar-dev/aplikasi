<?php
/**
 * EMAIL HELPER FUNCTIONS
 * 
 * Helper functions untuk mengirim email notification menggunakan PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/email_config.php';

/**
 * Inisialisasi PHPMailer dengan konfigurasi SMTP
 * 
 * @return PHPMailer
 */
function initMailer() {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        
        // Gunakan SMTPS (SSL) untuk port 465
        if (SMTP_SECURE === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port       = SMTP_PORT;
        
        // Default From
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);
        
        // Format
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        return $mail;
        
    } catch (Exception $e) {
        error_log("Email Init Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Kirim email notifikasi izin baru ke HR dan Kepala Toko
 * 
 * @param array $izin_data Data izin yang diajukan
 * @param array $user_data Data user yang mengajukan
 * @param PDO $pdo Database connection
 * @return bool Success status
 */
function sendEmailIzinBaru($izin_data, $user_data, $pdo) {
    $mail = initMailer();
    if (!$mail) return false;
    
    try {
        // Get approver emails from database
        $approvers = getApproverEmails($pdo);
        
        // Add HR emails
        foreach ($approvers['hr'] as $hr) {
            $mail->addAddress($hr['email'], $hr['name']);
        }
        
        // Add Kepala Toko emails
        foreach ($approvers['kepala_toko'] as $kepala) {
            $mail->addAddress($kepala['email'], $kepala['name']);
        }
        
        // Jika tidak ada recipient, log error
        if (empty($approvers['hr']) && empty($approvers['kepala_toko'])) {
            error_log("⚠️ Tidak ada email approver (HR/Kepala Toko) yang ditemukan");
            return false;
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = '🔔 Permohonan Izin Baru - ' . $user_data['nama_lengkap'];
        
        // Build email body
        $approve_link = APP_URL . '/approve.php?id=' . $izin_data['id'];
        
        $mail->Body = getEmailTemplateIzinBaru($izin_data, $user_data, $approve_link);
        $mail->AltBody = strip_tags($mail->Body); // Plain text version
        
        // Send
        $result = $mail->send();
        
        if ($result) {
            error_log("✅ Email izin baru berhasil dikirim ke " . count($approvers['hr']) . " HR & " . count($approvers['kepala_toko']) . " Kepala Toko");
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("❌ Email Error: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Kirim email notifikasi approve/reject ke user yang mengajukan izin
 * 
 * @param array $izin_data Data izin
 * @param array $user_data Data user yang mengajukan
 * @param string $status 'Disetujui' atau 'Ditolak'
 * @param string $catatan Catatan dari approver (optional)
 * @param array $approver_data Data approver (optional)
 * @return bool Success status
 */
function sendEmailIzinStatus($izin_data, $user_data, $status, $catatan = '', $approver_data = null) {
    $mail = initMailer();
    if (!$mail) return false;
    
    try {
        // Recipient - kirim ke email user yang mengajukan
        if (empty($user_data['email'])) {
            error_log("⚠️ User tidak memiliki email, skip notification");
            return false;
        }
        
        $mail->addAddress($user_data['email'], $user_data['nama_lengkap']);
        
        // Content
        $mail->isHTML(true);
        $status_icon = $status === 'Disetujui' ? '✅' : '❌';
        $mail->Subject = $status_icon . ' Permohonan Izin ' . $status . ' - ' . date('d M Y', strtotime($izin_data['tanggal_mulai']));
        
        // Build email body
        $mail->Body = getEmailTemplateIzinStatus($izin_data, $user_data, $status, $catatan, $approver_data);
        $mail->AltBody = strip_tags($mail->Body);
        
        // Send
        $result = $mail->send();
        
        if ($result) {
            error_log("✅ Email status izin berhasil dikirim ke " . $user_data['email']);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("❌ Email Error: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Template email untuk notifikasi izin baru
 */
function getEmailTemplateIzinBaru($izin_data, $user_data, $approve_link) {
    $tanggal_mulai = date('d M Y', strtotime($izin_data['tanggal_mulai']));
    $tanggal_selesai = date('d M Y', strtotime($izin_data['tanggal_selesai']));
    $durasi = $izin_data['durasi_hari'] ?? 1;
    $jenis_izin = $izin_data['jenis_izin'] ?? 'Izin';
    
    return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #667eea; }
        .info-row { display: flex; padding: 10px 0; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; width: 150px; color: #666; }
        .info-value { flex: 1; color: #333; }
        .button { display: inline-block; background: #667eea; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; margin: 20px 10px 10px 0; font-weight: bold; }
        .button:hover { background: #5568d3; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .alert { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 6px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0;'>🔔 Permohonan Izin Baru</h1>
            <p style='margin: 10px 0 0 0; opacity: 0.9;'>Menunggu Persetujuan Anda</p>
        </div>
        
        <div class='content'>
            <div class='alert'>
                <strong>⏰ Perlu Tindakan:</strong> Ada permohonan izin baru yang memerlukan persetujuan Anda.
            </div>
            
            <div class='info-box'>
                <h2 style='margin-top: 0; color: #667eea;'>📋 Detail Permohonan Izin</h2>
                
                <div class='info-row'>
                    <div class='info-label'>Nama Pegawai:</div>
                    <div class='info-value'><strong>{$user_data['nama_lengkap']}</strong></div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Posisi:</div>
                    <div class='info-value'>{$user_data['posisi']}</div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Jenis Izin:</div>
                    <div class='info-value'><strong>{$jenis_izin}</strong></div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Tanggal Mulai:</div>
                    <div class='info-value'>{$tanggal_mulai}</div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Tanggal Selesai:</div>
                    <div class='info-value'>{$tanggal_selesai}</div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Durasi:</div>
                    <div class='info-value'>{$durasi} hari</div>
                </div>
                
                <div class='info-row' style='border-bottom: none;'>
                    <div class='info-label'>Alasan:</div>
                    <div class='info-value'>{$izin_data['alasan']}</div>
                </div>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$approve_link}' class='button'>
                    👉 Proses Permohonan Izin
                </a>
            </div>
            
            <p style='color: #666; font-size: 14px; text-align: center;'>
                Atau copy link berikut ke browser Anda:<br>
                <a href='{$approve_link}' style='color: #667eea;'>{$approve_link}</a>
            </p>
        </div>
        
        <div class='footer'>
            <p>Email ini dikirim otomatis oleh Sistem Absensi.</p>
            <p>Jika Anda memiliki pertanyaan, silakan hubungi HR.</p>
        </div>
    </div>
</body>
</html>
    ";
}

/**
 * Template email untuk notifikasi status izin (approve/reject)
 */
function getEmailTemplateIzinStatus($izin_data, $user_data, $status, $catatan, $approver_data) {
    $tanggal_mulai = date('d M Y', strtotime($izin_data['tanggal_mulai']));
    $tanggal_selesai = date('d M Y', strtotime($izin_data['tanggal_selesai']));
    $durasi = $izin_data['durasi_hari'] ?? 1;
    $jenis_izin = $izin_data['jenis_izin'] ?? 'Izin';
    
    $is_approved = $status === 'Disetujui';
    $status_color = $is_approved ? '#4CAF50' : '#f44336';
    $status_bg = $is_approved ? '#e8f5e9' : '#ffebee';
    $status_icon = $is_approved ? '✅' : '❌';
    $gradient = $is_approved ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
    
    $approver_name = $approver_data ? $approver_data['nama_lengkap'] : 'Admin';
    
    $catatan_html = '';
    if (!empty($catatan)) {
        $catatan_html = "
        <div class='info-row' style='border-bottom: none;'>
            <div class='info-label'>Catatan:</div>
            <div class='info-value'>{$catatan}</div>
        </div>
        ";
    }
    
    return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {$gradient}; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid {$status_color}; }
        .info-row { display: flex; padding: 10px 0; border-bottom: 1px solid #eee; }
        .info-label { font-weight: bold; width: 150px; color: #666; }
        .info-value { flex: 1; color: #333; }
        .status-badge { background: {$status_bg}; color: {$status_color}; padding: 15px 30px; border-radius: 8px; display: inline-block; font-size: 18px; font-weight: bold; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0;'>{$status_icon} Permohonan Izin {$status}</h1>
            <p style='margin: 10px 0 0 0; opacity: 0.9;'>Update Status Permohonan</p>
        </div>
        
        <div class='content'>
            <div style='text-align: center;'>
                <div class='status-badge'>
                    {$status_icon} STATUS: {$status}
                </div>
            </div>
            
            <p>Halo <strong>{$user_data['nama_lengkap']}</strong>,</p>
            
            <p>Permohonan izin Anda telah <strong>{$status}</strong> oleh <strong>{$approver_name}</strong>.</p>
            
            <div class='info-box'>
                <h2 style='margin-top: 0; color: {$status_color};'>📋 Detail Permohonan Izin</h2>
                
                <div class='info-row'>
                    <div class='info-label'>Jenis Izin:</div>
                    <div class='info-value'><strong>{$jenis_izin}</strong></div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Tanggal Mulai:</div>
                    <div class='info-value'>{$tanggal_mulai}</div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Tanggal Selesai:</div>
                    <div class='info-value'>{$tanggal_selesai}</div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Durasi:</div>
                    <div class='info-value'>{$durasi} hari</div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Status:</div>
                    <div class='info-value' style='color: {$status_color}; font-weight: bold;'>{$status}</div>
                </div>
                
                <div class='info-row'>
                    <div class='info-label'>Diproses oleh:</div>
                    <div class='info-value'>{$approver_name}</div>
                </div>
                
                {$catatan_html}
            </div>
            
            " . ($is_approved ? "
            <div style='background: #e8f5e9; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0; border-radius: 6px;'>
                <strong>✅ Selamat!</strong> Izin Anda telah disetujui. Pastikan untuk tetap menjaga komunikasi dengan tim Anda.
            </div>
            " : "
            <div style='background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 20px 0; border-radius: 6px;'>
                <strong>❌ Mohon Maaf,</strong> Permohonan izin Anda ditolak. Silakan hubungi HR atau atasan Anda untuk informasi lebih lanjut.
            </div>
            ") . "
        </div>
        
        <div class='footer'>
            <p>Email ini dikirim otomatis oleh Sistem Absensi.</p>
            <p>Jika Anda memiliki pertanyaan, silakan hubungi HR.</p>
        </div>
    </div>
</body>
</html>
    ";
}

/**
 * Test email configuration
 * 
 * @param string $test_email Email tujuan test
 * @return bool Success status
 */
function testEmailConfig($test_email) {
    $mail = initMailer();
    if (!$mail) return false;
    
    try {
        $mail->addAddress($test_email, 'Test User');
        $mail->Subject = '🧪 Test Email Configuration - Sistem Absensi';
        $mail->Body    = '
            <h1>✅ Email Configuration Berhasil!</h1>
            <p>Jika Anda menerima email ini, berarti konfigurasi email sudah benar.</p>
            <p>Sistem Absensi siap mengirim notifikasi.</p>
            <p><small>Email ini dikirim pada: ' . date('d M Y H:i:s') . '</small></p>
        ';
        $mail->AltBody = 'Email Configuration Test - Berhasil!';
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Test Email Error: " . $mail->ErrorInfo);
        return false;
    }
}
