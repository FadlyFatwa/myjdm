-- ============================================================
-- Penerimaan Barang Fleksibel (Item Ekstra + Tanpa PO) v1
-- Jalankan manual: mysql -uroot myjdm < po_direct_receiving_v1.sql
-- ============================================================

ALTER TABLE po_header ADD COLUMN is_direct TINYINT(1) NOT NULL DEFAULT 0
  COMMENT 'PO dibuat langsung dari penerimaan tanpa PO formal (WA-order dkk)';
