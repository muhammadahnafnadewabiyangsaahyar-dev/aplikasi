<?php
/**
 * TEST EMAIL NOTIFICATION - Web Interface
 * 
 * Interface web untuk test email notification system
 */

require_once 'connect.php';
require_once 'email_helper.php';

// Initialize
$test_results = [];
$email_config = [];
$approvers = [];
$test_user = null;

// Get configuration
$email_config = [
    'smtp_host' => SMTP_HOST,
    'smtp_port' => SMTP_PORT,
    'smtp_username' => SMTP_USERNAME,
    'email_from' => EMAIL_FROM,
    'app_url' => APP_URL
];

// Get approvers
$approvers = getApproverEmails($pdo);

// Get test user
$stmt = $pdo->query("SELECT id, nama_lengkap, posisi, email FROM register WHERE email IS NOT NULL AND email != '' LIMIT 1");
$test_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle test email request
if (isset($_POST['test_action'])) {
    $action = $_POST['test_action'];
    
    switch ($action) {
        case 'test_basic':
            $result = testEmailConfig($test_user['email']);
            $test_results[] = [
                'name' => 'Test Basic Email',
                'status' => $result,
                'message' => $result ? "Email berhasil dikirim ke {$test_user['email']}" : "Gagal mengirim email"
            ];
            break;
            
        case 'test_izin_baru':
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
            $result = sendEmailIzinBaru($dummy_izin, $dummy_user, $pdo);
            $test_results[] = [
                'name' => 'Test Email Izin Baru',
                'status' => $result,
                'message' => $result ? "Email berhasil dikirim ke HR & Kepala Toko" : "Gagal mengirim email (mungkin tidak ada email approver)"
            ];
            break;
            
        case 'test_approve':
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
            $dummy_approver = ['nama_lengkap' => 'HR Manager'];
            $result = sendEmailIzinStatus($dummy_izin, $dummy_user, 'Disetujui', 'Izin Anda disetujui. Selamat!', $dummy_approver);
            $test_results[] = [
                'name' => 'Test Email Approve',
                'status' => $result,
                'message' => $result ? "Email approve berhasil dikirim ke {$test_user['email']}" : "Gagal mengirim email"
            ];
            break;
            
        case 'test_reject':
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
            $dummy_approver = ['nama_lengkap' => 'HR Manager'];
            $result = sendEmailIzinStatus($dummy_izin, $dummy_user, 'Ditolak', 'Maaf, izin Anda ditolak karena bla bla bla', $dummy_approver);
            $test_results[] = [
                'name' => 'Test Email Reject',
                'status' => $result,
                'message' => $result ? "Email reject berhasil dikirim ke {$test_user['email']}" : "Gagal mengirim email"
            ];
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 Test Email Notification System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .section {
            margin: 30px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 5px solid #667eea;
        }
        
        .section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        
        .info-item i {
            font-size: 2em;
            margin-right: 15px;
            color: #667eea;
        }
        
        .info-item .label {
            font-weight: bold;
            color: #666;
            font-size: 0.85em;
        }
        
        .info-item .value {
            color: #333;
            margin-top: 5px;
        }
        
        .test-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        
        .btn {
            padding: 20px;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: bold;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #c92a2a 100%);
        }
        
        .btn i {
            margin-right: 10px;
        }
        
        .alert {
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 5px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        .result-box {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            border-left: 5px solid;
            display: flex;
            align-items: center;
        }
        
        .result-box.success {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .result-box.error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .result-box i {
            font-size: 2.5em;
            margin-right: 20px;
        }
        
        .result-box.success i {
            color: #28a745;
        }
        
        .result-box.error i {
            color: #dc3545;
        }
        
        .result-box .content {
            flex: 1;
            padding: 0;
        }
        
        .result-box .name {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 5px;
        }
        
        .result-box.success .name {
            color: #155724;
        }
        
        .result-box.error .name {
            color: #721c24;
        }
        
        .result-box .message {
            color: #666;
        }
        
        .approver-list {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .approver-item {
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 6px;
            display: flex;
            align-items: center;
        }
        
        .approver-item i {
            margin-right: 10px;
            color: #667eea;
        }
        
        .back-link {
            text-align: center;
            margin: 30px 0;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test Email Notification System</h1>
            <p>Test dan verifikasi sistem email notification</p>
        </div>
        
        <div class="content">
            <!-- Email Configuration -->
            <div class="section">
                <h2><i class="fas fa-cog"></i> Konfigurasi Email</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-server"></i>
                        <div>
                            <div class="label">SMTP Server</div>
                            <div class="value"><?= htmlspecialchars($email_config['smtp_host']) ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-plug"></i>
                        <div>
                            <div class="label">SMTP Port</div>
                            <div class="value"><?= htmlspecialchars($email_config['smtp_port']) ?> (SSL)</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <div class="label">Email Pengirim</div>
                            <div class="value"><?= htmlspecialchars($email_config['email_from']) ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-link"></i>
                        <div>
                            <div class="label">App URL</div>
                            <div class="value"><?= htmlspecialchars($email_config['app_url']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Approver Emails -->
            <div class="section">
                <h2><i class="fas fa-users"></i> Email Approver</h2>
                
                <h3 style="color: #667eea; margin: 15px 0;">HR:</h3>
                <div class="approver-list">
                    <?php if (empty($approvers['hr'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Tidak ada email HR ditemukan!</strong> Sistem akan fallback ke admin.
                        </div>
                    <?php else: ?>
                        <?php foreach ($approvers['hr'] as $hr): ?>
                            <div class="approver-item">
                                <i class="fas fa-user-tie"></i>
                                <strong><?= htmlspecialchars($hr['name']) ?></strong> &lt;<?= htmlspecialchars($hr['email']) ?>&gt;
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <h3 style="color: #667eea; margin: 15px 0;">Kepala Toko / Owner:</h3>
                <div class="approver-list">
                    <?php if (empty($approvers['kepala_toko'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Tidak ada email Kepala Toko ditemukan. Email akan dikirim hanya ke HR.
                        </div>
                    <?php else: ?>
                        <?php foreach ($approvers['kepala_toko'] as $kepala): ?>
                            <div class="approver-item">
                                <i class="fas fa-crown"></i>
                                <strong><?= htmlspecialchars($kepala['name']) ?></strong> &lt;<?= htmlspecialchars($kepala['email']) ?>&gt;
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Test User -->
            <div class="section">
                <h2><i class="fas fa-user-check"></i> Test User</h2>
                <?php if ($test_user): ?>
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <div class="label">Test user yang akan menerima email:</div>
                            <div class="value">
                                <strong><?= htmlspecialchars($test_user['nama_lengkap']) ?></strong> (<?= htmlspecialchars($test_user['posisi']) ?>)<br>
                                Email: <?= htmlspecialchars($test_user['email']) ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Tidak ada user dengan email!</strong> Tidak bisa test email.
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Test Buttons -->
            <?php if ($test_user): ?>
                <div class="section">
                    <h2><i class="fas fa-flask"></i> Test Email</h2>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Pilih salah satu test berikut. Email akan dikirim sesuai dengan scenario yang dipilih.
                    </div>
                    
                    <form method="POST" class="test-buttons">
                        <button type="submit" name="test_action" value="test_basic" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Test Basic Email
                        </button>
                        
                        <button type="submit" name="test_action" value="test_izin_baru" class="btn btn-success">
                            <i class="fas fa-file-alt"></i>
                            Test Izin Baru
                        </button>
                        
                        <button type="submit" name="test_action" value="test_approve" class="btn btn-success">
                            <i class="fas fa-check-circle"></i>
                            Test Approve
                        </button>
                        
                        <button type="submit" name="test_action" value="test_reject" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i>
                            Test Reject
                        </button>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Test Results -->
            <?php if (!empty($test_results)): ?>
                <div class="section">
                    <h2><i class="fas fa-clipboard-check"></i> Hasil Test</h2>
                    <?php foreach ($test_results as $result): ?>
                        <div class="result-box <?= $result['status'] ? 'success' : 'error' ?>">
                            <i class="fas fa-<?= $result['status'] ? 'check-circle' : 'times-circle' ?>"></i>
                            <div class="content">
                                <div class="name"><?= htmlspecialchars($result['name']) ?></div>
                                <div class="message"><?= htmlspecialchars($result['message']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="alert alert-info" style="margin-top: 20px;">
                        <i class="fas fa-envelope-open"></i>
                        <strong>Cek inbox email Anda!</strong> Email mungkin masuk ke folder SPAM. Jika tidak ada email, cek error log.
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Back Link -->
            <div class="back-link">
                <a href="email_notification_guide.html">
                    <i class="fas fa-arrow-left"></i> Kembali ke Guide
                </a>
            </div>
        </div>
    </div>
</body>
</html>
