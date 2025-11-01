<?php
session_start();
include 'connect.php'; // Ini memuat $pdo

// Keamanan: Pastikan hanya user yang sudah login yang bisa akses
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=unauthorized');
    exit;
}

$user_id = $_SESSION['user_id'];

// Inisialisasi variabel pesan
$password_error = $password_success = $profile_error = $profile_success = "";

// ========================================================
// --- BLOK 1: Penanganan Update Password (POST) ---
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Ambil password HASH saat ini dari DB
    try {
        $stmt_check = $pdo->prepare("SELECT password FROM register WHERE id = ?");
        $stmt_check->execute([$user_id]);
        $user = $stmt_check->fetch();

        if ($user && password_verify($current_password, $user['password'])) {
            // Password saat ini benar
            if ($new_password === $confirm_password) {
                if (strlen($new_password) < 6) { // Tambahkan validasi dasar
                    $password_error = "Password baru harus minimal 6 karakter.";
                } else {
                    // Hash password baru dan update
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $sql_update_pass = "UPDATE register SET password = ? WHERE id = ?";
                    $stmt_update_pass = $pdo->prepare($sql_update_pass);
                    
                    if ($stmt_update_pass->execute([$new_hashed_password, $user_id])) {
                        $password_success = "Password berhasil diperbarui.";
                    } else {
                        $password_error = "Gagal memperbarui password. Coba lagi.";
                    }
                }
            } else {
                $password_error = "Password baru dan konfirmasi tidak cocok.";
            }
        } else {
            $password_error = "Password saat ini yang Anda masukkan salah.";
        }
    } catch (PDOException $e) {
        $password_error = "Error database: " . $e->getMessage();
    }
}

// ========================================================
// --- BLOK 2: Penanganan Update Profil (POST) ---
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Ambil data dari form
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_wa = $_POST['no_wa'] ?? '';
    $outlet = $_POST['outlet'] ?? '';
    $username = $_POST['username'] ?? '';

    // Validasi sederhana (opsional tapi disarankan)
    if (empty($nama_lengkap) || empty($email) || empty($username)) {
        $profile_error = "Nama Lengkap, Email, dan Username tidak boleh kosong.";
    } else {
        try {
            $sql_update_profile = "UPDATE register SET nama_lengkap = ?, email = ?, no_whatsapp = ?, outlet = ?, username = ? WHERE id = ?";
            $stmt_update_profile = $pdo->prepare($sql_update_profile);
            
            if ($stmt_update_profile->execute([$nama_lengkap, $email, $no_wa, $outlet, $username, $user_id])) {
                // Update data di SESSION agar langsung berubah di halaman
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['username'] = $username;
                
                $profile_success = "Profil berhasil diperbarui.";
                // Refresh data di halaman ini akan diambil oleh BLOK 3
            } else {
                $profile_error = "Gagal memperbarui profil. Coba lagi.";
            }
        } catch (PDOException $e) {
            // Tangani error jika data duplikat (Error code 1062)
            if ($e->errorInfo[1] == 1062) {
                if (strpos($e->getMessage(), 'username')) {
                    $profile_error = 'Username ini sudah digunakan.';
                } elseif (strpos($e->getMessage(), 'email')) {
                    $profile_error = 'Email ini sudah digunakan.';
                } elseif (strpos($e->getMessage(), 'no_whatsapp')) {
                    $profile_error = 'No. WhatsApp ini sudah digunakan.';
                } else {
                    $profile_error = 'Data duplikat. Cek kembali data Anda.';
                }
            } else {
                $profile_error = "Error database: " . $e->getMessage();
            }
        }
    }
}

// ========================================================
// --- BLOK 3: Ambil Data Pengguna (GET) ---
// --- (Selalu jalankan untuk menampilkan data terbaru) ---
// ========================================================
try {
    $sql_select = "SELECT * FROM register WHERE id = ?";
    $stmt_select = $pdo->prepare($sql_select);
    $stmt_select->execute([$user_id]);
    $user_data = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        // Jika karena alasan aneh user_id di session tidak ada di DB
        session_destroy();
        header('Location: index.php?error=user_data_missing');
        exit;
    }
} catch (PDOException $e) {
    die("Error mengambil data pengguna: " . $e->getMessage());
}

