<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
include 'connect.php';

$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$user_id = $_SESSION['user_id'];

// === STATISTIK ABSENSI ===
$stats = [
    'total_hadir' => 0,
    'tepat_waktu' => 0,
    'terlambat' => 0,
    'alpha' => 0,
    'izin' => 0,
    'sakit' => 0,
    'persentase_kehadiran' => 0,
    'rata_keterlambatan' => 0
];

try {
    // Total hari kerja dalam bulan ini (asumsi)
    $bulan_ini = date('Y-m');
    $hari_kerja = 26; // Default 26 hari kerja per bulan
    
    // Hitung presensi valid (yang punya waktu_masuk, termasuk yang lupa absen pulang)
    // PERBAIKAN: "Lupa absen pulang" tetap dihitung sebagai hadir
    // PENTING: Hanya hitung yang status_kehadiran = 'Hadir', TIDAK termasuk Izin/Sakit
    $sql_hadir = "SELECT COUNT(DISTINCT tanggal_absensi) as total 
                  FROM absensi 
                  WHERE user_id = ? 
                  AND status_kehadiran = 'Hadir'
                  AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql_hadir);
    $stmt->execute([$user_id, $bulan_ini]);
    $stats['total_hadir'] = $stmt->fetchColumn();
    
    // === DETEKSI LUPA ABSEN PULANG ===
    // Cek absensi yang hanya ada waktu_masuk tapi tidak ada waktu_keluar
    // Kriteria: Sudah melewati jam 23:59 pada hari yang sama (dianggap lupa absen pulang)
    // 
    // Logika:
    // 1. Hari ini sebelum 23:59 => Masih bisa absen keluar (TIDAK dianggap lupa)
    // 2. Hari ini sudah lewat 23:59 (berarti sudah tanggal berikutnya) => Lupa absen pulang
    // 3. Hari kemarin atau lebih lama => Pasti lupa absen pulang
    //
    // Simplified: Semua absen yang tanggal_absensi < CURDATE() dan belum ada waktu_keluar = Lupa
    $sql_lupa_pulang = "SELECT 
                            id,
                            tanggal_absensi,
                            TIME(waktu_masuk) as jam_masuk,
                            DATEDIFF(CURDATE(), tanggal_absensi) as hari_lalu
                        FROM absensi 
                        WHERE user_id = ? 
                        AND waktu_masuk IS NOT NULL 
                        AND waktu_keluar IS NULL
                        AND tanggal_absensi < CURDATE()
                        ORDER BY tanggal_absensi DESC 
                        LIMIT 10";
    $stmt_lupa = $pdo->prepare($sql_lupa_pulang);
    $stmt_lupa->execute([$user_id]);
    $lupa_absen_pulang = $stmt_lupa->fetchAll(PDO::FETCH_ASSOC);
    $stats['lupa_absen_pulang'] = count($lupa_absen_pulang);
    
    // Hitung tepat waktu (hanya untuk yang status = 'Hadir')
    $sql_tepat = "SELECT COUNT(DISTINCT tanggal_absensi) as total 
                  FROM absensi 
                  WHERE user_id = ? 
                  AND status_kehadiran = 'Hadir'
                  AND status_keterlambatan = 'tepat waktu'
                  AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql_tepat);
    $stmt->execute([$user_id, $bulan_ini]);
    $stats['tepat_waktu'] = $stmt->fetchColumn();
    
    // Hitung terlambat (pastikan tidak negatif, hanya untuk yang status = 'Hadir')
    $stats['terlambat'] = max(0, $stats['total_hadir'] - $stats['tepat_waktu']);
    
    // Hitung izin dan sakit dari tabel absensi (lebih akurat)
    $sql_izin = "SELECT COUNT(DISTINCT tanggal_absensi) as total 
                 FROM absensi 
                 WHERE user_id = ? 
                 AND status_kehadiran = 'Izin'
                 AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql_izin);
    $stmt->execute([$user_id, $bulan_ini]);
    $stats['izin'] = $stmt->fetchColumn();
    
    $sql_sakit = "SELECT COUNT(DISTINCT tanggal_absensi) as total 
                  FROM absensi 
                  WHERE user_id = ? 
                  AND status_kehadiran = 'Sakit'
                  AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql_sakit);
    $stmt->execute([$user_id, $bulan_ini]);
    $stats['sakit'] = $stmt->fetchColumn();
    
    // Hitung alpha (tidak hadir tanpa keterangan)
    // Alpha = Total Hari Kerja - (Hadir + Izin + Sakit)
    $stats['alpha'] = $hari_kerja - $stats['total_hadir'] - $stats['izin'] - $stats['sakit'];
    
    // Persentase kehadiran (termasuk izin dan sakit sebagai "present with excuse")
    // Total Present = Hadir + Izin + Sakit
    $total_present = $stats['total_hadir'] + $stats['izin'] + $stats['sakit'];
    $stats['persentase_kehadiran'] = $hari_kerja > 0 ? round(($total_present / $hari_kerja) * 100, 1) : 0;
    
    // Rata-rata keterlambatan (menit, hanya untuk yang status = 'Hadir')
    $sql_avg_telat = "SELECT AVG(menit_terlambat) as rata 
                      FROM absensi 
                      WHERE user_id = ? 
                      AND status_kehadiran = 'Hadir'
                      AND menit_terlambat > 0
                      AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql_avg_telat);
    $stmt->execute([$user_id, $bulan_ini]);
    $stats['rata_keterlambatan'] = round($stmt->fetchColumn() ?? 0, 1);
    
    // Data untuk chart (7 hari terakhir, hanya yang status = 'Hadir')
    $sql_chart = "SELECT 
                    DATE_FORMAT(tanggal_absensi, '%d/%m') as tanggal,
                    COUNT(*) as jumlah,
                    SUM(CASE WHEN status_keterlambatan = 'tepat waktu' AND status_kehadiran = 'Hadir' THEN 1 ELSE 0 END) as tepat_waktu,
                    SUM(CASE WHEN menit_terlambat > 0 AND status_kehadiran = 'Hadir' THEN 1 ELSE 0 END) as terlambat
                  FROM absensi 
                  WHERE user_id = ? 
                  AND status_kehadiran = 'Hadir'
                  AND tanggal_absensi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  GROUP BY tanggal_absensi 
                  ORDER BY tanggal_absensi ASC";
    $stmt = $pdo->prepare($sql_chart);
    $stmt->execute([$user_id]);
    $chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_stats = "Error mengambil statistik: " . $e->getMessage();
}

