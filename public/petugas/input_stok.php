<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_stok') {
    header('Location: ../../php/login.php');
    exit;
}

$alert = get_alert();
$petugas_id = $_SESSION['user_id'];

// Process input stok
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sayuran_id = escape_string($_POST['sayuran_id']);
    $jumlah_masuk = escape_string($_POST['jumlah_masuk']);
    $harga_perolehan = escape_string($_POST['harga_perolehan']);
    $tanggal_masuk = escape_string($_POST['tanggal_masuk']);
    $tanggal_kadaluarsa = escape_string($_POST['tanggal_kadaluarsa']);
    $supplier = escape_string($_POST['supplier']);
    $catatan = escape_string($_POST['catatan']);
    
    // Generate nomor referensi
    $nomor_ref = generate_kode('PSM', 'pengelolaan_stok_masuk', 'nomor_referensi');
    
    // Insert ke pengelolaan_stok_masuk
    $query = "INSERT INTO pengelolaan_stok_masuk 
             (nomor_referensi, sayuran_id, petugas_id, jumlah_masuk, harga_perolehan, 
              tanggal_masuk, tanggal_kadaluarsa, supplier, catatan, status)
             VALUES ('$nomor_ref', '$sayuran_id', '$petugas_id', '$jumlah_masuk', '$harga_perolehan',
             '$tanggal_masuk', '$tanggal_kadaluarsa', '$supplier', '$catatan', 'terima')";
    
    if ($conn->query($query)) {
        // Insert ke stok_sayuran dengan batch number = nomor referensi
        $query2 = "INSERT INTO stok_sayuran 
                  (sayuran_id, batch_number, jumlah_stok, harga_perolehan, tanggal_masuk, tanggal_kadaluarsa, status)
                  VALUES ('$sayuran_id', '$nomor_ref', '$jumlah_masuk', '$harga_perolehan', '$tanggal_masuk', '$tanggal_kadaluarsa', 'tersedia')";
        
        if ($conn->query($query2)) {
            set_alert("Stok masuk berhasil dicatat dengan nomor referensi: $nomor_ref", 'success');
            header('Location: daftar_stok.php');
            exit;
        } else {
            set_alert("Error saat insert ke stok_sayuran: " . $conn->error, 'error');
        }
    } else {
        set_alert("Error saat insert: " . $conn->error, 'error');
    }
}

// Get all sayuran untuk select
$sayurans = $conn->query("SELECT id, kode_sayuran, nama_sayuran FROM sayuran WHERE status = 'aktif' ORDER BY nama_sayuran ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Stok Masuk - KangSayur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .header {
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .form-container {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 14px;
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
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
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
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box p {
            margin: 0;
            color: #1565c0;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../petugas.php" class="back-link">← Kembali ke Dashboard</a>
        
        <div class="header">
            <h1>📥 Input Stok Masuk</h1>
        </div>
        
        <?php if ($alert): ?>
            <div class="alert <?php echo $alert['type']; ?>">
                <?php echo $alert['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <p>📌 Sistem FIFO: Stok yang masuk terlebih dahulu akan dikeluarkan terlebih dahulu pada saat penjualan.</p>
        </div>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="sayuran_id">Pilih Sayuran *</label>
                        <select id="sayuran_id" name="sayuran_id" required>
                            <option value="">-- Pilih Sayuran --</option>
                            <?php while ($say = $sayurans->fetch_assoc()): ?>
                                <option value="<?php echo $say['id']; ?>">
                                    [<?php echo $say['kode_sayuran']; ?>] <?php echo $say['nama_sayuran']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_masuk">Jumlah Masuk (Unit) *</label>
                        <input type="number" id="jumlah_masuk" name="jumlah_masuk" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_perolehan">Harga Perolehan (Rp) *</label>
                        <input type="number" id="harga_perolehan" name="harga_perolehan" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal_masuk">Tanggal Masuk *</label>
                        <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal_kadaluarsa">Tanggal Kadaluarsa</label>
                        <input type="date" id="tanggal_kadaluarsa" name="tanggal_kadaluarsa">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="supplier">Supplier</label>
                        <input type="text" id="supplier" name="supplier">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="catatan">Catatan</label>
                    <textarea id="catatan" name="catatan" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Stok Masuk</button>
                    <a href="../petugas.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