// ========================================================
// --- BLOK 4: Penanganan Simpan/Hapus Tanda Tangan Digital ---
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_ttd'])) {
    $signature_data_base64 = $_POST['signature_data'] ?? '';
    if (!empty($signature_data_base64)) {
        if (preg_match('/^data:image\/(\w+);base64,/', $signature_data_base64, $type)) {
            $signature_data_base64 = substr($signature_data_base64, strpos($signature_data_base64, ',') + 1);
            $type = strtolower($type[1]);
            if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                $signature_data_biner = base64_decode($signature_data_base64);
                if ($signature_data_biner !== false) {
                    $nama_file_ttd = 'ttd_user_' . $user_id . '_' . time() . '.' . $type;
                    $path_simpan_ttd = 'uploads/tanda_tangan/' . $nama_file_ttd;
                    if (file_put_contents($path_simpan_ttd, $signature_data_biner)) {
                        // Hapus ttd lama jika ada
                        $stmt = $pdo->prepare('SELECT tanda_tangan_file FROM register WHERE id = ?');
                        $stmt->execute([$user_id]);
                        $old = $stmt->fetchColumn();
                        if ($old && file_exists('uploads/tanda_tangan/' . $old)) unlink('uploads/tanda_tangan/' . $old);
                        // Simpan ke DB
                        $stmt = $pdo->prepare('UPDATE register SET tanda_tangan_file = ? WHERE id = ?');
                        $stmt->execute([$nama_file_ttd, $user_id]);
                        header('Location: profile.php?ttd=sukses');
                        exit;
                    } else {
                        $profile_error = 'Gagal menyimpan file tanda tangan.';
                    }
                } else {
                    $profile_error = 'Data tanda tangan tidak valid.';
                }
            } else {
                $profile_error = 'Tipe file tanda tangan tidak valid.';
            }
        } else {
            $profile_error = 'Format data tanda tangan tidak valid.';
        }
    } else {
        $profile_error = 'Tanda tangan tidak boleh kosong.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_ttd'])) {
    $stmt = $pdo->prepare('SELECT tanda_tangan_file FROM register WHERE id = ?');
    $stmt->execute([$user_id]);
    $old = $stmt->fetchColumn();
    if ($old && file_exists('uploads/tanda_tangan/' . $old)) unlink('uploads/tanda_tangan/' . $old);
    $stmt = $pdo->prepare('UPDATE register SET tanda_tangan_file = NULL WHERE id = ?');
    $stmt->execute([$user_id]);
    header('Location: profile.php?ttd=hapus');
    exit;
}

