<?php
session_start();

// Set temporary admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['full_name'] = 'Test Admin';
$_SESSION['email'] = 'admin@test.com';
$_SESSION['cabang_id'] = 1;

echo "Session set for testing. <a href='kalender.html'>Go to Calendar</a>";
?>
