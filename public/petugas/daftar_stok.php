<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_stok') {
    header('Location: ../../php/login.php');
    exit;
}

// Get all stok with sayuran info
$query = "SELECT s.*, k.nama_kategori, 
          (SELECT SUM(jumlah_stok) FROM stok_sayuran WHERE sayuran_id = s.id AND status = 'tersedia') as stok_tersedia,
          (SELECT MIN(tanggal_masuk) FROM stok_sayuran WHERE sayuran_id = s.id AND status = 'tersedia') as tanggal_masuk_terlama
          FROM sayuran s
          JOIN kategori_sayuran k ON s.kategori_id = k.id
          WHERE s.status = 'aktif'
          ORDER BY s.nama_sayuran ASC";

$sayurans = $conn->query($query);

// Get total stok
$total_result = $conn->query("SELECT SUM(jumlah_stok) as total FROM stok_sayuran WHERE status = 'tersedia'");
$total_stok = $total_result->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Stok - KangSayur</title>
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
        
        .summary-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .summary-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #2e7d32;
        }
        
        .summary-card .label {
            color: #666;
            margin-top: 5px;
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
            border-bottom: 2px solid #ddd;
            font-weight: bold;
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
        
        .badge.aman {
            background: #4CAF50;
            color: white;
        }
        
        .badge.menipis {
            background: #ff9800;
            color: white;
        }
        
        .badge.habis {
            background: #f44336;
            color: white;
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
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../petugas.php" class="back-link">← Kembali ke Dashboard</a>
        
        <div class="header">
            <h1>📋 Daftar Stok Sayuran</h1>
            <a href="input_stok.php" class="btn-add">+ Input Stok Baru</a>
        </div>
        
        <div class="summary-card">
            <div class="value"><?php echo $total_stok; ?></div>
            <div class="label">Total Stok Tersedia (Unit)</div>
        </div>
        
        <?php if ($sayurans->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Sayuran</th>
                    <th>Kategori</th>
                    <th>Stok Tersedia</th>
                    <th>Min Stok</th>
                    <th>Harga Beli</th>
                    <th>Tanggal Masuk Terlama (FIFO)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($say = $sayurans->fetch_assoc()): 
                    $stok = $say['stok_tersedia'] ?? 0;
                    $min = $say['stok_minimum'];
                    
                    if ($stok == 0) {
                        $status = 'habis';
                        $status_label = 'Habis';
                    } elseif ($stok <= $min) {
                        $status = 'menipis';
                        $status_label = 'Menipis';
                    } else {
                        $status = 'aman';
                        $status_label = 'Aman';
                    }
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $say['kode_sayuran']; ?></td>
                    <td><?php echo $say['nama_sayuran']; ?></td>
                    <td><?php echo $say['nama_kategori']; ?></td>
                    <td><?php echo $stok; ?></td>
                    <td><?php echo $min; ?></td>
                    <td><?php echo format_rupiah($say['harga_beli']); ?></td>
                    <td><?php echo $say['tanggal_masuk_terlama'] ? format_tanggal($say['tanggal_masuk_terlama']) : '-'; ?></td>
                    <td>
                        <span class="badge <?php echo $status; ?>">
                            <?php echo $status_label; ?>
                        </span>
                    </td>
                    <td>
                        <a href="update_stok.php?id=<?php echo $say['id']; ?>" style="color: #2196F3; text-decoration: none;">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">
            <p>Tidak ada data stok</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
