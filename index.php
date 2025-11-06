<?php
// ========================================================
// --- SESSION CHECK: Redirect jika sudah login ---
// ========================================================
session_start();
require_once 'security_helper.php';

// Cek jika user sudah login dan session masih valid
if (isset($_SESSION['user_id']) && SecurityHelper::validateSession()) {
    // Redirect ke mainpage sesuai role
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: mainpage.php');
        } else {
            header('Location: mainpage.php');
        }
        exit;
    } else {
        // Fallback jika role tidak ada
        header('Location: mainpage.php');
        exit;
    }
}

// Jika session tidak valid, destroy session
if (isset($_SESSION['user_id']) && !SecurityHelper::validateSession()) {
    session_unset();
    session_destroy();
    session_start(); // Start fresh session
}

// ========================================================
// --- LOGIKA REGISTRASI & KONEKSI DB ---
// ========================================================
include 'connect.php'; // WAJIB ADA untuk koneksi DB (PDO)

$errors = []; // Array untuk menampung semua error
$form_data = []; // Array untuk "sticky form"
$registration_attempted = false; // Penanda untuk JS

// --- 1. PROSES JIKA ADA SUBMIT REGISTRASI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    
    error_log("=== REGISTRATION SUBMIT START ===");
    error_log("POST Data: " . print_r($_POST, true));
    
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
    
    error_log("Form data captured: " . print_r($form_data, true));

    // --- 2. VALIDASI PER-FIELD ---
    if (empty($form_data['nama_panjang'])) $errors['nama_panjang'] = 'Nama Lengkap harus diisi.';
    if (empty($form_data['posisi'])) $errors['posisi'] = 'Posisi harus dipilih.';
    if (empty($form_data['outlet'])) $errors['outlet'] = 'Outlet harus dipilih.';
    
    // Validasi No. WhatsApp: field kosong ATAU hanya berisi '+62 ' atau '+62' dianggap kosong
    $no_wa_cleaned = trim($form_data['no_wa']);
    if (empty($no_wa_cleaned) || $no_wa_cleaned === '+62' || $no_wa_cleaned === '+62 ') {
        $errors['no_wa'] = 'No. WhatsApp harus diisi.';
    } elseif (!preg_match('/^\+62\s[0-9]{8,12}$/', $no_wa_cleaned)) {
        $errors['no_wa'] = 'Format salah (Contoh: +62 81234567890).';
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
    
    error_log("Validation errors: " . print_r($errors, true));

    // --- 3. PROSES KE DATABASE (Hanya jika tidak ada error validasi) ---
    if (empty($errors)) {
        error_log("✅ No validation errors, proceeding to database...");
        
        try {
            // Cek Whitelist
            error_log("Checking whitelist for: " . $form_data['nama_panjang']);
            $sql_cek_nama = "SELECT * FROM pegawai_whitelist WHERE nama_lengkap = ?";
            $stmt_cek_nama = $pdo->prepare($sql_cek_nama);
            $stmt_cek_nama->execute([$form_data['nama_panjang']]);
            $pegawai_data = $stmt_cek_nama->fetch();
            
            error_log("Whitelist data: " . print_r($pegawai_data, true));

            if (!$pegawai_data) {
                error_log("❌ Name not found in whitelist");
                $errors['nama_panjang'] = 'Error! Nama anda belum terdaftar di sistem, silakan hubungi admin.';
            } elseif ($pegawai_data['status_registrasi'] == 'terdaftar') {
                error_log("❌ Name already registered");
                $errors['nama_panjang'] = 'Error! Nama ini sudah terdaftar. Silakan login.';
            } else {
                error_log("✅ Whitelist check passed, status: " . ($pegawai_data['status_registrasi'] ?? 'N/A'));
            }

            // Jika MASIH tidak ada error (nama ada di whitelist & 'pending')
            if (empty($errors)) {
                error_log("✅ Proceeding with registration...");
                
                // Hash password
                $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
                error_log("Password hashed successfully");
                
                // Ambil role dari whitelist, gunakan 'user' sebagai default jika kosong
                $role = isset($pegawai_data['role']) ? trim($pegawai_data['role']) : 'user';
                if ($role === '' || $role === null) {
                    $role = 'user'; // Default role jika tidak ada di whitelist
                }
                error_log("Role assigned: $role");
                
                // Ambil posisi dari whitelist
                $posisi = isset($pegawai_data['posisi']) ? trim($pegawai_data['posisi']) : $form_data['posisi'];
                error_log("Posisi assigned: $posisi");
                
                try {
                    // Mulai Transaksi
                    error_log("Starting transaction...");
                    $pdo->beginTransaction();

                    // 1. INSERT ke tabel 'register'
                    $sql_insert = "INSERT INTO register (nama_lengkap, posisi, outlet, no_whatsapp, email, password, username, role) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    error_log("INSERT Query: $sql_insert");
                    error_log("INSERT Params: " . print_r([
                        $form_data['nama_panjang'], $posisi, $form_data['outlet'], 
                        $form_data['no_wa'], $form_data['email'], '[HASHED]', $form_data['username'], $role
                    ], true));
                    
                    $stmt_insert = $pdo->prepare($sql_insert);
                    $insert_result = $stmt_insert->execute([
                        $form_data['nama_panjang'], $posisi, $form_data['outlet'], 
                        $form_data['no_wa'], $form_data['email'], $hashed_password, $form_data['username'], $role
                    ]);
                    
                    if ($insert_result) {
                        $new_user_id = $pdo->lastInsertId();
                        error_log("✅ INSERT to register SUCCESS (New ID: $new_user_id)");
                    } else {
                        error_log("❌ INSERT to register FAILED");
                    }

                    // 2. UPDATE tabel 'pegawai_whitelist'
                    $sql_update_wl = "UPDATE pegawai_whitelist SET status_registrasi = 'terdaftar' WHERE nama_lengkap = ?";
                    error_log("UPDATE Query: $sql_update_wl");
                    $stmt_update_wl = $pdo->prepare($sql_update_wl);
                    $update_result = $stmt_update_wl->execute([$form_data['nama_panjang']]);
                    $affected_rows = $stmt_update_wl->rowCount();
                    
                    if ($update_result) {
                        error_log("✅ UPDATE pegawai_whitelist SUCCESS (Affected rows: $affected_rows)");
                    } else {
                        error_log("❌ UPDATE pegawai_whitelist FAILED");
                    }

                    // 3. AUTO-SYNC: Copy salary data from pegawai_whitelist to komponen_gaji
                    error_log("🔄 Checking for salary data in pegawai_whitelist...");
                    $sql_check_salary = "SELECT gaji_pokok, tunjangan_transport, tunjangan_makan, overwork, 
                                               tunjangan_jabatan, bonus_kehadiran, bonus_marketing, insentif_omset 
                                        FROM pegawai_whitelist WHERE nama_lengkap = ?";
                    $stmt_salary = $pdo->prepare($sql_check_salary);
                    $stmt_salary->execute([$form_data['nama_panjang']]);
                    $salary_data = $stmt_salary->fetch(PDO::FETCH_ASSOC);
                    
                    if ($salary_data && ($salary_data['gaji_pokok'] > 0 || $salary_data['tunjangan_transport'] > 0 || $salary_data['tunjangan_makan'] > 0)) {
                        // Ada data gaji, sync ke komponen_gaji
                        error_log("✅ Salary data found! Syncing to komponen_gaji...");
                        $sql_insert_gaji = "INSERT INTO komponen_gaji 
                                           (register_id, jabatan, gaji_pokok, tunjangan_transport, tunjangan_makan, 
                                            overwork, tunjangan_jabatan, bonus_kehadiran, bonus_marketing, insentif_omset) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt_gaji = $pdo->prepare($sql_insert_gaji);
                        $stmt_gaji->execute([
                            $new_user_id,
                            $posisi,
                            $salary_data['gaji_pokok'] ?? 0,
                            $salary_data['tunjangan_transport'] ?? 0,
                            $salary_data['tunjangan_makan'] ?? 0,
                            $salary_data['overwork'] ?? 0,
                            $salary_data['tunjangan_jabatan'] ?? 0,
                            $salary_data['bonus_kehadiran'] ?? 0,
                            $salary_data['bonus_marketing'] ?? 0,
                            $salary_data['insentif_omset'] ?? 0
                        ]);
                        error_log("✅ Salary data synced to komponen_gaji successfully!");
                    } else {
                        error_log("ℹ️ No salary data found in pegawai_whitelist or all values are 0");
                    }

                    // SUKSES: Commit transaksi
                    $pdo->commit();
                    error_log("✅ Transaction committed successfully");
                    error_log("🔄 Redirecting to success page...");
                    error_log("=== REGISTRATION SUBMIT END (SUCCESS) ===");
                    
                    header("Location: index.php?status=register_success");
                    exit();
                    
                } catch (PDOException $e_insert) {
                    // Gagal transaksi, rollback
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                        error_log("Transaction rolled back");
                    }
                    
                    // Log error untuk debugging
                    error_log("❌ Registrasi INSERT Gagal: " . $e_insert->getMessage());
                    error_log("Error Code: " . $e_insert->getCode());
                    error_log("Error Info: " . print_r($e_insert->errorInfo, true));
                    
                    // Cek error duplikat (Error code 1062)
                    if ($e_insert->getCode() == 1062 || (isset($e_insert->errorInfo[1]) && $e_insert->errorInfo[1] == 1062)) {
                        $error_msg = $e_insert->getMessage();
                        if (strpos($error_msg, 'username') !== false) {
                            $errors['username'] = 'Username ini sudah digunakan.';
                        } elseif (strpos($error_msg, 'email') !== false) {
                            $errors['email'] = 'Email ini sudah digunakan.';
                        } elseif (strpos($error_msg, 'no_whatsapp') !== false) {
                            $errors['no_wa'] = 'No. WhatsApp ini sudah digunakan.';
                        } else {
                            $errors['general'] = 'Data duplikat. Cek kembali data Anda.';
                        }
                    } else {
                        // Error database lainnya
                        $errors['general'] = 'Registrasi gagal: ' . $e_insert->getMessage();
                    }
                    error_log("=== REGISTRATION SUBMIT END (FAILED - INSERT ERROR) ===");
                }
            } else {
                // Error after whitelist check
                error_log("❌ Errors after whitelist check: " . print_r($errors, true));
                error_log("=== REGISTRATION SUBMIT END (FAILED - WHITELIST CHECK) ===");
            } // akhir 'if empty(errors)' setelah cek whitelist

        } catch (PDOException $e) {
            // Gagal transaksi, rollback
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
                error_log("Transaction rolled back (outer catch)");
            }

            error_log("❌ Registrasi Gagal (Outer Catch): " . $e->getMessage());
            error_log("Error Code: " . $e->getCode());
            error_log("Error Info: " . print_r($e->errorInfo, true));

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
            error_log("=== REGISTRATION SUBMIT END (FAILED - OUTER CATCH) ===");
        }
    } else {
        error_log("❌ Validation failed, cannot proceed to database");
        error_log("Errors: " . print_r($errors, true));
        error_log("=== REGISTRATION SUBMIT END (FAILED - VALIDATION) ===");
    }
} // --- AKHIR BLOK `if (POST)` ---


