<?php
ob_start();
session_start();
include 'connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log request info
error_log("=== PAGE LOAD: posisi_jabatan.php ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
error_log("GET params: " . print_r($_GET, true));
error_log("Session ID: " . session_id());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("User Role: " . ($_SESSION['role'] ?? 'NOT SET'));

// Hanya admin/superadmin/HR/owner yang boleh akses
$allowed_roles = ['superadmin', 'admin', 'HR', 'owner'];
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role'] ?? ''), array_map('strtolower', $allowed_roles))) {
    error_log("❌ ACCESS DENIED - Unauthorized access attempt");
    header('Location: index.php?error=unauthorized');
    exit;
}
error_log("✅ ACCESS GRANTED");

// Generate CSRF token jika belum ada
if (!isset($_SESSION['csrf_token_posisi'])) {
    $_SESSION['csrf_token_posisi'] = bin2hex(random_bytes(32));
}

// Ambil data posisi untuk edit DULU sebelum proses POST
$edit_posisi = null;
if (isset($_GET['edit']) && filter_var($_GET['edit'], FILTER_VALIDATE_INT)) {
    $edit_id = (int)$_GET['edit'];
    error_log("=== FETCHING EDIT DATA FOR POSISI ID: $edit_id ===");
    $stmt = $pdo->prepare('SELECT * FROM posisi_jabatan WHERE id=?');
    $stmt->execute([$edit_id]);
    $edit_posisi = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Edit Data: " . print_r($edit_posisi, true));
    error_log("=== END FETCHING EDIT DATA ===");
}

// Tambah/Edit posisi
$error = '';
$success = '';

