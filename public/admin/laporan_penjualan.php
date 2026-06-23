<?php
session_start();
include '../../config/database.php';

check_login();
check_role('admin');

// Get filter parameter
$bulan = escape_string($_GET['bulan'] ?? date('m'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));

// Normalisasi bulan jadi 2 digit
if (strlen($bulan) === 1) {
    $bulan = '0' . $bulan;
}

// Get laporan penjualan
$query = "SELECT lp.*, s.nama_sayuran, u.nama FROM laporan_penjualan lp
         JOIN sayuran s ON lp.sayuran_id = s.id
         JOIN users u ON lp.kasir_id = u.id
         WHERE MONTH(lp.tanggal_laporan) = '$bulan' AND YEAR(lp.tanggal_laporan) = '$tahun'
         ORDER BY lp.tanggal_laporan DESC";

$laparans = $conn->query($query);

// Get summary
$summary = $conn->query("SELECT 
                        COALESCE(SUM(lp.jumlah_terjual),0) as total_qty,
                        COALESCE(SUM(lp.total_penjualan),0) as total_penjualan,
                        COALESCE(SUM(lp.keuntungan),0) as total_keuntungan
                        FROM laporan_penjualan lp
                        WHERE MONTH(lp.tanggal_laporan) = '$bulan' AND YEAR(lp.tanggal_laporan) = '$tahun'")->fetch_assoc();

// Get months and years for dropdown
$months = array(
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
);

$years = array();
for ($y = date('Y') - 5; $y <= date('Y'); $y++) {
    $years[] = $y;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - KangSayur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 1200px;
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
        
        .filter-section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            max-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }
        
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn-filter {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-filter:hover {
            background: #45a049;
        }
        
        .btn-print {
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-print:hover {
            background: #0b7dda;
        }
        
        .summary-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .summary-card .label {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .summary-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #2e7d32;
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
        
        @media print {
            .filter-section, .back-link, .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../admin.php" class="back-link">← Kembali ke Dashboard</a>
        <?php include '../admin_nav.php'; ?>
        
        <div class="header">
            <h1>📊 Laporan Penjualan</h1>
        </div>
        
        <!-- Filter -->
        <form method="GET" class="filter-section">
            <div class="filter-group">
                <label for="bulan">Bulan</label>
                <select id="bulan" name="bulan">
                    <?php foreach ($months as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $bulan == $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="tahun">Tahun</label>
                <select id="tahun" name="tahun">
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $tahun == $year ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Filter</button>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ Cetak</button>
        </form>
        
        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-card">
                <div class="label">Total Qty Terjual</div>
                <div class="value"><?php echo $summary['total_qty'] ?? 0; ?> Unit</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Penjualan</div>
                <div class="value"><?php echo format_rupiah($summary['total_penjualan'] ?? 0); ?></div>
            </div>
            <div class="summary-card">
                <div class="label">Total Keuntungan</div>
                <div class="value"><?php echo format_rupiah($summary['total_keuntungan'] ?? 0); ?></div>
            </div>
        </div>
        
        <!-- Table -->
        <?php if ($laparans->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Kasir</th>
                    <th>Qty</th>
                    <th>Total Penjualan</th>
                    <th>Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($lap = $laparans->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo format_tanggal($lap['tanggal_laporan']); ?></td>
                    <td><?php echo $lap['nama_sayuran']; ?></td>
                    <td><?php echo $lap['nama']; ?></td>
                    <td><?php echo $lap['jumlah_terjual']; ?></td>
                    <td><?php echo format_rupiah($lap['total_penjualan']); ?></td>
                    <td><?php echo format_rupiah($lap['keuntungan']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">
            <p>Tidak ada data penjualan untuk periode yang dipilih</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
