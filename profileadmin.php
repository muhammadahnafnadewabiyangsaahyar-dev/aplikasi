<?php
session_start();
include 'connect.php'; // Ini memuat $pdo

// Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
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
    $posisi = $_POST['posisi'] ?? '';
    $username = $_POST['username'] ?? '';

    // Validasi sederhana (opsional tapi disarankan)
    if (empty($nama_lengkap) || empty($email) || empty($username)) {
        $profile_error = "Nama Lengkap, Email, dan Username tidak boleh kosong.";
    } else {
        try {
            $sql_update_profile = "UPDATE register SET nama_lengkap = ?, email = ?, no_whatsapp = ?, outlet = ?, posisi = ?, username = ? WHERE id = ?";
            $stmt_update_profile = $pdo->prepare($sql_update_profile);
            
            if ($stmt_update_profile->execute([$nama_lengkap, $email, $no_wa, $outlet, $posisi, $username, $user_id])) {
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
    <div class="headercontainer">
        <img class="logo" src="logo.png" alt="Logo">
        <div class="nav-links">
            <a href="<?php echo $home_url; ?>" class="home">Home</a>
            <a href="approve.php" class="surat">Surat Izin</a>
            <a href="profileadmin.php" class="profile">Profile</a>
            <a href="absen.php" class="absensi">Absensi</a>
            <a href="view_user.php" class="viewusers">Daftar Pengguna</a>
            <a href="view_absensi.php" class="viewabsensi">Daftar Absensi</a>
            <a href="rekapabsen.php" class="rekapabsen">Rekap Absensi</a>
            <a href="slipgaji.php" class="slipgaji">Slip Gaji</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    <div class="main-title">Profil Admin</div>
    <div class="subtitle-container">
        <p class="subtitle">Kelola informasi profil dan keamanan akun Anda.</p>
    </div>

    <div class="content-container profile-columns">
        
        <div class="profile-column-left">
            <h2>Informasi Profil</h2>
            
            <div class="profile-picture-container">
                <img src="uploads/<?php echo htmlspecialchars($user_data['foto_profil'] ?? 'default.png'); ?>" 
                     alt="Foto Profil" 
                     id="profile-pic-preview"
                     onerror="this.src='uploads/default.png';"> </div>
            
            <div class="upload-container">
                <p>Ganti Foto Profil (Max 2MB, JPG/PNG):</p>
                <iframe src="upload_foto.php" frameborder="0" scrolling="no" width="100%" height="60" id="upload-iframe"></iframe>
            </div>

            <hr>

            <form action="profileadmin.php" method="POST" autocomplete="off">
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
                    <input type="text" name="posisi" value="<?php echo htmlspecialchars($user_data['posisi']); ?>">
                    <label>Posisi</label>
                </div>
                <div class="input-group">
                    <input type="text" name="outlet" value="<?php echo htmlspecialchars($user_data['outlet']); ?>">
                    <label>Outlet</label>
                </div>
                <button type="submit" name="update_profile" class="btn">Update Profil</button>
            </form>
        </div>

        <div class="profile-column-right">
            <h2>Keamanan</h2>
            
            <form action="profileadmin.php" method="POST" autocomplete="off">
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
                    <img src="uploads/<?php echo htmlspecialchars($user_data['tanda_tangan_file']); ?>" alt="Tanda Tangan" style="max-width: 250px; border: 1px solid #ccc; padding: 5px;">
                <?php else: ?>
                    <p>(Belum ada tanda tangan)</p>
                <?php endif; ?>
                <br>
                </div>
        </div>
    </div>

</body>
<footer>
    <div class="footer-container">
        <p class="footer-text">© 2024 KAORI Indonesia. All rights reserved.</p>
    </div>
</footer>
</html>