// Tampilkan pesan sukses dari redirect
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'add':
            $success = 'Posisi berhasil ditambahkan!';
            break;
        case 'update':
            $success = 'Posisi dan role berhasil diupdate!';
            break;
        case 'delete':
            $nama = $_GET['name'] ?? 'tersebut';
            $success = "Posisi '$nama' berhasil dihapus! Semua pegawai dengan posisi ini telah dipindahkan ke 'Tidak Ada Posisi'.";
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DEBUG: Log POST data
    error_log("=== POSISI JABATAN POST START ===");
    error_log("POST Data: " . print_r($_POST, true));
    error_log("Session Token: " . ($_SESSION['csrf_token_posisi'] ?? 'NOT SET'));
    error_log("POST Token: " . ($_POST['csrf_token'] ?? 'NOT SET'));
    
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token_posisi']) {
        error_log("❌ CSRF TOKEN VALIDATION FAILED!");
        $error = 'Invalid request. Please try again.';
    } else {
        error_log("✅ CSRF TOKEN VALID");
        
        // Token persistent - tidak regenerate
        $nama_posisi = trim($_POST['nama_posisi'] ?? '');
        $role_posisi = trim($_POST['role_posisi'] ?? 'user');
        $id_posisi = isset($_POST['id_posisi']) ? intval($_POST['id_posisi']) : null;
        
        error_log("Nama Posisi: $nama_posisi");
        error_log("Role Posisi: $role_posisi");
        error_log("ID Posisi: " . ($id_posisi ?? 'NULL (INSERT MODE)'));
        
        // Ambil data posisi lama untuk update massal
        $old_posisi_data = null;
        if ($id_posisi) {
            $stmt_old = $pdo->prepare('SELECT * FROM posisi_jabatan WHERE id=?');
            $stmt_old->execute([$id_posisi]);
            $old_posisi_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
            error_log("Old Posisi Data: " . print_r($old_posisi_data, true));
        }
        
        // Validasi input
        if ($nama_posisi === '') {
            error_log("❌ Validasi gagal: Nama posisi kosong");
            $error = 'Nama posisi tidak boleh kosong!';
        } elseif ($role_posisi !== 'admin' && $role_posisi !== 'user') {
            error_log("❌ Validasi gagal: Role invalid ($role_posisi)");
            $error = 'Role posisi harus admin atau user!';
        } else {
            error_log("✅ Validasi input passed");
            
            // Cek duplikat
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM posisi_jabatan WHERE nama_posisi = ?' . ($id_posisi ? ' AND id != ?' : ''));
            $params = $id_posisi ? [$nama_posisi, $id_posisi] : [$nama_posisi];
            $stmt->execute($params);
            $duplicate_count = $stmt->fetchColumn();
            error_log("Duplicate check count: $duplicate_count");
            
            if ($duplicate_count > 0) {
                error_log("❌ Nama posisi sudah ada (duplikat)");
                $error = 'Nama posisi sudah ada!';
            } else {
                error_log("✅ No duplicate, proceeding...");
                
                // Proses insert/update
                if ($id_posisi && $old_posisi_data) {
                    error_log("🔄 MODE: UPDATE");
                    
                    // UPDATE posisi
                    error_log("Preparing UPDATE query...");
                    error_log("Query: UPDATE posisi_jabatan SET nama_posisi=?, role_posisi=? WHERE id=?");
                    error_log("Params: ['$nama_posisi', '$role_posisi', $id_posisi]");
                    
                    $stmt = $pdo->prepare('UPDATE posisi_jabatan SET nama_posisi=?, role_posisi=? WHERE id=?');
                    if ($stmt->execute([$nama_posisi, $role_posisi, $id_posisi])) {
                        $affected_rows = $stmt->rowCount();
                        error_log("✅ UPDATE posisi_jabatan SUCCESS (affected rows: $affected_rows)");
                        
                        // Verifikasi update dengan SELECT
                        $stmt_verify = $pdo->prepare('SELECT * FROM posisi_jabatan WHERE id=?');
                        $stmt_verify->execute([$id_posisi]);
                        $updated_data = $stmt_verify->fetch(PDO::FETCH_ASSOC);
                        error_log("📊 VERIFY UPDATE - Data after update: " . print_r($updated_data, true));
                        
                        // Update semua pegawai_whitelist dan register yang posisinya sama
                        error_log("Updating pegawai_whitelist...");
                        $stmt2 = $pdo->prepare('UPDATE pegawai_whitelist SET posisi=?, role=? WHERE posisi=?');
                        $stmt2->execute([$nama_posisi, $role_posisi, $old_posisi_data['nama_posisi']]);
                        $affected_whitelist = $stmt2->rowCount();
                        error_log("✅ UPDATE pegawai_whitelist: $affected_whitelist rows affected");
                        
                        error_log("Updating register...");
                        $stmt3 = $pdo->prepare('UPDATE register SET posisi=?, role=? WHERE posisi=?');
                        $stmt3->execute([$nama_posisi, $role_posisi, $old_posisi_data['nama_posisi']]);
                        $affected_register = $stmt3->rowCount();
                        error_log("✅ UPDATE register: $affected_register rows affected");
                        
                        error_log("🔄 REDIRECTING to posisi_jabatan.php?success=update");
                        error_log("=== POSISI JABATAN POST END ===");
                        
                        // Redirect setelah update berhasil (PRG Pattern)
                        header('Location: posisi_jabatan.php?success=update');
                        exit;
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        error_log("❌ UPDATE posisi_jabatan FAILED: " . print_r($errorInfo, true));
                        $error = 'Gagal update: ' . $errorInfo[2];
                    }
                } else {
                    error_log("➕ MODE: INSERT");
                    error_log("Preparing INSERT query...");
                    error_log("Query: INSERT INTO posisi_jabatan (nama_posisi, role_posisi) VALUES (?, ?)");
                    error_log("Params: ['$nama_posisi', '$role_posisi']");
                    
                    // INSERT posisi baru
                    $stmt = $pdo->prepare('INSERT INTO posisi_jabatan (nama_posisi, role_posisi) VALUES (?, ?)');
                    if ($stmt->execute([$nama_posisi, $role_posisi])) {
                        $new_id = $pdo->lastInsertId();
                        error_log("✅ INSERT posisi_jabatan SUCCESS (ID: $new_id)");
                        
                        // Verifikasi insert dengan SELECT
                        $stmt_verify = $pdo->prepare('SELECT * FROM posisi_jabatan WHERE id=?');
                        $stmt_verify->execute([$new_id]);
                        $inserted_data = $stmt_verify->fetch(PDO::FETCH_ASSOC);
                        error_log("📊 VERIFY INSERT - Data after insert: " . print_r($inserted_data, true));
                        
                        error_log("🔄 REDIRECTING to posisi_jabatan.php?success=add");
                        error_log("=== POSISI JABATAN POST END ===");
                        
                        // Redirect setelah insert berhasil (PRG Pattern)
                        header('Location: posisi_jabatan.php?success=add');
                        exit;
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        error_log("❌ INSERT posisi_jabatan FAILED: " . print_r($errorInfo, true));
                        $error = 'Gagal tambah: ' . $errorInfo[2];
                    }
                }
            }
        }
    } // End of CSRF validation
    
    error_log("=== POSISI JABATAN POST END (WITH ERROR) ===");
}

