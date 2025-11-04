<?php
/**
 * Fix Duplicate Data in pegawai_whitelist
 * Strategy: Keep the latest/best record, delete older duplicates
 */

echo "========================================\n";
echo "FIX DUPLICATE DATA\n";
echo "========================================\n\n";

include 'connect.php';

try {
    // Start transaction
    $pdo->beginTransaction();
    
    echo "1. Finding duplicates in pegawai_whitelist...\n\n";
    
    // Get all duplicates
    $stmt = $pdo->query("
        SELECT nama_lengkap, COUNT(*) as count
        FROM pegawai_whitelist
        GROUP BY nama_lengkap
        HAVING COUNT(*) > 1
    ");
    
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) == 0) {
        echo "✅ No duplicates found!\n";
        $pdo->rollBack();
        exit(0);
    }
    
    echo "Found " . count($duplicates) . " duplicate names:\n";
    foreach ($duplicates as $dup) {
        echo "   - {$dup['nama_lengkap']} (x{$dup['count']})\n";
    }
    echo "\n";
    
    echo "2. Removing duplicates (keeping one record per name)...\n\n";
    
    $total_deleted = 0;
    
    foreach ($duplicates as $dup) {
        $nama = $dup['nama_lengkap'];
        
        // Get all records for this name, ordered by status priority
        // Priority: terdaftar > pending
        $stmt = $pdo->prepare("
            SELECT * FROM pegawai_whitelist 
            WHERE nama_lengkap = ?
            ORDER BY 
                CASE status_registrasi 
                    WHEN 'terdaftar' THEN 1 
                    WHEN 'pending' THEN 2 
                    ELSE 3 
                END,
                CASE role
                    WHEN 'admin' THEN 1
                    WHEN 'user' THEN 2
                    ELSE 3
                END
        ");
        $stmt->execute([$nama]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Keep the first (best) record, delete the rest
        $keep = array_shift($records); // Remove first element (to keep)
        
        echo "   Processing: $nama\n";
        echo "     Keeping: Posisi={$keep['posisi']}, Role={$keep['role']}, Status={$keep['status_registrasi']}\n";
        
        foreach ($records as $rec) {
            echo "     Deleting: Posisi={$rec['posisi']}, Role={$rec['role']}, Status={$rec['status_registrasi']}\n";
            
            // Use a unique identifier if available, otherwise use nama+posisi combination
            // IMPORTANT: Be very careful here - we need to identify the exact record to delete
            $stmt = $pdo->prepare("
                DELETE FROM pegawai_whitelist 
                WHERE nama_lengkap = ? 
                AND posisi = ? 
                AND role = ?
                AND status_registrasi = ?
                LIMIT 1
            ");
            $stmt->execute([$rec['nama_lengkap'], $rec['posisi'], $rec['role'], $rec['status_registrasi']]);
            $total_deleted++;
        }
        echo "\n";
    }
    
    echo "3. Verifying results...\n\n";
    
    $stmt = $pdo->query("
        SELECT nama_lengkap, COUNT(*) as count
        FROM pegawai_whitelist
        GROUP BY nama_lengkap
        HAVING COUNT(*) > 1
    ");
    
    $remaining = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($remaining) > 0) {
        echo "⚠️  WARNING: Still have duplicates:\n";
        foreach ($remaining as $rem) {
            echo "   - {$rem['nama_lengkap']} (x{$rem['count']})\n";
        }
        echo "\nRolling back...\n";
        $pdo->rollBack();
        exit(1);
    } else {
        echo "✅ All duplicates removed!\n\n";
    }
    
    // Commit
    $pdo->commit();
    
    echo "========================================\n";
    echo "✅ FIX COMPLETE!\n";
    echo "========================================\n";
    echo "Total deleted: $total_deleted records\n";
    echo "All names are now unique\n\n";
    
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
