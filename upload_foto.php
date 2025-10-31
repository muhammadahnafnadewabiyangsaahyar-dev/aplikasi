<?php
session_start();
include 'connect.php'; // Ini memuat $pdo

$error_message = "";
$success_message = "";

// Cek jika user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=notloggedin");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; // 'admin' or 'user'

// Tentukan halaman redirect (profile.php atau profileadmin.php)
$redirect_page = ($user_role == 'admin') ? 'profileadmin.php' : 'profile.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_foto'])) {

    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        
        $file = $_FILES['foto_profil'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];

        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));

        // Whitelist ekstensi yang diizinkan
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($fileActualExt, $allowed)) {
            if ($fileError === 0) {
                // Batas ukuran file 5MB (sesuai kode asli Anda)
                if ($fileSize < 5000000) { 

                    // ========================================================
                    // --- BLOK PERBAIKAN: Gunakan PDO untuk update DB ---
                    // ========================================================
                    try {
                        // Ambil nama file lama untuk dihapus (opsional tapi bagus)
                        $stmt_old = $pdo->prepare("SELECT foto_profil FROM register WHERE id = ?");
                        $stmt_old->execute([$user_id]);
                        $old_foto = $stmt_old->fetchColumn();

                        // Update database dengan nama file baru
                        $sql_update_foto = "UPDATE register SET foto_profil = ? WHERE id = ?";
                        $stmt = $pdo->prepare($sql_update_foto);
                        
                        if ($stmt->execute([$nama_file_baru, $user_id])) {
                            $success_message = "Foto profil berhasil diupdate!";
                            
                            // Hapus file lama jika ada & bukan file default
                            if ($old_foto && $old_foto != 'default.png' && file_exists('uploads/' . $old_foto)) {
                                @unlink('uploads/' . $old_foto);
                            }
                            
                            // JS untuk merefresh gambar di halaman utama (parent)
                            echo '<script>parent.document.getElementById("profile-pic-preview").src = "' . $tujuan_upload . '?' . time() . '";</script>';
                            
                        } else {
                            $error_message = "Gagal menyimpan data ke database.";
                            @unlink($tujuan_upload); // Hapus file jika gagal update DB
                        }
                    } catch (PDOException $e) {
                        $error_message = "Database error: " . $e->getMessage();
                        @unlink($tujuan_upload); // Hapus file jika gagal update DB
                    }
                    // ========================================================

                } else {
                    $error_message = "Gagal memindahkan file yang diupload.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: transparent; font-size: 14px; }
        form { display: flex; align-items: center; }
        input[type="file"] { flex-grow: 1; font-size: 12px; }
        button { font-size: 12px; padding: 5px 8px; margin-left: 5px; }
        p { margin: 2px 0; font-size: 12px; }
    </style>
</head>
<body>
    <form action="upload_foto.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="foto_profil" required>
        <button type="submit" class="btn-small">Upload</button>
    </form>
    <?php if ($error_message): ?>
        <p style="color:red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <p style="color:green;"><?php echo $success_message; ?></p>
    <?php endif; ?>
</body>
</html>