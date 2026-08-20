-- Menambahkan kolom status pelabelan barang ke po_receipt
-- Dipakai untuk fitur notifikasi WA "Selesai Dilabeli" pada Purchase_order::mark_labeled()

ALTER TABLE po_receipt
  ADD COLUMN label_status ENUM('pending','labeled') NOT NULL DEFAULT 'pending' AFTER ongkir_expense_id,
  ADD COLUMN labeled_at DATETIME NULL AFTER label_status,
  ADD COLUMN labeled_by INT NULL AFTER labeled_at;
