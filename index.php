<?php
// ========================================================
// --- LOGIKA REGISTRASI & KONEKSI DB ---
// ========================================================
include 'connect.php'; // WAJIB ADA untuk koneksi DB (PDO)

$errors = []; // Array untuk menampung semua error
$form_data = []; // Array untuk "sticky form"
$registration_attempted = false; // Penanda untuk JS

// --- 1. PROSES JIKA ADA SUBMIT REGISTRASI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    
    $registration_attempted = true; // Tandai bahwa ada percobaan daftar
    
    // Ambil dan bersihkan data, simpan ke $form_data untuk sticky form
    // (Sanitasi dasar, htmlspecialchars akan digunakan saat output)
    $form_data['nama_panjang'] = $_POST['nama_panjang'] ?? '';
    $form_data['posisi'] = $_POST['posisi'] ?? '';
    $form_data['outlet'] = $_POST['outlet'] ?? '';
    $form_data['no_wa'] = $_POST['no_wa'] ?? '';
    $form_data['email'] = $_POST['email'] ?? '';
    $form_data['username'] = $_POST['username'] ?? '';
    $form_data['password'] = $_POST['password'] ?? '';
    $form_data['confirm_password'] = $_POST['confirm_password'] ?? '';

    // --- 2. VALIDASI PER-FIELD ---
    if (empty($form_data['nama_panjang'])) $errors['nama_panjang'] = 'Nama Lengkap harus diisi.';
    if (empty($form_data['posisi'])) $errors['posisi'] = 'Posisi harus dipilih.';
    if (empty($form_data['outlet'])) $errors['outlet'] = 'Outlet harus dipilih.';
    if (empty($form_data['no_wa'])) {
        $errors['no_wa'] = 'No. WhatsApp harus diisi.';
    } elseif (!preg_match('/^\+62[0-9]{10,12}$/', $form_data['no_wa'])) {
        $errors['no_wa'] = 'Format salah (Contoh: +6281234567890).';
    }
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email harus diisi.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }
    if (empty($form_data['username'])) $errors['username'] = 'Username harus diisi.';
    if (empty($form_data['password'])) $errors['password'] = 'Password harus diisi.';
    if (empty($form_data['confirm_password'])) {
        $errors['confirm_password'] = 'Konfirmasi password harus diisi.';
    } elseif ($form_data['password'] !== $form_data['confirm_password']) {
        $errors['confirm_password'] = 'Password dan Konfirmasi tidak cocok.';
    }

    // --- 3. PROSES KE DATABASE (Hanya jika tidak ada error validasi) ---
    if (empty($errors)) {
        
        try {
            // Cek Whitelist
            $sql_cek_nama = "SELECT * FROM pegawai_whitelist WHERE nama_lengkap = ?";
            $stmt_cek_nama = $pdo->prepare($sql_cek_nama);
            $stmt_cek_nama->execute([$form_data['nama_panjang']]);
            $pegawai_data = $stmt_cek_nama->fetch();

            if (!$pegawai_data) {
                $errors['nama_panjang'] = 'Error! Nama anda belum terdaftar di sistem, silakan hubungi admin.';
            } elseif ($pegawai_data['status_registrasi'] == 'terdaftar') {
                $errors['nama_panjang'] = 'Error! Nama ini sudah terdaftar. Silakan login.';
            }

            // Jika MASIH tidak ada error (nama ada di whitelist & 'pending')
            if (empty($errors)) {
                // Hash password
                $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
                $role = "user";

                // Mulai Transaksi
                $pdo->beginTransaction();

                // 1. INSERT ke tabel 'register'
                $sql_insert = "INSERT INTO register (nama_lengkap, posisi, outlet, no_whatsapp, email, username, password, role) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    $form_data['nama_panjang'], $form_data['posisi'], $form_data['outlet'], 
                    $form_data['no_wa'], $form_data['email'], $form_data['username'], 
                    $hashed_password, $role
                ]);

                // 2. UPDATE tabel 'pegawai_whitelist'
                $sql_update_wl = "UPDATE pegawai_whitelist SET status_registrasi = 'terdaftar' WHERE nama_lengkap = ?";
                $stmt_update_wl = $pdo->prepare($sql_update_wl);
                $stmt_update_wl->execute([$form_data['nama_panjang']]);

                // SUKSES: Commit transaksi
                $pdo->commit();
                header("Location: index.php?status=register_success");
                exit();
                
            } // akhir 'if empty(errors)' setelah cek whitelist

        } catch (PDOException $e) {
            // Gagal transaksi, rollback
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Cek error duplikat (Error code 1062)
            if ($e->getCode() == 1062 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                $error_msg = $e->getMessage();
                if (strpos($error_msg, 'username')) {
                    $errors['username'] = 'Username ini sudah digunakan.';
                } elseif (strpos($error_msg, 'email')) {
                    $errors['email'] = 'Email ini sudah digunakan.';
                } elseif (strpos($error_msg, 'no_whatsapp')) {
                    $errors['no_wa'] = 'No. WhatsApp ini sudah digunakan.';
                } else {
                    $errors['general'] = 'Data duplikat. Cek kembali data Anda.';
                }
            } else {
                // Error database lainnya
                error_log("Registrasi Gagal: " . $e->getMessage());
                $errors['general'] = 'Registrasi gagal. Hubungi admin. (' . $e->getMessage() . ')';
            }
        }
    }
} // --- AKHIR BLOK `if (POST)` ---


