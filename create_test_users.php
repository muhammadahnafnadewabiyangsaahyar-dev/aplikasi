<?php
/**
 * CREATE TEST USERS SCRIPT
 * 
 * Script untuk membuat 4 user test dengan email real:
 * 1. Kata Hnaf (katahnaf@gmail.com)
 * 2. Pilar Aforisma (pilaraforismacinta@gmail.com)
 * 3. Galih Ganji (galihganji@gmail.com)
 * 4. Dot Pikir (dotpikir@gmail.com)
 */

require_once 'connect.php';

// Set timezone
date_default_timezone_set('Asia/Makassar');

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Create Test Users - KAORI System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            line-height: 1.6;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 40px; 
            text-align: center; 
        }
        .header h1 { 
            font-size: 2em; 
            margin-bottom: 10px; 
        }
        .content { 
            padding: 40px; 
        }
        .user-card { 
            background: #f8f9fa; 
            border-left: 5px solid #667eea; 
            padding: 20px; 
            margin-bottom: 20px; 
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-card h3 { 
            color: #667eea; 
            margin-bottom: 10px; 
        }
        .user-info { 
            display: grid; 
            grid-template-columns: 120px 1fr; 
            gap: 10px; 
            margin: 5px 0;
        }
        .user-info strong { 
            color: #495057; 
        }
        .success { 
            background: #d4edda; 
            border-left: 5px solid #28a745; 
            color: #155724; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 15px;
        }
        .error { 
            background: #f8d7da; 
            border-left: 5px solid #dc3545; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 15px;
        }
        .info { 
            background: #d1ecf1; 
            border-left: 5px solid #17a2b8; 
            color: #0c5460; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 15px;
        }
        .summary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 10px; 
            margin-top: 30px;
        }
        .summary h2 { 
            margin-bottom: 20px; 
        }
        .credentials { 
            background: rgba(255,255,255,0.1); 
            padding: 20px; 
            border-radius: 8px; 
            margin-top: 20px;
        }
        .credentials h4 { 
            margin-bottom: 10px; 
        }
        .credentials ul { 
            list-style: none; 
        }
        .credentials li { 
            padding: 10px; 
            background: rgba(255,255,255,0.05); 
            margin: 5px 0; 
            border-radius: 5px;
        }
        code { 
            background: rgba(0,0,0,0.1); 
            padding: 3px 8px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>👥 Create Test Users</h1>
            <p>Membuat 4 user test untuk sistem KAORI</p>
            <p style='font-size: 0.9em; margin-top: 10px; opacity: 0.8;'>
                " . date('Y-m-d H:i:s') . "
            </p>
        </div>
        <div class='content'>";

// Data users yang akan dibuat
$users = [
    [
        'username' => 'katahnaf',
        'nama_lengkap' => 'Kata Hnaf',
        'email' => 'katahnaf@gmail.com',
        'no_whatsapp' => '081234567890',
        'posisi' => 'Staff',
        'outlet' => 'Cabang A'
    ],
    [
        'username' => 'pilaraforisma',
        'nama_lengkap' => 'Pilar Aforisma',
        'email' => 'pilaraforismacinta@gmail.com',
        'no_whatsapp' => '081234567891',
        'posisi' => 'Staff',
        'outlet' => 'Cabang A'
    ],
    [
        'username' => 'galihganji',
        'nama_lengkap' => 'Galih Ganji',
        'email' => 'galihganji@gmail.com',
        'no_whatsapp' => '081234567892',
        'posisi' => 'Staff',
        'outlet' => 'Cabang B'
    ],
    [
        'username' => 'dotpikir',
        'nama_lengkap' => 'Dot Pikir',
        'email' => 'dotpikir@gmail.com',
        'no_whatsapp' => '081234567893',
        'posisi' => 'Staff',
        'outlet' => 'Cabang B'
    ]
];

// Password yang sama untuk semua user
$password = 'Test123!';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Gaji dan tunjangan default
$gaji_pokok = 5000000;
$tunjangan_makan = 500000;
$tunjangan_transport = 500000;
$tunjangan_jabatan = 0;

$created_users = [];
$errors = [];

echo "<div class='info'><strong>🔄 Memulai pembuatan user...</strong></div>";

foreach ($users as $user_data) {
    try {
        // Cek apakah user sudah ada
        $stmt = $pdo->prepare("SELECT id FROM register WHERE email = ? OR username = ?");
        $stmt->execute([$user_data['email'], $user_data['username']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            echo "<div class='error'>
                <strong>❌ User {$user_data['nama_lengkap']} sudah ada!</strong><br>
                Email atau username sudah terdaftar. User ID: {$existing['id']}
            </div>";
            $errors[] = "User {$user_data['nama_lengkap']} sudah ada";
            continue;
        }
        
        // Mulai transaction
        $pdo->beginTransaction();
        
        // Insert user ke table register
        $stmt = $pdo->prepare("
            INSERT INTO register (
                username, password, nama_lengkap, email, no_whatsapp,
                role, posisi, outlet, gaji_pokok, tunjangan_transport, 
                tunjangan_makan, tunjangan_jabatan, created_at
            ) VALUES (?, ?, ?, ?, ?, 'user', ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_data['username'],
            $password_hash,
            $user_data['nama_lengkap'],
            $user_data['email'],
            $user_data['no_whatsapp'],
            $user_data['posisi'],
            $user_data['outlet'],
            $gaji_pokok,
            $tunjangan_transport,
            $tunjangan_makan,
            $tunjangan_jabatan
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // Insert komponen gaji
        $stmt_komponen = $pdo->prepare("
            INSERT INTO komponen_gaji (
                register_id, jabatan, gaji_pokok, tunjangan_makan, 
                tunjangan_transport, tunjangan_jabatan, overwork
            ) VALUES (?, ?, ?, ?, ?, ?, 50000)
        ");
        
        $stmt_komponen->execute([
            $user_id,
            $user_data['posisi'],
            $gaji_pokok,
            $tunjangan_makan,
            $tunjangan_transport,
            $tunjangan_jabatan
        ]);
        
        $pdo->commit();
        
        // Simpan info user yang berhasil dibuat
        $created_users[] = [
            'id' => $user_id,
            'username' => $user_data['username'],
            'nama_lengkap' => $user_data['nama_lengkap'],
            'email' => $user_data['email'],
            'posisi' => $user_data['posisi'],
            'outlet' => $user_data['outlet']
        ];
        
        // Tampilkan success message
        echo "<div class='success'>
            <strong>✅ User berhasil dibuat: {$user_data['nama_lengkap']}</strong>
        </div>";
        
        // Tampilkan detail user
        echo "<div class='user-card'>
            <h3>{$user_data['nama_lengkap']}</h3>
            <div class='user-info'><strong>User ID:</strong> <span>{$user_id}</span></div>
            <div class='user-info'><strong>Username:</strong> <span>{$user_data['username']}</span></div>
            <div class='user-info'><strong>Email:</strong> <span>{$user_data['email']}</span></div>
            <div class='user-info'><strong>No. WhatsApp:</strong> <span>{$user_data['no_whatsapp']}</span></div>
            <div class='user-info'><strong>Posisi:</strong> <span>{$user_data['posisi']}</span></div>
            <div class='user-info'><strong>Outlet:</strong> <span>{$user_data['outlet']}</span></div>
            <div class='user-info'><strong>Gaji Pokok:</strong> <span>Rp " . number_format($gaji_pokok, 0, ',', '.') . "</span></div>
            <div class='user-info'><strong>Tunj. Makan:</strong> <span>Rp " . number_format($tunjangan_makan, 0, ',', '.') . "</span></div>
            <div class='user-info'><strong>Tunj. Transport:</strong> <span>Rp " . number_format($tunjangan_transport, 0, ',', '.') . "</span></div>
        </div>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='error'>
            <strong>❌ Error membuat user {$user_data['nama_lengkap']}:</strong><br>
            {$e->getMessage()}
        </div>";
        $errors[] = "Error: {$user_data['nama_lengkap']} - {$e->getMessage()}";
    }
}

// Summary
echo "<div class='summary'>
    <h2>📊 Summary</h2>
    <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;'>
        <div style='background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; text-align: center;'>
            <div style='font-size: 3em; font-weight: bold;'>" . count($users) . "</div>
            <div>Total Users</div>
        </div>
        <div style='background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; text-align: center;'>
            <div style='font-size: 3em; font-weight: bold; color: #4CAF50;'>" . count($created_users) . "</div>
            <div>Berhasil</div>
        </div>
        <div style='background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; text-align: center;'>
            <div style='font-size: 3em; font-weight: bold; color: " . (count($errors) > 0 ? '#f44336' : '#4CAF50') . ";'>" . count($errors) . "</div>
            <div>Gagal</div>
        </div>
    </div>
    
    <div class='credentials'>
        <h4>🔑 Login Credentials untuk Semua User:</h4>
        <ul>";

foreach ($created_users as $user) {
    echo "<li>
        <strong>{$user['nama_lengkap']}</strong><br>
        Username: <code>{$user['username']}</code><br>
        Email: <code>{$user['email']}</code><br>
        Password: <code>{$password}</code>
    </li>";
}

echo "    </ul>
    </div>
    
    <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px; margin-top: 20px;'>
        <h4>📝 Catatan Penting:</h4>
        <ul style='list-style: none; padding-left: 0;'>
            <li>✓ Semua user memiliki role: <code>user</code></li>
            <li>✓ Password untuk semua user: <code>{$password}</code></li>
            <li>✓ Komponen gaji sudah dibuat untuk semua user</li>
            <li>✓ Overwork rate: Rp 50.000 per hari</li>
            <li>✓ User dapat login ke sistem menggunakan username atau email</li>
        </ul>
    </div>
    
    <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px; margin-top: 20px;'>
        <h4>🚀 Langkah Selanjutnya:</h4>
        <ol style='padding-left: 20px;'>
            <li>Login ke sistem dengan credentials di atas</li>
            <li>Assign shift untuk user (via jadwal_shift.php)</li>
            <li>User bisa confirm shift</li>
            <li>User bisa absen masuk/keluar</li>
            <li>User bisa ajukan izin/sakit</li>
            <li>Admin bisa approve/reject izin</li>
            <li>Generate slip gaji otomatis setiap bulan</li>
        </ol>
    </div>
</div>";

echo "        </div>
    </div>
</body>
</html>";
?>
