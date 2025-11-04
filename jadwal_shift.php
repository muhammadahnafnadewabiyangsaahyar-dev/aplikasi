<?php
session_start();
// Hanya user yang sudah login yang bisa akses
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=notloggedin');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Shift</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="main-title">Jadwal Shift</div>
    <div class="content-container" style="text-align:center; margin-top:40px;">
        <h2>Coming Soon</h2>
        <p>Fitur jadwal shift akan segera hadir.</p>
        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="Coming Soon" style="width:120px; opacity:0.5; margin-top:20px;">
    </div>
</body>
</html>
