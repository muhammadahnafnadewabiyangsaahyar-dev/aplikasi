<?php
/**
 * Central Function: Get Role by Posisi
 * Single source of truth untuk mapping posisi → role
 * Reads from database table: posisi_jabatan
 */

function getRoleByPosisiFromDB($pdo, $posisi) {
    if (empty($posisi)) {
        return 'user'; // default
    }
    
    try {
        // Lookup role dari tabel posisi_jabatan
        $stmt = $pdo->prepare("SELECT role_posisi FROM posisi_jabatan WHERE nama_posisi = ? LIMIT 1");
        $stmt->execute([trim($posisi)]);
        $result = $stmt->fetchColumn();
        
        if ($result) {
            // Role found in database
            return strtolower($result);
        } else {
            // Posisi tidak ditemukan di database, gunakan fallback logic
            return getFallbackRole($posisi);
        }
    } catch (PDOException $e) {
        error_log("Error getting role from DB: " . $e->getMessage());
        return getFallbackRole($posisi);
    }
}

function getFallbackRole($posisi) {
    // Fallback jika database lookup gagal
    // Hanya digunakan sebagai backup
    $posisi_lower = strtolower(trim($posisi));
    $admin_positions = ['hr', 'finance', 'marketing', 'scm', 'akuntan', 'owner', 'superadmin'];
    return in_array($posisi_lower, $admin_positions) ? 'admin' : 'user';
}

/**
 * Validate dan sync role dengan posisi
 * Memastikan role selalu consistent dengan posisi dari database
 */
function syncRoleWithPosisi($pdo, $posisi) {
    return getRoleByPosisiFromDB($pdo, $posisi);
}

/**
 * Get all posisi with their roles from database
 * Useful untuk dropdown, validation, etc.
 */
function getAllPosisiWithRoles($pdo) {
    try {
        $stmt = $pdo->query("SELECT nama_posisi, role_posisi FROM posisi_jabatan ORDER BY nama_posisi ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting all posisi: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if posisi exists in database
 */
function posisiExists($pdo, $posisi) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posisi_jabatan WHERE nama_posisi = ?");
        $stmt->execute([trim($posisi)]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking posisi exists: " . $e->getMessage());
        return false;
    }
}
?>
