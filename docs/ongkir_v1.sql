-- ============================================================
-- Ongkir (biaya pengiriman) saat penerimaan barang v1
-- Jalankan manual: mysql -uroot myjdm2 < ongkir_v1.sql
-- ============================================================

-- Tambah akun "Beban Angkut Pembelian" ke Chart of Accounts (di bawah grup Beban Operasional)
INSERT INTO finance_coa (coa_code, coa_name, coa_type, coa_subtype, parent_id, normal_balance, is_postable, is_system)
SELECT '6104', 'Beban Angkut Pembelian', 'beban', 'beban_angkut_pembelian', coa_id, 'debit', 1, 1
FROM finance_coa WHERE coa_code = '6000';

-- Tambah kolom ongkir di po_receipt
ALTER TABLE po_receipt
  ADD COLUMN ongkir INT NOT NULL DEFAULT 0,
  ADD COLUMN ongkir_payment_method ENUM('cash','transfer') NULL,
  ADD COLUMN ongkir_journal_id INT NULL,
  ADD CONSTRAINT fk_po_receipt_journal FOREIGN KEY (ongkir_journal_id) REFERENCES finance_journal(journal_id);
