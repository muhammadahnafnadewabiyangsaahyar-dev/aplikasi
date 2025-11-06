<?php
// Simple test to see if jadwal_shift.php renders correctly
session_start();

// Set dummy session for testing
$_SESSION['user_id'] = 1;
$_SESSION['nama_lengkap'] = 'Test User';
$_SESSION['role'] = 'user';
$_SESSION['username'] = 'testuser';

// Redirect to actual page
header('Location: jadwal_shift.php');
exit;
