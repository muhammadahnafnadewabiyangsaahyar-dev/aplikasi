<?php
session_start();
require_once 'connect.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$admin_id = $_SESSION['user_id'];

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $action = $input['action'] ?? $action;
    }
}

try {
    switch ($action) {
        case 'get_cabang':
            getCabang();
            break;
            
        case 'get_pegawai':
            getPegawai();
            break;
            
        case 'get_assignments':
            getAssignments();
            break;
            
        case 'create':
            createAssignment();
            break;
            
        case 'update':
            updateAssignment();
            break;
            
        case 'delete':
            deleteAssignment();
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Get all cabang
function getCabang() {
    global $pdo;
    
    $sql = "SELECT id, nama_cabang, nama_shift, jam_masuk, jam_keluar 
            FROM cabang 
            ORDER BY nama_cabang";
    
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $data]);
}

// Get pegawai (rows for calendar)
function getPegawai() {
    global $pdo;
    
    $cabang_id = $_GET['cabang_id'] ?? null;
    
    $sql = "SELECT id, nama_lengkap as name, posisi, outlet, id_cabang 
            FROM register 
            WHERE role = 'user'";
    
    if ($cabang_id) {
        $sql .= " AND id_cabang = :cabang_id";
    }
    
    $sql .= " ORDER BY nama_lengkap";
    
    $stmt = $pdo->prepare($sql);
    
    if ($cabang_id) {
        $stmt->execute(['cabang_id' => $cabang_id]);
    } else {
        $stmt->execute();
    }
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $data]);
}

// Get shift assignments
function getAssignments() {
    global $pdo;
    
    $month = $_GET['month'] ?? date('Y-m');
    $cabang_id = $_GET['cabang_id'] ?? null;
    
    // Parse month to get start and end dates
    $startDate = $month . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));
    
    $sql = "SELECT 
                sa.id,
                sa.user_id,
                sa.cabang_id,
                sa.tanggal_shift,
                DATE_FORMAT(CONCAT(sa.tanggal_shift, ' ', c.jam_masuk), '%Y-%m-%d %H:%i:%s') as start,
                DATE_FORMAT(CONCAT(sa.tanggal_shift, ' ', c.jam_keluar), '%Y-%m-%d %H:%i:%s') as end,
                c.nama_cabang,
                c.nama_shift,
                c.jam_masuk,
                c.jam_keluar,
                r.nama_lengkap
            FROM shift_assignments sa
            JOIN cabang c ON sa.cabang_id = c.id
            JOIN register r ON sa.user_id = r.id
            WHERE sa.tanggal_shift BETWEEN :start_date AND :end_date";
    
    if ($cabang_id) {
        $sql .= " AND sa.cabang_id = :cabang_id";
    }
    
    $sql .= " ORDER BY sa.tanggal_shift, sa.user_id";
    
    $stmt = $pdo->prepare($sql);
    
    $params = [
        'start_date' => $startDate,
        'end_date' => $endDate
    ];
    
    if ($cabang_id) {
        $params['cabang_id'] = $cabang_id;
    }
    
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $data]);
}

// Create new assignment
function createAssignment() {
    global $pdo, $admin_id;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'] ?? null;
    $cabang_id = $input['cabang_id'] ?? null;
    $tanggal_shift = $input['tanggal_shift'] ?? null;
    
    if (!$user_id || !$cabang_id || !$tanggal_shift) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        return;
    }
    
    // Check if assignment already exists
    $sql_check = "SELECT id FROM shift_assignments 
                  WHERE user_id = ? AND tanggal_shift = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$user_id, $tanggal_shift]);
    
    if ($stmt_check->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Pegawai sudah memiliki shift pada tanggal ini']);
        return;
    }
    
    // Insert new assignment
    $sql = "INSERT INTO shift_assignments 
            (user_id, cabang_id, tanggal_shift, created_by, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $cabang_id, $tanggal_shift, $admin_id]);
    
    $id = $pdo->lastInsertId();
    
    // Get cabang info with shift time
    $sql_cabang = "SELECT nama_cabang, nama_shift, jam_masuk, jam_keluar FROM cabang WHERE id = ?";
    $stmt_cabang = $pdo->prepare($sql_cabang);
    $stmt_cabang->execute([$cabang_id]);
    $cabang = $stmt_cabang->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Shift berhasil di-assign',
        'data' => [
            'id' => $id,
            'nama_cabang' => $cabang['nama_cabang'],
            'nama_shift' => $cabang['nama_shift'],
            'jam_masuk' => $cabang['jam_masuk'],
            'jam_keluar' => $cabang['jam_keluar']
        ]
    ]);
}

// Update assignment
function updateAssignment() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? null;
    $user_id = $input['user_id'] ?? null;
    $tanggal_shift = $input['tanggal_shift'] ?? null;
    
    if (!$id || !$user_id || !$tanggal_shift) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        return;
    }
    
    // Check if new user already has assignment on that date
    $sql_check = "SELECT id FROM shift_assignments 
                  WHERE user_id = ? AND tanggal_shift = ? AND id != ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$user_id, $tanggal_shift, $id]);
    
    if ($stmt_check->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Pegawai sudah memiliki shift pada tanggal ini']);
        return;
    }
    
    // Update assignment
    $sql = "UPDATE shift_assignments 
            SET user_id = ?, tanggal_shift = ?, status_konfirmasi = 'pending', updated_at = NOW()
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $tanggal_shift, $id]);
    
    echo json_encode(['status' => 'success', 'message' => 'Shift berhasil diupdate']);
}

// Delete assignment
function deleteAssignment() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
        return;
    }
    
    $sql = "DELETE FROM shift_assignments WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    
    echo json_encode(['status' => 'success', 'message' => 'Shift berhasil dihapus']);
}
?>
