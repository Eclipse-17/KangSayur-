# TODO - KangSayur (Kasir FIFO)

- [x] Update `public/kasir.php` agar menu mengarah ke halaman kasir: penjualan_baru, riwayat_transaksi, cetak_struk + nav bawah.

- [ ] Buat `public/kasir/penjualan_baru.php` (form tambah item, simpan transaksi, FIFO stok, buat detail_transaksi_penjualan + nota_transaksi).
- [ ] Buat `public/kasir/riwayat_transaksi.php` (list transaksi kasir login).
- [ ] Buat `public/kasir/cetak_struk.php` (render struk berdasarkan transaksi_id).
- [ ] Verifikasi integritas FIFO: batch stok_sayuran terpakai berurutan tanggal_masuk ASC, status batch jadi habis bila habis.
- [ ] Uji end-to-end: login kasir → buat transaksi → cek stok berkurang → cek riwayat → cetak struk.

