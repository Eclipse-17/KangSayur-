<?php
// ===================================================================
// Database Configuration - KangSayur System
// ===================================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kangsayur_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Define BASE_URL global
if (!defined('BASE_URL')) {
    define('BASE_URL', '/KangSayur/');
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Function to escape string
function escape_string($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

// Function untuk format rupiah
function format_rupiah($nominal) {
    return "Rp " . number_format($nominal, 0, ',', '.');
}

// Function untuk format tanggal
function format_tanggal($tanggal) {
    $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Function untuk generate kode unik
function generate_kode($prefix, $table, $column) {
    global $conn;
    
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING($column, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) as max_code FROM $table WHERE $column LIKE '$prefix%'");
    $row = $result->fetch_assoc();
    $max_code = $row['max_code'] ?? 0;
    
    return $prefix . str_pad($max_code + 1, 4, '0', STR_PAD_LEFT);
}

// Check if user is logged in
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'public/index.php');
        exit;
    }
}

// Check if user has specific role
function check_role($required_role) {
    if ($_SESSION['role'] !== $required_role) {
        header('Location: ' . BASE_URL . 'public/index.php');
        exit;
    }
}

// Alert message handler
function set_alert($message, $type = 'success') {
    $_SESSION['alert'] = array(
        'message' => $message,
        'type' => $type // success, error, warning, info
    );
}

function get_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}
?>
