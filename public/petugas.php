<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is petugas stok
check_login();
check_role('petugas_stok');

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
    $stok_ini = (float) $row['stok_saat_ini'];
    if ($stok_ini <= 0)
      $total_habis++;
    else
      $total_menipis++;
  }
  // reset result cursor: re-run query
  $alerts = $conn->query($query);
}

// Get dashboard statistics
$res_produk = $conn->query("SELECT COUNT(*) as count FROM sayuran WHERE status = 'aktif'");
$total_produk = ($res_produk) ? $res_produk->fetch_assoc()['count'] : 0;

$res_stok = $conn->query("SELECT SUM(jumlah_stok) as total FROM stok_sayuran WHERE status = 'tersedia'");
$total_stok = ($res_stok) ? ($res_stok->fetch_assoc()['total'] ?? 0) : 0;

$res_masuk = $conn->query("SELECT COUNT(*) as count FROM pengelolaan_stok_masuk WHERE status = 'terima' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$stok_masuk_bulan = ($res_masuk) ? $res_masuk->fetch_assoc()['count'] : 0;

// Hitung stok menipis/habis langsung dari stok_sayuran yang valid (sinkron dengan petugas/stok_menipis.php dan kasir FIFO)
$res_menipis = $conn->query("SELECT 
    SUM(CASE WHEN stok_saat_ini <= 0 THEN 1 ELSE 0 END) as count_habis,
    SUM(CASE WHEN stok_saat_ini > 0 AND stok_saat_ini <= s.stok_minimum THEN 1 ELSE 0 END) as count_menipis
FROM (
    SELECT s.id, s.stok_minimum, COALESCE(SUM(st.jumlah_stok),0) as stok_saat_ini
    FROM sayuran s
    LEFT JOIN stok_sayuran st ON st.sayuran_id = s.id
        AND st.status = 'tersedia'
        AND (st.tanggal_kadaluarsa IS NULL OR st.tanggal_kadaluarsa >= CURDATE())
    WHERE s.status = 'aktif'
    GROUP BY s.id, s.stok_minimum
) s");

$count_habis = 0;
$count_menipis = 0;
if ($res_menipis) {
  $r = $res_menipis->fetch_assoc();
  $count_habis = (int) ($r['count_habis'] ?? 0);
  $count_menipis = (int) ($r['count_menipis'] ?? 0);
}
$stok_menipis = $count_habis + $count_menipis;

$alert = get_alert();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Petugas Stok - KangSayur</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <header class="site-header" style="text-align: left; position: relative;">
    <div class="header-inner">
      <h1 class="brand">Kang<span>Sayur</span> <span class="role-badge"
          style="background: rgba(255,255,255,0.2); color: #fff;">Petugas Stok</span></h1>
      <form method="post" action="../php/logout.php" style="display:inline">
        <button class="logout-btn" title="Logout">⏻</button>
      </form>

      <div class="greeting" style="margin-top: 20px;">
        <p class="hello">Halo,</p>
        <h2 class="who"
          style="font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-weight: normal; font-size: 32px; text-transform: capitalize; margin-top: 5px;">
          <?php echo $_SESSION['nama']; ?>
        </h2>
        <p class="date"><?php echo date('l, j F Y'); ?></p>
      </div>
    </div>
  </header>
  <main class="dashboard-wrap">
    <section class="dashboard">

      <?php if ($alert): ?>
        <div class="alert <?php echo $alert['type']; ?>">
          <?php echo $alert['message']; ?>
        </div>
      <?php endif; ?>

      <div class="stats-grid">
        <div class="stat">
          <div class="stat-icon">🥬</div>
          <div class="stat-value"><?php echo $total_produk; ?></div>
          <div class="stat-label">Total Produk</div>
        </div>
        <div class="stat">
          <div class="stat-icon">📦</div>
          <div class="stat-value"><?php echo $total_stok; ?></div>
          <div class="stat-label">Total Stok</div>
        </div>
        <div class="stat">
          <div class="stat-icon">📥</div>
          <div class="stat-value"><?php echo $stok_masuk_bulan; ?></div>
          <div class="stat-label">Stok Masuk Bulan Ini</div>
        </div>
        <div class="stat">
          <div class="stat-icon">⚠️</div>
          <div class="stat-value"><?php echo (int) $stok_menipis; ?></div>
          <div class="stat-label">Stok Menipis/Habis</div>
        </div>
      </div>

      <h3 class="section-title">Kelola Stok</h3>
      <div class="menu-grid">
        <a href="petugas/daftar_stok.php" style="text-decoration: none;">
          <div class="menu-card">📋 Daftar Stok</div>
        </a>
        <a href="petugas/input_stok.php" style="text-decoration: none;">
          <div class="menu-card">📥 Input Stok</div>
        </a>
        <a href="petugas/update_stok.php" style="text-decoration: none;">
          <div class="menu-card">✏️ Update Stok</div>
        </a>
        <a href="petugas/stok_menipis.php" style="text-decoration: none;">
          <div class="menu-card">⚠️ Stok Menipis</div>
        </a>
        <a href="petugas/riwayat.php" style="text-decoration: none;">
          <div class="menu-card">🕘 Riwayat</div>
        </a>
      </div>
    </section>
  </main>

  <nav class="bottom-nav">
    <a href="petugas.php" class="nav-item active" style="text-decoration: none;">🏠<span>Home</span></a>
    <a href="petugas/daftar_stok.php" class="nav-item" style="text-decoration: none;">📦<span>Stok</span></a>
    <a href="petugas/stok_menipis.php" class="nav-item" style="text-decoration: none;">⚠️<span>Alert</span></a>
    <a href="../php/logout.php" class="nav-item" style="text-decoration: none;">🚪<span>Logout</span></a>
  </nav>
</body>

</html>