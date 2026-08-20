-- ============================================================
-- PPN 3 Mode (none / add_distribute / inclusive) v1
-- Ganti kena_ppn + ppn_masuk_harga_beli (2 boolean independen)
-- jadi 1 kolom mode karena ternyata 3 pilihan saling eksklusif,
-- bukan kombinasi bebas.
-- Jalankan manual: mysql -uroot myjdm < po_ppn_mode_v1.sql
-- ============================================================

ALTER TABLE po_receipt
  DROP COLUMN kena_ppn,
  DROP COLUMN ppn_masuk_harga_beli,
  ADD COLUMN ppn_mode ENUM('none','add_distribute','inclusive') NOT NULL DEFAULT 'none'
    COMMENT 'none=tanpa PPN | add_distribute=PPN dihitung dari subtotal lalu didistribusi ke harga beli tiap item | inclusive=harga beli sudah termasuk PPN, diekstrak cuma buat catatan'
    AFTER diskon_invoice;
