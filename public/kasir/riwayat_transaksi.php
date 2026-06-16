<?php
session_start();
include '../../config/database.php';

check_login();
check_role('kasir');

$kasir_id = (int)$_SESSION['user_id'];

// Optional filter by status/date
$status = $_GET['status'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$where = " WHERE tp.kasir_id = '$kasir_id' ";

if ($status !== '') {
    $status = escape_string($status);
    $where .= " AND tp.status = '$status' ";
}
if ($from !== '') {
    $from = escape_string($from);
    $where .= " AND DATE(tp.tanggal_transaksi) >= '$from' ";
}
if ($to !== '') {
    $to = escape_string($to);
    $where .= " AND DATE(tp.tanggal_transaksi) <= '$to' ";
}

$sql = "SELECT tp.id, tp.nomor_transaksi, tp.tanggal_transaksi, tp.total_harga, tp.diskon, tp.total_bayar, tp.metode_pembayaran, tp.status
        FROM transaksi_penjualan tp
        $where
        ORDER BY tp.tanggal_transaksi DESC";

$res = $conn->query($sql);

$alert = get_alert();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Kasir</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container{max-width:1100px;margin:18px auto;padding:0 16px;}
        .back-link{display:inline-block;margin-bottom:14px;color:#2196F3;text-decoration:none;}
        .card{background:#fff;border:1px solid #e9e9e9;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);}
        table{width:100%;border-collapse:collapse;margin-top:10px;}
        th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;font-size:14px;vertical-align:top;}
        th{background:#fafafa;}
        .alert{padding:12px 14px;border-radius:8px;margin-bottom:12px;}
        .alert.success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert.error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        .btn{padding:10px 14px;border:none;border-radius:8px;cursor:pointer;font-weight:800;display:inline-block;text-decoration:none;}
        .btn-primary{background:#4CAF50;color:#fff;}
        .btn-secondary{background:#6c757d;color:#fff;}
        .btn-danger{background:#dc3545;color:#fff;}
        .filter-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;}
        @media(max-width:800px){.filter-grid{grid-template-columns:1fr;}}
        .field label{display:block;font-weight:700;font-size:13px;margin-bottom:6px;color:#333;}
        .field input,.field select{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px;box-sizing:border-box;}
        .actions{white-space:nowrap;}
        .pill{display:inline-block;padding:4px 10px;border-radius:999px;background:#f3f3f3;font-weight:800;font-size:12px;}
    </style>
</head>
<body>
    <div class="container">
        <a href="../kasir.php" class="back-link">← Kembali ke Dashboard</a>

        <?php if ($alert): ?>
            <div class="alert <?php echo $alert['type']; ?>"><?php echo $alert['message']; ?></div>
        <?php endif; ?>

        <div class="card" style="margin-bottom:14px;">
            <h2 style="margin:0 0 10px 0;">🧾 Riwayat Transaksi</h2>
            <form method="GET" action="">
                <div class="filter-grid">
                    <div class="field">
                        <label>Status</label>
                        <select name="status">
                            <option value="" <?php echo ($status==='')?'selected':''; ?>>Semua</option>
                            <option value="selesai" <?php echo ($status==='selesai')?'selected':''; ?>>Selesai</option>
                            <option value="batal" <?php echo ($status==='batal')?'selected':''; ?>>Batal</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Dari (Tanggal)</label>
                        <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" />
                    </div>
                    <div class="field">
                        <label>Sampai (Tanggal)</label>
                        <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" />
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="riwayat_transaksi.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="card">
            <?php if ($res && $res->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th class="actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($t = $res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:900;">#<?php echo htmlspecialchars($t['nomor_transaksi']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($t['tanggal_transaksi']))); ?></td>
                                <td>
                                    <?php echo format_rupiah((float)$t['total_harga']); ?>
                                    <?php if ((float)$t['diskon'] > 0): ?>
                                        <div style="color:#666;font-size:12px;">Diskon: <?php echo format_rupiah((float)$t['diskon']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight:900;">(<?php echo htmlspecialchars($t['status']); ?>) <?php echo format_rupiah((float)$t['total_bayar']); ?></td>
                                <td><?php echo htmlspecialchars($t['metode_pembayaran']); ?></td>
                                <td><span class="pill"><?php echo htmlspecialchars($t['status']); ?></span></td>
                                <td class="actions">
                                    <a class="btn btn-primary" style="padding:8px 12px;" href="cetak_struk.php?transaksi_id=<?php echo (int)$t['id']; ?>">Cetak</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="margin:0;color:#666;">Belum ada transaksi.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

