<?php
session_start();
require_once 'connect_mysqli.php';
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - Please login first']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_cabang':
            getCabang($conn);
            break;
            
        case 'get_users':
            $cabang_id = $_GET['cabang_id'] ?? null;
            getUsers($conn, $cabang_id);
            break;
            
        case 'get_shifts':
            $cabang_id = $_GET['cabang_id'] ?? null;
            $month = $_GET['month'] ?? null;
            $year = $_GET['year'] ?? null;
            getShifts($conn, $cabang_id, $month, $year);
            break;
            
        case 'save_shift':
            saveShift($conn);
            break;
            
        case 'delete_shift':
            deleteShift($conn);
            break;
            
        case 'get_summary':
            $cabang_id = $_GET['cabang_id'] ?? null;
            $month = $_GET['month'] ?? null;
            $year = $_GET['year'] ?? null;
            getSummary($conn, $cabang_id, $month, $year);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getCabang($conn) {
    $sql = "SELECT id, nama_cabang FROM cabang ORDER BY nama_cabang";
    $result = $conn->query($sql);
    
    $cabang = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cabang[] = [
                'id' => $row['id'],
                'nama' => $row['nama_cabang']
            ];
        }
    }
    
    echo json_encode(['cabang' => $cabang]);
}

function getUsers($conn, $cabang_id) {
    if (!$cabang_id) {
        echo json_encode(['users' => []]);
        return;
    }
    
    $sql = "SELECT u.id, u.full_name, u.email, u.role 
            FROM users u 
            WHERE u.cabang_id = ? AND u.role IN ('karyawan', 'admin')
            ORDER BY u.full_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cabang_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'id' => $row['id'],
                'name' => $row['full_name'],
                'email' => $row['email'],
                'role' => $row['role']
            ];
        }
    }
    
    echo json_encode(['users' => $users]);
}

function getShifts($conn, $cabang_id, $month, $year) {
    if (!$cabang_id || !$month || !$year) {
        echo json_encode(['shifts' => []]);
        return;
    }
    
    // Get shift assignments for the month
    $sql = "SELECT sa.id, sa.user_id, sa.tanggal, sa.shift_masuk, sa.shift_keluar,
                   u.full_name as user_name,
                   c.shift_pagi_masuk, c.shift_pagi_keluar,
                   c.shift_siang_masuk, c.shift_siang_keluar,
                   c.shift_malam_masuk, c.shift_malam_keluar
            FROM shift_assignments sa
            JOIN users u ON sa.user_id = u.id
            JOIN cabang c ON u.cabang_id = c.id
            WHERE u.cabang_id = ? 
            AND YEAR(sa.tanggal) = ? 
            AND MONTH(sa.tanggal) = ?
            ORDER BY sa.tanggal, u.full_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $cabang_id, $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $shifts = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Determine shift type based on time
            $shift_type = 'Unknown';
            $shift_label = '';
            
            if ($row['shift_masuk'] == $row['shift_pagi_masuk'] && $row['shift_keluar'] == $row['shift_pagi_keluar']) {
                $shift_type = 'pagi';
                $shift_label = 'Pagi (' . $row['shift_masuk'] . ' - ' . $row['shift_keluar'] . ')';
            } elseif ($row['shift_masuk'] == $row['shift_siang_masuk'] && $row['shift_keluar'] == $row['shift_siang_keluar']) {
                $shift_type = 'siang';
                $shift_label = 'Siang (' . $row['shift_masuk'] . ' - ' . $row['shift_keluar'] . ')';
            } elseif ($row['shift_masuk'] == $row['shift_malam_masuk'] && $row['shift_keluar'] == $row['shift_malam_keluar']) {
                $shift_type = 'malam';
                $shift_label = 'Malam (' . $row['shift_masuk'] . ' - ' . $row['shift_keluar'] . ')';
            } else {
                $shift_label = 'Custom (' . $row['shift_masuk'] . ' - ' . $row['shift_keluar'] . ')';
            }
            
            $shifts[] = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'user_name' => $row['user_name'],
                'date' => $row['tanggal'],
                'shift_type' => $shift_type,
                'shift_label' => $shift_label,
                'shift_masuk' => $row['shift_masuk'],
                'shift_keluar' => $row['shift_keluar']
            ];
        }
    }
    
    echo json_encode(['shifts' => $shifts]);
}

