<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Kasir - KangSayur</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="logo-icons">🥔 🌽 🍅 🥕 🥬</div>
      <h1 class="brand">Kang<span>Sayur</span> <span class="role-badge">Kasir</span></h1>
      <form method="post" action="../php/logout.php" style="display:inline">
        <button class="logout-btn" title="Logout">⏻</button>
      </form>
    </div>
  </header>

  <main class="center-wrap dashboard-wrap">
    <section class="dashboard">
      <div class="greeting">
        <p class="hello">Halo,</p>
        <h2 class="who">Kasir</h2>
        <p class="date"><?php echo date('l, j F Y'); ?></p>
      </div>

      <h3 class="section-title">Transaksi</h3>
      <div class="menu-grid">
        <div class="menu-card">Penjualan Baru</div>
        <div class="menu-card">Riwayat Transaksi</div>
        <div class="menu-card">Cetak Struk</div>
      </div>
    </section>
  </main>
</body>
</html>
