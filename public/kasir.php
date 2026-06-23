<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header('Location: index.php');
    exit;
}

$alert = get_alert();
$namaKasir = $_SESSION['nama'] ?? 'Kasir';

// Statistics
$resOmset = $conn->query("SELECT COALESCE(SUM(total_bayar),0) AS total FROM transaksi_penjualan WHERE kasir_id='".(int)$_SESSION['user_id']."' AND status='selesai'");
$totalOmset = ($resOmset) ? (float)($resOmset->fetch_assoc()['total'] ?? 0) : 0;

$today = date('Y-m-d');
$resToday = $conn->query("SELECT COUNT(*) AS count FROM transaksi_penjualan WHERE kasir_id='".(int)$_SESSION['user_id']."' AND status='selesai' AND DATE(tanggal_transaksi)='".$today."'");
$transToday = ($resToday) ? (int)($resToday->fetch_assoc()['count'] ?? 0) : 0;


$resLow = $conn->query("SELECT COUNT(*) AS count
    FROM sayuran s
    WHERE s.status='aktif'
    AND (
        SELECT COALESCE(SUM(st.jumlah_stok),0)
        FROM stok_sayuran st
        WHERE st.sayuran_id=s.id AND st.status='tersedia'
    ) <= s.stok_minimum");
$stokMenipis = ($resLow) ? (int)($resLow->fetch_assoc()['count'] ?? 0) : 0;

$hariIni = date('l, j F Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>KangSayur Kasir - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-emerald-50/60 font-sans min-h-screen pb-24">

    <header class="bg-emerald-800 text-white shadow-md rounded-b-[2rem] px-6 pt-6 pb-12 relative">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <span class="text-2xl">🥔🌽🍅</span>
                <h1 class="text-xl font-bold tracking-wide">Kang<span class="text-emerald-300">Sayur</span></h1>
                <span class="bg-emerald-700 text-emerald-200 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-emerald-600">Kasir</span>
            </div>
            <form method="post" action="../php/logout.php">
                <button type="submit" class="bg-emerald-700 hover:bg-emerald-600 p-2.5 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-400" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 -mt-6">

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-100/50 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <p class="text-sm text-gray-400 font-medium">Selamat Datang,</p>
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($namaKasir); ?></h2>
            </div>
            <div class="text-sm text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg font-medium self-start sm:self-auto flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9 3h.008v.008H12V15zm0 2.25h.008v.008H12v-.008z" />
                </svg>
                <?php echo htmlspecialchars($hariIni); ?>
            </div>
        </div>

        <?php if ($alert): ?>
            <div class="mb-6 px-4 py-3 rounded-xl border text-sm <?php echo $alert['type']==='success' ? 'bg-green-50 border-green-200 text-green-800' : ($alert['type']==='error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-blue-50 border-blue-200 text-blue-800'); ?>">
                <?php echo htmlspecialchars($alert['message']); ?>
            </div>
        <?php endif; ?>

        <section class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs font-semibold text-gray-400 block mb-1">Total Omset</span>
                <span class="text-base font-bold text-gray-800"><?php echo format_rupiah((float)$totalOmset); ?></span>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <span class="text-xs font-semibold text-gray-400 block mb-1">Transaksi Hari Ini</span>
                <span class="text-base font-bold text-gray-800"><?php echo (int)$transToday; ?> Nota</span>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 col-span-2 sm:col-span-1">
                <span class="text-xs font-semibold text-red-400 block mb-1">Stok Menipis</span>
                <span class="text-base font-bold text-red-600 flex items-center gap-1">
                    <?php echo (int)$stokMenipis; ?> Produk
                    <span class="text-xs font-normal bg-red-50 px-1.5 py-0.5 rounded text-red-500">Cek!</span>
                </span>
            </div>
        </section>

        <section class="mb-8">
            <h3 class="text-sm font-bold text-emerald-900 tracking-wider uppercase mb-4">Menu Transaksi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <a href="kasir/penjualan_baru.php" class="block">
                    <div class="bg-white hover:bg-emerald-50 border border-emerald-100 p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex sm:flex-col items-center sm:justify-center text-left sm:text-center gap-4 group">
                        <div class="p-3 bg-emerald-100 rounded-xl group-hover:bg-emerald-200 text-emerald-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">Penjualan Baru</h4>
                            <p class="text-xs text-gray-400 mt-0.5 hidden sm:block">Mulai transaksi kasir baru</p>
                        </div>
                    </div>
                </a>

                <a href="kasir/riwayat_transaksi.php" class="block">
                    <div class="bg-white hover:bg-emerald-50 border border-emerald-100 p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex sm:flex-col items-center sm:justify-center text-left sm:text-center gap-4 group">

                        <div class="p-3 bg-amber-100 rounded-xl group-hover:bg-amber-200 text-amber-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">Riwayat Transaksi</h4>
                            <p class="text-xs text-gray-400 mt-0.5 hidden sm:block">Lihat data penjualan lalu</p>
                        </div>
                    </div>
                </a>

                <a href="kasir/cetak_struk.php" class="block">
                    <div class="bg-white hover:bg-emerald-50 border border-emerald-100 p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 flex sm:flex-col items-center sm:justify-center text-left sm:text-center gap-4 group">
                        <div class="p-3 bg-blue-100 rounded-xl group-hover:bg-blue-200 text-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-14.326 0C3.768 7.44 3 8.376 3 9.456v6.294a2.25 2.25 0 002.25 2.25h1.091M12 10.125c-.621 0-1.125-.504-1.125-1.125S11.379 7.875 12 7.875s1.125.504 1.125 1.125-.504 1.125-1.125 1.125z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">Cetak Struk</h4>
                            <p class="text-xs text-gray-400 mt-0.5 hidden sm:block">Reprint nota pembayaran</p>
                        </div>
                    </div>
                </a>

            </div>
        </section>

        <div class="bg-amber-50 border border-amber-200/70 rounded-xl p-4 flex items-start space-x-3">
            <span class="text-xl mt-0.5">💡</span>
            <div>
                <h5 class="font-bold text-amber-900 text-sm">Tips Efisiensi Stok</h5>
                <p class="text-xs text-amber-800/90 leading-relaxed mt-0.5">Gunakan menu <strong class="text-amber-950 font-semibold">"Penjualan Baru"</strong> lalu lakukan Checkout. Sistem akan secara otomatis mengurangi kuantitas stok menggunakan metode <strong class="text-amber-950 font-semibold">FIFO</strong> (First In, First Out).</p>
            </div>
        </div>

    </main>

    <!-- Bottom nav (match your Tailwind example, not kasir_nav.php) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] px-6 py-2.5 z-50">
        <div class="max-w-md mx-auto flex justify-around items-center">

            <a href="kasir.php" class="flex flex-col items-center space-y-1 text-emerald-600 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M11.47 3.82a.75.75 0 011.06 0l8.69 8.69a.75.75 0 11-1.06 1.06l-.22-.22v7.42a1.5 1.5 0 01-1.5 1.5h-3a1.5 1.5 0 01-1.5-1.5v-4.5h-3v4.5a1.5 1.5 0 01-1.5 1.5h-3a1.5 1.5 0 01-1.5-1.5V13.35l-.22.22a.75.75 0 01-1.06-1.06l8.69-8.69z" />
                </svg>
                <span class="text-[11px]">Home</span>
            </a>

            <a href="kasir/penjualan_baru.php" class="flex flex-col items-center space-y-1 text-gray-400 hover:text-emerald-600 transition-colors font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
                <span class="text-[11px]">Penjualan</span>
            </a>

                <a href="kasir/riwayat_transaksi.php" class="flex flex-col items-center space-y-1 text-gray-400 hover:text-emerald-600 transition-colors font-medium">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-.621-.504-1.125-1.125-1.125H9.75M9.663 5.485a1 1 0 01.464-.175 48.664 48.664 0 018.686 0 1 1 0 01.464.175m-10.076 0L9.34 5.09a2.25 2.25 0 011.831-2.445 48.294 48.294 0 017.054 0 2.25 2.25 0 011.83.244l.43.33M9.663 5.485a4.935 4.935 0 01A4.924 4.924 0 0112 3.75c1.233 0 2.37.45 3.245 1.19m-4.522 13.91l-2.117 2.117a1.625 1.625 0 01-2.3-2.3l2.117-2.117m2.117 2.117a1.625 1.625 0 002.3-2.3l-2.117-2.117m0 4.417v-4.417m0 0a1.625 1.625 0 012.3-2.3l2.117 2.117m-6.717 0a1.625 1.625 0 012.3-2.3l2.117 2.117" />
                </svg>
                <span class="text-[11px]">Riwayat</span>
            </a>

            <a href="../php/logout.php" class="flex flex-col items-center space-y-1 text-gray-400 hover:text-emerald-600 transition-colors font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m10 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v1" />
                </svg>
                <span class="text-[11px]">Logout</span>
            </a>

        </div>
    </nav>


</body>
</html>

