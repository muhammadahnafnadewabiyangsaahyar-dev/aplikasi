<?php
include 'connect.php';

echo "=== STRUKTUR TABEL komponen_gaji ===\n\n";

try {
    $stmt = $pdo->query("DESCRIBE komponen_gaji");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "Field: " . $col['Field'] . "\n";
        echo "  Type: " . $col['Type'] . "\n";
        echo "  Null: " . $col['Null'] . "\n";
        echo "  Default: " . ($col['Default'] ?? 'NULL') . "\n";
        echo "  Extra: " . $col['Extra'] . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
