<?php
/**
 * VERIFY TEST USERS
 * Script untuk memverifikasi dan menampilkan info 4 test users
 */

require_once 'connect.php';
date_default_timezone_set('Asia/Makassar');

$test_emails = [
    'katahnaf@gmail.com',
    'pilaraforismacinta@gmail.com',
    'galihganji@gmail.com',
    'dotpikir@gmail.com'
];

?>
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verify Test Users - KAORI System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            line-height: 1.6;
        }
        .container { 
            max-width: 1200px; 
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
        .content { padding: 40px; }
        .user-card { 
            background: #f8f9fa; 
            border-left: 5px solid #28a745; 
            padding: 25px; 
            margin-bottom: 25px; 
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-card h3 { 
            color: #28a745; 
            margin-bottom: 15px; 
            font-size: 1.5em;
        }
        .user-details { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 15px; 
            margin-top: 15px;
        }
        .detail-item { 
            background: white; 
            padding: 12px; 
            border-radius: 5px; 
            border-left: 3px solid #667eea;
        }
        .detail-label { 
            font-size: 0.85em; 
            color: #6c757d; 
            margin-bottom: 5px;
        }
        .detail-value { 
            font-weight: bold; 
            color: #333; 
            font-size: 1.1em;
        }
        .credentials-box { 
            background: #fff3cd; 
            border-left: 5px solid #ffc107; 
            padding: 20px; 
            border-radius: 8px; 
            margin-top: 15px;
        }
        .credentials-box code { 
            background: rgba(0,0,0,0.1); 
            padding: 5px 10px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace;
            font-size: 1.1em;
        }
        .summary { 
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 10px; 
            margin-top: 30px;
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin: 20px 0;
        }
        .stat-card { 
            background: rgba(255,255,255,0.2); 
            padding: 25px; 
            border-radius: 10px; 
            text-align: center;
        }
        .stat-value { 
            font-size: 3em; 
            font-weight: bold; 
            margin: 10px 0;
        }
        .alert { 
            background: #d1ecf1; 
            border-left: 5px solid #17a2b8; 
            color: #0c5460; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            background: white;
        }
        th, td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #dee2e6;
        }
        th { 
            background: #667eea; 
            color: white; 
            font-weight: 600;
        }
        tr:hover { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>✅ Test Users Verification</h1>
            <p>Verifikasi 4 User Test untuk Sistem KAORI</p>
            <p style='font-size: 0.9em; margin-top: 10px; opacity: 0.8;'>
                <?php echo date('Y-m-d H:i:s'); ?>
            </p>
        </div>
        <div class='content'>
            
            <div class='alert'>
                <strong>ℹ️ Info:</strong> Script ini memverifikasi keberadaan dan kelengkapan data 4 user test di database.
            </div>

            <?php
            $users_found = [];
            $total_found = 0;
            
            foreach ($test_emails as $email) {
                $stmt = $pdo->prepare("
                    SELECT r.*, k.overwork, k.tunjangan_jabatan as tunj_jabatan
                    FROM register r
                    LEFT JOIN komponen_gaji k ON r.id = k.register_id
                    WHERE r.email = ?
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $total_found++;
                    $users_found[] = $user;
                    
                    echo "<div class='user-card'>
                        <h3>✓ {$user['nama_lengkap']}</h3>
                        
                        <div class='user-details'>
                            <div class='detail-item'>
                                <div class='detail-label'>User ID</div>
                                <div class='detail-value'>{$user['id']}</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Username</div>
                                <div class='detail-value'>{$user['username']}</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Email</div>
                                <div class='detail-value'>{$user['email']}</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>No. WhatsApp</div>
                                <div class='detail-value'>{$user['no_whatsapp']}</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Role</div>
                                <div class='detail-value'>" . strtoupper($user['role']) . "</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Posisi</div>
                                <div class='detail-value'>{$user['posisi']}</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Outlet</div>
                                <div class='detail-value'>{$user['outlet']}</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Gaji Pokok</div>
                                <div class='detail-value'>Rp " . number_format($user['gaji_pokok'], 0, ',', '.') . "</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Tunj. Makan</div>
                                <div class='detail-value'>Rp " . number_format($user['tunjangan_makan'], 0, ',', '.') . "</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Tunj. Transport</div>
                                <div class='detail-value'>Rp " . number_format($user['tunjangan_transport'], 0, ',', '.') . "</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Overwork Rate</div>
                                <div class='detail-value'>Rp " . number_format($user['overwork'], 0, ',', '.') . "</div>
                            </div>
                            <div class='detail-item'>
                                <div class='detail-label'>Komponen Gaji</div>
                                <div class='detail-value'>" . ($user['overwork'] ? '✓ Ada' : '✗ Tidak Ada') . "</div>
                            </div>
                        </div>
                        
                        <div class='credentials-box'>
                            <strong>🔑 Login Credentials:</strong><br>
                            Username/Email: <code>{$user['username']}</code> atau <code>{$user['email']}</code><br>
                            Password: <code>Test123!</code>
                        </div>
                    </div>";
                } else {
                    echo "<div class='user-card' style='border-left-color: #dc3545;'>
                        <h3 style='color: #dc3545;'>✗ User dengan email {$email} tidak ditemukan</h3>
                    </div>";
                }
            }
            ?>
            
            <div class='summary'>
                <h2>📊 Summary Verification</h2>
                
                <div class='stats-grid'>
                    <div class='stat-card'>
                        <div class='stat-label'>Total User Dicari</div>
                        <div class='stat-value'>4</div>
                    </div>
                    <div class='stat-card'>
                        <div class='stat-label'>User Ditemukan</div>
                        <div class='stat-value' style='color: #4CAF50;'><?php echo $total_found; ?></div>
                    </div>
                    <div class='stat-card'>
                        <div class='stat-label'>User Tidak Ada</div>
                        <div class='stat-value' style='color: <?php echo (4 - $total_found) > 0 ? '#f44336' : '#4CAF50'; ?>'>
                            <?php echo (4 - $total_found); ?>
                        </div>
                    </div>
                    <div class='stat-card'>
                        <div class='stat-label'>Status</div>
                        <div class='stat-value' style='font-size: 2em;'>
                            <?php echo $total_found == 4 ? '✅' : '⚠️'; ?>
                        </div>
                    </div>
                </div>
                
                <div style='background: rgba(255,255,255,0.2); padding: 20px; border-radius: 8px; margin-top: 20px;'>
                    <h4>📋 Quick Access Table</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Outlet</th>
                                <th>Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users_found as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo $u['nama_lengkap']; ?></td>
                                <td><code><?php echo $u['username']; ?></code></td>
                                <td><?php echo $u['email']; ?></td>
                                <td><?php echo $u['outlet']; ?></td>
                                <td><code>Test123!</code></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px; margin-top: 20px;'>
                    <h4>🚀 Langkah Selanjutnya:</h4>
                    <ol style='padding-left: 20px;'>
                        <li><strong>Login Test:</strong> Coba login dengan salah satu user di atas</li>
                        <li><strong>Assign Shift:</strong> Admin assign shift untuk user melalui jadwal_shift.php</li>
                        <li><strong>Confirm Shift:</strong> User confirm shift yang sudah di-assign</li>
                        <li><strong>Absensi:</strong> User melakukan absen masuk dan keluar</li>
                        <li><strong>Pengajuan Izin:</strong> User ajukan izin/sakit jika perlu</li>
                        <li><strong>Generate Slip Gaji:</strong> Run auto_generate_slipgaji.php</li>
                        <li><strong>Verify Email:</strong> Cek email untuk slip gaji yang terkirim</li>
                    </ol>
                </div>
                
                <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px; margin-top: 20px;'>
                    <h4>🔗 Useful Links:</h4>
                    <ul style='list-style: none; padding-left: 0;'>
                        <li>📝 <a href='index.php' style='color: white;'>Login Page</a></li>
                        <li>👤 <a href='mainpageadmin.php' style='color: white;'>Admin Dashboard</a></li>
                        <li>📅 <a href='jadwal_shift.php' style='color: white;'>Jadwal Shift</a></li>
                        <li>📊 <a href='view_absensi.php' style='color: white;'>View Absensi</a></li>
                        <li>✉️ <a href='approve.php' style='color: white;'>Approve Izin/Sakit</a></li>
                        <li>💰 <a href='slip_gaji_management.php' style='color: white;'>Slip Gaji Management</a></li>
                        <li>🧪 <a href='comprehensive_integration_test.php' style='color: white;'>Run Integration Test</a></li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>
