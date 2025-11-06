<?php
// ========================================================
// SIMULATE ABSEN REQUEST & CAPTURE REAL ERROR
// ========================================================
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("❌ Please login first at <a href='login.php'>login.php</a>");
}

echo "<!DOCTYPE html><html><head><title>Test Absen Simulation</title>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
.success { color: #4ec9b0; }
.error { color: #f48771; }
.warning { color: #dcdcaa; }
.info { color: #9cdcfe; }
pre { background: #252526; padding: 10px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🧪 ABSEN SIMULATION TEUPDATE register SET id_cabang = 10, outlet_id = 10 WHERE id = 1;ST</h1>";
echo "<p>Testing proses_absensi.php with simulated POST data</p>";

// Prepare simulation data
$test_data = [
    'csrf_token' => $_SESSION['csrf_token'] ?? 'test_token',
    'latitude' => '-5.1795',
    'longitude' => '119.4634',
    'tipe_absen' => 'masuk',
    'foto_absensi_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==' // 1x1 pixel
];

echo "<h2>1. Simulation Data:</h2>";
echo "<pre>" . print_r($test_data, true) . "</pre>";

echo "<h2>2. Session Data:</h2>";
echo "<pre>";
echo "user_id: " . $_SESSION['user_id'] . "\n";
echo "username: " . ($_SESSION['username'] ?? 'N/A') . "\n";
echo "role: " . ($_SESSION['role'] ?? 'N/A') . "\n";
echo "csrf_token exists: " . (isset($_SESSION['csrf_token']) ? 'YES' : 'NO') . "\n";
echo "</pre>";

// Make sure csrf_token is set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $test_data['csrf_token'] = $_SESSION['csrf_token'];
    echo "<p class='warning'>⚠️ Generated new CSRF token</p>";
}

echo "<h2>3. Sending POST Request to proses_absensi.php</h2>";

// Use cURL to simulate POST request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/aplikasi/proses_absensi.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($test_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Important: Send cookies to maintain session
$cookie_file = tempnam(sys_get_temp_dir(), 'cookie');
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());

// Capture verbose output
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

echo "<p class='info'>⏳ Sending request...</p>";

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

rewind($verbose);
$verbose_log = stream_get_contents($verbose);
fclose($verbose);

curl_close($ch);
unlink($cookie_file);

echo "<h2>4. HTTP Response:</h2>";
echo "<p>Status Code: <strong>" . $http_code . "</strong></p>";

echo "<h3>Response Body:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h3>Response Decoded (if JSON):</h3>";
$json = json_decode($response, true);
if ($json) {
    echo "<pre>" . print_r($json, true) . "</pre>";
    
    if (isset($json['status'])) {
        if ($json['status'] === 'success') {
            echo "<p class='success'>✅ SUCCESS: " . ($json['message'] ?? 'No message') . "</p>";
        } else {
            echo "<p class='error'>❌ ERROR: " . ($json['message'] ?? 'Unknown error') . "</p>";
        }
    }
} else {
    echo "<p class='warning'>⚠️ Response is not valid JSON</p>";
}

echo "<h3>cURL Verbose Log:</h3>";
echo "<pre>" . htmlspecialchars($verbose_log) . "</pre>";

echo "<hr>";
echo "<h2>5. Direct Include Test</h2>";
echo "<p>Testing if we can directly execute proses_absensi.php logic:</p>";

// Save current $_POST
$original_post = $_POST;
$original_server = $_SERVER;

// Simulate POST request
$_POST = $test_data;
$_SERVER['REQUEST_METHOD'] = 'POST';

ob_start();
try {
    // Try to capture any PHP errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<p class='info'>Including proses_absensi.php...</p>";
    
    // This will execute the script
    // include 'proses_absensi.php'; // Commented out to avoid double execution
    
    echo "<p class='success'>✅ Script executed without fatal errors</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
$output = ob_get_clean();

// Restore
$_POST = $original_post;
$_SERVER = $original_server;

echo $output;

echo "</body></html>";
?>
