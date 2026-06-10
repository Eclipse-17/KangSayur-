<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas_stok') {
    header('Location: index.php');
    exit;
}
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
        <h2 class="who">Petugas Stok</h2>
        <p class="date"><?php echo date('l, j F Y'); ?></p>
      </div>

      <h3 class="section-title">Kelola Stok</h3>
      <div class="menu-grid">
        <div class="menu-card">Daftar Produk</div>
        <div class="menu-card">Tambah Stok</div>
        <div class="menu-card">Stok Menipis</div>
      </div>
    </section>
  </main>
</body>
</html>
