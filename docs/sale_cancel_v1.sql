-- Menambahkan kemampuan "Batal Transaksi" pada t_sale.
-- Sebelumnya, "Hapus" di Laporan Penjualan langsung DELETE FROM t_sale, yang gagal
-- (foreign key constraint) untuk transaksi kredit yang sudah punya ar_invoice terkait,
-- karena ar_invoice.sale_id -> t_sale.sale_id tanpa ON DELETE CASCADE (dan memang tidak
-- boleh CASCADE, supaya histori piutang/jurnal tidak ikut hilang).
--
-- Solusinya: transaksi tidak lagi di-DELETE fisik, melainkan ditandai dibatalkan
-- (row t_sale tetap ada untuk audit trail), stok dikembalikan, dan piutang terkait
-- (kalau ada & belum dibayar) di-void otomatis lewat modul Finance.

ALTER TABLE t_sale
  ADD COLUMN is_cancelled  TINYINT(1) NOT NULL DEFAULT 0 AFTER payment_status,
  ADD COLUMN cancel_reason VARCHAR(255) DEFAULT NULL AFTER is_cancelled,
  ADD COLUMN cancelled_by  INT DEFAULT NULL AFTER cancel_reason,
  ADD COLUMN cancelled_at  DATETIME DEFAULT NULL AFTER cancelled_by,
  ADD CONSTRAINT fk_sale_cancelled_by FOREIGN KEY (cancelled_by) REFERENCES user(user_id);
