<?php
/**
 * Fix Duplicate in komponen_gaji table
 * Keep the best record, delete duplicates
 */

echo "========================================\n";
echo "FIX: Duplicate komponen_gaji\n";
echo "========================================\n\n";

include 'connect.php';

try {
    $pdo->beginTransaction();
    
    echo "1. Finding duplicates in komponen_gaji...\n\n";
    
    $stmt = $pdo->query("
        SELECT register_id, COUNT(*) as count
        FROM komponen_gaji
        GROUP BY register_id
        HAVING COUNT(*) > 1
    ");
    
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) == 0) {
        echo "✅ No duplicates found!\n";
        $pdo->rollBack();
        exit(0);
    }
    
    echo "Found " . count($duplicates) . " duplicate register_id:\n\n";
    
    $total_deleted = 0;
    
    foreach ($duplicates as $dup) {
        $register_id = $dup['register_id'];
        
        // Get nama from register
        $stmt = $pdo->prepare("SELECT nama_lengkap FROM register WHERE id = ?");
        $stmt->execute([$register_id]);
        $nama = $stmt->fetchColumn();
        
        echo "Processing: register_id = $register_id ($nama)\n";
        
        // Get all komponen_gaji records for this register_id
        $stmt = $pdo->prepare("
            SELECT * FROM komponen_gaji 
            WHERE register_id = ?
            ORDER BY 
                CASE WHEN gaji_pokok > 0 THEN 1 ELSE 2 END,
                id DESC
        ");
        $stmt->execute([$register_id]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Keep the first (best) record
        $keep = array_shift($records);
        
        echo "  Keeping record ID={$keep['id']}:\n";
        echo "    - Jabatan: {$keep['jabatan']}\n";
        echo "    - Gaji Pokok: " . number_format($keep['gaji_pokok'], 0, ',', '.') . "\n";
        echo "    - Transport: " . number_format($keep['tunjangan_transport'], 0, ',', '.') . "\n";
        echo "\n";
        
        // Delete the rest
        foreach ($records as $rec) {
            echo "  Deleting record ID={$rec['id']}:\n";
            echo "    - Jabatan: {$rec['jabatan']}\n";
            echo "    - Gaji Pokok: " . number_format($rec['gaji_pokok'], 0, ',', '.') . "\n";
            
            $stmt = $pdo->prepare("DELETE FROM komponen_gaji WHERE id = ?");
            $stmt->execute([$rec['id']]);
            $total_deleted++;
            echo "    ✓ Deleted\n\n";
        }
    }
    
    echo "2. Verifying results...\n\n";
    
    $stmt = $pdo->query("
        SELECT register_id, COUNT(*) as count
        FROM komponen_gaji
        GROUP BY register_id
        HAVING COUNT(*) > 1
    ");
    
    $remaining = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($remaining) > 0) {
        echo "⚠️  WARNING: Still have duplicates!\n";
        foreach ($remaining as $rem) {
            echo "   - register_id {$rem['register_id']} (x{$rem['count']})\n";
        }
        echo "\nRolling back...\n";
        $pdo->rollBack();
        exit(1);
    } else {
        echo "✅ All duplicates removed!\n\n";
    }
    
    // Add UNIQUE constraint to prevent future duplicates
    echo "3. Adding UNIQUE constraint...\n\n";
    
    try {
        $pdo->exec("ALTER TABLE komponen_gaji ADD UNIQUE KEY unique_register_id (register_id)");
        echo "✅ UNIQUE constraint added successfully\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "✓ UNIQUE constraint already exists\n\n";
        } else {
            throw $e;
        }
    }
    
    $pdo->commit();
    
    echo "========================================\n";
    echo "✅ FIX COMPLETE!\n";
    echo "========================================\n";
    echo "Total deleted: $total_deleted records\n";
    echo "Each register_id now has only one komponen_gaji record\n";
    echo "UNIQUE constraint added to prevent future duplicates\n\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "\n========================================\n";
    echo "❌ FIX FAILED!\n";
    echo "========================================\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