// ========================================================
// --- AMBIL DATA DROPDOWN (setelah logika POST) ---
// ========================================================
// Menggunakan try-catch untuk mengambil data
$daftar_posisi = [];
$daftar_cabang = [];

try {
    // Log: Ambil data dropdown
    error_log("=== FETCHING DROPDOWN DATA FOR REGISTRATION ===");
    
    // Ambil Daftar Posisi (dari tabel baru 'posisi_jabatan')
    $sql_posisi = "SELECT nama_posisi FROM posisi_jabatan ORDER BY nama_posisi ASC";
    $daftar_posisi = $pdo->query($sql_posisi)->fetchAll(PDO::FETCH_COLUMN);
    error_log("Posisi fetched: " . count($daftar_posisi) . " items");
    error_log("Posisi list: " . print_r($daftar_posisi, true));

    // Ambil Daftar Cabang (dari tabel 'cabang_outlet' yang diganti nama)
    $sql_cabang = "SELECT nama_cabang FROM cabang_outlet ORDER BY nama_cabang ASC";
    $daftar_cabang = $pdo->query($sql_cabang)->fetchAll(PDO::FETCH_COLUMN);
    error_log("Cabang fetched: " . count($daftar_cabang) . " items");
    
    error_log("=== END FETCHING DROPDOWN DATA ===");

} catch (PDOException $e) {
    // Jika tabel tidak ada, set error general
    $errors['general'] = "Gagal memuat data formulir. Database belum siap. (" . $e->getMessage() . ")";
    error_log("❌ Gagal fetch dropdown: " . $e->getMessage());
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
</head>
<body>
    <div class="headercontainer">
    <img class="logo" src="logo.png" alt="Logo">
    </div>

    <!-- Modal/Jendela Check Whitelist -->
    <div class="container" id="check-whitelist" style="display: none;">
        <h1 class="form-title">Cek Nama di Whitelist</h1>
        <form id="whitelistForm" autocomplete="off">
            <div class="input-group">
                <i class="fa fa-user"></i>
                <input type="text" name="whitelist_nama" id="whitelist_nama" placeholder="Masukkan Nama Lengkap" required>
                <label for="whitelist_nama">Nama Lengkap</label>
            </div>
            <button type="submit" class="btn">Cek Whitelist</button>
        </form>
        <div id="whitelist-result" style="margin-top: 15px;"></div>
        <button id="lanjutDaftarBtn" class="btn" style="display:none; margin-top:10px;">Lanjut Daftar</button>
        <div class="links">
            <button id="backToLoginBtn">Kembali ke Login</button>
        </div>
    </div>

    <!-- Form Registrasi (hanya muncul setelah whitelist valid) -->
    <div class="container" id="signup" style="display:none;">
        <h1 class="form-title">Daftar</h1>
        <form method="POST" action="index.php" autocomplete="off" id="signupForm">
            <!-- Nama & Posisi readonly -->
            <div class="input-group">
                <i class="fa fa-user"></i>
                <input type="text" name="nama_panjang" id="signup_nama" placeholder="Nama Lengkap" readonly required value="<?php echo htmlspecialchars($form_data['nama_panjang'] ?? ''); ?>">
                <label for="signup_nama">Nama Lengkap</label>
            </div>
            <div class="input-group">
                <i class="fa fa-briefcase"></i>
                <input type="text" name="posisi" id="signup_posisi" placeholder="Posisi" readonly required value="<?php echo htmlspecialchars($form_data['posisi'] ?? ''); ?>">
                <label for="signup_posisi">Jabatan</label>
            </div>
            <!-- Field lain seperti biasa -->
            <div class="input-group">
                <i class="fa fa-location-dot"></i>
                <select name="outlet" id="outlet" required class="<?php echo isset($errors['outlet']) ? 'input-error' : ''; ?>">
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
            <div class="input-group">
                <i class="fa fa-brands fa-whatsapp"></i>
                <input type="tel" name="no_wa" id="no_wa" placeholder="+62 8123xxxxxxx" autocomplete="off" required 
                       pattern="\+62\s[0-9]{8,12}" 
                       title="Format: +62 diikuti spasi dan 8-12 digit angka (Contoh: +62 81234567890)"
                       value="<?php echo isset($form_data['no_wa']) ? htmlspecialchars($form_data['no_wa']) : ''; ?>"
                       class="<?php echo isset($errors['no_wa']) ? 'input-error' : ''; ?>">
                <label for="no_wa">No WhatsApp</label>
            </div>
            <div class="input-group">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" autocomplete="off" required
                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                       class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>">
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <i class="fa fa-user"></i>
                <input type="text" name="username" placeholder="Username" autocomplete="off" required
                       value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                       class="<?php echo isset($errors['username']) ? 'input-error' : ''; ?>">
                <label for="username">Username</label>
            </div>
            <div class="input-group">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Password" autocomplete="new-password" required
                       class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>">
                <label for="password">Password</label>
            </div>
            <div class="input-group">
                <i class="fa fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" autocomplete="new-password" required
                       class="<?php echo isset($errors['confirm_password']) ? 'input-error' : ''; ?>">
                <label for="confirm_password">Confirm Password</label>
            </div>
            <?php if (!empty($errors)): ?>
                <div class="error-list" style="color:red;margin-bottom:10px;">
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($errors as $field => $msg): ?>
                            <li><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <button type="submit" name="register" class="btn">Daftar</button>
        </form>
        <div class="links">
            <button id="backToWhitelistBtn">Kembali ke Cek Whitelist</button>
        </div>
    </div>

    <!-- Login Form tetap -->
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
                    case 'toomanyattempts':
                        $error_message = 'Terlalu banyak percobaan login. Silakan tunggu 15 menit dan coba lagi.';
                        break;
                    case 'sessionexpired':
                        $error_message = 'Sesi Anda telah berakhir. Silakan login kembali.';
                        break;
                    case 'notloggedin':
                        $error_message = 'Anda harus login terlebih dahulu.';
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
                <a href="forgot_password.php">Lupa Password?</a>
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
    <div style="margin-top:20px;text-align:center;">
        <a href="forgot_password.php">Lupa Password?</a>
    </div>
    <script src="script.js"></script>
    <script>
// Autofill dan kunci prefix '+62 ' hanya saat user fokus
document.addEventListener('DOMContentLoaded', function() {
    var noWaInput = document.getElementById('no_wa');
    if (noWaInput) {
        // Set prefix hanya saat fokus pertama kali (jika kosong)
        noWaInput.addEventListener('focus', function() {
            if (this.value === '' || this.value === '+62') {
                this.value = '+62 ';
                // Set cursor position setelah prefix
                setTimeout(() => {
                    this.setSelectionRange(4, 4);
                }, 0);
            }
        });
        
        noWaInput.addEventListener('keydown', function(e) {
            // Prevent deleting prefix
            if ((this.selectionStart <= 4 && (e.key === 'Backspace' || e.key === 'Delete')) ||
                (this.selectionStart < 4 && e.key.length === 1)) {
                e.preventDefault();
                this.setSelectionRange(4, 4);
            }
        });
        
        noWaInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            // Bersihkan dan format ulang
            const cleaned = pastedText.replace(/[^0-9]/g, '');
            if (cleaned.length >= 8) {
                this.value = '+62 ' + cleaned.substring(0, 12);
            }
        });
    }
});
</script>
    <?php
    // --- BLOK BARU: Memaksa form registrasi terbuka jika ada error ---
    if ($registration_attempted && !empty($errors)) {
        echo "
        <script>
            document.getElementById('login').style.display = 'none';
            document.getElementById('signup').style.display = 'block';
            document.getElementById('check-whitelist').style.display = 'none';
        </script>
        ";
    }
    ?>
</body>
</html>