// ========================================================
// --- AMBIL DATA DROPDOWN (setelah logika POST) ---
// ========================================================
// Menggunakan try-catch untuk mengambil data
$daftar_posisi = [];
$daftar_cabang = [];

try {
    // Ambil Daftar Posisi (dari tabel baru 'posisi_jabatan')
    $sql_posisi = "SELECT nama_posisi FROM posisi_jabatan ORDER BY nama_posisi ASC";
    $daftar_posisi = $pdo->query($sql_posisi)->fetchAll(PDO::FETCH_COLUMN);

    // Ambil Daftar Cabang (dari tabel 'cabang_outlet' yang diganti nama)
    $sql_cabang = "SELECT nama_cabang FROM cabang_outlet ORDER BY nama_cabang ASC";
    $daftar_cabang = $pdo->query($sql_cabang)->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    // Jika tabel tidak ada, set error general
    $errors['general'] = "Gagal memuat data formulir. Database belum siap. (" . $e->getMessage() . ")";
    error_log("Gagal fetch dropdown: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Login & Register</title>
    <style>
        /* (CSS Anda dari file asli tetap di sini) */
        /* ... */
        .input-group select { width: 100%; padding: 15px; background: transparent; border: none; outline: none; border-radius: 5px; color: #333; font-size: 1em; position: relative; z-index: 2; -webkit-appearance: none; -moz-appearance: none; appearance: none; }
        .input-group select option { background: #fff; color: #333; }
        .input-group select:valid + label, .input-group select:focus + label { top: 2px; left: 15px; font-size: 0.75em; color: #555; z-index: 3; }
        .input-group input.input-error, .input-group select.input-error { border: 1px solid #dc3545 !important; box-shadow: 0 0 5px rgba(220, 53, 69, 0.5) !important; }
        .error-message { color: #dc3545; font-size: 0.85em; margin-top: -10px; margin-bottom: 10px; padding-left: 5px; text-align: left; }
        .general-error { color: red; background-color: #ffebee; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="headercontainer">
    <img class="logo" src="logo.png" alt="Logo">
    </div>
    
    <div class="container" id="signup">
        <h1 class="form-title">Daftar</h1>
        
        <form method="POST" action="index.php" autocomplete="off">
            
            <?php if (isset($errors['general'])): ?>
                <p class="general-error"><?php echo htmlspecialchars($errors['general']); ?></p>
            <?php endif; ?>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'register_success'): ?>
                <p style="color: green; background-color: #e8f5e9; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
                    Pendaftaran berhasil! Silakan login.
                </p>
            <?php endif; ?>
            
            <div class="input-group">
                <i class="fa fa-user"></i>
                <input type="text" name="nama_panjang" placeholder="Nama Lengkap" autocomplete="off" required 
                       value="<?php echo htmlspecialchars($form_data['nama_panjang'] ?? ''); ?>"
                       class="<?php echo isset($errors['nama_panjang']) ? 'input-error' : ''; ?>">
                <label for="nama_panjang">Nama Lengkap</label>
            </div>
            <?php if (isset($errors['nama_panjang'])): ?>
                <p class="error-message"><?php echo $errors['nama_panjang']; ?></p>
            <?php endif; ?>

            <div class="input-group">
                <i class="fa fa-briefcase"></i>
                <select name="posisi" id="posisi" required 
                        class="<?php echo isset($errors['posisi']) ? 'input-error' : ''; ?>">
                    <option value="" <?php echo empty($form_data['posisi']) ? 'selected' : ''; ?>>-- Pilih Posisi --</option>
                    <?php foreach ($daftar_posisi as $posisi): ?>
                        <option value="<?php echo htmlspecialchars($posisi); ?>" 
                                <?php echo (isset($form_data['posisi']) && $form_data['posisi'] == $posisi) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($posisi); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="posisi">Jabatan</label>
            </div>
            <?php if (isset($errors['posisi'])): ?>
                <p class="error-message"><?php echo $errors['posisi']; ?></p>
            <?php endif; ?>
            
            <div class="input-group">
                <i class="fa fa-location-dot"></i>
                <select name="outlet" id="outlet" required
                        class="<?php echo isset($errors['outlet']) ? 'input-error' : ''; ?>">
                    <option value="" <?php echo empty($form_data['outlet']) ? 'selected' : ''; ?>>-- Pilih Outlet --</option>
                    <?php foreach ($daftar_cabang as $cabang): ?>
                        <option value="<?php echo htmlspecialchars($cabang); ?>"
                                <?php echo (isset($form_data['outlet']) && $form_data['outlet'] == $cabang) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cabang); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="outlet">Outlet</label>
            </div>
            <?php if (isset($errors['outlet'])): ?>
                <p class="error-message"><?php echo $errors['outlet']; ?></p>
            <?php endif; ?>

            <div class="input-group">
                <i class="fa fa-brands fa-whatsapp"></i>
                <input type="tel" name="no_wa" placeholder="No WhatsApp" autocomplete="off" required 
                       pattern="\+62[0-9]{10,12}" 
                       title="Format: +62 diikuti 10-12 digit angka (Contoh: +6281234567890)"
                       value="<?php echo htmlspecialchars($form_data['no_wa'] ?? ''); ?>"
                       class="<?php echo isset($errors['no_wa']) ? 'input-error' : ''; ?>">
                <label for="no_wa">No WhatsApp</label>
            </div>
            <?php if (isset($errors['no_wa'])): ?>
                <p class="error-message"><?php echo $errors['no_wa']; ?></p>
            <?php endif; ?>

            <div class="input-group">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" autocomplete="off" required
                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                       class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>">
                <label for="email">Email</label>
            </div>
            <?php if (isset($errors['email'])): ?>
                <p class="error-message"><?php echo $errors['email']; ?></p>
            <?php endif; ?>

            <div class="input-group">
                <i class="fa fa-user"></i>
                <input type="text" name="username" placeholder="Username" autocomplete="off" required
                       value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                       class="<?php echo isset($errors['username']) ? 'input-error' : ''; ?>">
                <label for="username">Username</label>
            </div>
            <?php if (isset($errors['username'])): ?>
                <p class="error-message"><?php echo $errors['username']; ?></p>
            <?php endif; ?>

            <div class="input-group">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Password" autocomplete="new-password" required
                       class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>">
                <label for="password">Password</label>
            </div>
            <?php if (isset($errors['password'])): ?>
                <p class="error-message"><?php echo $errors['password']; ?></p>
            <?php endif; ?>

            <div class="input-group">
                <i class="fa fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" autocomplete="new-password" required
                       class="<?php echo isset($errors['confirm_password']) ? 'input-error' : ''; ?>">
                <label for="confirm_password">Confirm Password</label>
            </div>
            <?php if (isset($errors['confirm_password'])): ?>
                <p class="error-message"><?php echo $errors['confirm_password']; ?></p>
            <?php endif; ?>
            
            <button type="submit" name="register" class="btn">Daftar</button>
        </form>
        <p class="or">-----Atau-----</p>
        <div class="icon">
            <i class="fa fa-brands fa-google"></i>
            <i class="fa fa-brands fa-facebook-f"></i>
            <i class="fa fa-brands fa-twitter"></i>
        </div>
        <div class="links">
            <p>Sudah Punya Akun?</p>
            <button id="loginbutton">Masuk</button>
        </div>
    </div>

    <div class="container" id="login">
        <h1 class="form-title">Masuk</h1>
        <form method="POST" action="login.php" autocomplete="off">
            <?php 
            // Logika untuk menampilkan error dari login.php
            if (isset($_GET['error'])) {
                $error_message = '';
                switch ($_GET['error']) {
                    case 'invalidpassword':
                        $error_message = 'Password yang Anda masukkan salah.';
                        break;
                    case 'usernotfound':
                        $error_message = 'Username tidak ditemukan.';
                        break;
                    case 'emptyfields':
                        $error_message = 'Username dan Password harus diisi.';
                        break;
                    case 'dberror':
                        $error_message = 'Terjadi masalah pada database. Hubungi admin.';
                        break;
                }
                if ($error_message) {
                    echo '<p class="general-error">' . htmlspecialchars($error_message) . '</p>';
                }
            }
            ?>
            <div class="input-group">
                <i class="fa fa-user"></i>
                <input type="text" name="username" placeholder="Username" autocomplete="off" required>
                <label for="username">Username</label>
            </div>
            <div class="input-group">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Password" autocomplete="new-password" required>
                <label for="password">Password</label>
            </div>
            <div class="recover">
                <a href="#">Lupa Password?</a>
            </div>
            <button type="submit" name="login" class="btn">Masuk</button>
        </form>
        <p class="or">-----Atau-----</p>
        <div class="icon">
            <i class="fa fa-brands fa-google"></i>
            <i class="fa fa-brands fa-facebook-f"></i>
            <i class="fa fa-brands fa-twitter"></i>
        </div>
        <div class="links">
            <p>Belum Punya Akun?</p>
            <button id="signupbutton">Daftar</button>
        </div>
    </div>
    
    <script src="script.js"></script>
    <?php
    // --- BLOK BARU: Memaksa form registrasi terbuka jika ada error ---
    if ($registration_attempted && !empty($errors)) {
        echo "
        <script>
            document.getElementById('login').style.display = 'none';
            document.getElementById('signup').style.display = 'block';
        </script>
        ";
    }
    ?>
</body>
</html>