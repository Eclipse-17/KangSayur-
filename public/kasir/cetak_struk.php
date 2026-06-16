<?php
session_start();
include '../../config/database.php';

check_login();
check_role('kasir');

$transaksi_id = isset($_GET['transaksi_id']) ? (int)$_GET['transaksi_id'] : 0;
if ($transaksi_id <= 0) {
    header('Location: riwayat_transaksi.php');
    exit;
}

// Fetch transaksi header (only this kasir)
$kasir_id = (int)$_SESSION['user_id'];
$sql_header = "SELECT tp.*, n.nomor_nota, n.tanggal_nota, n.customer_name, n.cashier_name, n.items_detail, n.payment_method
                FROM transaksi_penjualan tp
                JOIN nota_transaksi n ON n.transaksi_id = tp.id
                WHERE tp.id = '$transaksi_id' AND tp.kasir_id = '$kasir_id' AND n.status = 'aktif'
                LIMIT 1";
$hres = $conn->query($sql_header);
if (!$hres || $hres->num_rows === 0) {
    set_alert('Transaksi tidak ditemukan.', 'error');
    header('Location: riwayat_transaksi.php');
    exit;
}
$header = $hres->fetch_assoc();

$items_detail = [];
if (!empty($header['items_detail'])) {
    $tmp = json_decode($header['items_detail'], true);
    if (is_array($tmp)) $items_detail = $tmp;
}

function qr_escape($v) {
    return htmlspecialchars((string)$v);
}

// update printed count (best-effort)
$printed_query = "UPDATE nota_transaksi
                   SET printed_count = printed_count + 1,
                       last_printed_at = NOW()
                   WHERE transaksi_id = '$transaksi_id'";
$conn->query($printed_query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cetak Struk - KangSayur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body{background:#fafafa;}
        .receipt-wrap{max-width:520px;margin:18px auto;padding:0 14px;}
        .receipt{background:#fff;border:1px solid #e9e9e9;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);}
        .receipt h2{margin:0 0 6px 0;}
        .muted{color:#666;font-size:12px;}
        .row{display:flex;justify-content:space-between;gap:10px;margin-top:6px;}
        .divider{height:1px;background:#eee;margin:12px 0;}
        table{width:100%;border-collapse:collapse;}
        th,td{padding:8px 0;border-bottom:1px dashed #eee;font-size:13px;vertical-align:top;}
        th{color:#333;font-weight:900;text-transform:uppercase;letter-spacing:.02em;font-size:11px;}
        .total{font-size:16px;font-weight:1000;margin-top:6px;}
        .print-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:14px;}
        @media print{ .print-actions{display:none;} .receipt-wrap{margin:0;max-width:none;} body{background:#fff;} }
        .center{text-align:center;}
    </style>
</head>
<body>
<div class="receipt-wrap">
    <div class="print-actions">
        <a class="btn btn-secondary" style="text-decoration:none;" href="riwayat_transaksi.php">← Kembali</a>
        <button class="btn btn-primary" onclick="window.print()">🖨️ Cetak</button>
    </div>

    <div class="receipt">
        <div class="center">
            <div class="logo-icons" style="margin-bottom:6px;">🥬 🧾 🧄</div>
            <h2>KangSayur</h2>
            <div class="muted">Nota Transaksi</div>
        </div>

        <div class="divider"></div>

        <div class="row"><div class="muted">Nomor Nota</div><div><?php echo qr_escape($header['nomor_nota']); ?></div></div>
        <div class="row"><div class="muted">Tanggal</div><div><?php echo qr_escape($header['tanggal_nota']); ?></div></div>
        <div class="row"><div class="muted">Kasir</div><div><?php echo qr_escape($header['cashier_name']); ?></div></div>
        <?php if (!empty($header['customer_name'])): ?>
            <div class="row"><div class="muted">Customer</div><div><?php echo qr_escape($header['customer_name']); ?></div></div>
        <?php endif; ?>
        <div class="row"><div class="muted">Metode</div><div><?php echo qr_escape($header['payment_method']); ?></div></div>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="center">Qty</th>
                    <th class="center">Harga</th>
                    <th class="center">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items_detail)): ?>
                <tr><td colspan="4" class="muted">Tidak ada detail.</td></tr>
            <?php else: ?>
                <?php foreach ($items_detail as $it):
                    $sid = (int)($it['sayuran_id'] ?? 0);
                    $qty = (float)($it['jumlah'] ?? 0);
                    $harga = (float)($it['harga_satuan'] ?? 0);
                    $subtotal = (float)($it['subtotal'] ?? ($qty * $harga));

                    $sn = '-';
                    $qsn = $conn->query("SELECT nama_sayuran FROM sayuran WHERE id = '$sid' LIMIT 1");
                    if ($qsn && $qsn->num_rows > 0) $sn = $qsn->fetch_assoc()['nama_sayuran'];
                ?>
                    <tr>
                        <td>
                            <div style="font-weight:1000;"><?php echo qr_escape($sn); ?></div>
                            <div class="muted">Batch: <?php echo qr_escape($it['stok_id'] ?? '-'); ?></div>
                        </td>
                        <td class="center"><?php echo qr_escape($qty); ?></td>
                        <td class="center"><?php echo format_rupiah($harga); ?></td>
                        <td class="center"><?php echo format_rupiah($subtotal); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="row"><div class="muted">Total</div><div><?php echo format_rupiah((float)$header['total_harga']); ?></div></div>
        <?php if (!empty($header['diskon']) && (float)$header['diskon'] > 0): ?>
            <div class="row"><div class="muted">Diskon</div><div>- <?php echo format_rupiah((float)$header['diskon']); ?></div></div>
        <?php endif; ?>
        <div class="row total"><div>Total Bayar</div><div><?php echo format_rupiah((float)$header['total_bayar']); ?></div></div>

        <div class="divider"></div>
        <div class="muted" style="line-height:1.5;">
            Terima kasih atas transaksi Anda.
        </div>
    </div>
</div>
</body>
</html>

