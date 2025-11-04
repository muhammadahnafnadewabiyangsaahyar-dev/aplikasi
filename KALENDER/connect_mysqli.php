<?php
// Database connection for MySQLi
$host = "localhost";
$username = "root";
$password = "";
$database = "aplikasi";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Set timezone
$conn->query("SET time_zone = '+08:00'");
?>
