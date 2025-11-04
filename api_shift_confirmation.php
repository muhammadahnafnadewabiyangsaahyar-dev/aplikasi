<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$shift_id = $_POST['shift_id'] ?? null;
$status = $_POST['status'] ?? null; // 'confirmed' or 'declined'
$catatan = $_POST['catatan'] ?? '';

// Validation
if (!$shift_id || !$status) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit();
}

if (!in_array($status, ['confirmed', 'declined'])) {
    echo json_encode(['status' => 'error', 'message' => 'Status tidak valid']);
    exit();
}

// Verify that this shift belongs to the current user
$sql_verify = "SELECT id FROM shift_assignments WHERE id = ? AND user_id = ?";
$stmt_verify = $pdo->prepare($sql_verify);
$stmt_verify->execute([$shift_id, $user_id]);

if ($stmt_verify->rowCount() === 0) {
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

if ($stmt_update->execute([$status, $catatan, $shift_id])) {
    $message = $status === 'confirmed' ? 'Shift berhasil dikonfirmasi' : 'Shift berhasil ditolak';
    echo json_encode(['status' => 'success', 'message' => $message]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal update status']);
}
?>
```
