<?php
session_start();
include '../../config/database.php';

check_login();
check_role('admin');

$alert = get_alert();

// Tampilkan riwayat transaksi penjualan terbaru
$res_penjualan = $conn->query("SELECT 
        tp.id,
        tp.nomor_transaksi,
        tp.tanggal_transaksi,
        tp.total_bayar,
        tp.status,
        u.nama as kasir_nama
    FROM transaksi_penjualan tp
    JOIN users u ON u.id = tp.kasir_id
    ORDER BY tp.tanggal_transaksi DESC
    LIMIT 30");

// Tampilkan riwayat stok masuk (input stok) terbaru
$res_stok_masuk = $conn->query("SELECT 
        psm.id,
        psm.nomor_referensi,
        psm.tanggal_masuk,
        psm.jumlah_masuk,
        psm.harga_perolehan,
        psm.supplier,
        s.nama_sayuran,
        u.nama as petugas_nama
    FROM pengelolaan_stok_masuk psm
    JOIN sayuran s ON s.id = psm.sayuran_id
    JOIN users u ON u.id = psm.petugas_id
    WHERE psm.status = 'terima'
    ORDER BY psm.tanggal_masuk DESC
    LIMIT 30");

// Tampilkan riwayat update stok (rusak/pengurangan/penyesuaian) terbaru
$res_update_stok = $conn->query("SELECT 
        usb.id,
        usb.tanggal_update,
        usb.tipe_update,
        usb.jumlah_perubahan,
        usb.alasan,
        s.nama_sayuran,
        u.nama as petugas_nama
    FROM update_stok_barang usb
    JOIN sayuran s ON s.id = usb.sayuran_id
    JOIN users u ON u.id = usb.petugas_id
    ORDER BY usb.tanggal_update DESC
    LIMIT 30");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - Admin - KangSayur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container{max-width:1200px;margin:20px auto;padding:0 20px;}
        .header{margin-bottom:20px;}
        .header h1{margin:0 0 15px 0;color:#333;}
        .section{background:white;border:1px solid #ddd;border-radius:8px;padding:15px;margin-bottom:20px;}
        .section h2{margin:0 0 10px 0;font-size:16px;color:#222;}
        table{width:100%;border-collapse:collapse;background:white;box-shadow:0 2px 4px rgba(0,0,0,0.1);font-size:13px;}
        th{background:#f5f5f5;padding:12px;text-align:left;font-weight:bold;border-bottom:2px solid #ddd;}
        td{padding:12px;border-bottom:1px solid #ddd;vertical-align:top;}
        tr:hover{background:#f9f9f9;}
        .muted{color:#666;font-size:12px;}
        .row-actions a{display:inline-block;margin-right:8px;color:#2196F3;text-decoration:none;font-weight:bold;font-size:13px;}
        .no-data{text-align:center;padding:30px 10px;color:#666;}
    </style>
</head>
<body>
<div class="container">
    <a href="../admin.php" class="back-link">← Kembali ke Dashboard</a>
    <?php include '../admin_nav.php'; ?>

    <div class="header">
        <h1>🕘 Riwayat</h1>
    </div>

    <?php if ($alert): ?>
        <div class="alert <?php echo $alert['type']; ?>" style="margin-bottom:16px;">
            <?php echo $alert['message']; ?>
        </div>
    <?php endif; ?>

    <div class="section">
        <h2>Riwayat Penjualan (terbaru)</h2>
        <?php if ($res_penjualan && $res_penjualan->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor Transaksi</th>
                        <th>Kasir</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res_penjualan->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo format_tanggal($row['tanggal_transaksi']); ?></td>
                            <td>#<?php echo htmlspecialchars($row['nomor_transaksi']); ?></td>
                            <td><?php echo htmlspecialchars($row['kasir_nama'] ?? '-'); ?></td>
                            <td><?php echo format_rupiah((float)$row['total_bayar']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">Belum ada riwayat penjualan.</div>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Riwayat Stok Masuk (input stok) terbaru</h2>
        <?php if ($res_stok_masuk && $res_stok_masuk->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Masuk</th>
                        <th>Nomor Referensi</th>
                        <th>Sayuran</th>
                        <th>Jumlah</th>
                        <th>Harga Perolehan</th>
                        <th>Supplier</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res_stok_masuk->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo format_tanggal($row['tanggal_masuk']); ?></td>
                            <td><?php echo htmlspecialchars($row['nomor_referensi']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_sayuran']); ?></td>
                            <td><?php echo (float)$row['jumlah_masuk']; ?></td>
                            <td><?php echo format_rupiah((float)$row['harga_perolehan']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($row['supplier'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['petugas_nama'] ?? '-'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">Belum ada riwayat stok masuk.</div>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Riwayat Update Stok (pengurangan/rusak/penyesuaian) terbaru</h2>
        <?php if ($res_update_stok && $res_update_stok->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Update</th>
                        <th>Sayuran</th>
                        <th>Tipe</th>
                        <th>Perubahan</th>
                        <th>Alasan</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res_update_stok->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo format_tanggal($row['tanggal_update']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_sayuran']); ?></td>
                            <td><?php echo htmlspecialchars($row['tipe_update']); ?></td>
                            <td><?php echo (float)$row['jumlah_perubahan']; ?></td>
                            <td class="muted"><?php echo htmlspecialchars($row['alasan'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['petugas_nama'] ?? '-'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">Belum ada riwayat update stok.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

