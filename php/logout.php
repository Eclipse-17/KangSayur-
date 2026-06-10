<?php
session_start();
include '../config/database.php';

// Log aktivitas logout
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $log_query = "INSERT INTO aktivitas_log (user_id, aktivitas, deskripsi, ip_address, user_agent) 
                 VALUES ('$user_id', 'LOGOUT', 'User logout dari sistem', '$ip_address', '$user_agent')";
    $conn->query($log_query);
}

// Hapus session
session_destroy();

// Redirect ke login
header('Location: ../public/index.php');
exit;
