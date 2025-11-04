<?php
// Test script untuk verifikasi token reset password
include 'connect.php';

echo "=== TEST RESET PASSWORD TOKEN ===\n\n";

// 1. Generate token seperti di forgot_password.php (FIXED VERSION)
$user_id = 8; // User ID yang ada di database
$token = bin2hex(random_bytes(32));

echo "1. Generating token...\n";
echo "   Token: " . substr($token, 0, 20) . "...\n";

// 2. Simpan token dengan DATE_ADD (MySQL's NOW + 1 hour)
$stmt = $pdo->prepare('INSERT INTO reset_password (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
$stmt->execute([$user_id, $token]);
echo "   Token saved to database\n\n";

// 3. Query data yang baru disimpan
$stmt2 = $pdo->prepare('SELECT *, NOW() as current_db_time FROM reset_password WHERE token = ?');
$stmt2->execute([$token]);
$row = $stmt2->fetch(PDO::FETCH_ASSOC);

echo "2. Token data in database:\n";
echo "   User ID: " . $row['user_id'] . "\n";
echo "   Created at: " . $row['created_at'] . "\n";
echo "   Expires at: " . $row['expires_at'] . "\n";
echo "   Current time: " . $row['current_db_time'] . "\n";
echo "   Used: " . $row['used'] . "\n\n";

// 4. Validasi seperti di reset_password.php
echo "3. Validation check:\n";
if ($row['used'] == 1) {
    echo "   ❌ Token already used\n";
} elseif ($row['expires_at'] <= $row['current_db_time']) {
    echo "   ❌ Token expired\n";
    echo "   Expires: " . $row['expires_at'] . "\n";
    echo "   Current: " . $row['current_db_time'] . "\n";
} else {
    echo "   ✅ Token is VALID!\n";
    echo "   Time until expiry: " . (strtotime($row['expires_at']) - strtotime($row['current_db_time'])) . " seconds\n";
}

echo "\n4. Reset link:\n";
$reset_link = "http://localhost/aplikasi/reset_password.php?token=" . $token;
echo "   " . $reset_link . "\n";
echo "\n=== TEST COMPLETED ===\n";
?>
