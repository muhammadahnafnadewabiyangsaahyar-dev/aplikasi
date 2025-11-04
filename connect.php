<?php
// Set timezone untuk konsistensi PHP & MySQL
date_default_timezone_set('Asia/Makassar'); // WITA (UTC+8)

// Konfigurasi database
$host = "localhost";
$dbname = "aplikasi";
$username = "root";
$password = "";
$charset = "utf8mb4";

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Tambah unix_socket untuk CLI dan macOS XAMPP
if (php_sapi_name() === 'cli' || !file_exists('/tmp/mysql.sock')) {
    $dsn = "mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=$dbname;charset=$charset";
}

// Opsi untuk PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Melempar exception jika ada error SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mengembalikan data sebagai associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Menggunakan prepared statements asli dari database
];

try {
    // Buat instance PDO
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Set MySQL timezone to match PHP timezone
    $pdo->exec("SET time_zone = '+08:00'"); // WITA (UTC+8)
} catch (\PDOException $e) {
    // Tangani error koneksi
    // Di lingkungan produksi, jangan tampilkan detail error ke pengguna
    error_log("Koneksi Gagal: " . $e->getMessage());
    die("Koneksi ke database gagal. Silakan coba lagi nanti.");
}

// Tidak perlu mysqli_set_charset, sudah diatur di DSN.
// $pdo sekarang adalah variabel koneksi Anda.
// NOTE: Closing tag dihilangkan untuk mencegah whitespace output (PSR standard)