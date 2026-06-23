<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_stok') {
    header('Location: ../../php/login.php');
    exit;
}

// Ambil stok tersedia saat ini langsung dari tabel stok_sayuran (menghindari beda sinkronisasi dengan monitoring_stok_menipis)
$query = "SELECT 
            s.id,
            s.kode_sayuran,
            s.nama_sayuran,
            k.nama_kategori,
            s.stok_minimum,
            COALESCE(SUM(st.jumlah_stok),0) as stok_saat_ini
         FROM sayuran s
         JOIN kategori_sayuran k ON k.id = s.kategori_id
         LEFT JOIN stok_sayuran st ON st.sayuran_id = s.id AND st.status = 'tersedia'
         WHERE s.status = 'aktif'
         GROUP BY s.id, s.kode_sayuran, s.nama_sayuran, k.nama_kategori, s.stok_minimum
         HAVING COALESCE(SUM(st.jumlah_stok),0) <= s.stok_minimum
         ORDER BY (COALESCE(SUM(st.jumlah_stok),0)) ASC";

$alerts = $conn->query($query);

// summary
$total_habis = 0;
$total_menipis = 0;
if ($alerts) {
    while ($row = $alerts->fetch_assoc()) {
        $stok_ini = (float)$row['stok_saat_ini'];
        if ($stok_ini <= 0) $total_habis++; else $total_menipis++;
    }
    // reset result cursor: re-run query
    $alerts = $conn->query($query);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Stok Menipis - KangSayur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .header { margin-bottom: 20px; }
        .header h1 { margin: 0 0 15px 0; color: #333; }
        .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; }
        .summary-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .summary-card .value { font-size: 32px; font-weight: bold; margin: 10px 0; }
        .summary-card.danger .value { color: #f44336; }
        .summary-card.warning .value { color: #ff9800; }
        .summary-card .label { color: #666; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th { background: #f5f5f5; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; font-weight: bold; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge.habis { background: #f44336; color: white; }
        .badge.menipis { background: #ff9800; color: white; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2196F3; text-decoration: none; font-weight: bold; }
        .no-data { text-align: center; padding: 40px; color: #666; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .muted{ color:#666; font-size:12px; }
        .alert-box { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <a href="../petugas.php" class="back-link">← Kembali ke Dashboard</a>

    <div class="header">
        <h1>⚠️ Monitoring Stok Menipis/Habis</h1>
    </div>

    <div class="summary-grid">
        <div class="summary-card danger">
            <div class="label">🚨 Stok Habis</div>
            <div class="value"><?php echo (int)$total_habis; ?></div>
        </div>
        <div class="summary-card warning">
            <div class="label">⚠️ Stok Menipis</div>
            <div class="value"><?php echo (int)$total_menipis; ?></div>
        </div>
    </div>

    <?php if ($alerts && $alerts->num_rows > 0): ?>
        <?php if ($total_habis > 0): ?>
            <div class="alert-box">
                <strong>⚠️ Perhatian:</strong> Ada <?php echo (int)$total_habis; ?> produk stoknya habis. Segera lakukan pengisian stok!
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Stok Saat Ini</th>
                    <th>Min Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = $alerts->fetch_assoc()): ?>
                    <?php
                        $stok_ini = (float)$row['stok_saat_ini'];
                        $status_stok = ($stok_ini <= 0) ? 'habis' : 'menipis';
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['kode_sayuran']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_sayuran']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                        <td><?php echo $stok_ini; ?></td>
                        <td><?php echo (float)$row['stok_minimum']; ?></td>
                        <td>
                            <span class="badge <?php echo $status_stok; ?>">
                                <?php echo ($status_stok === 'habis') ? '🚨 HABIS' : '⚠️ MENIPIS'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="input_stok.php" style="color:#4CAF50;text-decoration:none;font-weight:bold;">Input Stok</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <p>✅ Semua stok dalam kondisi aman!</p>
            <p class="muted">Tidak ada produk dengan stok menipis atau habis.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

