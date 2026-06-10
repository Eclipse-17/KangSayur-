<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is petugas stok
check_login();
check_role('petugas_stok');

// Get dashboard statistics
$res_produk = $conn->query("SELECT COUNT(*) as count FROM sayuran WHERE status = 'aktif'");
$total_produk = ($res_produk) ? $res_produk->fetch_assoc()['count'] : 0;

$res_stok = $conn->query("SELECT SUM(jumlah_stok) as total FROM stok_sayuran WHERE status = 'tersedia'");
$total_stok = ($res_stok) ? ($res_stok->fetch_assoc()['total'] ?? 0) : 0;

$res_masuk = $conn->query("SELECT COUNT(*) as count FROM pengelolaan_stok_masuk WHERE status = 'terima' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$stok_masuk_bulan = ($res_masuk) ? $res_masuk->fetch_assoc()['count'] : 0;

$res_menipis = $conn->query("SELECT COUNT(*) as count FROM monitoring_stok_menipis WHERE status_stok IN ('menipis', 'habis')");
$stok_menipis = ($res_menipis) ? $res_menipis->fetch_assoc()['count'] : 0;

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
  <header class="site-header">
    <div class="header-inner">
      <div class="logo-icons">🥔 🌽 🍅 🥕 🥬</div>
      <h1 class="brand">Kang<span>Sayur</span> <span class="role-badge">Petugas Stok</span></h1>
      <form method="post" action="../php/logout.php" style="display:inline">
        <button class="logout-btn" title="Logout">⏻</button>
      </form>
    </div>
  </header>

  <main class="center-wrap dashboard-wrap">
    <section class="dashboard">
      <div class="greeting">
        <p class="hello">Halo,</p>
        <h2 class="who"><?php echo $_SESSION['nama']; ?></h2>
        <p class="date"><?php echo date('l, j F Y'); ?></p>
      </div>

      <?php if ($alert): ?>
          <div class="alert <?php echo $alert['type']; ?>">
              <?php echo $alert['message']; ?>
          </div>
      <?php endif; ?>

      <div class="stats-grid">
        <div class="stat"> <div class="stat-icon">🥬</div> <div class="stat-value"><?php echo $total_produk; ?></div> <div class="stat-label">Total Produk</div> </div>
        <div class="stat"> <div class="stat-icon">📦</div> <div class="stat-value"><?php echo $total_stok; ?></div> <div class="stat-label">Total Stok</div> </div>
        <div class="stat"> <div class="stat-icon">📥</div> <div class="stat-value"><?php echo $stok_masuk_bulan; ?></div> <div class="stat-label">Stok Masuk Bulan Ini</div> </div>
        <div class="stat"> <div class="stat-icon">⚠️</div> <div class="stat-value"><?php echo $stok_menipis; ?></div> <div class="stat-label">Stok Menipis/Habis</div> </div>
      </div>

      <h3 class="section-title">Kelola Stok</h3>
      <div class="menu-grid">
        <a href="petugas/daftar_stok.php" style="text-decoration: none;"><div class="menu-card">📋 Daftar Stok</div></a>
        <a href="petugas/input_stok.php" style="text-decoration: none;"><div class="menu-card">📥 Input Stok</div></a>
        <a href="petugas/update_stok.php" style="text-decoration: none;"><div class="menu-card">✏️ Update Stok</div></a>
        <a href="petugas/stok_menipis.php" style="text-decoration: none;"><div class="menu-card">⚠️ Stok Menipis</div></a>
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
