<?php
session_start();
require_once 'security_helper.php';

// Start secure session
SecurityHelper::secureSessionStart();

// 1. PENJAGA GERBANG: Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=notloggedin'); 
    exit;
}
$user_id = $_SESSION['user_id'];

// Validate session
if (!SecurityHelper::validateSession()) {
    session_destroy();
    header('Location: index.php?error=sessionexpired');
    exit;
}

// Generate CSRF token
$csrf_token = SecurityHelper::generateCSRFToken();

// 2. Muat Koneksi & Ambil Data User
include 'connect.php';

// Ambil tanda tangan tersimpan
$tanda_tangan_tersimpan = null;
$sql_user_ttd = "SELECT tanda_tangan_file FROM register WHERE id = ?";
$stmt_user_ttd = $pdo->prepare($sql_user_ttd);
$stmt_user_ttd->execute([$user_id]);
$user_ttd_data = $stmt_user_ttd->fetch(PDO::FETCH_ASSOC);
if ($user_ttd_data) {
    $tanda_tangan_tersimpan = $user_ttd_data['tanda_tangan_file'];
}

// Ambil shift yang sudah di-assign untuk user ini (untuk info hari kerja)
$sql_shifts = "SELECT sa.tanggal_shift, c.nama_shift 
               FROM shift_assignments sa
               LEFT JOIN cabang c ON sa.cabang_id = c.id
               WHERE sa.user_id = ? 
               AND sa.status_konfirmasi = 'confirmed'
               AND sa.tanggal_shift >= CURDATE()
               ORDER BY sa.tanggal_shift ASC
               LIMIT 30";
$stmt_shifts = $pdo->prepare($sql_shifts);
$stmt_shifts->execute([$user_id]);
$upcoming_shifts = $stmt_shifts->fetchAll(PDO::FETCH_ASSOC);

