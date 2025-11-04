<?php
/**
 * Migration Script: Fix Komponen Gaji Default Values
 * Tujuan: Mengubah kolom komponen gaji untuk memiliki default value 0
 */

echo "========================================\n";
echo "MIGRATION: Fix Komponen Gaji Default\n";
echo "========================================\n\n";

// Include connection
include 'connect.php';

try {
    echo "1. Connecting to database...\n";
    
    // Start transaction
    $pdo->beginTransaction();
    
    echo "2. Updating NULL values to 0...\n";
    
    // Update existing NULL values menjadi 0
    $updates = [
        'gaji_pokok',
        'tunjangan_transport',
        'tunjangan_makan',
        'overwork',
        'tunjangan_jabatan',
        'bonus_kehadiran',
        'bonus_marketing',
        'insentif_omset'
    ];
    
    foreach ($updates as $column) {
        $sql = "UPDATE komponen_gaji SET $column = 0 WHERE $column IS NULL";
        $affected = $pdo->exec($sql);
        echo "   - Updated $column: $affected rows\n";
    }
    
    echo "\n3. Modifying table structure with default values...\n";
    
    // Alter table structure (one by one untuk compatibility)
    $alterStatements = [
        "ALTER TABLE komponen_gaji MODIFY COLUMN gaji_pokok DECIMAL(10,2) NOT NULL DEFAULT 0",
        "ALTER TABLE komponen_gaji MODIFY COLUMN tunjangan_transport DECIMAL(10,2) NOT NULL DEFAULT 0",
        "ALTER TABLE komponen_gaji MODIFY COLUMN tunjangan_makan DECIMAL(10,2) NOT NULL DEFAULT 0",
        "ALTER TABLE komponen_gaji MODIFY COLUMN overwork DECIMAL(10,2) NOT NULL DEFAULT 0",
        "ALTER TABLE komponen_gaji MODIFY COLUMN tunjangan_jabatan DECIMAL(10,2) NOT NULL DEFAULT 0",
        "ALTER TABLE komponen_gaji MODIFY COLUMN bonus_kehadiran DECIMAL(10,2) NOT NULL DEFAULT 0",
        "ALTER TABLE komponen_gaji MODIFY COLUMN bonus_marketing DECIMAL(10,2) NOT NULL DEFAULT 0",
        "ALTER TABLE komponen_gaji MODIFY COLUMN insentif_omset DECIMAL(10,2) NOT NULL DEFAULT 0"
    ];
    
    foreach ($alterStatements as $sql) {
        $pdo->exec($sql);
        echo "   ✓ Modified column\n";
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "\n4. Verifying results...\n";
    
    // Count total records
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM komponen_gaji");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   Total records: $total\n";
    
    // Count zeros
    $stmt = $pdo->query("SELECT 
        SUM(CASE WHEN gaji_pokok = 0 THEN 1 ELSE 0 END) as gaji_zero,
        SUM(CASE WHEN tunjangan_transport = 0 THEN 1 ELSE 0 END) as transport_zero,
        SUM(CASE WHEN tunjangan_makan = 0 THEN 1 ELSE 0 END) as makan_zero
    FROM komponen_gaji");
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Records with gaji_pokok = 0: {$counts['gaji_zero']}\n";
    echo "   Records with transport = 0: {$counts['transport_zero']}\n";
    echo "   Records with makan = 0: {$counts['makan_zero']}\n";
    
    echo "\n========================================\n";
    echo "✅ MIGRATION SUCCESS!\n";
    echo "========================================\n";
    echo "\nSemua kolom komponen gaji sekarang:\n";
    echo "- Memiliki default value 0\n";
    echo "- NOT NULL constraint\n";
    echo "- Tidak akan ada lagi error 'Column cannot be null'\n";
    
} catch (PDOException $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "\n========================================\n";
    echo "❌ MIGRATION FAILED!\n";
    echo "========================================\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
