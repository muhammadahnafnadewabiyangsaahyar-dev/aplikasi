<?php
/**
 * Check for duplicate data in whitelist and related tables
 */

echo "========================================\n";
echo "CHECKING FOR DUPLICATE DATA\n";
echo "========================================\n\n";

include 'connect.php';

try {
    // 1. Check duplicate in pegawai_whitelist
    echo "1. Checking pegawai_whitelist duplicates...\n";
    $stmt = $pdo->query("
        SELECT nama_lengkap, posisi, COUNT(*) as count, GROUP_CONCAT(id) as ids
        FROM pegawai_whitelist
        GROUP BY nama_lengkap, posisi
        HAVING count > 1
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "   ⚠️ Found " . count($duplicates) . " duplicate entries:\n";
        foreach ($duplicates as $dup) {
            echo "   - {$dup['nama_lengkap']} ({$dup['posisi']}): {$dup['count']} times (IDs: {$dup['ids']})\n";
        }
    } else {
        echo "   ✓ No duplicates found\n";
    }
    
    // 2. Check duplicate in register based on nama_lengkap
    echo "\n2. Checking register duplicates...\n";
    $stmt = $pdo->query("
        SELECT nama_lengkap, COUNT(*) as count, GROUP_CONCAT(id) as ids
        FROM register
        GROUP BY nama_lengkap
        HAVING count > 1
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "   ⚠️ Found " . count($duplicates) . " duplicate entries:\n";
        foreach ($duplicates as $dup) {
            echo "   - {$dup['nama_lengkap']}: {$dup['count']} times (IDs: {$dup['ids']})\n";
        }
    } else {
        echo "   ✓ No duplicates found\n";
    }
    
    // 3. Check duplicate in komponen_gaji
    echo "\n3. Checking komponen_gaji duplicates...\n";
    $stmt = $pdo->query("
        SELECT register_id, COUNT(*) as count, GROUP_CONCAT(id) as ids
        FROM komponen_gaji
        GROUP BY register_id
        HAVING count > 1
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "   ⚠️ Found " . count($duplicates) . " duplicate entries:\n";
        foreach ($duplicates as $dup) {
            echo "   - register_id {$dup['register_id']}: {$dup['count']} times (IDs: {$dup['ids']})\n";
        }
    } else {
        echo "   ✓ No duplicates found\n";
    }
    
    // 4. Check query hasil JOIN (seperti di whitelist.php)
    echo "\n4. Checking whitelist query results...\n";
    $stmt = $pdo->query("
        SELECT 
            pw.nama_lengkap,
            pw.posisi,
            pw.role,
            pw.status_registrasi,
            COUNT(*) as count
        FROM pegawai_whitelist pw
        LEFT JOIN register r ON pw.nama_lengkap = r.nama_lengkap
        LEFT JOIN komponen_gaji kg ON r.id = kg.register_id
        GROUP BY pw.nama_lengkap, pw.posisi, pw.role, pw.status_registrasi
        HAVING count > 1
    ");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "   ⚠️ JOIN query produces duplicates:\n";
        foreach ($duplicates as $dup) {
            echo "   - {$dup['nama_lengkap']} ({$dup['posisi']}): appears {$dup['count']} times\n";
        }
    } else {
        echo "   ✓ No duplicates in JOIN results\n";
    }
    
    // 5. Total counts
    echo "\n5. Total record counts...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pegawai_whitelist");
    $total_whitelist = $stmt->fetch()['total'];
    echo "   pegawai_whitelist: $total_whitelist records\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM register");
    $total_register = $stmt->fetch()['total'];
    echo "   register: $total_register records\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM komponen_gaji");
    $total_gaji = $stmt->fetch()['total'];
    echo "   komponen_gaji: $total_gaji records\n";
    
    echo "\n========================================\n";
    echo "CHECK COMPLETE\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
