-- ============================================================
-- AR Gross Amount v1: dukung kesepakatan brutto/netto per customer
-- untuk Piutang Manual + tampilan Subtotal brutto di cetak Kontra Bon.
-- Jalankan manual: mysql -uroot myjdm < ar_gross_amount_v1.sql
-- ============================================================

ALTER TABLE customer
  ADD COLUMN gross_discount_percent DECIMAL(5,2) NULL AFTER payment_term_days;

ALTER TABLE ar_invoice
  ADD COLUMN gross_amount INT NULL AFTER amount;
