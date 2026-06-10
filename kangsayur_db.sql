-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2026 at 06:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kangsayur_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `aktivitas_log`
--

CREATE TABLE `aktivitas_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `tabel_target` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aktivitas_log`
--

INSERT INTO `aktivitas_log` (`id`, `user_id`, `aktivitas`, `tabel_target`, `record_id`, `deskripsi`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN', NULL, NULL, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 15:08:56'),
(2, 1, 'LOGIN', NULL, NULL, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 15:10:04'),
(3, 1, 'LOGIN', NULL, NULL, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 15:11:03'),
(4, 1, 'LOGIN', NULL, NULL, 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-10 16:22:46');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi_penjualan`
--

CREATE TABLE `detail_transaksi_penjualan` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `sayuran_id` int(11) NOT NULL,
  `stok_id` int(11) DEFAULT NULL,
  `jumlah_beli` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_sayuran`
--

CREATE TABLE `kategori_sayuran` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori_sayuran`
--

INSERT INTO `kategori_sayuran` (`id`, `nama_kategori`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sayuran Daun', 'Jenis sayuran yang berdaun seperti bayam, kangkung', 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(2, 'Sayuran Buah', 'Jenis sayuran buah seperti tomat, cabai, paprika', 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(3, 'Sayuran Akar', 'Jenis sayuran umbi/akar seperti kentang, wortel', 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(4, 'Sayuran Pods', 'Jenis sayuran polong seperti kacang panjang, buncis', 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_penjualan`
--

CREATE TABLE `laporan_penjualan` (
  `id` int(11) NOT NULL,
  `tanggal_laporan` date NOT NULL,
  `sayuran_id` int(11) NOT NULL,
  `jumlah_terjual` int(11) NOT NULL,
  `total_penjualan` decimal(10,2) NOT NULL,
  `keuntungan` decimal(10,2) DEFAULT NULL,
  `kasir_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_stok`
--

CREATE TABLE `laporan_stok` (
  `id` int(11) NOT NULL,
  `tanggal_laporan` date NOT NULL,
  `sayuran_id` int(11) NOT NULL,
  `stok_awal` int(11) NOT NULL,
  `stok_masuk` int(11) NOT NULL,
  `stok_keluar` int(11) NOT NULL,
  `stok_rusak` int(11) DEFAULT 0,
  `stok_akhir` int(11) NOT NULL,
  `nilai_stok` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monitoring_stok_menipis`
--

CREATE TABLE `monitoring_stok_menipis` (
  `id` int(11) NOT NULL,
  `sayuran_id` int(11) NOT NULL,
  `stok_saat_ini` int(11) NOT NULL,
  `stok_minimum` int(11) NOT NULL,
  `status_stok` enum('aman','menipis','habis') NOT NULL,
  `tanggal_monitor` datetime NOT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `notifikasi_dikirim` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nota_transaksi`
--

CREATE TABLE `nota_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `nomor_nota` varchar(50) NOT NULL,
  `tanggal_nota` datetime NOT NULL,
  `cashier_name` varchar(100) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `items_detail` longtext DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `printed_count` int(11) DEFAULT 0,
  `last_printed_at` datetime DEFAULT NULL,
  `status` enum('aktif','dibatalkan') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengelolaan_stok_masuk`
--

CREATE TABLE `pengelolaan_stok_masuk` (
  `id` int(11) NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `sayuran_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `jumlah_masuk` int(11) NOT NULL,
  `harga_perolehan` decimal(10,2) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('terima','proses','tolak') DEFAULT 'terima',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sayuran`
--

CREATE TABLE `sayuran` (
  `id` int(11) NOT NULL,
  `kode_sayuran` varchar(50) NOT NULL,
  `nama_sayuran` varchar(100) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `harga_beli` decimal(10,2) NOT NULL,
  `harga_jual` decimal(10,2) NOT NULL,
  `satuan` varchar(20) NOT NULL DEFAULT 'kg',
  `stok_minimum` int(11) DEFAULT 10,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sayuran`
--

INSERT INTO `sayuran` (`id`, `kode_sayuran`, `nama_sayuran`, `kategori_id`, `harga_beli`, `harga_jual`, `satuan`, `stok_minimum`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SAY001', 'Bayam Hijau', 1, 3000.00, 5000.00, 'kg', 20, NULL, 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(2, 'SAY002', 'Kangkung', 1, 2500.00, 4500.00, 'kg', 25, NULL, 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(3, 'SAY003', 'Tomat Merah', 2, 5000.00, 8000.00, 'kg', 15, NULL, 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(4, 'SAY004', 'Cabai Merah', 2, 15000.00, 25000.00, 'kg', 10, NULL, 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(5, 'SAY005', 'Wortel', 3, 4000.00, 6500.00, 'kg', 20, NULL, 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(6, 'SAY006', 'Kentang', 3, 3500.00, 5500.00, 'kg', 30, NULL, 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(7, 'SAY007', 'Kacang Panjang', 4, 6000.00, 10000.00, 'kg', 15, NULL, 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(8, 'SAY008', 'Buncis', 4, 5500.00, 9000.00, 'kg', 15, NULL, 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26');

-- --------------------------------------------------------

--
-- Table structure for table `stok_sayuran`
--

CREATE TABLE `stok_sayuran` (
  `id` int(11) NOT NULL,
  `sayuran_id` int(11) NOT NULL,
  `batch_number` varchar(50) NOT NULL,
  `jumlah_stok` int(11) NOT NULL,
  `harga_perolehan` decimal(10,2) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `status` enum('tersedia','habis') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_penjualan`
--

CREATE TABLE `transaksi_penjualan` (
  `id` int(11) NOT NULL,
  `nomor_transaksi` varchar(50) NOT NULL,
  `kasir_id` int(11) NOT NULL,
  `tanggal_transaksi` datetime NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `diskon` decimal(10,2) DEFAULT 0.00,
  `total_bayar` decimal(10,2) NOT NULL,
  `metode_pembayaran` enum('tunai','debit','kredit','transfer') NOT NULL,
  `status` enum('selesai','batal') DEFAULT 'selesai',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `update_stok_barang`
--

CREATE TABLE `update_stok_barang` (
  `id` int(11) NOT NULL,
  `sayuran_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `tipe_update` enum('penambahan','pengurangan','penyesuaian','rusak') NOT NULL,
  `jumlah_awal` int(11) NOT NULL,
  `jumlah_perubahan` int(11) NOT NULL,
  `jumlah_akhir` int(11) NOT NULL,
  `alasan` text DEFAULT NULL,
  `tanggal_update` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas_stok','kasir') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `username`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@kangsayur.com', 'admin', '1234', 'admin', 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:25:10'),
(2, 'Budi Petugas', 'budi@kangsayur.com', 'budi_petugas', '9c5fa085ce256c7c598f6710584ab25d', 'petugas_stok', 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26'),
(3, 'Eko Kasir', 'eko@kangsayur.com', 'eko_kasir', '8e1a070e9b0340da2b0ea4f193c172f0', 'kasir', 'aktif', '2026-06-10 14:09:26', '2026-06-10 14:09:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aktivitas_log`
--
ALTER TABLE `aktivitas_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_aktivitas` (`user_id`,`created_at`);

--
-- Indexes for table `detail_transaksi_penjualan`
--
ALTER TABLE `detail_transaksi_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sayuran_id` (`sayuran_id`),
  ADD KEY `stok_id` (`stok_id`),
  ADD KEY `idx_detail_transaksi` (`transaksi_id`);

--
-- Indexes for table `kategori_sayuran`
--
ALTER TABLE `kategori_sayuran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `laporan_penjualan`
--
ALTER TABLE `laporan_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kasir_id` (`kasir_id`),
  ADD KEY `idx_tanggal` (`tanggal_laporan`),
  ADD KEY `idx_sayuran` (`sayuran_id`);

--
-- Indexes for table `laporan_stok`
--
ALTER TABLE `laporan_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tanggal` (`tanggal_laporan`),
  ADD KEY `idx_sayuran` (`sayuran_id`);

--
-- Indexes for table `monitoring_stok_menipis`
--
ALTER TABLE `monitoring_stok_menipis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `petugas_id` (`petugas_id`),
  ADD KEY `idx_sayuran_status` (`sayuran_id`,`status_stok`);

--
-- Indexes for table `nota_transaksi`
--
ALTER TABLE `nota_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaksi_id` (`transaksi_id`),
  ADD UNIQUE KEY `nomor_nota` (`nomor_nota`);

--
-- Indexes for table `pengelolaan_stok_masuk`
--
ALTER TABLE `pengelolaan_stok_masuk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_referensi` (`nomor_referensi`),
  ADD KEY `sayuran_id` (`sayuran_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indexes for table `sayuran`
--
ALTER TABLE `sayuran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_sayuran` (`kode_sayuran`),
  ADD KEY `idx_sayuran_kategori` (`kategori_id`),
  ADD KEY `idx_sayuran_status` (`status`);

--
-- Indexes for table `stok_sayuran`
--
ALTER TABLE `stok_sayuran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sayuran_fifo` (`sayuran_id`,`tanggal_masuk`),
  ADD KEY `idx_stok_sayuran_status` (`status`);

--
-- Indexes for table `transaksi_penjualan`
--
ALTER TABLE `transaksi_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_transaksi` (`nomor_transaksi`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal_transaksi`),
  ADD KEY `idx_transaksi_kasir` (`kasir_id`);

--
-- Indexes for table `update_stok_barang`
--
ALTER TABLE `update_stok_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sayuran_id` (`sayuran_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aktivitas_log`
--
ALTER TABLE `aktivitas_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `detail_transaksi_penjualan`
--
ALTER TABLE `detail_transaksi_penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_sayuran`
--
ALTER TABLE `kategori_sayuran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `laporan_penjualan`
--
ALTER TABLE `laporan_penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan_stok`
--
ALTER TABLE `laporan_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `monitoring_stok_menipis`
--
ALTER TABLE `monitoring_stok_menipis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nota_transaksi`
--
ALTER TABLE `nota_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengelolaan_stok_masuk`
--
ALTER TABLE `pengelolaan_stok_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sayuran`
--
ALTER TABLE `sayuran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stok_sayuran`
--
ALTER TABLE `stok_sayuran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi_penjualan`
--
ALTER TABLE `transaksi_penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `update_stok_barang`
--
ALTER TABLE `update_stok_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aktivitas_log`
--
ALTER TABLE `aktivitas_log`
  ADD CONSTRAINT `aktivitas_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `detail_transaksi_penjualan`
--
ALTER TABLE `detail_transaksi_penjualan`
  ADD CONSTRAINT `detail_transaksi_penjualan_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi_penjualan` (`id`),
  ADD CONSTRAINT `detail_transaksi_penjualan_ibfk_2` FOREIGN KEY (`sayuran_id`) REFERENCES `sayuran` (`id`),
  ADD CONSTRAINT `detail_transaksi_penjualan_ibfk_3` FOREIGN KEY (`stok_id`) REFERENCES `stok_sayuran` (`id`);

--
-- Constraints for table `laporan_penjualan`
--
ALTER TABLE `laporan_penjualan`
  ADD CONSTRAINT `laporan_penjualan_ibfk_1` FOREIGN KEY (`sayuran_id`) REFERENCES `sayuran` (`id`),
  ADD CONSTRAINT `laporan_penjualan_ibfk_2` FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `laporan_stok`
--
ALTER TABLE `laporan_stok`
  ADD CONSTRAINT `laporan_stok_ibfk_1` FOREIGN KEY (`sayuran_id`) REFERENCES `sayuran` (`id`);

--
-- Constraints for table `monitoring_stok_menipis`
--
ALTER TABLE `monitoring_stok_menipis`
  ADD CONSTRAINT `monitoring_stok_menipis_ibfk_1` FOREIGN KEY (`sayuran_id`) REFERENCES `sayuran` (`id`),
  ADD CONSTRAINT `monitoring_stok_menipis_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `nota_transaksi`
--
ALTER TABLE `nota_transaksi`
  ADD CONSTRAINT `nota_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi_penjualan` (`id`);

--
-- Constraints for table `pengelolaan_stok_masuk`
--
ALTER TABLE `pengelolaan_stok_masuk`
  ADD CONSTRAINT `pengelolaan_stok_masuk_ibfk_1` FOREIGN KEY (`sayuran_id`) REFERENCES `sayuran` (`id`),
  ADD CONSTRAINT `pengelolaan_stok_masuk_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sayuran`
--
ALTER TABLE `sayuran`
  ADD CONSTRAINT `sayuran_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_sayuran` (`id`);

--
-- Constraints for table `stok_sayuran`
--
ALTER TABLE `stok_sayuran`
  ADD CONSTRAINT `stok_sayuran_ibfk_1` FOREIGN KEY (`sayuran_id`) REFERENCES `sayuran` (`id`);

--
-- Constraints for table `transaksi_penjualan`
--
ALTER TABLE `transaksi_penjualan`
  ADD CONSTRAINT `transaksi_penjualan_ibfk_1` FOREIGN KEY (`kasir_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `update_stok_barang`
--
ALTER TABLE `update_stok_barang`
  ADD CONSTRAINT `update_stok_barang_ibfk_1` FOREIGN KEY (`sayuran_id`) REFERENCES `sayuran` (`id`),
  ADD CONSTRAINT `update_stok_barang_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`);
COMMIT;
