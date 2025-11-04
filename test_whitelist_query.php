<?php
/**
 * Test Query: Whitelist dengan Komponen Gaji
 * Purpose: Verify JOIN query bekerja dengan benar
 */

echo "========================================\n";
echo "TEST QUERY: Whitelist + Komponen Gaji\n";
echo "========================================\n\n";

include 'connect.php';

try {
    echo "1. Testing NEW JOIN query (menggunakan register_id)...\n\n";
    
    $stmt = $pdo->query("
        SELECT 
            pw.nama_lengkap, 
            pw.posisi, 
            pw.role, 
            pw.status_registrasi, 
            kg.jabatan, 
            kg.gaji_pokok, 
            kg.tunjangan_transport, 
            kg.tunjangan_makan, 
            r.id as register_id
        FROM pegawai_whitelist pw 
        LEFT JOIN register r ON pw.nama_lengkap = r.nama_lengkap
        LEFT JOIN komponen_gaji kg ON r.id = kg.register_id
        ORDER BY pw.nama_lengkap ASC
    ");
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total rows: " . count($data) . "\n\n";
    
    if (count($data) > 0) {
        echo "Sample Data:\n";
        echo str_repeat("-", 100) . "\n";
        
        foreach ($data as $row) {
            echo "Nama: {$row['nama_lengkap']}\n";
            echo "  Register ID: " . ($row['register_id'] ?? 'NULL') . "\n";
            echo "  Posisi: {$row['posisi']}\n";
            echo "  Role: {$row['role']}\n";
            echo "  Status: {$row['status_registrasi']}\n";
            echo "  Jabatan (Gaji): " . ($row['jabatan'] ?? '-') . "\n";
            
            if ($row['gaji_pokok'] !== null) {
                echo "  Gaji Pokok: Rp " . number_format($row['gaji_pokok'], 0, ',', '.') . "\n";
                echo "  Transport: Rp " . number_format($row['tunjangan_transport'], 0, ',', '.') . "\n";
                echo "  Makan: Rp " . number_format($row['tunjangan_makan'], 0, ',', '.') . "\n";
            } else {
                echo "  Gaji: Belum diset (NULL)\n";
            }
            echo "\n";
        }
        echo str_repeat("-", 100) . "\n\n";
    }
    
    echo "2. Checking data integrity...\n\n";
    
    // Check whitelist tanpa register
    $stmt = $pdo->query("
        SELECT pw.nama_lengkap 
        FROM pegawai_whitelist pw 
        LEFT JOIN register r ON pw.nama_lengkap = r.nama_lengkap
        WHERE r.id IS NULL
    ");
    $no_register = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($no_register) > 0) {
        echo "⚠️  WARNING: Pegawai di whitelist tapi belum ada di register:\n";
        foreach ($no_register as $nama) {
            echo "   - $nama\n";
        }
        echo "\n";
    } else {
        echo "✅ Semua pegawai di whitelist sudah terdaftar di register\n\n";
    }
    
    // Check register dengan gaji
    $stmt = $pdo->query("
        SELECT r.nama_lengkap 
        FROM register r
        INNER JOIN komponen_gaji kg ON r.id = kg.register_id
    ");
    $with_gaji = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ Pegawai yang sudah punya komponen gaji: " . count($with_gaji) . "\n";
    if (count($with_gaji) > 0) {
        foreach ($with_gaji as $nama) {
            echo "   - $nama\n";
        }
    }
    
    echo "\n========================================\n";
    echo "✅ TEST COMPLETE!\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
