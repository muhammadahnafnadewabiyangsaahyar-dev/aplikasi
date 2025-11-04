<?php
/**
 * Debug: Show exact data structure from whitelist query
 */

echo "========================================\n";
echo "DEBUG: Whitelist Data Structure\n";
echo "========================================\n\n";

include 'connect.php';

try {
    echo "Query yang digunakan di whitelist.php:\n\n";
    
    $query = "
    SELECT 
        pw.nama_lengkap, 
        pw.posisi, 
        pw.role, 
        pw.status_registrasi, 
        kg.jabatan, 
        kg.gaji_pokok, 
        kg.tunjangan_transport, 
        kg.tunjangan_makan, 
        kg.overwork, 
        kg.tunjangan_jabatan, 
        kg.bonus_kehadiran, 
        kg.bonus_marketing, 
        kg.insentif_omset,
        r.id as register_id,
        kg.id as komponen_gaji_id
    FROM pegawai_whitelist pw 
    LEFT JOIN register r ON pw.nama_lengkap = r.nama_lengkap
    LEFT JOIN komponen_gaji kg ON r.id = kg.register_id
    ORDER BY pw.nama_lengkap ASC
    ";
    
    echo $query . "\n\n";
    
    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total rows returned: " . count($data) . "\n\n";
    
    // Group by nama to see duplicates
    $grouped = [];
    foreach ($data as $row) {
        $nama = $row['nama_lengkap'];
        if (!isset($grouped[$nama])) {
            $grouped[$nama] = [];
        }
        $grouped[$nama][] = $row;
    }
    
    echo "Checking for duplicate names in result:\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($grouped as $nama => $records) {
        if (count($records) > 1) {
            echo "\n⚠️  DUPLICATE: $nama (appears " . count($records) . " times)\n\n";
            
            foreach ($records as $i => $rec) {
                echo "  Record #" . ($i+1) . ":\n";
                echo "    - Register ID: " . ($rec['register_id'] ?? 'NULL') . "\n";
                echo "    - Komponen Gaji ID: " . ($rec['komponen_gaji_id'] ?? 'NULL') . "\n";
                echo "    - Posisi: {$rec['posisi']}\n";
                echo "    - Role: {$rec['role']}\n";
                echo "    - Status: {$rec['status_registrasi']}\n";
                echo "    - Jabatan: " . ($rec['jabatan'] ?? 'NULL') . "\n";
                echo "    - Gaji Pokok: " . ($rec['gaji_pokok'] ?? 'NULL') . "\n";
                echo "\n";
            }
        }
    }
    
    echo str_repeat("-", 100) . "\n\n";
    
    // Check individual tables
    echo "Checking individual tables:\n\n";
    
    // pegawai_whitelist
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pegawai_whitelist");
    $count = $stmt->fetch()['total'];
    echo "pegawai_whitelist: $count records\n";
    
    // register
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM register");
    $count = $stmt->fetch()['total'];
    echo "register: $count records\n";
    
    $stmt = $pdo->query("SELECT nama_lengkap, COUNT(*) as count FROM register GROUP BY nama_lengkap HAVING COUNT(*) > 1");
    $dups = $stmt->fetchAll();
    if (count($dups) > 0) {
        echo "  ⚠️  Duplicates in register:\n";
        foreach ($dups as $dup) {
            echo "     - {$dup['nama_lengkap']} (x{$dup['count']})\n";
        }
    }
    echo "\n";
    
    // komponen_gaji
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM komponen_gaji");
    $count = $stmt->fetch()['total'];
    echo "komponen_gaji: $count records\n";
    
    $stmt = $pdo->query("SELECT register_id, COUNT(*) as count FROM komponen_gaji GROUP BY register_id HAVING COUNT(*) > 1");
    $dups = $stmt->fetchAll();
    if (count($dups) > 0) {
        echo "  ⚠️  Duplicates in komponen_gaji:\n";
        foreach ($dups as $dup) {
            // Get nama from register
            $stmt2 = $pdo->prepare("SELECT nama_lengkap FROM register WHERE id = ?");
            $stmt2->execute([$dup['register_id']]);
            $nama = $stmt2->fetchColumn();
            echo "     - register_id {$dup['register_id']} ($nama) (x{$dup['count']})\n";
        }
    }
    
    echo "\n========================================\n";
    echo "DEBUG COMPLETE\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
