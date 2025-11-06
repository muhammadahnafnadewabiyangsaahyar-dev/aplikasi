<?php
// Set timezone untuk konsistensi PHP & MySQL
date_default_timezone_set('Asia/Makassar'); // WITA (UTC+8)

// ============================================================
// KONFIGURASI DATABASE BYETHOST
// ============================================================
$host = "sql100.byethost6.com";      // Host dari ByetHost panel
$dbname = "b6_40348133_kaori";        // Database Name
$username = "b6_40348133_kaori";      // Username (sama dengan database name)
$password = "6T3DIF3p@";              // Password database
$charset = "utf8mb4";

// ============================================================
// DATA SOURCE NAME (DSN)
// ============================================================
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// ============================================================
// PDO OPTIONS
// ============================================================
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Melempar exception jika ada error SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mengembalikan data sebagai associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Menggunakan prepared statements asli dari database
];

// ============================================================
// CREATE PDO CONNECTION
// ============================================================
try {
    // Buat instance PDO
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Set MySQL timezone to match PHP timezone (WITA = UTC+8)
    $pdo->exec("SET time_zone = '+08:00'");
    
    // UNCOMMENT untuk test koneksi (hapus setelah berhasil)
    // echo "✅ Koneksi database berhasil!<br>";
    // echo "Connected to: " . $dbname . " on " . $host . "<br>";
    
} catch (\PDOException $e) {
    // Tangani error koneksi
    // Di lingkungan produksi, jangan tampilkan detail error ke pengguna
    error_log("Koneksi Gagal: " . $e->getMessage());
    die("Koneksi ke database gagal. Silakan coba lagi nanti.");
}

// $pdo sekarang adalah variabel koneksi Anda
// NOTE: Closing tag dihilangkan untuk mencegah whitespace output (PSR standard)
