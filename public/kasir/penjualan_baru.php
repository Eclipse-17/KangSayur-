<?php
session_start();
include '../../config/database.php';

check_login();
check_role('kasir');

$alert = get_alert();
if (!isset($_SESSION['kasir_cart'])) {
    $_SESSION['kasir_cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_item') {
        $sayuran_id = (int)($_POST['sayuran_id'] ?? 0);
        $qty = (float)($_POST['qty'] ?? 0);
        $diskon_item = (float)($_POST['diskon_item'] ?? 0);

        if ($sayuran_id <= 0 || $qty <= 0) {
            set_alert('Qty atau sayuran tidak valid.', 'error');
            header('Location: penjualan_baru.php');
            exit;
        }

        // Ambil batch FIFO yang paling tua untuk sayuran ini
        // (harga_satuan untuk item diambil dari harga_perolehan batch)
        $sql = "SELECT id, jumlah_stok, harga_perolehan, tanggal_masuk
                FROM stok_sayuran
                WHERE sayuran_id = '$sayuran_id'
                  AND status = 'tersedia'
                  AND (tanggal_kadaluarsa IS NULL OR tanggal_kadaluarsa >= CURDATE())
                ORDER BY tanggal_masuk ASC, id ASC
                LIMIT 1";
        $res = $conn->query($sql);
        if (!$res || $res->num_rows === 0) {
            set_alert('Stok untuk sayuran ini habis.', 'error');
            header('Location: penjualan_baru.php');
            exit;
        }
        $batch = $res->fetch_assoc();
        $harga_satuan = (float)$batch['harga_perolehan'];

        // Simpan harga batch saat item ditambahkan (agar konsisten saat checkout)
        $key = (string)$sayuran_id;
        if (!isset($_SESSION['kasir_cart'][$key])) {
            $_SESSION['kasir_cart'][$key] = [
                'sayuran_id' => $sayuran_id,
                'qty' => 0,
                'harga_satuan' => $harga_satuan,
                'diskon_item' => 0,
            ];
        }
        $_SESSION['kasir_cart'][$key]['qty'] += $qty;
        $_SESSION['kasir_cart'][$key]['diskon_item'] += $diskon_item;

        set_alert('Item ditambahkan ke keranjang.', 'success');
        header('Location: penjualan_baru.php');
        exit;
    }

    if ($action === 'remove_item') {
        $sayuran_id = (int)($_POST['sayuran_id'] ?? 0);
        unset($_SESSION['kasir_cart'][(string)$sayuran_id]);
        set_alert('Item dihapus.', 'success');
        header('Location: penjualan_baru.php');
        exit;
    }

    if ($action === 'clear_cart') {
        $_SESSION['kasir_cart'] = [];
        set_alert('Keranjang dibersihkan.', 'success');
        header('Location: penjualan_baru.php');
        exit;
    }

    if ($action === 'checkout') {
        $metode_pembayaran = $_POST['metode_pembayaran'] ?? 'tunai';
        $diskon_transaksi = (float)($_POST['diskon'] ?? 0);
        $uang_bayar = (float)($_POST['uang_bayar'] ?? 0);

        $cart = $_SESSION['kasir_cart'];
        if (empty($cart)) {
            set_alert('Keranjang kosong.', 'error');
            header('Location: penjualan_baru.php');
            exit;
        }

        // Hitung total dan siapkan FIFO pengambilan batch saat checkout
        $conn->begin_transaction();
        try {
            $kasir_id = (int)$_SESSION['user_id'];
            $nomor_transaksi = generate_kode('TRX', 'transaksi_penjualan', 'nomor_transaksi');

            // total biaya FIFO dihitung ulang berbasis batch FIFO saat ini
            $total_harga = 0.0;
            $items_to_insert = []; // [{sayuran_id, jumlah_beli, harga_satuan, stok_id}...]

            foreach ($cart as $item) {
                $sayuran_id = (int)$item['sayuran_id'];
                $qty_needed = (float)$item['qty'];

                if ($qty_needed <= 0) continue;

                $remaining = $qty_needed;

                // Ambil batch FIFO sampai qty terpenuhi
                $batches = $conn->query("SELECT id, jumlah_stok, harga_perolehan
                                          FROM stok_sayuran
                                          WHERE sayuran_id = '$sayuran_id' AND status = 'tersedia'
                                          ORDER BY tanggal_masuk ASC, id ASC" );
                if (!$batches) throw new Exception('Gagal mengambil batch FIFO.');

                while ($remaining > 0) {
                    if ($batches->num_rows === 0) {
                        throw new Exception('Stok tidak mencukupi untuk sayuran ' . $sayuran_id);
                    }
                    $batch = $batches->fetch_assoc();
                    if (!$batch) {
                        throw new Exception('Stok tidak mencukupi untuk sayuran ' . $sayuran_id);
                    }

                    $batch_id = (int)$batch['id'];
                    $batch_stok = (float)$batch['jumlah_stok'];
                    $batch_harga = (float)$batch['harga_perolehan'];

                    $ambil = min($remaining, $batch_stok);
                    if ($ambil <= 0) {
                        continue;
                    }

                    $subtotal = $ambil * $batch_harga;
                    $total_harga += $subtotal;
                    $items_to_insert[] = [
                        'sayuran_id' => $sayuran_id,
                        'stok_id' => $batch_id,
                        'jumlah_beli' => $ambil,
                        'harga_satuan' => $batch_harga,
                        'subtotal' => $subtotal,
                    ];

                    $remaining -= $ambil;

                    // update stok batch dalam transaksi
                    $sisa = $batch_stok - $ambil;
                    if ($sisa <= 0) {
                        $conn->query("UPDATE stok_sayuran SET jumlah_stok = 0, status = 'habis' WHERE id = '$batch_id'");
                    } else {
                        $conn->query("UPDATE stok_sayuran SET jumlah_stok = '$sisa', status = 'tersedia' WHERE id = '$batch_id'");
                    }

                    // note: mysqli result cursor will advance automatically
                }
            }

            $diskon_transaksi = max(0.0, $diskon_transaksi);
            $grand_total = $total_harga - $diskon_transaksi;
            if ($grand_total < 0) $grand_total = 0.0;

            $total_bayar = $grand_total;
            $kembalian = $uang_bayar - $total_bayar;
            if ($metode_pembayaran === 'tunai' && $uang_bayar < $total_bayar) {
                throw new Exception('Uang bayar kurang.');
            }

            $conn->query("INSERT INTO transaksi_penjualan 
                (nomor_transaksi, kasir_id, tanggal_transaksi, total_harga, diskon, total_bayar, metode_pembayaran, status)
                VALUES (
                    '$nomor_transaksi',
                    '$kasir_id',
                    NOW(),
                    '$total_harga',
                    '$diskon_transaksi',
                    '$total_bayar',
                    '$metode_pembayaran',
                    'selesai'
                )");

            $transaksi_id = $conn->insert_id;

            // Insert detail transaksi
            foreach ($items_to_insert as $it) {
                $conn->query("INSERT INTO detail_transaksi_penjualan
                    (transaksi_id, sayuran_id, stok_id, jumlah_beli, harga_satuan, subtotal)
                    VALUES (
                        '$transaksi_id',
                        '{$it['sayuran_id']}',
                        '{$it['stok_id']}',
                        '{$it['jumlah_beli']}',
                        '{$it['harga_satuan']}',
                        '{$it['subtotal']}'
                    )");
            }

            // ====================== LAPORAN PENJUALAN (pakai FIFO cost) ======================
            // Kita insert/upsert ke tabel laporan_penjualan berdasarkan item yang terjual.
            // keuntungan = (harga_jual - harga_fifo_cost) * qty, fifo_cost diambil dari harga_satuan (harga_perolehan batch) yang sudah kita hitung di items_to_insert.
            $tanggal_laporan = date('Y-m-d');
            $kasir_id = (int)$kasir_id;

            // Ambil harga jual per sayuran sekali untuk efisiensi
            $sayuran_ids = [];
            foreach ($items_to_insert as $it) {
                $sayuran_ids[(int)$it['sayuran_id']] = true;
            }
            $sayuran_ids_list = implode(',', array_keys($sayuran_ids));

            $harga_jual_map = [];
            if (!empty($sayuran_ids_list)) {
                $q_hj = $conn->query("SELECT id, harga_jual FROM sayuran WHERE id IN ($sayuran_ids_list)");
                if ($q_hj) {
                    while ($row = $q_hj->fetch_assoc()) {
                        $harga_jual_map[(int)$row['id']] = (float)$row['harga_jual'];
                    }
                }
            }

            foreach ($items_to_insert as $it) {
                $sayuran_id = (int)$it['sayuran_id'];
                $qty = (float)$it['jumlah_beli'];
                $harga_fifo_cost = (float)$it['harga_satuan'];

                $harga_jual = $harga_jual_map[$sayuran_id] ?? 0.0;
                $total_penjualan_item = $qty * $harga_jual; // total penjualan pakai harga jual standar
                $keuntungan_item = $qty * ($harga_jual - $harga_fifo_cost);
                if ($keuntungan_item < 0) $keuntungan_item = 0.0;

                // Upsert laporan_penjualan untuk (tanggal, sayuran, kasir)
                $check = $conn->query("SELECT id FROM laporan_penjualan
                                        WHERE tanggal_laporan = '$tanggal_laporan'
                                          AND sayuran_id = '$sayuran_id'
                                          AND kasir_id = '$kasir_id'
                                        LIMIT 1");

                if ($check && $check->num_rows > 0) {
                    $upd = $check->fetch_assoc();
                    $conn->query("UPDATE laporan_penjualan
                                   SET jumlah_terjual = jumlah_terjual + '$qty',
                                       total_penjualan = total_penjualan + '$total_penjualan_item',
                                       keuntungan = COALESCE(keuntungan,0) + '$keuntungan_item'
                                   WHERE id = '{$upd['id']}'");
                } else {
                    $conn->query("INSERT INTO laporan_penjualan
                        (tanggal_laporan, sayuran_id, jumlah_terjual, total_penjualan, keuntungan, kasir_id, created_at)
                        VALUES
                        ('$tanggal_laporan', '$sayuran_id', '$qty', '$total_penjualan_item', '$keuntungan_item', '$kasir_id', NOW())");
                }
            }

            // ====================== BUAT NOTA TRANSAKSI ======================
            $nomor_nota = generate_kode('NOTA', 'nota_transaksi', 'nomor_nota');

            $items_detail = [];
            foreach ($items_to_insert as $it) {
                $items_detail[] = [
                    'sayuran_id' => $it['sayuran_id'],
                    'stok_id' => $it['stok_id'],
                    'jumlah' => $it['jumlah_beli'],
                    'harga_satuan' => $it['harga_satuan'],
                    'subtotal' => $it['subtotal'],
                ];
            }

            $items_detail_json = json_encode($items_detail, JSON_UNESCAPED_UNICODE);

            $cashier_name = escape_string($_SESSION['nama']);
            $customer_name = escape_string($_POST['customer_name'] ?? '');
            $payment_method = escape_string($metode_pembayaran);

            $conn->query("INSERT INTO nota_transaksi
                (transaksi_id, nomor_nota, tanggal_nota, cashier_name, customer_name, items_detail, total_amount, payment_method, status)
                VALUES (
                    '$transaksi_id',
                    '$nomor_nota',
                    NOW(),
                    '$cashier_name',
                    '$customer_name',
                    '$items_detail_json',
                    '$total_bayar',
                    '$payment_method',
                    'aktif'
                )");

            $conn->commit();
            $_SESSION['kasir_last_transaksi_id'] = $transaksi_id;
            $_SESSION['kasir_last_nota_id'] = $nomor_nota;

            // bersihkan cart setelah sukses
            $_SESSION['kasir_cart'] = [];

            set_alert('Transaksi berhasil dibuat. Nota: ' . $nomor_nota, 'success');
            header('Location: cetak_struk.php?transaksi_id=' . $transaksi_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            set_alert('Gagal checkout: ' . $e->getMessage(), 'error');
            header('Location: penjualan_baru.php');
            exit;
        }
    }
}

// load sayuran aktif untuk picker
$sayurans = $conn->query("SELECT id, kode_sayuran, nama_sayuran, harga_jual, satuan FROM sayuran WHERE status = 'aktif' ORDER BY nama_sayuran ASC");

$cart = $_SESSION['kasir_cart'];
$cart_items = array_values($cart);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan Baru - Kasir</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container{max-width:1000px;margin:18px auto;padding:0 16px;}
        .back-link{display:inline-block;margin-bottom:14px;color:#2196F3;text-decoration:none;}
        .card{background:#fff;border:1px solid #e9e9e9;border-radius:10px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);}
        .row{display:flex;gap:12px;flex-wrap:wrap;}
        .col{flex:1;min-width:240px;}
        .title{margin:0 0 10px 0;}
        .form-group{margin-bottom:12px;}
        .form-group label{display:block;font-weight:700;font-size:13px;margin-bottom:6px;color:#333;}
        .form-group input,.form-group select{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px;}
        .btn{padding:10px 14px;border:none;border-radius:8px;cursor:pointer;font-weight:800;}
        .btn-primary{background:#4CAF50;color:#fff;}
        .btn-primary:hover{background:#45a049;}
        .btn-secondary{background:#6c757d;color:#fff;}
        .btn-secondary:hover{background:#5a6268;}
        .btn-danger{background:#dc3545;color:#fff;}
        .btn-danger:hover{background:#c82333;}
        table{width:100%;border-collapse:collapse;margin-top:8px;}
        th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;font-size:14px;}
        th{background:#fafafa;}
        .alert{padding:12px 14px;border-radius:8px;margin-bottom:12px;}
        .alert.success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert.error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
        @media (max-width: 800px){.grid-2{grid-template-columns:1fr;}}
        .note-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;}
        .chip{display:inline-block;padding:4px 10px;border-radius:999px;background:#f3f3f3;font-weight:800;font-size:12px;}
    </style>
</head>
<body>
    <div class="container">
        <a href="../kasir.php" class="back-link">← Kembali ke Dashboard</a>

        <?php if ($alert): ?>
            <div class="alert <?php echo $alert['type']; ?>"> <?php echo $alert['message']; ?> </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom:14px;">
            <h2 class="title">🧾 Penjualan Baru</h2>
            <div class="info-box" style="background:#e3f2fd;border-left:4px solid #2196F3;padding:12px 14px;border-radius:8px;">
                <p style="margin:0;color:#1565c0;font-size:13px;">
                    Sistem FIFO: biaya per item diambil dari batch stok yang paling dulu masuk (tanggal_masuk terlama).
                </p>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <h3 style="margin-top:0;">Tambah Item</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_item" />
                    <div class="form-group">
                        <label for="sayuran_id">Sayuran</label>
                        <select name="sayuran_id" id="sayuran_id" required>
                            <option value="">-- Pilih --</option>
                            <?php while($s=$sayurans->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>">
                                    [<?php echo $s['kode_sayuran']; ?>] <?php echo $s['nama_sayuran']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="qty">Qty (unit)</label>
                                <input type="number" step="0.01" min="0" name="qty" id="qty" required />
                            </div>
                        </div>
                    </div>
                    <div class="note-actions">
                        <button type="submit" class="btn btn-primary">+ Tambah</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">Keranjang</h3>
                <?php if (empty($cart_items)): ?>
                    <p style="color:#666;margin-top:10px;">Keranjang kosong.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Sayuran</th>
                                <th>Qty</th>
                                <th>Harga/Unit (FIFO)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cart_items as $ci): ?>
                                <?php
                                    $sid = (int)$ci['sayuran_id'];
                                    $qty = (float)$ci['qty'];
                                    $harga = (float)$ci['harga_satuan'];

                                    // Ambil nama sayuran untuk tampilan
                                    $nquery = $conn->query("SELECT nama_sayuran FROM sayuran WHERE id = '$sid' LIMIT 1");
                                    $nama_sayuran = '-';
                                    if ($nquery && $nquery->num_rows > 0) {
                                        $nama_sayuran = $nquery->fetch_assoc()['nama_sayuran'];
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:900;">#<?php echo htmlspecialchars((string)$ci['sayuran_id']); ?></div>
                                        <div style="color:#333;"><?php echo htmlspecialchars($nama_sayuran); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars((string)$qty); ?></td>
                                    <td>
                                        <?php echo format_rupiah($harga); ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="" onsubmit="return confirm('Hapus item dari keranjang?');">
                                            <input type="hidden" name="action" value="remove_item" />
                                            <input type="hidden" name="sayuran_id" value="<?php echo (int)$ci['sayuran_id']; ?>" />
                                            <button type="submit" class="btn btn-danger" style="padding:8px 12px;">🗑️ Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="note-actions" style="margin-top:14px;">
                        <form method="POST" action="" style="margin:0;">
                            <input type="hidden" name="action" value="clear_cart" />
                            <button type="submit" class="btn btn-secondary">Bersihkan Keranjang</button>
                        </form>
                    </div>

                    <?php
                        // Tampilkan total belanja dari keranjang (harga FIFO per item)
                        $total_keranjang = 0.0;
                        foreach ($cart_items as $ci) {
                            $total_keranjang += ((float)$ci['qty']) * ((float)$ci['harga_satuan']);
                        }
                    ?>

                    <div class="card" style="margin-top:14px; padding:16px;">
                        <h3 style="margin-top:0;">Checkout</h3>
                        <div style="margin-bottom:12px;background:#f5f5f5;border:1px solid #eee;border-radius:10px;padding:10px 12px;">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;">
                                <div style="color:#333;font-weight:900;">Total Keranjang</div>
                                <div style="color:#1b5e20;font-weight:1000;"><?php echo format_rupiah((float)$total_keranjang); ?></div>

                            </div>
                        </div>

                        <script>
                            // placeholder untuk format rupiah (PHP sudah menghitung nominal)
                        </script>

                        <form method="POST" action="">
                            <input type="hidden" name="action" value="checkout" />
                            <input type="hidden" name="total_keranjang" value="<?php echo htmlspecialchars((string)$total_keranjang); ?>" />

                            <div class="form-row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
                                <div class="form-group">
                                    <label>Customer (opsional)</label>
                                    <input type="text" name="customer_name" />
                                </div>
                                <div class="form-group">
                                    <label>Metode Pembayaran</label>
                                    <select name="metode_pembayaran">
                                        <option value="tunai" selected>tunai</option>
                                        <option value="transfer">transfer</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Uang Bayar (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="uang_bayar" required />
                                </div>
                            </div>
                            <div class="note-actions" style="margin-top:12px;">
                                <button type="submit" class="btn btn-primary">✅ Selesaikan & Cetak Struk</button>
                            </div>
                        </form>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>


