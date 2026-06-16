<?php
// Shared bottom navigation for Kasir pages
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
?>
<nav class="bottom-nav">
  <a href="kasir.php" class="nav-item active" style="text-decoration: none;">
    🏠<span>Home</span>
  </a>
  <a href="kasir/penjualan_baru.php" class="nav-item" style="text-decoration: none;">
    🧾<span>Penjualan</span>
  </a>
  <a href="kasir/riwayat_transaksi.php" class="nav-item" style="text-decoration: none;">
    📚<span>Riwayat</span>
  </a>
  <a href="../php/logout.php" class="nav-item" style="text-decoration: none;">
    🚪<span>Logout</span>
  </a>
</nav>

