<?php
// ========================================================
// CEK STRUKTUR TABEL REGISTER
// ========================================================
include 'connect.php';

echo "<!DOCTYPE html><html><head><title>Check Table Structure</title>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; background: #2d2d30; }
th, td { border: 1px solid #3e3e42; padding: 12px; text-align: left; }
th { background: #252526; color: #569cd6; }
tr:hover { background: #383838; }
.success { color: #4ec9b0; }
.error { color: #f48771; }
.warning { color: #dcdcaa; }
h1 { color: #569cd6; }
h2 { color: #4ec9b0; }
</style></head><body>";

echo "<h1>🔍 CHECK TABLE STRUCTURE</h1>";

// ========================================================
// 1. CEK STRUKTUR TABEL REGISTER
// ========================================================
echo "<h2>1. Struktur Tabel REGISTER</h2>";

try {
    $stmt = $pdo->query("DESCRIBE register");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $has_outlet_id = false;
    $has_id_cabang = false;
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($col['Extra'] ?? '') . "</td>";
        echo "</tr>";
        
        if ($col['Field'] === 'outlet_id') $has_outlet_id = true;
        if ($col['Field'] === 'id_cabang') $has_id_cabang = true;
    }
    echo "</table>";
    
    echo "<h3>Status Kolom:</h3>";
    echo "<p class='" . ($has_outlet_id ? "success" : "error") . "'>";
    echo ($has_outlet_id ? "✅" : "❌") . " outlet_id: " . ($has_outlet_id ? "EXISTS" : "NOT FOUND");
    echo "</p>";
    
    echo "<p class='" . ($has_id_cabang ? "success" : "error") . "'>";
    echo ($has_id_cabang ? "✅" : "❌") . " id_cabang: " . ($has_id_cabang ? "EXISTS" : "NOT FOUND");
    echo "</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// ========================================================
// 2. CEK DATA ADMIN
// ========================================================
echo "<h2>2. Data Admin (User ID = 1)</h2>";

try {
    $stmt = $pdo->prepare("SELECT * FROM register WHERE id = 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($admin as $key => $value) {
            if ($key !== 'password') {
                $highlight = ($key === 'outlet_id' || $key === 'id_cabang') ? 'style="background:#3b4b00;"' : '';
                echo "<tr $highlight>";
                echo "<td><strong>$key</strong></td>";
                echo "<td>" . ($value ?? 'NULL') . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Admin user not found!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// ========================================================
// 3. REKOMENDASI SQL
// ========================================================
echo "<h2>3. SQL Rekomendasi</h2>";

if (!$has_outlet_id && $has_id_cabang) {
    echo "<p class='warning'>⚠️ Tabel register TIDAK punya kolom 'outlet_id', hanya 'id_cabang'</p>";
    echo "<p class='success'>✅ Solusi: Gunakan field 'id_cabang' saja, TIDAK perlu 'outlet_id'</p>";
    echo "<h3>SQL untuk update admin:</h3>";
    echo "<pre style='background:#252526;padding:10px;'>UPDATE register SET id_cabang = 10 WHERE id = 1;</pre>";
} elseif ($has_outlet_id && !$has_id_cabang) {
    echo "<p class='warning'>⚠️ Tabel register punya 'outlet_id' tapi TIDAK punya 'id_cabang'</p>";
    echo "<h3>SQL untuk update admin:</h3>";
    echo "<pre style='background:#252526;padding:10px;'>UPDATE register SET outlet_id = 10 WHERE id = 1;</pre>";
} elseif ($has_outlet_id && $has_id_cabang) {
    echo "<p class='success'>✅ Tabel register punya KEDUA kolom: 'outlet_id' DAN 'id_cabang'</p>";
    echo "<h3>SQL untuk update admin (update keduanya):</h3>";
    echo "<pre style='background:#252526;padding:10px;'>UPDATE register SET outlet_id = 10, id_cabang = 10 WHERE id = 1;</pre>";
} else {
    echo "<p class='error'>❌ Tabel register TIDAK punya 'outlet_id' maupun 'id_cabang'!</p>";
    echo "<h3>SQL untuk tambah kolom:</h3>";
    echo "<pre style='background:#252526;padding:10px;'>ALTER TABLE register ADD COLUMN id_cabang INT NULL AFTER role;</pre>";
}

// ========================================================
// 4. GENERATE CREATE TABLE STATEMENT
// ========================================================
echo "<h2>4. Generate CREATE TABLE Statement</h2>";

try {
    $stmt = $pdo->query("SHOW CREATE TABLE register");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>SQL untuk recreate tabel register:</h3>";
    echo "<pre style='background:#252526;padding:10px;overflow-x:auto;'>";
    echo htmlspecialchars($result['Create Table']);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>
