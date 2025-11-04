#!/bin/bash

echo "=== Testing Rekapabsen Page Output ==="
echo ""

# Simulate browser request
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi

echo "Fetching first 500 bytes of rekapabsen.php output..."
php -d display_errors=1 -r "
// Simulate session
\$_SESSION = array(
    'user_id' => 1,
    'role' => 'admin',
    'nama_lengkap' => 'Test Admin'
);

// Capture output
ob_start();
include 'rekapabsen.php';
\$output = ob_get_clean();

// Show first 500 chars
echo substr(\$output, 0, 500) . \"...\n\n\";

// Check for weird characters
if (preg_match('/}}\\s*\\?>/', \$output, \$matches)) {
    echo \"✗ FOUND: Weird characters detected!\n\";
    echo \"Match: \" . var_export(\$matches[0], true) . \"\n\";
} else {
    echo \"✓ PASS: No weird characters found\n\";
}
" 2>&1 | head -40

echo ""
echo "=== Test Complete ==="
