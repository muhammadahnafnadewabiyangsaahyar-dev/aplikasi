<?php
// Strict error handling for JSON API
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering immediately
ob_start();

session_start();
require_once 'connect.php';

// Clear any previous output (including from connect.php)
ob_end_clean();

// Start fresh output buffer
ob_start();

// Set JSON header first
header('Content-Type: application/json; charset=utf-8');

// Disable any potential output from error handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Silently log errors instead of outputting them
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$shift_id = $_POST['shift_id'] ?? null;
$status = $_POST['status'] ?? null; // 'confirmed' or 'declined'
$catatan = $_POST['catatan'] ?? '';

// Validation
if (!$shift_id || !$status) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit();
}

if (!in_array($status, ['confirmed', 'declined'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Status tidak valid']);
    exit();
}

// Verify that this shift belongs to the current user
$sql_verify = "SELECT id FROM shift_assignments WHERE id = ? AND user_id = ?";
$stmt_verify = $pdo->prepare($sql_verify);
$stmt_verify->execute([$shift_id, $user_id]);

if ($stmt_verify->rowCount() === 0) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Shift tidak ditemukan atau bukan milik Anda']);
    exit();
}

// Update shift status
$sql_update = "UPDATE shift_assignments 
               SET status_konfirmasi = ?, 
                   waktu_konfirmasi = NOW(), 
                   catatan_pegawai = ?,
                   updated_at = NOW()
               WHERE id = ?";
$stmt_update = $pdo->prepare($sql_update);

// Clear buffer before final output
ob_end_clean();

if ($stmt_update->execute([$status, $catatan, $shift_id])) {
    $message = $status === 'confirmed' ? 'Shift berhasil dikonfirmasi' : 'Shift berhasil ditolak';
    echo json_encode(['status' => 'success', 'message' => $message]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal update status']);
}

// Ensure no trailing output
exit();
?>
```
