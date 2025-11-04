<?php
// Test file untuk memastikan tidak ada output tak diinginkan
// yang mengganggu rendering icon Font Awesome

// Mulai output buffering
ob_start();

// Include file seperti di index.php
include 'connect.php';

// Tangkap dan buang output tak diinginkan
$unwanted_output = ob_get_clean();

// Jika ada output, log ke file
if (!empty($unwanted_output)) {
    file_put_contents('debug_output.log', 
        date('Y-m-d H:i:s') . " - Unwanted output detected:\n" . 
        bin2hex($unwanted_output) . "\n" . 
        var_export($unwanted_output, true) . "\n\n",
        FILE_APPEND
    );
}

// Mulai output HTML yang bersih
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Test Index.php Output</title>
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
        }
        .icon-row {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .icon-row i {
            font-size: 24px;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Test Output Index.php</h1>
        
        <?php if (!empty($unwanted_output)): ?>
            <div style="background: #ff0000; color: white; padding: 10px; border-radius: 5px;">
                <strong>❌ PERINGATAN:</strong> Ada output tak diinginkan terdeteksi! 
                Periksa file debug_output.log
            </div>
        <?php else: ?>
            <div style="background: #4CAF50; color: white; padding: 10px; border-radius: 5px;">
                <strong>✅ SUKSES:</strong> Tidak ada output tak diinginkan!
            </div>
        <?php endif; ?>
        
        <div class="icon-row">
            <h2>Font Awesome Icons Test:</h2>
            <i class="fa fa-user"></i>
            <i class="fa fa-lock"></i>
            <i class="fa fa-envelope"></i>
            <i class="fa fa-phone"></i>
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-twitter"></i>
            <i class="fa-brands fa-instagram"></i>
        </div>
        
        <div style="margin-top: 20px;">
            <h3>Informasi:</h3>
            <ul>
                <li>PHP Version: <?php echo phpversion(); ?></li>
                <li>Output Buffering: <?php echo ini_get('output_buffering') ? 'Enabled' : 'Disabled'; ?></li>
                <li>Unwanted Output Length: <?php echo strlen($unwanted_output); ?> bytes</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="index.php" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                Kembali ke Index.php
            </a>
        </div>
    </div>
</body>
</html>