// Hapus posisi
if (isset($_GET['delete']) && filter_var($_GET['delete'], FILTER_VALIDATE_INT)) {
    error_log("=== POSISI JABATAN DELETE START ===");
    $id_hapus = (int)$_GET['delete'];
    error_log("Delete ID: $id_hapus");
    
    // Ambil nama posisi yang akan dihapus
    $stmt_check = $pdo->prepare('SELECT nama_posisi FROM posisi_jabatan WHERE id=?');
    $stmt_check->execute([$id_hapus]);
    $posisi_to_delete = $stmt_check->fetchColumn();
    error_log("Posisi to delete: " . ($posisi_to_delete ?? 'NOT FOUND'));
    
    if ($posisi_to_delete) {
        // Cek apakah ini posisi default
        if ($posisi_to_delete === 'Tidak Ada Posisi') {
            error_log("❌ Cannot delete default position");
            $error = 'Tidak bisa hapus posisi default "Tidak Ada Posisi"!';
        } else {
            error_log("✅ Proceeding with delete...");
            
            // Pastikan posisi default ada, jika belum ada buat dulu
            $stmt_default = $pdo->prepare('SELECT COUNT(*) FROM posisi_jabatan WHERE nama_posisi = ?');
            $stmt_default->execute(['Tidak Ada Posisi']);
            $default_exists = $stmt_default->fetchColumn();
            error_log("Default position exists: " . ($default_exists ? 'YES' : 'NO'));
            
            if ($default_exists == 0) {
                error_log("Creating default position...");
                $pdo->prepare('INSERT INTO posisi_jabatan (nama_posisi, role_posisi) VALUES (?, ?)')->execute(['Tidak Ada Posisi', 'user']);
                error_log("✅ Default position created");
            }
            
            // Update semua pegawai yang menggunakan posisi ini ke posisi default
            $stmt_update_whitelist = $pdo->prepare('UPDATE pegawai_whitelist SET posisi=?, role=? WHERE posisi=?');
            $stmt_update_whitelist->execute(['Tidak Ada Posisi', 'user', $posisi_to_delete]);
            $affected_whitelist = $stmt_update_whitelist->rowCount();
            error_log("✅ Moved $affected_whitelist employees in whitelist to default position");
            
            $stmt_update_register = $pdo->prepare('UPDATE register SET posisi=?, role=? WHERE posisi=?');
            $stmt_update_register->execute(['Tidak Ada Posisi', 'user', $posisi_to_delete]);
            $affected_register = $stmt_update_register->rowCount();
            error_log("✅ Moved $affected_register employees in register to default position");
            
            // Hapus posisi
            error_log("Preparing DELETE query...");
            error_log("Query: DELETE FROM posisi_jabatan WHERE id=?");
            error_log("Params: [$id_hapus]");
            
            $stmt = $pdo->prepare('DELETE FROM posisi_jabatan WHERE id=?');
            if (!$stmt->execute([$id_hapus])) {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ DELETE FAILED: " . print_r($errorInfo, true));
                $error = 'Gagal hapus: ' . $errorInfo[2];
            } else {
                $affected_rows = $stmt->rowCount();
                error_log("✅ DELETE SUCCESS (affected rows: $affected_rows)");
                
                // Verifikasi delete dengan SELECT
                $stmt_verify = $pdo->prepare('SELECT * FROM posisi_jabatan WHERE id=?');
                $stmt_verify->execute([$id_hapus]);
                $deleted_check = $stmt_verify->fetch(PDO::FETCH_ASSOC);
                error_log("📊 VERIFY DELETE - Data after delete (should be empty): " . print_r($deleted_check, true));
                
                error_log("🔄 REDIRECTING to posisi_jabatan.php?success=delete&name=$posisi_to_delete");
                error_log("=== POSISI JABATAN DELETE END ===");
                
                // Redirect setelah hapus berhasil (PRG Pattern)
                header('Location: posisi_jabatan.php?success=delete&name=' . urlencode($posisi_to_delete));
                exit;
            }
        }
    } else {
        error_log("❌ Position not found in database");
        $error = 'Posisi tidak ditemukan!';
    }
    error_log("=== POSISI JABATAN DELETE END (WITH ERROR) ===");
}

