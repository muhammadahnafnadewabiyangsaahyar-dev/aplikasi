<?php
// Test untuk memastikan tidak ada output tak diinginkan

// Mulai output buffering
ob_start();

// Include file yang digunakan oleh rekapabsen.php
include 'connect.php';

define('INCLUDED_FROM_WEB', true);
include 'calculate_status_kehadiran.php';

// Tangkap output
$output = ob_get_clean();

// Tampilkan hasil
if (empty($output)) {
    echo "✅ SUCCESS: Tidak ada output tak diinginkan dari file include\n";
} else {
    echo "❌ ERROR: Ada output tak diinginkan:\n";
    echo "Length: " . strlen($output) . " bytes\n";
    echo "Hex: " . bin2hex($output) . "\n";
    echo "Visible: " . var_export($output, true) . "\n";
}
