<?php
session_start();
include '../../config/database.php';

// Riwayat untuk kasir (role kasir)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header('Location: ../../php/login.php');
    exit;
}

$kasir_id = (int)$_SESSION['user_id'];
$alert = get_alert();

// Riwayat transaksi kasir (hanya milik kasir)
$res_transaksi = $conn->query("SELECT tp.id, tp.nomor_transaksi, tp.tanggal_transaksi, tp.total_bayar, tp.status, tp.metode_pembayaran
                                FROM transaksi_penjualan tp
                                WHERE tp.kasir_id = '$kasir_id'
                                ORDER BY tp.tanggal_transaksi DESC
                                LIMIT 30");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Kasir - KangSayur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container{max-width:1100px;margin:20px auto;padding:0 16px;}
        .header{margin-bottom:20px;}
        .header h1{margin:0 0 15px 0;color:#333;}
        table{width:100%;border-collapse:collapse;background:white;box-shadow:0 2px 4px rgba(0,0,0,0.1);font-size:13px;}
        th{background:#f5f5f5;padding:12px;text-align:left;font-weight:bold;border-bottom:2px solid #ddd;}
        td{padding:12px;border-bottom:1px solid #ddd;vertical-align:top;}
        tr:hover{background:#f9f9f9;}
        .no-data{text-align:center;padding:30px 10px;color:#666;}
        .back-link{display:inline-block;margin-bottom:10px;color:#2196F3;text-decoration:none;font-weight:bold;}
        .pill{display:inline-block;padding:4px 10px;border-radius:999px;background:#f3f3f3;font-weight:800;font-size:12px;}
    </style>
</head>
<body>
<div class="container">
    <a href="../kasir.php" class="back-link">← Kembali ke Dashboard</a>

    <div class="header">
        <h1>🕘 Riwayat - Kasir</h1>
    </div>

    <?php if ($alert): ?>
        <div class="alert <?php echo $alert['type']; ?>" style="margin-bottom:16px;padding:12px 14px;border-radius:8px;border:1px solid #ddd;">
            <?php echo $alert['message']; ?>
        </div>
    <?php endif; ?>

    <?php if ($res_transaksi && $res_transaksi->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Tanggal</th>
                    <th>Total Bayar</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $res_transaksi->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['nomor_transaksi']); ?></td>
                    <td><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($row['tanggal_transaksi']))); ?></td>
                    <td><?php echo format_rupiah((float)$row['total_bayar']); ?></td>
                    <td><?php echo htmlspecialchars($row['metode_pembayaran']); ?></td>
                    <td><span class="pill"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
                        <a class="btn btn-primary" style="padding:8px 12px;text-decoration:none;display:inline-block;" href="cetak_struk.php?transaksi_id=<?php echo (int)$row['id']; ?>">Cetak</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">Belum ada transaksi.</div>
    <?php endif; ?>
</div>
</body>
</html>

