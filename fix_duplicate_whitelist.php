<?php
/**
 * Fix duplicate data in whitelist and related tables
 * This script will:
 * 1. Remove duplicate entries in pegawai_whitelist (keep lowest id_wl)
 * 2. Remove duplicate entries in register (keep lowest id)
 * 3. Remove duplicate entries in komponen_gaji (keep lowest id)
 */

echo "========================================\n";
echo "FIXING DUPLICATE DATA\n";
echo "========================================\n\n";

include 'connect.php';

try {
    $pdo->beginTransaction();
    
    // 1. Fix duplicates in pegawai_whitelist
    echo "1. Fixing pegawai_whitelist duplicates...\n";
    
    // Find duplicates
    $stmt = $pdo->query("
        SELECT nama_lengkap, posisi, MIN(id) as keep_id, GROUP_CONCAT(id ORDER BY id) as all_ids
        FROM pegawai_whitelist
        GROUP BY nama_lengkap, posisi
        HAVING COUNT(*) > 1
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup['all_ids']);
            $keep_id = $dup['keep_id'];
            $delete_ids = array_filter($ids, function($id) use ($keep_id) {
                return $id != $keep_id;
            });
            
            if (count($delete_ids) > 0) {
                $delete_ids_str = implode(',', $delete_ids);
                $stmt = $pdo->query("DELETE FROM pegawai_whitelist WHERE id IN ($delete_ids_str)");
                $deleted = $stmt->rowCount();
                echo "   Deleted $deleted duplicate(s) for {$dup['nama_lengkap']} (kept id: $keep_id)\n";
            }
        }
    } else {
        echo "   ✓ No duplicates found\n";
    }
    
    // 2. Fix duplicates in register
    echo "\n2. Fixing register duplicates...\n";
    
    $stmt = $pdo->query("
        SELECT nama_lengkap, MIN(id) as keep_id, GROUP_CONCAT(id ORDER BY id) as all_ids
        FROM register
        GROUP BY nama_lengkap
        HAVING COUNT(*) > 1
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup['all_ids']);
            $keep_id = $dup['keep_id'];
            $delete_ids = array_filter($ids, function($id) use ($keep_id) {
                return $id != $keep_id;
            });
            
            if (count($delete_ids) > 0) {
                $delete_ids_str = implode(',', $delete_ids);
                
                // First, update foreign key references in komponen_gaji
                $stmt = $pdo->prepare("UPDATE komponen_gaji SET register_id = ? WHERE register_id IN ($delete_ids_str)");
                $stmt->execute([$keep_id]);
                
                // Then delete from register
                $stmt = $pdo->query("DELETE FROM register WHERE id IN ($delete_ids_str)");
                $deleted = $stmt->rowCount();
                echo "   Deleted $deleted duplicate(s) for {$dup['nama_lengkap']} (kept id: $keep_id)\n";
            }
        }
    } else {
        echo "   ✓ No duplicates found\n";
    }
    
    // 3. Fix duplicates in komponen_gaji (after fixing register)
    echo "\n3. Fixing komponen_gaji duplicates...\n";
    
    $stmt = $pdo->query("
        SELECT register_id, MIN(id) as keep_id, GROUP_CONCAT(id ORDER BY id) as all_ids
        FROM komponen_gaji
        GROUP BY register_id
        HAVING COUNT(*) > 1
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup['all_ids']);
            $keep_id = $dup['keep_id'];
            $delete_ids = array_filter($ids, function($id) use ($keep_id) {
                return $id != $keep_id;
            });
            
            if (count($delete_ids) > 0) {
                $delete_ids_str = implode(',', $delete_ids);
                $stmt = $pdo->query("DELETE FROM komponen_gaji WHERE id IN ($delete_ids_str)");
                $deleted = $stmt->rowCount();
                echo "   Deleted $deleted duplicate(s) for register_id {$dup['register_id']} (kept id: $keep_id)\n";
            }
        }
    } else {
        echo "   ✓ No duplicates found\n";
    }
    
    // Add UNIQUE constraint to prevent future duplicates
    echo "\n4. Adding UNIQUE constraints...\n";
    
    try {
        $pdo->query("ALTER TABLE pegawai_whitelist ADD UNIQUE KEY unique_pegawai (nama_lengkap, posisi)");
        echo "   ✓ Added UNIQUE constraint to pegawai_whitelist (nama_lengkap, posisi)\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ⓘ UNIQUE constraint already exists on pegawai_whitelist\n";
        } else {
            throw $e;
        }
    }
    
    try {
        $pdo->query("ALTER TABLE register ADD UNIQUE KEY unique_nama (nama_lengkap)");
        echo "   ✓ Added UNIQUE constraint to register (nama_lengkap)\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ⓘ UNIQUE constraint already exists on register\n";
        } else {
            throw $e;
        }
    }
    
    try {
        $pdo->query("ALTER TABLE komponen_gaji ADD UNIQUE KEY unique_register (register_id)");
        echo "   ✓ Added UNIQUE constraint to komponen_gaji (register_id)\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ⓘ UNIQUE constraint already exists on komponen_gaji\n";
        } else {
            throw $e;
        }
    }
    
    $pdo->commit();
    
    echo "\n========================================\n";
    echo "FIX COMPLETE - All duplicates removed!\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