$home_url = 'mainpageadmin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Teman KAORI</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="main-title">Profil</div>
    <div class="subtitle-container">
        <p class="subtitle">Kelola informasi profil dan keamanan akun Anda.</p>
    </div>

    <div class="content-container profile-columns">
        
        <div class="profile-column-left">
            <h2>Informasi Pribadi</h2>
            
            <div class="profile-picture-container">
                <?php if (!empty($user_data['foto_profil'])): ?>
                    <img src="uploads/foto_profil/<?php echo htmlspecialchars($user_data['foto_profil']); ?>" 
                         alt="Foto Profil" 
                         id="profile-pic-preview"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                    <span style="display:none;"><i class="fa fa-user" style="font-size: 100px; color: #bbb;"></i></span>
                <?php else: ?>
                    <i class="fa fa-user" style="font-size: 100px; color: #bbb;"></i>
                <?php endif; ?>
            </div>
            
            <div class="upload-container">
                <p>Ganti Foto Profil (Max 2MB, JPG/PNG):</p>
                <iframe src="upload_foto.php" frameborder="0" scrolling="no" width="100%" height="60" id="upload-iframe"></iframe>
            </div>

            <hr>

            <form action="profile.php" method="POST" autocomplete="off">
                <?php if ($profile_success): ?><p class="success-message"><?php echo $profile_success; ?></p><?php endif; ?>
                <?php if ($profile_error): ?><p class="error-message"><?php echo $profile_error; ?></p><?php endif; ?>

                <div class="input-group">
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required>
                    <label>Nama Lengkap</label>
                </div>
                <div class="input-group">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    <label>Email</label>
                </div>
                <div class="input-group">
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                    <label>Username</label>
                </div>
                <div class="input-group">
                    <input type="text" name="no_wa" value="<?php echo htmlspecialchars($user_data['no_whatsapp']); ?>">
                    <label>No. WhatsApp</label>
                </div>
                <div class="input-group">
                    <input type="text" name="outlet" value="<?php echo htmlspecialchars($user_data['outlet']); ?>">
                    <label>Outlet</label>
                </div>
                <button type="submit" name="update_profile" class="btn">Update Profil</button>
            </form>

            <!-- Tampilkan posisi hanya sebagai teks -->
            <div class="input-group">
                <input type="text" value="<?php echo htmlspecialchars($user_data['posisi']); ?>" readonly disabled>
                <label>Posisi</label>
            </div>
        </div>

        <div class="profile-column-right">
            <h2>Keamanan</h2>
            
            <form action="profile.php" method="POST" autocomplete="off">
                <h3>Ganti Password</h3>
                <?php if ($password_success): ?><p class="success-message"><?php echo $password_success; ?></p><?php endif; ?>
                <?php if ($password_error): ?><p class="error-message"><?php echo $password_error; ?></p><?php endif; ?>
                
                <div class="input-group">
                    <input type="password" name="current_password" required autocomplete="current-password">
                    <label>Password Saat Ini</label>
                </div>
                <div class="input-group">
                    <input type="password" name="new_password" required autocomplete="new-password">
                    <label>Password Baru</label>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" required autocomplete="new-password">
                    <label>Konfirmasi Password Baru</label>
                </div>
                <button type="submit" name="update_password" class="btn">Ganti Password</button>
            </form>

            <hr>
            
            <h3>Tanda Tangan Digital</h3>
            <div class="signature-container">
                <?php if (!empty($user_data['tanda_tangan_file'])): ?>
                    <img src="uploads/tanda_tangan/<?php echo htmlspecialchars($user_data['tanda_tangan_file']); ?>" alt="Tanda Tangan" style="max-width: 250px; border: 1px solid #ccc; padding: 5px;">
                    <form action="profile.php" method="POST" id="form-hapus-ttd" style="margin-top:10px;">
                        <button type="submit" name="hapus_ttd" class="btn" onclick="return confirm('Yakin hapus tanda tangan?')">Hapus Tanda Tangan</button>
                    </form>
                    <button type="button" id="btn-edit-ttd" class="btn" style="margin-top:10px;">Ganti Tanda Tangan</button>
                    <div id="edit-ttd-container" style="display:none; margin-top:15px;">
                        <form action="profile.php" method="POST" id="form-edit-ttd">
                            <label for="signature-pad">Gambar Tanda Tangan Baru:</label><br>
                            <canvas id="signature-pad" width="400" height="200" style="border:1px solid #000;"></canvas><br>
                            <button type="button" id="clear-signature">Hapus</button>
                            <input type="hidden" name="signature_data" id="signature-data">
                            <br><br>
                            <button type="submit" name="simpan_ttd" class="btn">Simpan Tanda Tangan Baru</button>
                            <button type="button" id="cancel-edit-ttd" class="btn">Batal</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>(Belum ada tanda tangan)</p>
                    <form action="profile.php" method="POST" id="form-edit-ttd">
                        <label for="signature-pad">Gambar Tanda Tangan:</label><br>
                        <canvas id="signature-pad" width="400" height="200" style="border:1px solid #000;"></canvas><br>
                        <button type="button" id="clear-signature">Hapus</button>
                        <input type="hidden" name="signature_data" id="signature-data">
                        <br><br>
                        <button type="submit" name="simpan_ttd" class="btn">Simpan Tanda Tangan</button>
                    </form>
                <?php endif; ?>
                <br>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.10/dist/signature_pad.umd.min.js"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var canvas = document.getElementById('signature-pad');
                var clearBtn = document.getElementById('clear-signature');
                var input = document.getElementById('signature-data');
                var form = document.getElementById('form-edit-ttd');
                var editBtn = document.getElementById('btn-edit-ttd');
                var editContainer = document.getElementById('edit-ttd-container');
                var cancelBtn = document.getElementById('cancel-edit-ttd');
                var signaturePad;
                if (canvas) {
                    signaturePad = new SignaturePad(canvas, { penColor: 'rgb(0,0,0)' });
                    if (clearBtn) clearBtn.addEventListener('click', function() { signaturePad.clear(); });
                    if (form) form.addEventListener('submit', function(e) {
                        if (signaturePad.isEmpty()) {
                            alert('Mohon gambar tanda tangan Anda.');
                            e.preventDefault();
                        } else {
                            input.value = signaturePad.toDataURL('image/png');
                        }
                    });
                }
                if (editBtn && editContainer) {
                    editBtn.addEventListener('click', function() {
                        editContainer.style.display = 'block';
                    });
                }
                if (cancelBtn && editContainer) {
                    cancelBtn.addEventListener('click', function() {
                        editContainer.style.display = 'none';
                        if (signaturePad) signaturePad.clear();
                    });
                }
            });
            </script>
        </div>
    </div>

</body>
<footer>
    <div class="footer-container">
        <p class="footer-text">© 2024 KAORI Indonesia. All rights reserved.</p>
    </div>
</footer>
</html>