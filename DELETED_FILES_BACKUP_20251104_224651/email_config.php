<?php
/**
 * EMAIL CONFIGURATION
 * 
 * Konfigurasi untuk PHPMailer
 * Untuk production, gunakan environment variable atau file terpisah yang tidak di-commit ke git
 */

// SMTP Configuration (Menggunakan konfigurasi yang sudah ada dari forgot_password.php)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465); // Port untuk SMTPS
define('SMTP_USERNAME', 'kaori.aplikasi.notif@gmail.com');
define('SMTP_PASSWORD', 'imjq nmeq vyig umgn'); // App password
define('SMTP_SECURE', 'ssl'); // Gunakan SSL untuk port 465

// Email Settings
define('EMAIL_FROM', 'kaori.aplikasi.notif@gmail.com');
define('EMAIL_FROM_NAME', 'Sistem KAORI - HR');
define('EMAIL_REPLY_TO', 'kaori.aplikasi.notif@gmail.com');

// Application URL
define('APP_URL', 'http://localhost/aplikasi'); // Base URL aplikasi (ganti untuk production)

// Debug Mode (set false untuk production)
define('EMAIL_DEBUG', true); // true = tampilkan error detail, false = hide error

/**
 * Get email HR dan Kepala Toko dari database secara dinamis
 * 
 * @param PDO $pdo Database connection
 * @return array ['hr' => array of emails, 'kepala_toko' => array of emails]
 */
function getApproverEmails($pdo) {
    $emails = [
        'hr' => [],
        'kepala_toko' => []
    ];
    
    try {
        // Get HR emails
        $stmt_hr = $pdo->query("SELECT email, nama_lengkap FROM register WHERE posisi LIKE '%HR%' OR posisi LIKE '%hr%'");
        $hr_users = $stmt_hr->fetchAll(PDO::FETCH_ASSOC);
        foreach ($hr_users as $user) {
            if (!empty($user['email'])) {
                $emails['hr'][] = [
                    'email' => $user['email'],
                    'name' => $user['nama_lengkap']
                ];
            }
        }
        
        // Get Kepala Toko/Owner/Manager emails
        $stmt_kepala = $pdo->query("SELECT email, nama_lengkap FROM register WHERE posisi LIKE '%owner%' OR posisi LIKE '%Owner%' OR posisi LIKE '%kepala%' OR posisi LIKE '%Kepala%' OR posisi LIKE '%manager%' OR posisi LIKE '%Manager%'");
        $kepala_users = $stmt_kepala->fetchAll(PDO::FETCH_ASSOC);
        foreach ($kepala_users as $user) {
            if (!empty($user['email'])) {
                $emails['kepala_toko'][] = [
                    'email' => $user['email'],
                    'name' => $user['nama_lengkap']
                ];
            }
        }
        
        // Fallback: jika tidak ada HR atau Kepala Toko, gunakan admin
        if (empty($emails['hr']) && empty($emails['kepala_toko'])) {
            $stmt_admin = $pdo->query("SELECT email, nama_lengkap FROM register WHERE role = 'admin' LIMIT 1");
            $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
            if ($admin && !empty($admin['email'])) {
                $emails['hr'][] = [
                    'email' => $admin['email'],
                    'name' => $admin['nama_lengkap']
                ];
            }
        }
        
    } catch (PDOException $e) {
        error_log("Error getting approver emails: " . $e->getMessage());
    }
    
    return $emails;
}

/**
 * CATATAN PENTING:
 * 
 * Untuk Gmail:
 * 1. Enable "Less secure app access" ATAU
 * 2. Generate "App Password" (recommended):
 *    - Go to: https://myaccount.google.com/apppasswords
 *    - Generate app password untuk "Mail"
 *    - Gunakan app password tersebut sebagai SMTP_PASSWORD
 * 
 * Untuk SMTP server lain:
 * - Sesuaikan SMTP_HOST, SMTP_PORT, dan SMTP_SECURE
 * - Konsultasi dengan provider email Anda
 */
