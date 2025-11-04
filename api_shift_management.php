<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$admin_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? 'assign';

// ASSIGN SHIFT
if ($action === 'assign' || !isset($_POST['action'])) {
    $pegawai_id = $_POST['pegawai_id'] ?? null;
    $cabang_id = $_POST['cabang_id'] ?? null;
    $tanggal_shift = $_POST['tanggal_shift'] ?? null;
    
    // Validation
    if (!$pegawai_id || !$cabang_id || !$tanggal_shift) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit();
    }
    
    // Check if already assigned
    $sql_check = "SELECT id FROM shift_assignments WHERE user_id = ? AND tanggal_shift = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$pegawai_id, $tanggal_shift]);
    
    if ($stmt_check->rowCount() > 0) {
        // Update existing assignment
        $sql_update = "UPDATE shift_assignments 
                       SET cabang_id = ?, status_konfirmasi = 'pending', updated_at = NOW()
                       WHERE user_id = ? AND tanggal_shift = ?";
        $stmt_update = $pdo->prepare($sql_update);
        
        if ($stmt_update->execute([$cabang_id, $pegawai_id, $tanggal_shift])) {
            echo json_encode(['status' => 'success', 'message' => 'Shift berhasil diupdate']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal update shift']);
        }
    } else {
        // Insert new assignment
        $sql_insert = "INSERT INTO shift_assignments 
                       (user_id, cabang_id, tanggal_shift, created_by, created_at) 
                       VALUES (?, ?, ?, ?, NOW())";
        $stmt_insert = $pdo->prepare($sql_insert);
        
        if ($stmt_insert->execute([$pegawai_id, $cabang_id, $tanggal_shift, $admin_id])) {
            // TODO: Send notification to employee
            echo json_encode(['status' => 'success', 'message' => 'Shift berhasil di-assign']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal assign shift']);
        }
    }
}

// DELETE ASSIGNMENT
elseif ($action === 'delete') {
    $assignment_id = $_POST['assignment_id'] ?? null;
    
    if (!$assignment_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID assignment tidak valid']);
        exit();
    }
    
    $sql_delete = "DELETE FROM shift_assignments WHERE id = ?";
    $stmt_delete = $pdo->prepare($sql_delete);
    
    if ($stmt_delete->execute([$assignment_id])) {
        echo json_encode(['status' => 'success', 'message' => 'Assignment berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus']);
    }
}

// BULK ASSIGN (for date range)
elseif ($action === 'bulk_assign') {
    $pegawai_id = $_POST['pegawai_id'] ?? null;
    $cabang_id = $_POST['cabang_id'] ?? null;
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    
    // Validation
    if (!$pegawai_id || !$cabang_id || !$start_date || !$end_date) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit();
    }
    
    // Generate dates between start and end
    $current_date = new DateTime($start_date);
    $end = new DateTime($end_date);
    $success_count = 0;
    $error_count = 0;
    
    while ($current_date <= $end) {
        $date_str = $current_date->format('Y-m-d');
        
        // Check if already assigned
        $sql_check = "SELECT id FROM shift_assignments WHERE user_id = ? AND tanggal_shift = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$pegawai_id, $date_str]);
        
        if ($stmt_check->rowCount() > 0) {
            // Update
            $sql_update = "UPDATE shift_assignments 
                           SET cabang_id = ?, status_konfirmasi = 'pending', updated_at = NOW()
                           WHERE user_id = ? AND tanggal_shift = ?";
            $stmt_update = $pdo->prepare($sql_update);
            if ($stmt_update->execute([$cabang_id, $pegawai_id, $date_str])) $success_count++;
            else $error_count++;
        } else {
            // Insert
            $sql_insert = "INSERT INTO shift_assignments 
                           (user_id, cabang_id, tanggal_shift, created_by, created_at) 
                           VALUES (?, ?, ?, ?, NOW())";
            $stmt_insert = $pdo->prepare($sql_insert);
            if ($stmt_insert->execute([$pegawai_id, $cabang_id, $date_str, $admin_id])) $success_count++;
            else $error_count++;
        }
        
        $current_date->modify('+1 day');
    }
    
    echo json_encode([
        'status' => 'success', 
        'message' => "Bulk assign selesai: $success_count berhasil, $error_count gagal"
    ]);
}

// GET ASSIGNMENTS (for AJAX)
elseif ($action === 'get_assignments') {
    $month = $_GET['month'] ?? date('Y-m');
    
    $sql = "SELECT sa.*, r.nama_lengkap, c.nama_cabang, c.nama_shift
            FROM shift_assignments sa
            JOIN register r ON sa.user_id = r.id
            JOIN cabang c ON sa.cabang_id = c.id
            WHERE DATE_FORMAT(sa.tanggal_shift, '%Y-%m') = ?
            ORDER BY sa.tanggal_shift DESC, r.nama_lengkap";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$month]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $assignments]);
}

else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
```
