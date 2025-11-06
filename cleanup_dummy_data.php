<?php
require_once 'connect.php';

echo "=== CLEANUP DATABASE: HAPUS DATA DUMMY ===\n\n";

// === STEP 1: Baca daftar user dari CSV whitelist ===
echo "STEP 1: Baca daftar user dari CSV whitelist...\n";

$csv_file = 'datawhitelistpegawai.csv';
if (!file_exists($csv_file)) {
    echo "❌ File CSV tidak ditemukan: $csv_file\n";
    exit;
}

$whitelisted_users = [];
$handle = fopen($csv_file, 'r');

// Skip header (BOM + header row)
$header = fgetcsv($handle, 1000, ';');

// Read all names
while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
    if (isset($data[1]) && !empty(trim($data[1]))) {
        $whitelisted_users[] = trim($data[1]);
    }
}
fclose($handle);

echo "✅ Ditemukan " . count($whitelisted_users) . " user di whitelist:\n";
foreach ($whitelisted_users as $name) {
    echo "  - $name\n";
}
echo "\n";

// === STEP 2: Temukan user di register yang TIDAK ada di whitelist ===
echo "STEP 2: Temukan user dummy (tidak ada di whitelist)...\n";

$placeholders = str_repeat('?,', count($whitelisted_users) - 1) . '?';
$query = "SELECT id, nama_lengkap, username, email FROM register 
          WHERE nama_lengkap NOT IN ($placeholders)
          AND role != 'admin'"; // Jangan hapus admin

$stmt = $pdo->prepare($query);
$stmt->execute($whitelisted_users);
$dummy_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($dummy_users)) {
    echo "✅ Tidak ada user dummy yang perlu dihapus!\n";
    exit;
}

echo "⚠️  Ditemukan " . count($dummy_users) . " user dummy:\n";
foreach ($dummy_users as $user) {
    echo "  - ID: {$user['id']}, Nama: {$user['nama_lengkap']}, Email: {$user['email']}\n";
}
echo "\n";

// === STEP 3: Konfirmasi sebelum hapus ===
echo "❓ Yakin ingin menghapus " . count($dummy_users) . " user dummy beserta semua data terkait?\n";
echo "   (shift, absensi, izin, slip gaji, dll)\n";
echo "   Ketik 'YA' untuk melanjutkan, atau apapun untuk batal: ";

$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));

if (strtoupper($confirmation) !== 'YA') {
    echo "\n❌ Pembatalan! Tidak ada data yang dihapus.\n";
    exit;
}

// === STEP 4: Hapus data dummy ===
echo "\nSTEP 4: Menghapus data dummy...\n\n";

$pdo->beginTransaction();

try {
    $deleted_count = [
        'shift_assignments' => 0,
        'absensi' => 0,
        'pengajuan_izin' => 0,
        'riwayat_gaji' => 0,
        'komponen_gaji' => 0,
        'register' => 0
    ];

    foreach ($dummy_users as $user) {
        $user_id = $user['id'];
        $user_name = $user['nama_lengkap'];
        
        echo "Menghapus data untuk: $user_name (ID: $user_id)\n";
        
        // Hapus shift assignments
        $stmt = $pdo->prepare("DELETE FROM shift_assignments WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $deleted_count['shift_assignments'] += $stmt->rowCount();
        
        // Hapus absensi
        $stmt = $pdo->prepare("DELETE FROM absensi WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $deleted_count['absensi'] += $stmt->rowCount();
        
        // Hapus pengajuan izin
        $stmt = $pdo->prepare("DELETE FROM pengajuan_izin WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $deleted_count['pengajuan_izin'] += $stmt->rowCount();
        
        // Hapus riwayat gaji (uses register_id, not user_id)
        $stmt = $pdo->prepare("DELETE FROM riwayat_gaji WHERE register_id = ?");
        $stmt->execute([$user_id]);
        $deleted_count['riwayat_gaji'] += $stmt->rowCount();
        
        // Hapus komponen gaji (uses register_id, not user_id)
        $stmt = $pdo->prepare("DELETE FROM komponen_gaji WHERE register_id = ?");
        $stmt->execute([$user_id]);
        $deleted_count['komponen_gaji'] += $stmt->rowCount();
        
        // Hapus user dari register
        $stmt = $pdo->prepare("DELETE FROM register WHERE id = ?");
        $stmt->execute([$user_id]);
        $deleted_count['register'] += $stmt->rowCount();
        
        echo "  ✅ Berhasil dihapus\n";
    }
    
    $pdo->commit();
    
    echo "\n=== RINGKASAN PENGHAPUSAN ===\n";
    echo "✅ User dihapus: {$deleted_count['register']}\n";
    echo "✅ Shift assignments dihapus: {$deleted_count['shift_assignments']}\n";
    echo "✅ Absensi dihapus: {$deleted_count['absensi']}\n";
    echo "✅ Pengajuan izin dihapus: {$deleted_count['pengajuan_izin']}\n";
    echo "✅ Riwayat gaji dihapus: {$deleted_count['riwayat_gaji']}\n";
    echo "✅ Komponen gaji dihapus: {$deleted_count['komponen_gaji']}\n";
    echo "\n✅ Database berhasil dibersihkan!\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Database dikembalikan ke kondisi semula.\n";
}
?>
