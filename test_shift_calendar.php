<?php
// Test Shift Calendar System
echo "🧪 TESTING SHIFT CALENDAR SYSTEM\n";
echo "================================\n\n";

require_once 'connect.php';

// Test 1: Check Cabang Data
echo "1️⃣  Testing Cabang Data:\n";
echo "------------------------\n";
$stmt = $pdo->query("SELECT id, nama_cabang, nama_shift, jam_masuk, jam_keluar FROM cabang ORDER BY id");
$cabang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($cabang_list) > 0) {
    echo "✅ Found " . count($cabang_list) . " cabang:\n";
    foreach ($cabang_list as $cabang) {
        echo "   • {$cabang['nama_cabang']} - {$cabang['nama_shift']} ({$cabang['jam_masuk']} - {$cabang['jam_keluar']})\n";
    }
} else {
    echo "❌ No cabang data found!\n";
}
echo "\n";

// Test 2: Check if shift_assignments table exists and has data
echo "2️⃣  Testing Shift Assignments:\n";
echo "-----------------------------\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM shift_assignments");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Total shift assignments: {$result['total']}\n";
    
    // Get assignments for November 2025
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM shift_assignments 
        WHERE DATE_FORMAT(tanggal_shift, '%Y-%m') = '2025-11'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Assignments for November 2025: {$result['total']}\n";
    
    // Show sample assignments
    $stmt = $pdo->query("
        SELECT sa.*, r.nama_lengkap, c.nama_cabang, c.nama_shift, c.jam_masuk, c.jam_keluar
        FROM shift_assignments sa
        JOIN register r ON sa.user_id = r.id
        JOIN cabang c ON sa.cabang_id = c.id
        WHERE DATE_FORMAT(sa.tanggal_shift, '%Y-%m') = '2025-11'
        ORDER BY sa.tanggal_shift
        LIMIT 5
    ");
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($assignments) > 0) {
        echo "\n📋 Sample Assignments:\n";
        foreach ($assignments as $a) {
            echo "   • {$a['nama_lengkap']} → {$a['nama_cabang']} ({$a['tanggal_shift']}) - {$a['nama_shift']}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Check Pegawai by Cabang
echo "3️⃣  Testing Pegawai by Cabang:\n";
echo "------------------------------\n";
foreach ($cabang_list as $cabang) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM register 
        WHERE id_cabang = ? AND role = 'user'
    ");
    $stmt->execute([$cabang['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   • {$cabang['nama_cabang']}: {$result['total']} pegawai\n";
}

echo "\n";

// Test 4: Verify JOIN Query (like calendar uses)
echo "4️⃣  Testing JOIN Query (like calendar uses):\n";
echo "--------------------------------------------\n";
$stmt = $pdo->query("
    SELECT 
        sa.id,
        sa.user_id,
        sa.cabang_id,
        sa.tanggal_shift,
        DATE_FORMAT(CONCAT(sa.tanggal_shift, ' ', c.jam_masuk), '%Y-%m-%d %H:%i:%s') as start,
        DATE_FORMAT(CONCAT(sa.tanggal_shift, ' ', c.jam_keluar), '%Y-%m-%d %H:%i:%s') as end,
        c.nama_cabang,
        c.nama_shift,
        c.jam_masuk,
        c.jam_keluar,
        r.nama_lengkap
    FROM shift_assignments sa
    JOIN cabang c ON sa.cabang_id = c.id
    JOIN register r ON sa.user_id = r.id
    WHERE sa.tanggal_shift >= '2025-11-05'
    ORDER BY sa.tanggal_shift
    LIMIT 3
");

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($results) > 0) {
    echo "✅ JOIN Query successful! Sample data:\n";
    foreach ($results as $r) {
        echo "   • {$r['nama_lengkap']} @ {$r['nama_cabang']} on {$r['tanggal_shift']}\n";
        echo "     Start: {$r['start']}, End: {$r['end']}\n";
    }
} else {
    echo "❌ No data returned from JOIN query\n";
}

echo "\n";

// Test 5: Check Admin Users
echo "5️⃣  Testing Admin Users with NEW emails:\n";
echo "-----------------------------------------\n";
$stmt = $pdo->query("
    SELECT username, nama_lengkap, email, role 
    FROM register 
    WHERE role = 'admin' 
    ORDER BY username
    LIMIT 5
");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($admins) > 0) {
    echo "✅ Found " . count($admins) . " admin users:\n";
    foreach ($admins as $admin) {
        echo "   • {$admin['username']} ({$admin['nama_lengkap']}) - {$admin['email']}\n";
    }
} else {
    echo "❌ No admin users found!\n";
}

echo "\n";
echo "================================\n";
echo "✅ ALL TESTS COMPLETED!\n";
echo "================================\n";
echo "\n";
echo "📝 Next Steps:\n";
echo "   1. Open: http://localhost/aplikasi/shift_calendar.php\n";
echo "   2. Login as admin\n";
echo "   3. Select a cabang from dropdown\n";
echo "   4. Verify employees appear\n";
echo "   5. Verify shift assignments display correctly\n";
echo "\n";
echo "🎯 Or run dummy data install:\n";
echo "   ./install_dummy_data.sh\n";
echo "\n";
?>
