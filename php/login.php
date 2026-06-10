<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Query untuk check user
    $query = "SELECT * FROM users WHERE username = '$username' AND status = 'aktif'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password langsung (Teks Biasa)
        if ($password == $user['password']) {
            // Login sukses
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Log aktivitas login
            $user_id = $user['id'];
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            
            $log_query = "INSERT INTO aktivitas_log (user_id, aktivitas, deskripsi, ip_address, user_agent) 
                         VALUES ('$user_id', 'LOGIN', 'User berhasil login', '$ip_address', '$user_agent')";
            $conn->query($log_query);

            // Cek jika request datang dari fetch (AJAX)
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || isset($_POST['ajax'])) {
                echo json_encode(['success' => true, 'role' => $user['role']]);
                exit;
            }

            // Redirect standar untuk form submit biasa
            $redirects = [
                'admin' => '../public/admin.php',
                'petugas_stok' => '../public/petugas.php',
                'kasir' => '../public/kasir.php'
            ];
            header('Location: ' . ($redirects[$user['role']] ?? '../public/index.php'));
            exit;
        } else {
            $error = "Username atau password salah";
        }
    } else {
        $error = "Username tidak ditemukan";
    }

    if (isset($_POST['ajax'])) {
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }

    // Jika diakses langsung tanpa POST, arahkan ke index.php
    header('Location: ../public/index.php');
    exit;
} else {
    header('Location: ../public/index.php');
    exit;
}
