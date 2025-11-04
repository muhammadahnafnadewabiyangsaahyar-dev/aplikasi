<?php
session_start();
include 'connect.php';

// Cek autentikasi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

// Generate CSRF token untuk halaman ini
if (!isset($_SESSION['csrf_token_import_page'])) {
    $_SESSION['csrf_token_import_page'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Whitelist Pegawai</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .import-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .import-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .import-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .import-header p {
            color: #666;
            font-size: 14px;
        }
        
        .import-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .import-method-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #fafafa;
        }
        
        .import-method-card:hover {
            border-color: #4CAF50;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
            transform: translateY(-2px);
        }
        
        .import-method-card.recommended {
            border-color: #4CAF50;
            background: #f1f8f4;
        }
        
        .import-method-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .import-method-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .import-method-card p {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .import-method-card .badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .import-method-card .features {
            text-align: left;
            font-size: 12px;
            color: #555;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        
        .import-method-card .features li {
            margin-bottom: 5px;
        }
        
        .import-method-card button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        
        .import-method-card button:hover {
            background: #45a049;
        }
        
        .import-method-card.simple button {
            background: #2196F3;
        }
        
        .import-method-card.simple button:hover {
            background: #0b7dda;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #4CAF50;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .info-box h4 {
            margin: 0 0 8px 0;
            color: #1976D2;
        }
        
        .info-box p {
            margin: 0;
            color: #555;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .csv-format-example {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
        }
        
        .csv-format-example h4 {
            margin: 0 0 10px 0;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
<div class="headercontainer">
    <?php include 'navbar.php'; ?>
</div>
<div class="main-title">Teman KAORI</div>
<div class="subtitle-container">
    <p class="subtitle">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?= htmlspecialchars($_SESSION['role']); ?>]</p>
</div>

<div class="import-container">
    <div class="import-header">
        <h1>📥 Import Whitelist Pegawai</h1>
        <p>Pilih metode import yang sesuai dengan kebutuhan Anda</p>
    </div>
    
    <div class="info-box">
        <h4>ℹ️ Format File CSV</h4>
        <p>
            File CSV harus menggunakan delimiter <strong>titik koma (;)</strong> dengan format:<br>
            <code>No;Nama Lengkap;Posisi</code><br>
            Role akan otomatis ditentukan berdasarkan posisi yang dipilih dari database.
        </p>
        <div class="csv-format-example">
            <h4>Contoh Format CSV:</h4>
            <pre>No;Nama Lengkap;Posisi
1;Budi Santoso;Manager
2;Siti Rahayu;Staff
3;Ahmad Wijaya;Supervisor</pre>
        </div>
    </div>
    
    <div class="import-methods">
        <!-- Mode 1: Import Sederhana -->
        <div class="import-method-card simple" onclick="document.getElementById('form-mode1').submit();">
            <div class="icon">⚡</div>
            <h3>Import Cepat</h3>
            <p>Import langsung tanpa konfirmasi. Data yang sudah ada akan dilewati (tidak di-update).</p>
            <div class="features">
                <strong>Fitur:</strong>
                <ul>
                    <li>✓ Proses cepat</li>
                    <li>✓ Anti-duplikat otomatis</li>
                    <li>✓ Role auto-detect</li>
                    <li>✓ Laporan hasil import</li>
                </ul>
            </div>
            <form id="form-mode1" method="get" action="import_csv_enhanced.php" style="margin-top: 15px;">
                <input type="hidden" name="from" value="whitelist">
                <button type="submit">Pilih Mode Ini</button>
            </form>
        </div>
        
        <!-- Mode 2: Import Pintar (Smart) -->
        <div class="import-method-card recommended" onclick="document.getElementById('form-mode2').submit();">
            <div class="badge">🌟 REKOMENDASI</div>
            <div class="icon">🎯</div>
            <h3>Import Pintar (Smart)</h3>
            <p>Import dengan wizard 3 langkah. Anda dapat memilih data mana yang akan di-update atau dilewati.</p>
            <div class="features">
                <strong>Fitur:</strong>
                <ul>
                    <li>✓ Wizard 3 langkah</li>
                    <li>✓ Pilih data untuk update</li>
                    <li>✓ Preview sebelum import</li>
                    <li>✓ Kontrol penuh atas konflik</li>
                    <li>✓ Role auto-detect</li>
                </ul>
            </div>
            <form id="form-mode2" method="get" action="import_csv_smart.php" style="margin-top: 15px;">
                <input type="hidden" name="from" value="whitelist">
                <button type="submit">Pilih Mode Ini</button>
            </form>
        </div>
        
        <!-- Mode 3: Import Manual (Legacy) -->
        <div class="import-method-card" onclick="window.location.href='whitelist.php#import-manual';">
            <div class="icon">✍️</div>
            <h3>Import Manual</h3>
            <p>Upload file CSV langsung dari halaman whitelist (metode lama). Tidak direkomendasikan.</p>
            <div class="features">
                <strong>Fitur:</strong>
                <ul>
                    <li>✓ Import langsung</li>
                    <li>✓ Anti-duplikat dasar</li>
                    <li>✓ Role auto-detect</li>
                </ul>
            </div>
            <button type="button" onclick="window.location.href='whitelist.php#import-manual';" 
                    style="background: #757575;">
                Kembali ke Whitelist
            </button>
        </div>
    </div>
    
    <div class="back-link">
        <a href="whitelist.php">← Kembali ke Daftar Whitelist</a>
    </div>
</div>

<script>
// Prevent form resubmission on refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Add keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = 'whitelist.php';
    }
});
</script>
</body>
</html>
