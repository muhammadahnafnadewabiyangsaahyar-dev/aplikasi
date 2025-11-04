<?php
/**
 * TEST SCRIPT: Email Notification System
 * 
 * Script untuk test konfigurasi email dan fungsi-fungsi email helper
 */

require_once 'connect.php';
require_once 'email_helper.php';

echo "========================================\n";
echo "TEST EMAIL NOTIFICATION SYSTEM\n";
echo "========================================\n\n";

// ========================================
// 1. TEST EMAIL CONFIGURATION
// ========================================
echo "1. TESTING EMAIL CONFIGURATION...\n";
echo "   SMTP Host: " . SMTP_HOST . "\n";
echo "   SMTP Port: " . SMTP_PORT . "\n";
echo "   SMTP Username: " . SMTP_USERNAME . "\n";
echo "   Email From: " . EMAIL_FROM . "\n";
echo "   App URL: " . APP_URL . "\n\n";

// ========================================
// 2. GET APPROVER EMAILS
// ========================================
echo "2. GET APPROVER EMAILS FROM DATABASE...\n";
$approvers = getApproverEmails($pdo);

echo "   HR Emails (" . count($approvers['hr']) . "):\n";
if (empty($approvers['hr'])) {
    echo "      ⚠️ Tidak ada HR email ditemukan\n";
} else {
    foreach ($approvers['hr'] as $hr) {
        echo "      - {$hr['name']} <{$hr['email']}>\n";
    }
}

echo "\n   Kepala Toko Emails (" . count($approvers['kepala_toko']) . "):\n";
if (empty($approvers['kepala_toko'])) {
    echo "      ⚠️ Tidak ada Kepala Toko email ditemukan\n";
} else {
    foreach ($approvers['kepala_toko'] as $kepala) {
        echo "      - {$kepala['name']} <{$kepala['email']}>\n";
    }
}
echo "\n";

// ========================================
// 3. GET TEST USER DATA
// ========================================
echo "3. GET TEST USER DATA...\n";
$stmt = $pdo->query("SELECT id, nama_lengkap, posisi, email FROM register WHERE role != 'admin' AND email IS NOT NULL AND email != '' LIMIT 1");
$test_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$test_user) {
    echo "   ❌ Tidak ada user test ditemukan\n";
    echo "   Gunakan admin user untuk test...\n";
    $stmt = $pdo->query("SELECT id, nama_lengkap, posisi, email FROM register WHERE role = 'admin' LIMIT 1");
    $test_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($test_user) {
    echo "   ✅ Test User: {$test_user['nama_lengkap']} ({$test_user['posisi']}) <{$test_user['email']}>\n\n";
} else {
    echo "   ❌ ERROR: Tidak ada user dengan email\n";
    exit(1);
}

// ========================================
// 4. TEST BASIC EMAIL SENDING
// ========================================
echo "4. TEST BASIC EMAIL (send to test user)...\n";
$test_email_result = testEmailConfig($test_user['email']);

if ($test_email_result) {
    echo "   ✅ Test email berhasil dikirim ke {$test_user['email']}\n";
    echo "   Silakan cek inbox email Anda!\n\n";
} else {
    echo "   ❌ Test email gagal dikirim\n";
    echo "   Cek PHP error log untuk detail\n\n";
}

// ========================================
// 5. TEST EMAIL IZIN BARU (Simulasi)
// ========================================
echo "5. TEST EMAIL IZIN BARU (Simulasi)...\n";

// Buat dummy data izin
$dummy_izin = [
    'id' => 9999,
    'tanggal_mulai' => date('Y-m-d'),
    'tanggal_selesai' => date('Y-m-d', strtotime('+3 days')),
    'durasi_hari' => 3,
    'alasan' => 'Test email notification - keperluan keluarga',
    'jenis_izin' => 'Izin Sakit'
];

$dummy_user = [
    'nama_lengkap' => $test_user['nama_lengkap'],
    'posisi' => $test_user['posisi'],
    'email' => $test_user['email']
];

echo "   Mengirim email ke approver (HR & Kepala Toko)...\n";
$izin_baru_result = sendEmailIzinBaru($dummy_izin, $dummy_user, $pdo);

if ($izin_baru_result) {
    echo "   ✅ Email izin baru berhasil dikirim!\n";
    echo "   Cek inbox HR & Kepala Toko!\n\n";
} else {
    echo "   ❌ Email izin baru gagal dikirim\n";
    echo "   Kemungkinan tidak ada email HR/Kepala Toko\n\n";
}

// ========================================
// 6. TEST EMAIL IZIN STATUS (Approve)
// ========================================
echo "6. TEST EMAIL IZIN STATUS - APPROVED (Simulasi)...\n";

$dummy_approver = [
    'nama_lengkap' => 'HR Manager'
];

echo "   Mengirim email approve ke user...\n";
$approve_result = sendEmailIzinStatus($dummy_izin, $dummy_user, 'Disetujui', 'Izin Anda disetujui. Selamat!', $dummy_approver);

if ($approve_result) {
    echo "   ✅ Email approve berhasil dikirim ke {$test_user['email']}\n";
    echo "   Cek inbox user!\n\n";
} else {
    echo "   ❌ Email approve gagal dikirim\n\n";
}

// ========================================
// 7. TEST EMAIL IZIN STATUS (Reject)
// ========================================
echo "7. TEST EMAIL IZIN STATUS - REJECTED (Simulasi)...\n";

echo "   Mengirim email reject ke user...\n";
$reject_result = sendEmailIzinStatus($dummy_izin, $dummy_user, 'Ditolak', 'Maaf, izin Anda ditolak karena bla bla bla', $dummy_approver);

if ($reject_result) {
    echo "   ✅ Email reject berhasil dikirim ke {$test_user['email']}\n";
    echo "   Cek inbox user!\n\n";
} else {
    echo "   ❌ Email reject gagal dikirim\n\n";
}

// ========================================
// SUMMARY
// ========================================
echo "========================================\n";
echo "✅ TEST COMPLETE!\n";
echo "========================================\n\n";

echo "SUMMARY:\n";
echo "- Test Email: " . ($test_email_result ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "- Email Izin Baru: " . ($izin_baru_result ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "- Email Approve: " . ($approve_result ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "- Email Reject: " . ($reject_result ? "✅ SUCCESS" : "❌ FAILED") . "\n\n";

echo "NEXT STEPS:\n";
echo "1. Cek inbox email yang Anda gunakan untuk test\n";
echo "2. Jika ada email yang gagal, cek PHP error log:\n";
echo "   tail -f /Applications/XAMPP/xamppfiles/logs/error_log\n\n";
echo "3. Jika semua berhasil, sistem email siap digunakan!\n\n";

echo "========================================\n";
echo "TROUBLESHOOTING:\n";
echo "========================================\n";
echo "- Jika email tidak sampai, cek folder SPAM\n";
echo "- Pastikan Gmail App Password sudah benar\n";
echo "- Pastikan koneksi internet stabil\n";
echo "- Cek error log untuk detail error\n\n";