// === CEK SETUP AKUN ===
try {
    $sql_profile = "SELECT foto_profil, tanda_tangan_file, outlet, no_whatsapp FROM register WHERE id = ?";
    $stmt = $pdo->prepare($sql_profile);
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $setup_steps = [
        [
            'title' => 'Upload Foto Profil',
            'desc' => 'Tambahkan foto profil Anda',
            'link' => 'profile.php',
            'completed' => !empty($profile['foto_profil']) && $profile['foto_profil'] != 'default.png'
        ],
        [
            'title' => 'Upload Tanda Tangan',
            'desc' => 'Upload tanda tangan digital untuk surat izin',
            'link' => 'profile.php',
            'completed' => !empty($profile['tanda_tangan_file'])
        ],
        [
            'title' => 'Lengkapi Data Diri',
            'desc' => 'Isi outlet dan nomor WhatsApp',
            'link' => 'profile.php',
            'completed' => !empty($profile['outlet']) && !empty($profile['no_whatsapp'])
        ]
    ];
    
    $all_completed = true;
    foreach ($setup_steps as $step) {
        if (!$step['completed']) {
            $all_completed = false;
            break;
        }
    }
} catch (PDOException $e) {
    $error_setup = "Error cek setup: " . $e->getMessage();
}

// === ADMIN: DAFTAR PENGGUNA ===
$daftar_pengguna = [];
if ($is_admin) {
    try {
        $sql_users = "SELECT id, nama_lengkap, username, email, role, time_created FROM register ORDER BY time_created DESC LIMIT 10";
        $stmt = $pdo->query($sql_users);
        $daftar_pengguna = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_pengguna = "Error mengambil data pengguna: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard - KAORI Indonesia</title>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .stat-card.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card.red {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .stat-card.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .stat-icon {
            font-size: 2em;
            opacity: 0.3;
            float: right;
        }
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .setup-wizard {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .setup-step {
            display: flex;
            align-items: center;
            padding: 15px;
            border-left: 4px solid #e0e0e0;
            margin: 10px 0;
            transition: all 0.3s ease;
        }
        .setup-step:hover {
            background: #f5f5f5;
            border-left-color: #667eea;
        }
        .setup-step.completed {
            border-left-color: #38ef7d;
            background: #f0fff4;
        }
        .setup-step.completed .step-icon {
            background: #38ef7d;
            color: white;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2em;
        }
        .step-content {
            flex: 1;
        }
        .step-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .step-desc {
            font-size: 0.9em;
            color: #666;
        }
        .step-action {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9em;
            transition: background 0.3s ease;
        }
        .step-action:hover {
            background: #5568d3;
        }
        .wizard-complete {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 12px;
            margin: 20px 0;
        }
        .wizard-complete i {
            font-size: 3em;
            margin-bottom: 10px;
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        h2 i {
            color: #667eea;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>]</p>
    </div>
    
    <div class="content-container">
        <!-- Setup Wizard (hanya muncul jika belum lengkap) -->
        <?php if (!$all_completed): ?>
        <div class="setup-wizard">
            <h2><i class="fa fa-list-check"></i> Selesaikan Pengaturan Akun</h2>
            <p style="color: #666; margin-bottom: 20px;">Lengkapi pengaturan akun Anda untuk mengakses semua fitur</p>
            
            <?php foreach ($setup_steps as $index => $step): ?>
            <div class="setup-step <?= $step['completed'] ? 'completed' : '' ?>">
                <div class="step-icon">
                    <?php if ($step['completed']): ?>
                        <i class="fa fa-check"></i>
                    <?php else: ?>
                        <?= $index + 1 ?>
                    <?php endif; ?>
                </div>
                <div class="step-content">
                    <div class="step-title"><?= $step['title'] ?></div>
                    <div class="step-desc"><?= $step['desc'] ?></div>
                </div>
                <?php if (!$step['completed']): ?>
                    <a href="<?= $step['link'] ?>" class="step-action">Lengkapi</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="wizard-complete">
            <i class="fa fa-check-circle"></i>
            <h3 style="margin: 10px 0;">Setup Akun Selesai!</h3>
            <p style="opacity: 0.9;">Semua pengaturan akun Anda sudah lengkap</p>
        </div>
        <?php endif; ?>

        <!-- Statistik Absensi -->
        <h2><i class="fa fa-chart-line"></i> Overview Absensi Bulan Ini</h2>
        
        <!-- WARNING: Lupa Absen Pulang -->
        <?php if (!empty($lupa_absen_pulang)): ?>
        <div class="alert alert-warning" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fa fa-exclamation-triangle" style="font-size: 2em; color: #ff6b6b;"></i>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 10px 0; color: #856404;">
                        <i class="fa fa-user-clock"></i> Anda Lupa Absen Pulang! (<?= count($lupa_absen_pulang) ?> hari)
                    </h3>
                    <p style="margin: 0 0 10px 0; color: #856404;">
                        Berikut adalah hari-hari di mana Anda absen masuk tapi lupa absen pulang. 
                        Anda tetap dihitung <strong>hadir</strong>, tapi dengan catatan <strong>"Lupa Absen Pulang"</strong>.
                    </p>
                    <div style="background: white; padding: 10px; border-radius: 6px; margin-top: 10px;">
                        <?php foreach ($lupa_absen_pulang as $index => $lupa): ?>
                            <div style="padding: 8px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                                <span>
                                    <i class="fa fa-calendar"></i> 
                                    <strong><?= date('d M Y (l)', strtotime($lupa['tanggal_absensi'])) ?></strong>
                                </span>
                                <span style="color: #666;">
                                    <i class="fa fa-clock"></i> Masuk: <?= $lupa['jam_masuk'] ?>
                                    <i class="fa fa-arrow-right" style="margin: 0 5px;"></i>
                                    <span style="color: #f5576c; font-weight: bold;">Keluar: -</span>
                                </span>
                                <span style="background: #fff3cd; padding: 4px 12px; border-radius: 12px; font-size: 0.85em; font-weight: bold; color: #856404;">
                                    <i class="fa fa-info-circle"></i> Lupa Absen Pulang
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin: 10px 0 0 0; font-size: 0.9em; color: #856404;">
                        <i class="fa fa-lightbulb"></i> <strong>Tips:</strong> Gunakan fitur reminder atau set alarm untuk mengingatkan absen pulang.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <div class="stat-card green">
                <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                <div class="stat-label">Total Kehadiran</div>
                <div class="stat-value"><?= $stats['total_hadir'] ?></div>
                <div class="stat-label">Dari <?= $hari_kerja ?> hari kerja</div>
            </div>
            
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fa fa-clock"></i></div>
                <div class="stat-label">Tepat Waktu</div>
                <div class="stat-value"><?= $stats['tepat_waktu'] ?></div>
                <div class="stat-label">Hari</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
                <div class="stat-label">Terlambat</div>
                <div class="stat-value"><?= $stats['terlambat'] ?></div>
                <div class="stat-label">Rata-rata <?= $stats['rata_keterlambatan'] ?> menit</div>
            </div>
            
            <div class="stat-card red">
                <div class="stat-icon"><i class="fa fa-times-circle"></i></div>
                <div class="stat-label">Tidak Hadir (Alpha)</div>
                <div class="stat-value"><?= $stats['alpha'] ?></div>
                <div class="stat-label">Hari</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-icon"><i class="fa fa-file-medical"></i></div>
                <div class="stat-label">Izin</div>
                <div class="stat-value"><?= $stats['izin'] ?></div>
                <div class="stat-label">Hari (Disetujui)</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                <div class="stat-icon"><i class="fa fa-notes-medical"></i></div>
                <div class="stat-label">Sakit</div>
                <div class="stat-value"><?= $stats['sakit'] ?></div>
                <div class="stat-label">Hari (Disetujui)</div>
            </div>
            
            <?php if ($stats['lupa_absen_pulang'] > 0): ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #ffa726 0%, #ff6b6b 100%); grid-column: span 2;">
                <div class="stat-icon"><i class="fa fa-user-clock"></i></div>
                <div class="stat-label">Lupa Absen Pulang</div>
                <div class="stat-value"><?= $stats['lupa_absen_pulang'] ?></div>
                <div class="stat-label">Hari (Dihitung hadir dengan catatan)</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Persentase Kehadiran -->
        <div class="chart-container">
            <h2><i class="fa fa-percentage"></i> Persentase Kehadiran</h2>
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="flex: 1;">
                    <canvas id="kehadiranChart" height="80"></canvas>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 3em; font-weight: bold; color: <?= $stats['persentase_kehadiran'] >= 90 ? '#38ef7d' : ($stats['persentase_kehadiran'] >= 75 ? '#ffa726' : '#f5576c') ?>">
                        <?= $stats['persentase_kehadiran'] ?>%
                    </div>
                    <div style="color: #666;">Tingkat Kehadiran</div>
                    <div style="margin-top: 10px; padding: 10px; background: <?= $stats['persentase_kehadiran'] >= 90 ? '#e8f5e9' : ($stats['persentase_kehadiran'] >= 75 ? '#fff3e0' : '#ffebee') ?>; border-radius: 8px;">
                        <strong><?= $stats['persentase_kehadiran'] >= 90 ? 'Sangat Baik!' : ($stats['persentase_kehadiran'] >= 75 ? 'Baik' : 'Perlu Ditingkatkan') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik 7 Hari Terakhir -->
        <div class="chart-container">
            <h2><i class="fa fa-chart-bar"></i> Aktivitas Absensi 7 Hari Terakhir</h2>
            <canvas id="aktivityChart"></canvas>
        </div>

    </div>

    <script>
    // Chart Persentase Kehadiran (Doughnut) - Breakdown Detail
    const kehadiranCtx = document.getElementById('kehadiranChart').getContext('2d');
    new Chart(kehadiranCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
            datasets: [{
                data: [<?= $stats['total_hadir'] ?>, <?= $stats['izin'] ?>, <?= $stats['sakit'] ?>, <?= $stats['alpha'] ?>],
                backgroundColor: ['#38ef7d', '#667eea', '#30cfd0', '#f5576c'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Chart Aktivitas 7 Hari Terakhir
    const aktivityCtx = document.getElementById('aktivityChart').getContext('2d');
    new Chart(aktivityCtx, {
        type: 'bar',
        data: {
            labels: [<?php foreach($chart_data as $d) echo "'".$d['tanggal']."',"; ?>],
            datasets: [
                {
                    label: 'Tepat Waktu',
                    data: [<?php foreach($chart_data as $d) echo $d['tepat_waktu'].","; ?>],
                    backgroundColor: '#38ef7d'
                },
                {
                    label: 'Terlambat',
                    data: [<?php foreach($chart_data as $d) echo $d['terlambat'].","; ?>],
                    backgroundColor: '#ffa726'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    </script>
</body>
<footer>
    <div class="footer-container">
        <p class="footer-text">© 2024 KAORI Indonesia. All rights reserved.</p>
        <p class="footer-text">Follow us on:</p>
        <div class="social-icons">
            <i class="fa fa-brands fa-facebook-f footer-link"></i>
            <i class="fa fa-brands fa-twitter footer-link"></i>
            <i class="fa fa-brands fa-instagram footer-link"></i>
        </div>
    </div>
</footer>
</html>