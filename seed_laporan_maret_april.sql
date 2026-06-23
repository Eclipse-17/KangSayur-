-- Seed data laporan_penjualan & laporan_stok untuk Maret dan April (contoh)
-- Sesuaikan YEAR/TOTAL jika Anda butuh.

-- GANTI TAHUN bila perlu
SET @year_maret = 2026;
SET @year_april = 2026;

-- Harga contoh (akan dipakai hanya untuk nilai stok & penjualan di tabel laporan)
-- Kasir_id contoh: gunakan salah satu user kasir yang ada di tabel users (role=kasir)
-- Dari dump: id kasir = 3
SET @kasir_id = 3;

-- LIST sayuran yang ada (dari dump): 1..8
-- Untuk sederhana, seed untuk semua sayuran.

-- =======================
-- INSERT / UPSERT laporan_penjualan
-- =======================
-- Kita isi setiap hari di Maret dan April dengan pola qty/stok contoh.
-- NOTE: Ini seed, jadi bisa Anda modifikasi.

DELIMITER $$

-- Maret
CREATE PROCEDURE seed_maret()
BEGIN
  DECLARE d INT DEFAULT 1;
  DECLARE maxd INT DEFAULT 31;
  DECLARE t DATE;
  WHILE d <= maxd DO
    SET t = CONCAT(@year_maret, '-03-', LPAD(d,2,'0'));
    -- insert/upsert per sayuran
    INSERT INTO laporan_penjualan (tanggal_laporan, sayuran_id, jumlah_terjual, total_penjualan, keuntungan, kasir_id, created_at)
    SELECT
      t,
      s.id,
      2 + (s.id % 3) AS qty,
      ( (2 + (s.id % 3)) * s.harga_jual ) AS total_penjualan,
      ( (2 + (s.id % 3)) * (s.harga_jual - s.harga_beli) ) AS keuntungan,
      @kasir_id,
      NOW()
    FROM sayuran s
    WHERE s.status='aktif'
    ON DUPLICATE KEY UPDATE
      jumlah_terjual = laporan_penjualan.jumlah_terjual + VALUES(jumlah_terjual),
      total_penjualan = laporan_penjualan.total_penjualan + VALUES(total_penjualan),
      keuntungan = COALESCE(laporan_penjualan.keuntungan,0) + VALUES(keuntungan);

    SET d = d + 1;
  END WHILE;
END$$

-- April
CREATE PROCEDURE seed_april()
BEGIN
  DECLARE d INT DEFAULT 1;
  DECLARE maxd INT DEFAULT 30;
  DECLARE t DATE;
  WHILE d <= maxd DO
    SET t = CONCAT(@year_april, '-04-', LPAD(d,2,'0'));

    INSERT INTO laporan_penjualan (tanggal_laporan, sayuran_id, jumlah_terjual, total_penjualan, keuntungan, kasir_id, created_at)
    SELECT
      t,
      s.id,
      3 + (s.id % 4) AS qty,
      ( (3 + (s.id % 4)) * s.harga_jual ) AS total_penjualan,
      ( (3 + (s.id % 4)) * (s.harga_jual - s.harga_beli) ) AS keuntungan,
      @kasir_id,
      NOW()
    FROM sayuran s
    WHERE s.status='aktif'
    ON DUPLICATE KEY UPDATE
      jumlah_terjual = laporan_penjualan.jumlah_terjual + VALUES(jumlah_terjual),
      total_penjualan = laporan_penjualan.total_penjualan + VALUES(total_penjualan),
      keuntungan = COALESCE(laporan_penjualan.keuntungan,0) + VALUES(keuntungan);

    SET d = d + 1;
  END WHILE;
END$$

DELIMITER ;

-- Seed laporan stok
-- Kita isi per hari stok sederhana: stok_awal = 50 + sayuran_id*2, stok_masuk/keluar contoh
-- (Karena laporan_stok butuh stok_awal/stok_akhir/nilai_stok; stok_akhir dihitung sederhana)

DELIMITER $$
CREATE PROCEDURE seed_laporan_stok_maret()
BEGIN
  DECLARE d INT DEFAULT 1;
  DECLARE maxd INT DEFAULT 31;
  DECLARE t DATE;
  WHILE d <= maxd DO
    SET t = CONCAT(@year_maret, '-03-', LPAD(d,2,'0'));

    INSERT INTO laporan_stok (tanggal_laporan, sayuran_id, stok_awal, stok_masuk, stok_keluar, stok_rusak, stok_akhir, nilai_stok, created_at)
    SELECT
      t,
      s.id,
      50 + (s.id * 2) AS stok_awal,
      10 AS stok_masuk,
      8 AS stok_keluar,
      1 AS stok_rusak,
      ( (50 + (s.id * 2)) + 10 - 8 - 1 ) AS stok_akhir,
      ( ( (50 + (s.id * 2)) + 10 - 8 - 1 ) * s.harga_beli ) AS nilai_stok,
      NOW()
    FROM sayuran s
    WHERE s.status='aktif'
    ON DUPLICATE KEY UPDATE
      stok_masuk = laporan_stok.stok_masuk + VALUES(stok_masuk),
      stok_keluar = laporan_stok.stok_keluar + VALUES(stok_keluar),
      stok_rusak = laporan_stok.stok_rusak + VALUES(stok_rusak),
      stok_akhir = VALUES(stok_akhir),
      nilai_stok = VALUES(nilai_stok),
      stok_awal = VALUES(stok_awal);

    SET d = d + 1;
  END WHILE;
END$$

CREATE PROCEDURE seed_laporan_stok_april()
BEGIN
  DECLARE d INT DEFAULT 1;
  DECLARE maxd INT DEFAULT 30;
  DECLARE t DATE;
  WHILE d <= maxd DO
    SET t = CONCAT(@year_april, '-04-', LPAD(d,2,'0'));

    INSERT INTO laporan_stok (tanggal_laporan, sayuran_id, stok_awal, stok_masuk, stok_keluar, stok_rusak, stok_akhir, nilai_stok, created_at)
    SELECT
      t,
      s.id,
      60 + (s.id * 2) AS stok_awal,
      12 AS stok_masuk,
      9 AS stok_keluar,
      0 AS stok_rusak,
      ( (60 + (s.id * 2)) + 12 - 9 - 0 ) AS stok_akhir,
      ( ( (60 + (s.id * 2)) + 12 - 9 - 0 ) * s.harga_beli ) AS nilai_stok,
      NOW()
    FROM sayuran s
    WHERE s.status='aktif'
    ON DUPLICATE KEY UPDATE
      stok_masuk = laporan_stok.stok_masuk + VALUES(stok_masuk),
      stok_keluar = laporan_stok.stok_keluar + VALUES(stok_keluar),
      stok_rusak = laporan_stok.stok_rusak + VALUES(stok_rusak),
      stok_akhir = VALUES(stok_akhir),
      nilai_stok = VALUES(nilai_stok),
      stok_awal = VALUES(stok_awal);

    SET d = d + 1;
  END WHILE;
END$$
DELIMITER ;

-- Jalankan procedure
CALL seed_maret();
CALL seed_april();
CALL seed_laporan_stok_maret();
CALL seed_laporan_stok_april();

-- Cleanup (opsional)
DROP PROCEDURE IF EXISTS seed_maret;
DROP PROCEDURE IF EXISTS seed_april;
DROP PROCEDURE IF EXISTS seed_laporan_stok_maret;
DROP PROCEDURE IF EXISTS seed_laporan_stok_april;

