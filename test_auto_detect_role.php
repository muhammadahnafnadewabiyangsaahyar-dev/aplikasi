<?php
/**
 * Test Script: Auto-Detect Role dari Posisi
 * Untuk memverifikasi bahwa fungsi getRoleByPosisi bekerja dengan benar
 */

echo "========================================\n";
echo "TEST: Auto-Detect Role dari Posisi\n";
echo "========================================\n\n";

// Fungsi yang sama dengan di whitelist.php
function getRoleByPosisi($posisi) {
    $posisi_lower = strtolower(trim($posisi));
    $admin_positions = ['hr', 'finance', 'marketing', 'scm', 'akuntan', 'owner', 'superadmin'];
    return in_array($posisi_lower, $admin_positions) ? 'admin' : 'user';
}

// Test cases
$testCases = [
    // Admin positions (case insensitive)
    ['HR', 'admin'],
    ['hr', 'admin'],
    ['Hr', 'admin'],
    ['Finance', 'admin'],
    ['finance', 'admin'],
    ['FINANCE', 'admin'],
    ['Marketing', 'admin'],
    ['SCM', 'admin'],
    ['scm', 'admin'],
    ['Akuntan', 'admin'],
    ['Owner', 'admin'],
    ['Superadmin', 'admin'],
    ['SUPERADMIN', 'admin'],
    
    // User positions
    ['Barista', 'user'],
    ['BARISTA', 'user'],
    ['Kitchen', 'user'],
    ['kitchen', 'user'],
    ['Server', 'user'],
    ['Kasir', 'user'],
    ['Security', 'user'],
    ['Cleaning Service', 'user'],
    
    // Edge cases
    ['  HR  ', 'admin'], // dengan spasi
    ['', 'user'], // kosong
    ['Unknown Position', 'user'], // posisi tidak dikenal
];

$passed = 0;
$failed = 0;

echo "Running tests...\n\n";

foreach ($testCases as $index => $test) {
    $posisi = $test[0];
    $expectedRole = $test[1];
    $actualRole = getRoleByPosisi($posisi);
    
    $status = ($actualRole === $expectedRole) ? '✅ PASS' : '❌ FAIL';
    
    if ($actualRole === $expectedRole) {
        $passed++;
    } else {
        $failed++;
        echo "$status | Posisi: '$posisi' | Expected: $expectedRole | Got: $actualRole\n";
    }
}

echo "\n========================================\n";
echo "TEST RESULTS\n";
echo "========================================\n";
echo "Total Tests: " . count($testCases) . "\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";

if ($failed === 0) {
    echo "\n🎉 ALL TESTS PASSED!\n";
    echo "\nFungsi getRoleByPosisi bekerja dengan sempurna!\n";
} else {
    echo "\n⚠️ SOME TESTS FAILED!\n";
    echo "Silakan periksa kembali fungsi getRoleByPosisi.\n";
}

echo "\n========================================\n";
echo "ADMIN POSITIONS LIST\n";
echo "========================================\n";
$admin_positions = ['hr', 'finance', 'marketing', 'scm', 'akuntan', 'owner', 'superadmin'];
foreach ($admin_positions as $pos) {
    echo "- " . strtoupper($pos) . " → admin\n";
}

echo "\nSemua posisi lainnya → user\n";
echo "========================================\n";
