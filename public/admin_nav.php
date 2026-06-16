<?php
// Shared bottom navigation for Admin pages
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
$current = $_GET['active'] ?? 'home';
?>
<nav class="bottom-nav">
  <a href="admin.php" class="nav-item <?php echo $current==='home'?'active':''; ?>" style="text-decoration: none;">
    🏠<span>Home</span>
  </a>
  <a href="admin/sayuran.php" class="nav-item <?php echo $current==='produk'?'active':''; ?>" style="text-decoration: none;">
    🥬<span>Produk</span>
  </a>
  <a href="admin/laporan_penjualan.php" class="nav-item <?php echo $current==='laporan'?'active':''; ?>" style="text-decoration: none;">
    📊<span>Laporan</span>
  </a>
  <a href="admin/users.php" class="nav-item <?php echo $current==='users'?'active':''; ?>" style="text-decoration: none;">
    👥<span>Users</span>
  </a>
</nav>

