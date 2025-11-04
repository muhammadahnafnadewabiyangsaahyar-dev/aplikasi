<?php
// Test Script: Cek output yang tidak diinginkan dari file include

echo "=== Testing Include Files ===\n\n";

echo "1. Testing connect.php...\n";
ob_start();
include 'connect.php';
$output1 = ob_get_clean();
if (empty(trim($output1))) {
    echo "✓ PASS: Tidak ada output dari connect.php\n";
} else {
    echo "✗ FAIL: Ada output dari connect.php:\n";
    echo "Output: " . var_export($output1, true) . "\n";
}

echo "\n2. Testing calculate_status_kehadiran.php...\n";
ob_start();
include 'calculate_status_kehadiran.php';
$output2 = ob_get_clean();
if (empty(trim($output2))) {
    echo "✓ PASS: Tidak ada output dari calculate_status_kehadiran.php\n";
} else {
    echo "✗ FAIL: Ada output dari calculate_status_kehadiran.php:\n";
    echo "Output: " . var_export($output2, true) . "\n";
}

echo "\n3. Testing navbar.php...\n";
// Setup session untuk navbar
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'user';
$_SESSION['nama_lengkap'] = 'Test User';

ob_start();
include 'navbar.php';
$output3 = ob_get_clean();
if (strpos($output3, '?>') === false) {
    echo "✓ PASS: Tidak ada karakter ?> yang tidak tertutup di navbar.php\n";
} else {
    echo "✗ FAIL: Ditemukan karakter ?> yang tidak tertutup di navbar.php\n";
    echo "Preview (first 500 chars): " . substr($output3, 0, 500) . "\n";
}

echo "\n=== Test Selesai ===\n";
?>
