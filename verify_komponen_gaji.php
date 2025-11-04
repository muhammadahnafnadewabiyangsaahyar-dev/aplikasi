<?php
/**
 * Verification Script: Check Komponen Gaji Structure
 */

echo "========================================\n";
echo "VERIFICATION: Komponen Gaji Structure\n";
echo "========================================\n\n";

include 'connect.php';

try {
    echo "1. Checking table structure...\n\n";
    
    // Get column definitions
    $stmt = $pdo->query("SHOW COLUMNS FROM komponen_gaji");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $gaji_columns = [
        'gaji_pokok',
        'tunjangan_transport',
        'tunjangan_makan',
        'overwork',
        'tunjangan_jabatan',
        'bonus_kehadiran',
        'bonus_marketing',
        'insentif_omset'
    ];
    
    echo "Kolom Komponen Gaji:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-25s %-20s %-10s %-20s\n", "Field", "Type", "Null", "Default");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($columns as $col) {
        if (in_array($col['Field'], $gaji_columns)) {
            printf("%-25s %-20s %-10s %-20s\n", 
                $col['Field'], 
                $col['Type'], 
                $col['Null'], 
                $col['Default'] ?? 'NULL'
            );
        }
    }
    echo str_repeat("-", 80) . "\n\n";
    
    echo "2. Checking data...\n\n";
    
    // Count records
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM komponen_gaji");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total records: $total\n\n";
    
    if ($total > 0) {
        // Sample data
        $stmt = $pdo->query("SELECT jabatan, gaji_pokok, tunjangan_transport, tunjangan_makan FROM komponen_gaji LIMIT 5");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Sample Data:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($samples as $row) {
            echo "Jabatan: {$row['jabatan']}\n";
            echo "  - Gaji Pokok: " . number_format($row['gaji_pokok'], 0, ',', '.') . "\n";
            echo "  - Transport: " . number_format($row['tunjangan_transport'], 0, ',', '.') . "\n";
            echo "  - Makan: " . number_format($row['tunjangan_makan'], 0, ',', '.') . "\n";
            echo "\n";
        }
    }
    
    echo "========================================\n";
    echo "✅ VERIFICATION COMPLETE!\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
