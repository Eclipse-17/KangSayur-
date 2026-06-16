<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is admin
check_login();
check_role('admin');

$active = 'home';


// Get dashboard statistics
$res_jenis = $conn->query("SELECT COUNT(*) as count FROM sayuran WHERE status = 'aktif'");
$total_jenis = ($res_jenis) ? $res_jenis->fetch_assoc()['count'] : 0;

$res_stok = $conn->query("SELECT SUM(stok) as total FROM sayuran WHERE status = 'aktif'");
$total_stok = ($res_stok) ? ($res_stok->fetch_assoc()['total'] ?? 0) : 0;

$res_pendapatan = $conn->query("SELECT SUM(total_penjualan) as total FROM laporan_penjualan");
$total_pendapatan = ($res_pendapatan) ? ($res_pendapatan->fetch_assoc()['total'] ?? 0) : 0;

$res_menipis = $conn->query("SELECT COUNT(*) as count FROM sayuran WHERE status = 'aktif' AND stok <= stok_minimum");
$stok_menipis = ($res_menipis) ? $res_menipis->fetch_assoc()['count'] : 0;

$alert = get_alert();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard Admin - KangSayur</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="logo-icons">🥔 🌽 🍅 🥕 🥬</div>
      <h1 class="brand">Kang<span>Sayur</span> <span class="role-badge">Admin</span></h1>
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
        <div class="stat"> <div class="stat-icon">🥬</div> <div class="stat-value"><?php echo $total_jenis; ?></div> <div class="stat-label">Jenis Sayuran</div> </div>
        <div class="stat"> <div class="stat-icon">📦</div> <div class="stat-value"><?php echo $total_stok; ?></div> <div class="stat-label">Total Stok</div> </div>
        <div class="stat"> <div class="stat-icon">💰</div> <div class="stat-value"><?php echo format_rupiah($total_pendapatan); ?></div> <div class="stat-label">Total Pendapatan</div> </div>
        <div class="stat"> <div class="stat-icon">⚠️</div> <div class="stat-value"><?php echo $stok_menipis; ?></div> <div class="stat-label">Stok Menipis</div> </div>
      </div>

      <h3 class="section-title">Menu Admin</h3>
      <div class="menu-grid">
        <a href="admin/sayuran.php" style="text-decoration: none;"><div class="menu-card">Sayuran</div></a>
        <a href="admin/kategori.php" style="text-decoration: none;"><div class="menu-card">Kategori</div></a>
        <a href="admin/laporan_penjualan.php" style="text-decoration: none;"><div class="menu-card">Lap. Jual</div></a>
        <a href="admin/laporan_stok.php" style="text-decoration: none;"><div class="menu-card">Lap. Stok</div></a>
        <a href="admin/users.php" style="text-decoration: none;"><div class="menu-card">Kelola User</div></a>
        <a href="#" style="text-decoration: none;"><div class="menu-card">Riwayat</div></a>
      </div>
    </section>
  </main>

  <?php include 'admin_nav.php'; ?>
</body>
</html>

