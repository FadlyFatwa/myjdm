-- ============================================================
-- Harga List, Diskon Item, dan PPN/Diskon Invoice di Penerimaan v1
-- Jalankan manual: mysql -uroot myjdm < po_harga_list_ppn_v1.sql
-- ============================================================

ALTER TABLE po_detail
  ADD COLUMN harga_list DECIMAL(12,2) DEFAULT NULL COMMENT 'Harga sebelum diskon, opsional',
  ADD COLUMN diskon_persen DECIMAL(5,2) DEFAULT NULL COMMENT 'Diskon dari harga_list, opsional';

ALTER TABLE po_receipt
  ADD COLUMN diskon_invoice INT NOT NULL DEFAULT 0 COMMENT 'Potongan nominal dari total invoice, opsional',
  ADD COLUMN kena_ppn TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Checkbox 1: invoice ini kena PPN atau tidak',
  ADD COLUMN ppn_masuk_harga_beli TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Checkbox 2 (relevan kalau kena_ppn=1): PPN didistribusi ke harga beli tiap item, atau tetap kepisah cuma nambah total tagihan',
  ADD COLUMN ppn_persen DECIMAL(5,2) DEFAULT NULL COMMENT 'Tarif efektif PPN yang dipakai saat itu (auto-set 11.00 dari rumus 11/12x12% kalau kena_ppn=1), disimpan untuk jejak audit',
  ADD COLUMN ppn_nominal INT NOT NULL DEFAULT 0 COMMENT 'Nominal PPN hasil hitungan, 0 kalau kena_ppn=0',
  ADD COLUMN total_amount INT NOT NULL DEFAULT 0 COMMENT 'Subtotal barang - diskon_invoice + ppn_nominal (TIDAK termasuk ongkir)';
