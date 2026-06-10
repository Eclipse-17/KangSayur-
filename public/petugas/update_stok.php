<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_stok') {
    header('Location: ../../php/login.php');
    exit;
}

$alert = get_alert();
$petugas_id = $_SESSION['user_id'];

// Get sayuran for editing
$edit_sayuran = null;
if (isset($_GET['id'])) {
    $id = escape_string($_GET['id']);
    $result = $conn->query("SELECT * FROM sayuran WHERE id = '$id'");
    $edit_sayuran = $result->fetch_assoc();
}

// Process update stok
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sayuran_id = escape_string($_POST['sayuran_id']);
    $tipe_update = escape_string($_POST['tipe_update']);
    $jumlah_perubahan = escape_string($_POST['jumlah_perubahan']);
    $alasan = escape_string($_POST['alasan']);
    
    // Get current stok
    $current = $conn->query("SELECT SUM(jumlah_stok) as total FROM stok_sayuran WHERE sayuran_id = '$sayuran_id' AND status = 'tersedia'");
    $stok_awal = $current->fetch_assoc()['total'] ?? 0;
    
    // Calculate new stok
    $stok_akhir = $stok_awal;
    if ($tipe_update == 'penambahan') {
        $stok_akhir = $stok_awal + $jumlah_perubahan;
    } elseif ($tipe_update == 'pengurangan' || $tipe_update == 'rusak') {
        $stok_akhir = $stok_awal - $jumlah_perubahan;
    }
    
    // Insert ke update_stok_barang
    $query = "INSERT INTO update_stok_barang 
             (sayuran_id, petugas_id, tipe_update, jumlah_awal, jumlah_perubahan, jumlah_akhir, alasan, tanggal_update)
             VALUES ('$sayuran_id', '$petugas_id', '$tipe_update', '$stok_awal', '$jumlah_perubahan', '$stok_akhir', '$alasan', NOW())";
    
    if ($conn->query($query)) {
        // Update stok_sayuran based on tipe_update
        if ($tipe_update == 'rusak') {
            // Kurangi stok dari batch paling tua (FIFO)
            $stok_rusak = $conn->query("SELECT id, jumlah_stok FROM stok_sayuran WHERE sayuran_id = '$sayuran_id' AND status = 'tersedia' ORDER BY tanggal_masuk ASC LIMIT 1");
            
            if ($stok_rusak->num_rows > 0) {
                $batch = $stok_rusak->fetch_assoc();
                $sisa = $batch['jumlah_stok'] - $jumlah_perubahan;
                
                if ($sisa <= 0) {
                    $conn->query("UPDATE stok_sayuran SET status = 'habis' WHERE id = '{$batch['id']}'");
                } else {
                    $conn->query("UPDATE stok_sayuran SET jumlah_stok = '$sisa' WHERE id = '{$batch['id']}'");
                }
            }
        }
        
        set_alert("Stok berhasil diperbarui", 'success');
        header('Location: daftar_stok.php');
        exit;
    } else {
        set_alert("Error: " . $conn->error, 'error');
    }
}

// Get all sayuran
$sayurans = $conn->query("SELECT id, kode_sayuran, nama_sayuran FROM sayuran WHERE status = 'aktif' ORDER BY nama_sayuran ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Stok Barang - KangSayur</title>
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
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box p {
            margin: 0;
            color: #856404;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../petugas.php" class="back-link">← Kembali ke Dashboard</a>
        
        <div class="header">
            <h1>✏️ Update Stok Barang</h1>
        </div>
        
        <?php if ($alert): ?>
            <div class="alert <?php echo $alert['type']; ?>">
                <?php echo $alert['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <p>📌 Pencatatan perubahan stok untuk penyesuaian fisik, barang rusak, atau koreksi inventaris.</p>
        </div>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="sayuran_id">Pilih Sayuran *</label>
                        <select id="sayuran_id" name="sayuran_id" required>
                            <option value="">-- Pilih Sayuran --</option>
                            <?php while ($say = $sayurans->fetch_assoc()): ?>
                                <option value="<?php echo $say['id']; ?>" <?php echo ($edit_sayuran['id'] ?? '') == $say['id'] ? 'selected' : ''; ?>>
                                    [<?php echo $say['kode_sayuran']; ?>] <?php echo $say['nama_sayuran']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipe_update">Tipe Update *</label>
                        <select id="tipe_update" name="tipe_update" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="penambahan">Penambahan (Stok Naik)</option>
                            <option value="pengurangan">Pengurangan (Stok Turun)</option>
                            <option value="penyesuaian">Penyesuaian (Koreksi)</option>
                            <option value="rusak">Rusak (Barang Tidak Layak Jual)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jumlah_perubahan">Jumlah Perubahan (Unit) *</label>
                        <input type="number" id="jumlah_perubahan" name="jumlah_perubahan" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="alasan">Alasan/Keterangan *</label>
                    <textarea id="alasan" name="alasan" rows="4" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Update</button>
                    <a href="daftar_stok.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
