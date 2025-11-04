<?php
/**
 * Check & Fix Duplicate Data
 */

echo "========================================\n";
echo "CHECK DUPLICATE DATA\n";
echo "========================================\n\n";

include 'connect.php';

try {
    echo "1. Checking duplicate in pegawai_whitelist...\n\n";
    
    $stmt = $pdo->query("
        SELECT nama_lengkap, COUNT(*) as count
        FROM pegawai_whitelist
        GROUP BY nama_lengkap
        HAVING COUNT(*) > 1
    ");
    
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "❌ Found " . count($duplicates) . " duplicate names in pegawai_whitelist:\n";
        foreach ($duplicates as $dup) {
            echo "   - {$dup['nama_lengkap']} (x{$dup['count']})\n";
        }
        echo "\n";
    } else {
        echo "✅ No duplicates in pegawai_whitelist\n\n";
    }
    
    echo "2. Checking duplicate in register...\n\n";
    
    $stmt = $pdo->query("
        SELECT nama_lengkap, COUNT(*) as count
        FROM register
        GROUP BY nama_lengkap
        HAVING COUNT(*) > 1
    ");
    
    $duplicates_register = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates_register) > 0) {
        echo "❌ Found " . count($duplicates_register) . " duplicate names in register:\n";
        foreach ($duplicates_register as $dup) {
            echo "   - {$dup['nama_lengkap']} (x{$dup['count']})\n";
        }
        echo "\n";
    } else {
        echo "✅ No duplicates in register\n\n";
    }
    
    echo "3. Checking duplicate in komponen_gaji...\n\n";
    
    $stmt = $pdo->query("
        SELECT register_id, COUNT(*) as count
        FROM komponen_gaji
        GROUP BY register_id
        HAVING COUNT(*) > 1
    ");
    
    $duplicates_gaji = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates_gaji) > 0) {
        echo "❌ Found " . count($duplicates_gaji) . " duplicate register_id in komponen_gaji:\n";
        foreach ($duplicates_gaji as $dup) {
            echo "   - register_id: {$dup['register_id']} (x{$dup['count']})\n";
        }
        echo "\n";
    } else {
        echo "✅ No duplicates in komponen_gaji\n\n";
    }
    
    echo "4. Detailed view of duplicates...\n\n";
    
    if (count($duplicates) > 0) {
        echo "Detail pegawai_whitelist duplicates:\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($duplicates as $dup) {
            $stmt = $pdo->prepare("SELECT * FROM pegawai_whitelist WHERE nama_lengkap = ?");
            $stmt->execute([$dup['nama_lengkap']]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\nNama: {$dup['nama_lengkap']}\n";
            foreach ($records as $i => $rec) {
                echo "  Record " . ($i+1) . ":\n";
                echo "    - Posisi: {$rec['posisi']}\n";
                echo "    - Role: {$rec['role']}\n";
                echo "    - Status: {$rec['status_registrasi']}\n";
            }
        }
        echo str_repeat("-", 80) . "\n\n";
    }
    
    // Suggest fix
    if (count($duplicates) > 0 || count($duplicates_register) > 0 || count($duplicates_gaji) > 0) {
        echo "\n========================================\n";
        echo "⚠️  ACTION REQUIRED!\n";
        echo "========================================\n";
        echo "Run fix script: php fix_duplicate_data.php\n\n";
    } else {
        echo "\n========================================\n";
        echo "✅ NO DUPLICATES FOUND!\n";
        echo "========================================\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
