<?php
// Test untuk memastikan tidak ada output tak diinginkan di index.php

// Mulai output buffering
ob_start();

// Include file yang digunakan oleh index.php
include 'connect.php';

// Tangkap output
$output = ob_get_clean();

// Tampilkan hasil
if (empty($output)) {
    echo "✅ SUCCESS: Tidak ada output tak diinginkan dari connect.php\n";
} else {
    echo "❌ ERROR: Ada output tak diinginkan dari connect.php:\n";
    echo "Length: " . strlen($output) . " bytes\n";
    echo "Hex: " . bin2hex($output) . "\n";
    echo "Visible: " . var_export($output, true) . "\n";
}
