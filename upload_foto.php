<?php
session_start();
include 'connect.php';

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
                    // --- BLOK BARU: Ambil nama foto lama ---
                    // ========================================================
                    $old_foto_profil = null;
                    $sql_get_old = "SELECT foto_profil FROM register WHERE id = ?";
                    $stmt_get = mysqli_prepare($conn, $sql_get_old);
                    
                    if ($stmt_get) {
                        mysqli_stmt_bind_param($stmt_get, "i", $user_id);
                        mysqli_stmt_execute($stmt_get);
                        $result_get = mysqli_stmt_get_result($stmt_get);
                        $data_user = mysqli_fetch_assoc($result_get);
                        if ($data_user && !empty($data_user['foto_profil'])) {
                            $old_foto_profil = $data_user['foto_profil'];
                        }
                        mysqli_stmt_close($stmt_get);
                    }
                    // --- AKHIR BLOK BARU ---
                    
                    // Buat nama file baru yang unik (sesuai kode asli Anda)
                    $fileNameNew = $user_id . "_" . time() . "." . $fileActualExt;
                    $fileDestination = 'uploads/' . $fileNameNew;

                    // Pindahkan file baru ke folder uploads
                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        
                        // Update database dengan nama file baru
                        $sql_update = "UPDATE register SET foto_profil = ? WHERE id = ?";
                        $stmt_update = mysqli_prepare($conn, $sql_update);
                        
                        if ($stmt_update) {
                            mysqli_stmt_bind_param($stmt_update, "si", $fileNameNew, $user_id);
                            mysqli_stmt_execute($stmt_update);
                            mysqli_stmt_close($stmt_update);

                            // ========================================================
                            // --- BLOK BARU: Hapus foto lama (JIKA ADA) ---
                            // ========================================================
                            // Cek jika nama file lama ada DAN file-nya benar-benar ada di server
                            if ($old_foto_profil !== null && file_exists('uploads/' . $old_foto_profil)) {
                                // Coba hapus file lama, @ untuk menekan error jika gagal (opsional)
                                @unlink('uploads/' . $old_foto_profil);
                            }
                            // --- AKHIR BLOK BARU ---
                            
                            header("Location: " . $redirect_page . "?upload=success");
                            exit();
                            
                        } else {
                            // Gagal prepare statement update
                            @unlink($fileDestination); // Hapus file baru yang sudah terlanjur di-upload
                            header("Location: " . $redirect_page . "?error=dberror");
                            exit();
                        }
                        
                    } else {
                        header("Location: " . $redirect_page . "?error=movefileerror");
                        exit();
                    }

                } else {
                    header("Location: " . $redirect_page . "?error=filesize");
                    exit();
                }
            } else {
                header("Location: ". $redirect_page . "?error=fileerror");
                exit();
            }
        } else {
            header("Location: " . $redirect_page . "?error=filetype");
            exit();
        }
    } else {
        header("Location: " . $redirect_page . "?error=nofile");
        exit();
    }
} else {
    header("Location: " . $redirect_page . "?error=invalidrequest");
    exit();
}

mysqli_close($conn);
?>