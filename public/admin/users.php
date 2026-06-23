<?php
session_start();
include '../../config/database.php';

check_login();
check_role('admin');

$action = $_GET['action'] ?? 'list';
$alert = get_alert();

// Add/Edit User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'add' || $action == 'edit')) {
    $nama = escape_string($_POST['nama']);
    $email = escape_string($_POST['email']);
    $username = escape_string($_POST['username']);
    $password = isset($_POST['password']) && !empty($_POST['password']) ? escape_string($_POST['password']) : null; // Simpan tanpa hash
    $role = escape_string($_POST['role']);
    $status = escape_string($_POST['status']);
    
    if ($action == 'add') {
        if (!$password) {
            $alert_msg = "Password tidak boleh kosong";
            set_alert($alert_msg, 'error');
        } else {
            $query = "INSERT INTO users (nama, email, username, password, role, status) 
                     VALUES ('$nama', '$email', '$username', '$password', '$role', '$status')";
            
            if ($conn->query($query)) {
                set_alert("User berhasil ditambahkan", 'success');
                header('Location: users.php');
                exit;
            } else {
                set_alert("Error: " . $conn->error, 'error');
            }
        }
    } elseif ($action == 'edit') {
        $id = escape_string($_POST['id']);
        $password_part = $password ? ", password = '$password'" : '';
        
        $query = "UPDATE users SET nama = '$nama', email = '$email', username = '$username', 
                 role = '$role', status = '$status' $password_part WHERE id = '$id'";
        
        if ($conn->query($query)) {
            set_alert("User berhasil diperbarui", 'success');
            header('Location: users.php');
            exit;
        } else {
            set_alert("Error: " . $conn->error, 'error');
        }
    }
}

// Delete User
if ($action == 'delete' && isset($_GET['id'])) {
    $id = escape_string($_GET['id']);
    
    if ($conn->query("DELETE FROM users WHERE id = '$id'")) {
        set_alert("User berhasil dihapus", 'success');
        header('Location: users.php');
        exit;
    }
}

// Get user for edit
$edit_user = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = escape_string($_GET['id']);
    $result = $conn->query("SELECT * FROM users WHERE id = '$id'");
    $edit_user = $result->fetch_assoc();
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - KangSayur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
        }
        
        .btn-add {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-add:hover {
            background: #45a049;
        }
        
        .form-container {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge.admin {
            background: #d32f2f;
            color: white;
        }
        
        .badge.petugas {
            background: #ffc107;
            color: black;
        }
        
        .badge.kasir {
            background: #2196F3;
            color: white;
        }
        
        .badge.aktif {
            background: #4CAF50;
            color: white;
        }
        
        .badge.nonaktif {
            background: #f44336;
            color: white;
        }
        
        .action-btns {
            display: flex;
            gap: 5px;
        }
        
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .btn-edit:hover {
            background: #0b7dda;
        }
        
        .btn-delete:hover {
            background: #da190b;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #2196F3;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../admin.php" class="back-link">← Kembali ke Dashboard</a>
        <?php if ($alert): ?>
            <div class="alert <?php echo $alert['type']; ?>">
                <?php echo $alert['message']; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action == 'list'): ?>
        
        <div class="header">
            <h1>📋 Kelola User</h1>
            <a href="users.php?action=add" class="btn-add">+ Tambah User</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $user['nama']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td>
                        <span class="badge <?php echo str_replace('_', '', $user['role']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?php echo $user['status']; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn-edit">Edit</a>
                            <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
        
        <a href="users.php" class="back-link">← Kembali ke Daftar User</a>
        
        <div class="form-container">
            <h2><?php echo $action == 'add' ? 'Tambah User Baru' : 'Edit User'; ?></h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" value="<?php echo $edit_user['nama'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo $edit_user['email'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo $edit_user['username'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <?php echo $action == 'edit' ? '(Kosongkan jika tidak ingin mengubah)' : '*'; ?></label>
                    <input type="password" id="password" name="password" <?php echo $action == 'add' ? 'required' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" <?php echo ($edit_user['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="petugas_stok" <?php echo ($edit_user['role'] ?? '') == 'petugas_stok' ? 'selected' : ''; ?>>Petugas Stok</option>
                        <option value="kasir" <?php echo ($edit_user['role'] ?? '') == 'kasir' ? 'selected' : ''; ?>>Kasir</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="aktif" <?php echo ($edit_user['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo ($edit_user['status'] ?? '') == 'nonaktif' ? 'selected' : ''; ?>>Non Aktif</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="users.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
        
        <?php endif; ?>
    </div>
</body>
</html>
