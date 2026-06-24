<?php
session_start();
include '../../config/database.php';

check_login();
check_role('admin');

$action = $_GET['action'] ?? 'list';
$alert = get_alert();

// Add/Edit Kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'add' || $action == 'edit')) {
    $nama_kategori = escape_string($_POST['nama_kategori']);
    $deskripsi = escape_string($_POST['deskripsi']);
    $status = escape_string($_POST['status']);
    
    if ($action == 'add') {
        $query = "INSERT INTO kategori_sayuran (nama_kategori, deskripsi, status) 
                 VALUES ('$nama_kategori', '$deskripsi', '$status')";
        
        if ($conn->query($query)) {
            set_alert("Kategori berhasil ditambahkan", 'success');
            header('Location: kategori.php');
            exit;
        } else {
            set_alert("Error: " . $conn->error, 'error');
        }
    } elseif ($action == 'edit') {
        $id = escape_string($_POST['id']);
        
        $query = "UPDATE kategori_sayuran SET nama_kategori = '$nama_kategori', 
                 deskripsi = '$deskripsi', status = '$status' WHERE id = '$id'";
        
        if ($conn->query($query)) {
            // Cascade: jika kategori di-nonaktifkan, sayuran di dalamnya ikut nonaktif; sebaliknya saat kategori aktif.
            $new_sayuran_status = ($status === 'nonaktif') ? 'nonaktif' : 'aktif';
            $conn->query("UPDATE sayuran SET status = '$new_sayuran_status' WHERE kategori_id = '$id'");

            set_alert("Kategori berhasil diperbarui", 'success');
            header('Location: kategori.php');
            exit;
        } else {
            set_alert("Error: " . $conn->error, 'error');
        }
    }
}

// Delete Kategori
if ($action == 'delete' && isset($_GET['id'])) {
    $id = escape_string($_GET['id']);
    
    // Check if kategori is used
    $check = $conn->query("SELECT COUNT(*) as count FROM sayuran WHERE kategori_id = '$id'");
    $result = $check->fetch_assoc();
    
    if ($result['count'] > 0) {
        set_alert("Kategori tidak dapat dihapus karena masih digunakan oleh produk", 'error');
        header('Location: kategori.php');
    } else {
        if ($conn->query("DELETE FROM kategori_sayuran WHERE id = '$id'")) {
            set_alert("Kategori berhasil dihapus", 'success');
            header('Location: kategori.php');
            exit;
        }
    }
}

// Get kategori for edit
$edit_kategori = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = escape_string($_GET['id']);
    $result = $conn->query("SELECT * FROM kategori_sayuran WHERE id = '$id'");
    $edit_kategori = $result->fetch_assoc();
}

// Get all kategori
$kategoris = $conn->query("SELECT * FROM kategori_sayuran ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Sayuran - KangSayur</title>
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
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
            <h1>📂 Kelola Kategori Sayuran</h1>
            <a href="kategori.php?action=add" class="btn-add">+ Tambah Kategori</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($kat = $kategoris->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $kat['nama_kategori']; ?></td>
                    <td><?php echo substr($kat['deskripsi'], 0, 50); ?></td>
                    <td>
                        <span class="badge <?php echo $kat['status']; ?>">
                            <?php echo ucfirst($kat['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="kategori.php?action=edit&id=<?php echo $kat['id']; ?>" class="btn-edit">Edit</a>
                            <a href="kategori.php?action=delete&id=<?php echo $kat['id']; ?>" class="btn-delete" onclick="return confirm('Yakin hapus kategori ini?')">Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
        
        <a href="kategori.php" class="back-link">← Kembali ke Daftar Kategori</a>
        
        <div class="form-container">
            <h2><?php echo $action == 'add' ? 'Tambah Kategori Baru' : 'Edit Kategori'; ?></h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_kategori['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nama_kategori">Nama Kategori *</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" value="<?php echo $edit_kategori['nama_kategori'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4"><?php echo $edit_kategori['deskripsi'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="aktif" <?php echo ($edit_kategori['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo ($edit_kategori['status'] ?? '') == 'nonaktif' ? 'selected' : ''; ?>>Non Aktif</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="kategori.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
        
        <?php endif; ?>
    </div>
</body>
</html>
