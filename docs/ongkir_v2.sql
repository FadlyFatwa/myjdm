-- ============================================================
-- Ongkir v2: link ke Beban Operasional (supaya ongkir penerimaan
-- muncul juga di listing "Beban Operasional", bukan cuma di jurnal)
-- Jalankan manual: mysql -uroot myjdm2 < ongkir_v2.sql
-- (harus dijalankan SETELAH beban_operasional_v1.sql)
-- ============================================================

ALTER TABLE po_receipt
  ADD COLUMN ongkir_expense_id INT NULL AFTER ongkir_journal_id,
  ADD CONSTRAINT fk_po_receipt_expense FOREIGN KEY (ongkir_expense_id) REFERENCES beban_operasional(expense_id);
