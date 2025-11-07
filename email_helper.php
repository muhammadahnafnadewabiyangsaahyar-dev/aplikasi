<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

function sendEmailIzinBaru($izin_data, $user_data, $pdo) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kaori.aplikasi.notif@gmail.com';
        $mail->Password = 'imjq nmeq vyig umgn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        // Set sender
        $mail->setFrom('kaori.aplikasi.notif@gmail.com', 'Sistem KAORI');

        // Get HR and Kepala Toko emails
        $sql = "SELECT email, nama_lengkap FROM register WHERE role IN ('hr', 'kepala_toko') AND email IS NOT NULL AND email != ''";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recipients as $recipient) {
            $mail->addAddress($recipient['email'], $recipient['nama_lengkap']);
        }

        // Email subject and body
        $mail->isHTML(true);
        $mail->Subject = 'Pengajuan Izin/Sakit Baru dari ' . $user_data['nama_lengkap'];
        $mail->Body = '<h3>Pengajuan Izin/Sakit Baru</h3>' .
            '<p><strong>Nama:</strong> ' . htmlspecialchars($user_data['nama_lengkap']) . '<br>' .
            '<strong>Perihal:</strong> ' . htmlspecialchars($izin_data['jenis_izin']) . '<br>' .
            '<strong>Tanggal Mulai:</strong> ' . htmlspecialchars($izin_data['tanggal_mulai']) . '<br>' .
            '<strong>Tanggal Selesai:</strong> ' . htmlspecialchars($izin_data['tanggal_selesai']) . '<br>' .
            '<strong>Lama:</strong> ' . htmlspecialchars($izin_data['durasi_hari']) . ' hari<br>' .
            '<strong>Alasan:</strong> ' . nl2br(htmlspecialchars($izin_data['alasan'])) . '</p>' .
            '<p>Silakan login ke sistem untuk memproses pengajuan ini.</p>';
        $mail->AltBody = 'Pengajuan Izin/Sakit Baru dari ' . $user_data['nama_lengkap'] . "\n" .
            'Perihal: ' . $izin_data['jenis_izin'] . "\n" .
            'Tanggal Mulai: ' . $izin_data['tanggal_mulai'] . "\n" .
            'Tanggal Selesai: ' . $izin_data['tanggal_selesai'] . "\n" .
            'Lama: ' . $izin_data['durasi_hari'] . " hari\n" .
            'Alasan: ' . $izin_data['alasan'] . "\n";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email izin gagal: ' . $mail->ErrorInfo . ' | Exception: ' . $e->getMessage());
        return false;
    }
}

function sendEmailIzinStatus($izin_data, $user_data, $status, $catatan = '', $approver_data = []) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kaori.aplikasi.notif@gmail.com';
        $mail->Password = 'imjq nmeq vyig umgn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('kaori.aplikasi.notif@gmail.com', 'Sistem KAORI');
        $mail->addAddress($user_data['email'], $user_data['nama_lengkap']);
        $mail->isHTML(true);
        $mail->Subject = "Status Pengajuan Izin/Sakit Anda: $status";
        $mail->Body = "<h3>Status Pengajuan Izin/Sakit: <b>$status</b></h3>"
            . "<p><strong>Nama:</strong> " . htmlspecialchars($user_data['nama_lengkap']) . "<br>"
            . "<strong>Perihal:</strong> " . htmlspecialchars($izin_data['jenis_izin']) . "<br>"
            . "<strong>Tanggal Mulai:</strong> " . htmlspecialchars($izin_data['tanggal_mulai']) . "<br>"
            . "<strong>Tanggal Selesai:</strong> " . htmlspecialchars($izin_data['tanggal_selesai']) . "<br>"
            . "<strong>Lama:</strong> " . htmlspecialchars($izin_data['durasi_hari']) . " hari<br>"
            . "<strong>Alasan:</strong> " . nl2br(htmlspecialchars($izin_data['alasan'])) . "</p>"
            . "<p><strong>Catatan Admin:</strong> " . nl2br(htmlspecialchars($catatan)) . "</p>"
            . "<p><strong>Diproses oleh:</strong> " . htmlspecialchars($approver_data['nama_lengkap'] ?? 'Admin') . "</p>"
            . "<p>Silakan login ke sistem untuk detail lebih lanjut.</p>";
        $mail->AltBody = "Status Pengajuan Izin/Sakit Anda: $status\nNama: " . $user_data['nama_lengkap'] . "\nPerihal: " . $izin_data['jenis_izin'] . "\nTanggal Mulai: " . $izin_data['tanggal_mulai'] . "\nTanggal Selesai: " . $izin_data['tanggal_selesai'] . "\nLama: " . $izin_data['durasi_hari'] . " hari\nAlasan: " . $izin_data['alasan'] . "\nCatatan Admin: " . $catatan . "\nDiproses oleh: " . ($approver_data['nama_lengkap'] ?? 'Admin');
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email status izin gagal: ' . $mail->ErrorInfo . ' | Exception: ' . $e->getMessage());
        return false;
    }
}