function saveShift($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id']) || !isset($data['date']) || !isset($data['shift_type'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $user_id = $data['user_id'];
    $date = $data['date'];
    $shift_type = $data['shift_type'];
    
    // Get cabang shift times
    $sql = "SELECT c.shift_pagi_masuk, c.shift_pagi_keluar,
                   c.shift_siang_masuk, c.shift_siang_keluar,
                   c.shift_malam_masuk, c.shift_malam_keluar
            FROM users u 
            JOIN cabang c ON u.cabang_id = c.id 
            WHERE u.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cabang_shifts = $result->fetch_assoc();
    
    if (!$cabang_shifts) {
        http_response_code(404);
        echo json_encode(['error' => 'User or cabang not found']);
        return;
    }
    
    // Set shift times based on type
    switch ($shift_type) {
        case 'pagi':
            $shift_masuk = $cabang_shifts['shift_pagi_masuk'];
            $shift_keluar = $cabang_shifts['shift_pagi_keluar'];
            break;
        case 'siang':
            $shift_masuk = $cabang_shifts['shift_siang_masuk'];
            $shift_keluar = $cabang_shifts['shift_siang_keluar'];
            break;
        case 'malam':
            $shift_masuk = $cabang_shifts['shift_malam_masuk'];
            $shift_keluar = $cabang_shifts['shift_malam_keluar'];
            break;
        case 'off':
            // Delete existing assignment for off day
            $delete_sql = "DELETE FROM shift_assignments WHERE user_id = ? AND tanggal = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("is", $user_id, $date);
            $delete_stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Shift set to OFF (deleted)']);
            return;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid shift type']);
            return;
    }
    
    // Insert or update shift assignment
    $upsert_sql = "INSERT INTO shift_assignments (user_id, tanggal, shift_masuk, shift_keluar) 
                   VALUES (?, ?, ?, ?)
                   ON DUPLICATE KEY UPDATE 
                   shift_masuk = VALUES(shift_masuk), 
                   shift_keluar = VALUES(shift_keluar)";
    
    $stmt = $conn->prepare($upsert_sql);
    $stmt->bind_param("isss", $user_id, $date, $shift_masuk, $shift_keluar);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Shift saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save shift: ' . $conn->error]);
    }
}

function deleteShift($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['user_id']) || !isset($data['date'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $user_id = $data['user_id'];
    $date = $data['date'];
    
    $sql = "DELETE FROM shift_assignments WHERE user_id = ? AND tanggal = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $date);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Shift deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete shift: ' . $conn->error]);
    }
}

function getSummary($conn, $cabang_id, $month, $year) {
    if (!$cabang_id || !$month || !$year) {
        echo json_encode(['summary' => []]);
        return;
    }
    
    // Get summary data for each user
    $sql = "SELECT u.id, u.full_name,
                   COUNT(sa.id) as total_shifts,
                   COUNT(CASE WHEN sa.shift_masuk = c.shift_pagi_masuk THEN 1 END) as pagi_count,
                   COUNT(CASE WHEN sa.shift_masuk = c.shift_siang_masuk THEN 1 END) as siang_count,
                   COUNT(CASE WHEN sa.shift_masuk = c.shift_malam_masuk THEN 1 END) as malam_count
            FROM users u
            LEFT JOIN shift_assignments sa ON u.id = sa.user_id 
                AND YEAR(sa.tanggal) = ? AND MONTH(sa.tanggal) = ?
            LEFT JOIN cabang c ON u.cabang_id = c.id
            WHERE u.cabang_id = ? AND u.role IN ('karyawan', 'admin')
            GROUP BY u.id, u.full_name
            ORDER BY u.full_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $year, $month, $cabang_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $summary = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $summary[] = [
                'user_id' => $row['id'],
                'name' => $row['full_name'],
                'total_shifts' => $row['total_shifts'],
                'pagi_count' => $row['pagi_count'],
                'siang_count' => $row['siang_count'],
                'malam_count' => $row['malam_count']
            ];
        }
    }
    
    echo json_encode(['summary' => $summary]);
}
?>
