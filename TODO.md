# TODO - KangSayur (Admin laporan tidak tampil)

- [x] Identifikasi sumber masalah: admin hanya membaca `laporan_penjualan` & `laporan_stok`, namun kasir/petugas belum mengisi tabel tersebut.
- [x] Update `public/kasir/penjualan_baru.php`: setelah checkout sukses, buat/akumulasi baris ke `laporan_penjualan` menggunakan FIFO cost (`harga_satuan` batch) untuk menghitung `keuntungan`.
- [ ] Update `public/petugas/input_stok.php`: buat/akumulasi baris ke `laporan_stok` saat stok masuk.
- [ ] Update `public/petugas/update_stok.php`: buat/akumulasi baris ke `laporan_stok` saat pengurangan/rusak/penyesuaian.
- [ ] Uji:
  - [ ] Checkout kasir muncul di `public/admin/laporan_penjualan.php`
  - [ ] Input stok petugas muncul di `public/admin/laporan_stok.php`

- [x] Perbaiki `public/admin/laporan_penjualan.php` & `public/admin/laporan_stok.php` agar filter bulan/tahun cocok dan summary null tidak bikin kosong.
- [x] Buat riwayat admin: `public/admin/riwayat.php` dan link dari `public/admin.php`.
- [x] Buat riwayat petugas: `public/petugas/riwayat.php` dan tombol dari `public/petugas.php`.
- [x] Buat riwayat kasir: `public/kasir/riwayat_login.php`.

- [x] Fix bug monitoring stok menipis: `public/petugas/stok_menipis.php` tidak lagi bergantung pada tabel `monitoring_stok_menipis`, tetapi hitung langsung dari `stok_sayuran`.