// Convert to JSON for JavaScript
$shifts_json = json_encode($upcoming_shifts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Ajukan Izin/Sakit - KAORI</title>
    <style>
        .perihal-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .perihal-option {
            border: 2px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        .perihal-option:hover {
            border-color: #4CAF50;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
        }
        .perihal-option.active {
            border-color: #4CAF50;
            background-color: #f1f8f4;
        }
        .perihal-option input[type="radio"] {
            display: none;
        }
        .perihal-option i {
            font-size: 3em;
            margin-bottom: 10px;
            display: block;
        }
        .perihal-option .option-title {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 5px;
        }
        .perihal-option .option-desc {
            font-size: 0.9em;
            color: #666;
        }
        .upcoming-shifts-info {
            background: #f9f9f9;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .shift-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 10px;
        }
        .shift-item {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .alert-info {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
            color: #1565c0;
        }
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="headercontainer">
        <?php include 'navbar.php'; ?>
    </div>
    <div class="main-title">Teman KAORI</div>
    <div class="subtitle-container">
        <p class="subtitle">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?php echo htmlspecialchars($_SESSION['role']); ?>]</p>
    </div>
    
    <div class="content-container">
        <h2><i class="fa fa-file-medical"></i> Ajukan Izin / Sakit</h2>
        <p class="info-text">
            <i class="fa fa-info-circle"></i> 
            Gunakan halaman ini untuk mengajukan permohonan izin atau sakit. 
            Pastikan Anda mengisi semua informasi dengan lengkap dan benar.
        </p>

        <!-- Alert untuk shift yang akan terpengaruh -->
        <div class="upcoming-shifts-info">
            <h3><i class="fa fa-calendar-alt"></i> Shift Anda yang Akan Datang</h3>
            <p>Berikut adalah shift yang telah di-assign untuk Anda. Jika Anda mengajukan izin/sakit pada tanggal tersebut, shift akan otomatis ter-cover.</p>
            <div class="shift-list">
                <?php if (empty($upcoming_shifts)): ?>
                    <p style="color: #666; font-style: italic;">Tidak ada shift yang di-assign untuk 30 hari ke depan.</p>
                <?php else: ?>
                    <?php foreach ($upcoming_shifts as $shift): ?>
                        <div class="shift-item">
                            <i class="fa fa-calendar-day"></i> 
                            <strong><?php echo date('d M Y (l)', strtotime($shift['tanggal_shift'])); ?></strong>
                            - <?php echo htmlspecialchars($shift['nama_shift']); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-info">
                <i class="fa fa-check-circle"></i> 
                <strong>Berhasil!</strong> Pengajuan izin/sakit Anda telah dikirim dan menunggu persetujuan.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-warning">
                <i class="fa fa-exclamation-triangle"></i> 
                <strong>Error:</strong> 
                <?php
                $errorMsg = 'Terjadi kesalahan saat memproses pengajuan Anda.';
                if ($_GET['error'] === 'datakosong') $errorMsg = 'Semua field wajib diisi.';
                elseif ($_GET['error'] === 'perihalvalid') $errorMsg = 'Perihal harus "Izin" atau "Sakit".';
                elseif ($_GET['error'] === 'ttdkosong') $errorMsg = 'Tanda tangan wajib diisi.';
                elseif ($_GET['error'] === 'gagalsimpan') $errorMsg = 'Gagal menyimpan data. Silakan coba lagi.';
                echo htmlspecialchars($errorMsg);
                ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="content-container">
        <h3>Formulir Pengajuan</h3>
        <form method="POST" action="proses_pengajuan_izin_sakit.php" class="form-surat-izin" id="form-izin-sakit">
            
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <!-- Pilih Perihal: Izin atau Sakit -->
            <div class="input-group">
                <label>Jenis Pengajuan: <span style="color: red;">*</span></label>
                <div class="perihal-options">
                    <div class="perihal-option" onclick="selectPerihal('Izin')">
                        <input type="radio" id="perihal-izin" name="perihal" value="Izin" required>
                        <i class="fa fa-file-alt" style="color: #2196F3;"></i>
                        <div class="option-title">Izin</div>
                        <div class="option-desc">Keperluan pribadi, keluarga, atau urusan penting lainnya</div>
                    </div>
                    <div class="perihal-option" onclick="selectPerihal('Sakit')">
                        <input type="radio" id="perihal-sakit" name="perihal" value="Sakit" required>
                        <i class="fa fa-briefcase-medical" style="color: #f44336;"></i>
                        <div class="option-title">Sakit</div>
                        <div class="option-desc">Kondisi kesehatan yang tidak memungkinkan bekerja</div>
                    </div>
                </div>
            </div>

            <div class="input-group">
                <label for="tanggal_mulai">Tanggal Mulai: <span style="color: red;">*</span></label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="input-group">
                <label for="tanggal_selesai">Tanggal Selesai: <span style="color: red;">*</span></label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="input-group">
                <label for="lama_izin">Lama (dalam hari):</label>
                <input type="number" id="lama_izin" name="lama_izin" min="1" readonly style="background: #f5f5f5;">
                <small style="color: #666;">* Akan dihitung otomatis berdasarkan tanggal mulai dan selesai</small>
            </div>

            <div class="input-group">
                <label for="alasan">Alasan: <span style="color: red;">*</span></label>
                <textarea id="alasan" name="alasan" rows="4" required placeholder="Jelaskan alasan izin/sakit Anda dengan jelas..."></textarea>
                <small id="surat-dokter-note" style="color: #f44336; display: none;">
                    <i class="fa fa-exclamation-circle"></i> 
                    <strong>Perhatian:</strong> Untuk sakit lebih dari 3 hari, wajib melampirkan surat keterangan dokter!
                </small>
            </div>

            <div class="input-group">
                <label for="file_surat">Upload Surat Pendukung (opsional):</label>
                <input type="file" id="file_surat" name="file_surat" accept=".pdf,.jpg,.jpeg,.png,.docx">
                <small style="color: #666;">Format: PDF, JPG, PNG, atau DOCX (Max 2MB). Untuk Sakit > 3 hari, wajib upload surat dokter!</small>
            </div>

            <?php if (empty($tanda_tangan_tersimpan)): ?>
                <div class="input-group">
                    <label for="signature">Tanda Tangan Digital: <span style="color: red;">*</span></label>
                    <canvas id="signature-pad" class="signature-pad" width="400" height="200" style="border: 2px solid #ddd; border-radius: 4px; background: white;"></canvas>
                    <div style="margin-top: 10px;">
                        <button type="button" id="clear-signature" class="btn-secondary">
                            <i class="fa fa-eraser"></i> Hapus Tanda Tangan
                        </button>
                    </div>
                    <input type="hidden" name="signature_data" id="signature-data">
                </div>
            <?php else: ?>
                <div class="input-group">
                    <label>Tanda Tangan:</label>
                    <p style="color: #4CAF50;"><i class="fa fa-check-circle"></i> Tanda tangan sudah tersimpan di profil Anda.</p>
                    <img src="uploads/tanda_tangan/<?php echo htmlspecialchars($tanda_tangan_tersimpan); ?>" 
                         alt="Tanda Tangan Tersimpan" 
                         style="max-width: 200px; border: 2px solid #ddd; border-radius: 4px; margin-top: 10px;">
                </div>
            <?php endif; ?>

            <div class="input-group">
                <button type="submit" class="btn-apply">
                    <i class="fa fa-paper-plane"></i> Ajukan Sekarang
                </button>
                <a href="mainpage.php" class="btn-secondary" style="display: inline-block; margin-left: 10px; text-decoration: none;">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>

    <footer>
        <div class="footer-container">
            <p class="footer-text">© 2024 KAORI Indonesia. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.10/dist/signature_pad.umd.min.js"></script>
    <script>
        // Shifts data from PHP
        const upcomingShifts = <?php echo $shifts_json; ?>;
        
        // Select Perihal (Izin or Sakit)
        function selectPerihal(type) {
            // Remove active class from all
            document.querySelectorAll('.perihal-option').forEach(opt => opt.classList.remove('active'));
            
            // Add active to selected
            const selected = document.querySelector(`#perihal-${type.toLowerCase()}`);
            selected.checked = true;
            selected.parentElement.classList.add('active');
            
            // Show/hide surat dokter note
            const note = document.getElementById('surat-dokter-note');
            if (type === 'Sakit') {
                note.style.display = 'block';
            } else {
                note.style.display = 'none';
            }
        }

        // Auto-calculate lama izin
        document.addEventListener('DOMContentLoaded', function() {
            const tglMulai = document.getElementById('tanggal_mulai');
            const tglSelesai = document.getElementById('tanggal_selesai');
            const lamaIzin = document.getElementById('lama_izin');

            function hitungLamaIzin() {
                if (tglMulai.value && tglSelesai.value) {
                    const start = new Date(tglMulai.value);
                    const end = new Date(tglSelesai.value);
                    if (!isNaN(start) && !isNaN(end) && end >= start) {
                        const diff = Math.floor((end - start) / (1000*60*60*24)) + 1;
                        lamaIzin.value = diff;
                        
                        // Check if sakit > 3 hari
                        const perihalSakit = document.getElementById('perihal-sakit');
                        if (perihalSakit && perihalSakit.checked && diff > 3) {
                            document.getElementById('surat-dokter-note').style.display = 'block';
                        }
                    } else {
                        lamaIzin.value = '';
                    }
                } else {
                    lamaIzin.value = '';
                }
            }

            tglMulai.addEventListener('change', hitungLamaIzin);
            tglSelesai.addEventListener('change', hitungLamaIzin);

            // Update tanggal_selesai min value when tanggal_mulai changes
            tglMulai.addEventListener('change', function() {
                tglSelesai.min = tglMulai.value;
            });

            // Signature Pad (if not saved)
            <?php if (empty($tanda_tangan_tersimpan)): ?>
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas);
            const signatureData = document.getElementById('signature-data');

            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
                signatureData.value = '';
            });

            document.getElementById('form-izin-sakit').addEventListener('submit', function(e) {
                if (!signaturePad.isEmpty()) {
                    signatureData.value = signaturePad.toDataURL();
                } else {
                    alert('Harap tanda tangan terlebih dahulu!');
                    e.preventDefault();
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>