// Ambil semua posisi
error_log("=== FETCHING POSISI DATA FROM DATABASE ===");
$query_posisi = 'SELECT * FROM posisi_jabatan ORDER BY nama_posisi ASC';
error_log("Query: $query_posisi");
$daftar_posisi = $pdo->query($query_posisi)->fetchAll(PDO::FETCH_ASSOC);
error_log("Total rows fetched: " . count($daftar_posisi));
error_log("Data: " . print_r($daftar_posisi, true));
error_log("=== END FETCHING POSISI DATA ===");

// Log data yang akan ditampilkan di form edit
if ($edit_posisi) {
    error_log("=== FORM EDIT VALUES ===");
    error_log("ID Posisi: " . ($edit_posisi['id'] ?? 'NULL'));
    error_log("Nama Posisi: " . ($edit_posisi['nama_posisi'] ?? 'NULL'));
    error_log("Role Posisi: " . ($edit_posisi['role_posisi'] ?? 'NULL'));
    error_log("=== END FORM EDIT VALUES ===");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Posisi Jabatan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-title">Manajemen Posisi Jabatan</div>
<div class="container">
    <a href="whitelist.php">&larr; Kembali ke Whitelist</a>
    <h2><?= $edit_posisi ? 'Edit Posisi' : 'Tambah Posisi Baru' ?></h2>
    <?php if ($error): ?><div style="color:red;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div style="color:green;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token_posisi'] ?>">
        <?php if ($edit_posisi): ?>
            <input type="hidden" name="id_posisi" value="<?= $edit_posisi['id'] ?>">
        <?php endif; ?>
        <input type="text" name="nama_posisi" value="<?= htmlspecialchars($edit_posisi['nama_posisi'] ?? '') ?>" placeholder="Nama posisi" required style="width:200px;">
        <select name="role_posisi" required style="width:120px;">
            <option value="user" <?= (isset($edit_posisi['role_posisi']) && $edit_posisi['role_posisi']==='user') ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= (isset($edit_posisi['role_posisi']) && $edit_posisi['role_posisi']==='admin') ? 'selected' : '' ?>>Admin</option>
        </select>
        <button type="submit"><?= $edit_posisi ? 'Update' : 'Tambah' ?></button>
        <?php if ($edit_posisi): ?>
            <a href="posisi_jabatan.php">Batal</a>
        <?php endif; ?>
    </form>
    <h3>Daftar Posisi</h3>
    <?php
    error_log("=== RENDERING TABLE ===");
    error_log("Number of rows to display: " . count($daftar_posisi));
    ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Nama Posisi</th><th>Role</th><th>Aksi</th></tr>
        <?php foreach ($daftar_posisi as $index => $p): 
            error_log("Row $index - ID: {$p['id']}, Nama: {$p['nama_posisi']}, Role: " . ($p['role_posisi'] ?? 'user'));
        ?>
            <tr>
                <td><?= htmlspecialchars($p['nama_posisi']) ?></td>
                <td><?= htmlspecialchars($p['role_posisi'] ?? 'user') ?></td>
                <td>
                    <a href="posisi_jabatan.php?edit=<?= $p['id'] ?>">Edit</a> |
                    <a href="posisi_jabatan.php?delete=<?= $p['id'] ?>" onclick="return confirm('Hapus posisi ini?')">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php error_log("=== END RENDERING TABLE ==="); ?>
</div>
<script>
// Prevent form resubmission on refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Prevent double submit
document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        var submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.disabled = true;
            var originalText = submitBtn.textContent;
            submitBtn.textContent = 'Memproses...';
            // Re-enable after 3 seconds as fallback
            setTimeout(function() {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }, 3000);
        }
    });
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>
