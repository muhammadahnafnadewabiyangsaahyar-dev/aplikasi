<?php
/**
 * Debug Script: Check Session & CSRF Token
 * Untuk troubleshooting masalah import CSV
 */

session_start();

echo "========================================\n";
echo "DEBUG: Session & CSRF Token\n";
echo "========================================\n\n";

echo "1. Session Status:\n";
echo "   - Session ID: " . session_id() . "\n";
echo "   - Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "\n\n";

echo "2. Session Variables:\n";
if (isset($_SESSION['csrf_token'])) {
    echo "   ✅ CSRF Token exists\n";
    echo "   - Value: " . $_SESSION['csrf_token'] . "\n";
} else {
    echo "   ❌ CSRF Token NOT SET!\n";
    echo "   - Generating new token...\n";
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo "   - New token: " . $_SESSION['csrf_token'] . "\n";
}

echo "\n3. User Session:\n";
echo "   - User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "   - Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "   - Nama: " . ($_SESSION['nama_lengkap'] ?? 'NOT SET') . "\n";
echo "   - Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";

echo "\n4. All Session Data:\n";
print_r($_SESSION);

echo "\n========================================\n";
echo "Troubleshooting Tips:\n";
echo "========================================\n";
echo "1. Pastikan Anda sudah login sebagai admin\n";
echo "2. Pastikan session tidak expire\n";
echo "3. Coba refresh halaman whitelist.php\n";
echo "4. Coba logout dan login kembali\n";
echo "\n";
?>
