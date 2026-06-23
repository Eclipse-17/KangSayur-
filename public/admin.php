<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is admin
check_login();
check_role('admin');

$active = 'home';
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

// Get dashboard statistics
$res_jenis = $conn->query("SELECT COUNT(*) as count FROM sayuran WHERE status = 'aktif'");
$total_jenis = ($res_jenis) ? $res_jenis->fetch_assoc()['count'] : 0;

// Total stok juga harus dihitung dari batch stok (stok_sayuran.status='tersedia')
$res_stok = $conn->query("SELECT COALESCE(SUM(st.jumlah_stok),0) as total
                         FROM sayuran s
                         LEFT JOIN stok_sayuran st ON st.sayuran_id = s.id AND st.status = 'tersedia'
                         WHERE s.status = 'aktif'");
$total_stok = ($res_stok && $res_stok->num_rows > 0) ? (float)($res_stok->fetch_assoc()['total'] ?? 0) : 0;

$res_pendapatan = $conn->query("SELECT SUM(total_penjualan) as total FROM laporan_penjualan");
$total_pendapatan = ($res_pendapatan) ? ($res_pendapatan->fetch_assoc()['total'] ?? 0) : 0;

// Stok menipis/habis harus dihitung dari stok batch yang tersedia (stok_sayuran.status='tersedia')
$stok_calc = $conn->query("SELECT 
    SUM(CASE WHEN stok_tersedia <= 0 THEN 1 ELSE 0 END) as count_habis,
    SUM(CASE WHEN stok_tersedia > 0 AND stok_tersedia <= s.stok_minimum THEN 1 ELSE 0 END) as count_menipis
FROM sayuran s
LEFT JOIN stok_sayuran st ON st.sayuran_id = s.id AND st.status = 'tersedia'
WHERE s.status = 'aktif'
GROUP BY s.id");

$count_habis = 0;
$count_menipis = 0;
if ($stok_calc) {
    // karena ada GROUP BY per sayuran, ambil agregat dengan loop
    while($r = $stok_calc->fetch_assoc()) {
        $count_habis += (int)($r['count_habis'] ?? 0);
        $count_menipis += (int)($r['count_menipis'] ?? 0);
    }
}
$stok_menipis = $count_habis + $count_menipis;

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
        <div class="stat"> <div class="stat-icon">⚠️</div> <div class="stat-value"><?php echo (int)$total_menipis; ?></div> <div class="stat-label">Stok Menipis</div> </div>
      </div>

      <h3 class="section-title">Menu Admin</h3>
      <div class="menu-grid">
        <a href="admin/sayuran.php" style="text-decoration: none;"><div class="menu-card">Sayuran</div></a>
        <a href="admin/kategori.php" style="text-decoration: none;"><div class="menu-card">Kategori</div></a>
        <a href="admin/laporan_penjualan.php" style="text-decoration: none;"><div class="menu-card">Lap. Jual</div></a>
        <a href="admin/laporan_stok.php" style="text-decoration: none;"><div class="menu-card">Lap. Stok</div></a>
        <a href="admin/users.php" style="text-decoration: none;"><div class="menu-card">Kelola User</div></a>
        <a href="admin/riwayat.php" style="text-decoration: none;"><div class="menu-card">Riwayat</div></a>
      </div>
    </section>
  </main>

  <?php include 'admin_nav.php'; ?>
</body>
</html>

