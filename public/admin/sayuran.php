<?php
session_start();
include '../../config/database.php';

check_login();
check_role('admin');

$action = $_GET['action'] ?? 'list';
$alert = get_alert();

// Add/Edit Sayuran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($action == 'add' || $action == 'edit')) {
    $kode_sayuran = escape_string($_POST['kode_sayuran']);
    $nama_sayuran = escape_string($_POST['nama_sayuran']);
    $kategori_id = escape_string($_POST['kategori_id']);
    $harga_beli = escape_string($_POST['harga_beli']);
    $harga_jual = escape_string($_POST['harga_jual']);
    $satuan = escape_string($_POST['satuan']);
    $stok_minimum = escape_string($_POST['stok_minimum']);
    $deskripsi = escape_string($_POST['deskripsi']);
    $status = escape_string($_POST['status']);
    
    if ($action == 'add') {
        $query = "INSERT INTO sayuran (kode_sayuran, nama_sayuran, kategori_id, harga_beli, harga_jual, 
                 satuan, stok_minimum, deskripsi, status) 
                 VALUES ('$kode_sayuran', '$nama_sayuran', '$kategori_id', '$harga_beli', '$harga_jual', 
                 '$satuan', '$stok_minimum', '$deskripsi', '$status')";
        
        if ($conn->query($query)) {
            set_alert("Sayuran berhasil ditambahkan", 'success');
            header('Location: sayuran.php');
            exit;
        } else {
            set_alert("Error: " . $conn->error, 'error');
        }
    } elseif ($action == 'edit') {
        $id = escape_string($_POST['id']);
        
        $query = "UPDATE sayuran SET kode_sayuran = '$kode_sayuran', nama_sayuran = '$nama_sayuran', 
                 kategori_id = '$kategori_id', harga_beli = '$harga_beli', harga_jual = '$harga_jual', 
                 satuan = '$satuan', stok_minimum = '$stok_minimum', deskripsi = '$deskripsi', 
                 status = '$status' WHERE id = '$id'";
        
        if ($conn->query($query)) {
            set_alert("Sayuran berhasil diperbarui", 'success');
            header('Location: sayuran.php');
            exit;
        } else {
            set_alert("Error: " . $conn->error, 'error');
        }
    }
}

// Delete Sayuran
if ($action == 'delete' && isset($_GET['id'])) {
    $id = escape_string($_GET['id']);
    
    if ($conn->query("DELETE FROM sayuran WHERE id = '$id'")) {
        set_alert("Sayuran berhasil dihapus", 'success');
        header('Location: sayuran.php');
        exit;
    }
}

$edit_sayuran = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = escape_string($_GET['id']);
    $result = $conn->query("SELECT * FROM sayuran WHERE id = '$id'");
    $edit_sayuran = $result->fetch_assoc();
}

// Get all sayuran with kategori
$sayurans = $conn->query("SELECT s.*, k.nama_kategori FROM sayuran s 
                         JOIN kategori_sayuran k ON s.kategori_id = k.id 
                         ORDER BY s.id DESC");

// Get all kategori for select
$kategoris = $conn->query("SELECT * FROM kategori_sayuran WHERE status = 'aktif'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Sayuran - KangSayur</title>
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
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
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
            font-size: 13px;
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
            font-size: 11px;
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
            font-size: 11px;
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
            <h1>🥕 Data Sayuran</h1>
            <a href="sayuran.php?action=add" class="btn-add">+ Tambah Sayuran</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Sayuran</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Satuan</th>
                    <th>Min Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($say = $sayurans->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $say['kode_sayuran']; ?></td>
                    <td><?php echo $say['nama_sayuran']; ?></td>
                    <td><?php echo $say['nama_kategori']; ?></td>
                    <td><?php echo format_rupiah($say['harga_beli']); ?></td>
                    <td><?php echo format_rupiah($say['harga_jual']); ?></td>
                    <td><?php echo $say['satuan']; ?></td>
                    <td><?php echo $say['stok_minimum']; ?></td>
                    <td>
                        <span class="badge <?php echo $say['status']; ?>">
                            <?php echo ucfirst($say['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="sayuran.php?action=edit&id=<?php echo $say['id']; ?>" class="btn-edit">Edit</a>
                            <a href="sayuran.php?action=delete&id=<?php echo $say['id']; ?>" class="btn-delete" onclick="return confirm('Yakin hapus sayuran ini?')">Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
        
        <a href="sayuran.php" class="back-link">← Kembali ke Daftar Sayuran</a>
        
        <div class="form-container">
            <h2><?php echo $action == 'add' ? 'Tambah Sayuran Baru' : 'Edit Sayuran'; ?></h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_sayuran['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kode_sayuran">Kode Sayuran *</label>
                        <input type="text" id="kode_sayuran" name="kode_sayuran" value="<?php echo $edit_sayuran['kode_sayuran'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_sayuran">Nama Sayuran *</label>
                        <input type="text" id="nama_sayuran" name="nama_sayuran" value="<?php echo $edit_sayuran['nama_sayuran'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kategori_id">Kategori *</label>
                        <select id="kategori_id" name="kategori_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($kat = $kategoris->fetch_assoc()): ?>
                                <option value="<?php echo $kat['id']; ?>" <?php echo ($edit_sayuran['kategori_id'] ?? '') == $kat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="satuan">Satuan *</label>
                        <input type="text" id="satuan" name="satuan" value="<?php echo $edit_sayuran['satuan'] ?? 'kg'; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="harga_beli">Harga Beli (Rp) *</label>
                        <input type="number" id="harga_beli" name="harga_beli" step="0.01" value="<?php echo $edit_sayuran['harga_beli'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_jual">Harga Jual (Rp) *</label>
                        <input type="number" id="harga_jual" name="harga_jual" step="0.01" value="<?php echo $edit_sayuran['harga_jual'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="stok_minimum">Stok Minimum *</label>
                        <input type="number" id="stok_minimum" name="stok_minimum" value="<?php echo $edit_sayuran['stok_minimum'] ?? '10'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="aktif" <?php echo ($edit_sayuran['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="nonaktif" <?php echo ($edit_sayuran['status'] ?? '') == 'nonaktif' ? 'selected' : ''; ?>>Non Aktif</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3"><?php echo $edit_sayuran['deskripsi'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="sayuran.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
        
        <?php endif; ?>
    </div>
</body>
</html>
