-- Backfill: data penerimaan yang sudah ada sebelum fitur "Selesai Dilabeli" dibuat
-- dianggap sudah dilabeli & disimpan (alur manual lama). Jalankan sekali saja,
-- setelah alter_po_receipt_label_status.sql.

UPDATE po_receipt
SET label_status = 'labeled'
WHERE label_status = 'pending';
