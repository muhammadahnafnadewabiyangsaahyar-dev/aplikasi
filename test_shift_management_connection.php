<?php
// Test PDO connection and shift_management query
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing Shift Management Database Connection ===\n\n";

// Include connect.php
require_once 'connect.php';

echo "✅ connect.php loaded successfully\n";
echo "✅ PDO object exists: " . (isset($pdo) ? 'YES' : 'NO') . "\n\n";

if (!isset($pdo)) {
    die("❌ ERROR: \$pdo is not defined!\n");
}

// Test 1: Get branches
echo "Test 1: Getting branches...\n";
try {
    $sql_cabang = "SELECT id, nama_cabang, nama_shift, jam_masuk, jam_keluar FROM cabang ORDER BY nama_cabang";
    $stmt_cabang = $pdo->query($sql_cabang);
    $branches = $stmt_cabang->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($branches) . " branches\n";
    foreach ($branches as $branch) {
        echo "   - {$branch['nama_cabang']} ({$branch['nama_shift']})\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Get employees
echo "Test 2: Getting employees...\n";
try {
    $sql_pegawai = "SELECT id, nama_lengkap, posisi, outlet, id_cabang 
                    FROM register 
                    WHERE role = 'user' 
                    ORDER BY nama_lengkap";
    $stmt_pegawai = $pdo->query($sql_pegawai);
    $employees = $stmt_pegawai->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($employees) . " employees\n";
    foreach (array_slice($employees, 0, 5) as $emp) {
        echo "   - {$emp['nama_lengkap']} ({$emp['posisi']})\n";
    }
    if (count($employees) > 5) {
        echo "   ... and " . (count($employees) - 5) . " more\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Get shift assignments
echo "Test 3: Getting shift assignments...\n";
try {
    $current_month = date('Y-m');
    $sql_assignments = "SELECT sa.*, r.nama_lengkap, c.nama_cabang, c.nama_shift
                        FROM shift_assignments sa
                        JOIN register r ON sa.user_id = r.id
                        JOIN cabang c ON sa.cabang_id = c.id
                        WHERE DATE_FORMAT(sa.tanggal_shift, '%Y-%m') = ?
                        ORDER BY sa.tanggal_shift DESC, r.nama_lengkap";
    $stmt_assignments = $pdo->prepare($sql_assignments);
    $stmt_assignments->execute([$current_month]);
    $assignments = $stmt_assignments->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($assignments) . " shift assignments for $current_month\n";
    foreach (array_slice($assignments, 0, 5) as $assign) {
        echo "   - {$assign['tanggal_shift']}: {$assign['nama_lengkap']} -> {$assign['nama_cabang']} ({$assign['status_konfirmasi']})\n";
    }
    if (count($assignments) > 5) {
        echo "   ... and " . (count($assignments) - 5) . " more\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check timezone
echo "Test 4: Checking timezone...\n";
try {
    $stmt = $pdo->query("SELECT @@session.time_zone AS tz, NOW() AS current_time");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database timezone: {$result['tz']}\n";
    echo "✅ Current database time: {$result['current_time']}\n";
    echo "✅ PHP timezone: " . date_default_timezone_get() . "\n";
    echo "✅ PHP current time: " . date('Y-m-d H:i:s') . "\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== All tests completed ===\n";
?>
