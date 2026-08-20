-- ============================================================
-- AP Module v1: Hutang (Accounts Payable) — mirror dari Finance
-- Module v1 (AR) + Kontra Bon v1, arah kebalik (supplier, bukan
-- customer). COA hutang_usaha (2101) & persediaan (1301) sudah
-- ada dari finance_module_v1.sql, tidak perlu seed ulang.
-- Jalankan manual: mysql -uroot myjdm < ap_module_v1.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1. ALTER TABLE supplier (tambahan field finance)
-- ------------------------------------------------------------
ALTER TABLE supplier
  ADD COLUMN payment_term_days INT NOT NULL DEFAULT 0 AFTER keterangan,
  ADD COLUMN ap_balance        INT NOT NULL DEFAULT 0 AFTER payment_term_days;

-- ------------------------------------------------------------
-- 2. ap_invoice (hutang per resi penerimaan)
-- ------------------------------------------------------------
CREATE TABLE ap_invoice (
  ap_invoice_id       INT NOT NULL AUTO_INCREMENT,
  ap_no               VARCHAR(30) NOT NULL,
  receipt_id          INT NULL,          -- nullable: po_receipt bisa dihapus fisik (delete_receipt/delete_receipt_detail),
                                          -- ap_invoice TIDAK ikut terhapus (void-only), jadi FK-nya ON DELETE SET NULL
  supplier_id         INT NOT NULL,
  invoice_date        DATE NOT NULL,
  due_date            DATE NOT NULL,
  description         VARCHAR(255) DEFAULT NULL,
  amount              INT NOT NULL,
  paid_amount         INT NOT NULL DEFAULT 0,
  outstanding_amount  INT NOT NULL,
  status              ENUM('outstanding','partial','paid','void') NOT NULL DEFAULT 'outstanding',
  payment_type        ENUM('cash','credit') NOT NULL,
  kontra_bon_id        INT NULL,
  created_by          INT NOT NULL,
  created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  voided_at           DATETIME DEFAULT NULL,
  voided_by           INT DEFAULT NULL,
  void_reason         VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (ap_invoice_id),
  UNIQUE KEY uq_ap_no (ap_no),
  UNIQUE KEY uq_receipt_id (receipt_id),
  KEY idx_supplier (supplier_id),
  KEY idx_status (status),
  KEY idx_due (due_date),
  KEY idx_kontra_bon (kontra_bon_id),
  CONSTRAINT fk_ap_receipt FOREIGN KEY (receipt_id) REFERENCES po_receipt(receipt_id) ON DELETE SET NULL,
  CONSTRAINT fk_ap_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id),
  CONSTRAINT fk_ap_user FOREIGN KEY (created_by) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 3. ap_payment (histori pembayaran/cicilan per invoice)
-- ------------------------------------------------------------
CREATE TABLE ap_payment (
  ap_payment_id   INT NOT NULL AUTO_INCREMENT,
  payment_no      VARCHAR(30) NOT NULL,
  ap_invoice_id   INT NOT NULL,
  payment_date    DATE NOT NULL,
  amount          INT NOT NULL,
  payment_method  ENUM('cash','transfer','qris','debit') NOT NULL,
  notes           VARCHAR(255) DEFAULT NULL,
  paid_by         INT NOT NULL,
  journal_id      INT DEFAULT NULL,
  is_void         TINYINT(1) NOT NULL DEFAULT 0,
  voided_at       DATETIME DEFAULT NULL,
  voided_by       INT DEFAULT NULL,
  void_reason     VARCHAR(255) DEFAULT NULL,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ap_payment_id),
  UNIQUE KEY uq_ap_payment_no (payment_no),
  KEY idx_ap_invoice (ap_invoice_id),
  KEY idx_payment_date (payment_date),
  CONSTRAINT fk_app_invoice FOREIGN KEY (ap_invoice_id) REFERENCES ap_invoice(ap_invoice_id),
  CONSTRAINT fk_app_user FOREIGN KEY (paid_by) REFERENCES user(user_id),
  CONSTRAINT fk_app_journal FOREIGN KEY (journal_id) REFERENCES finance_journal(journal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 4. ap_kontra_bon (header tagihan gabungan hutang)
-- ------------------------------------------------------------
CREATE TABLE ap_kontra_bon (
  kontra_bon_id       INT NOT NULL AUTO_INCREMENT,
  kontra_bon_no       VARCHAR(30) NOT NULL,
  supplier_id         INT NOT NULL,
  period_start        DATE NOT NULL,
  period_end          DATE NOT NULL,
  due_date            DATE NOT NULL,
  total_amount        INT NOT NULL,
  paid_amount         INT NOT NULL DEFAULT 0,
  outstanding_amount  INT NOT NULL,
  status              ENUM('outstanding','partial','paid','void') NOT NULL DEFAULT 'outstanding',
  created_by          INT NOT NULL,
  created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
  voided_at           DATETIME DEFAULT NULL,
  voided_by           INT DEFAULT NULL,
  void_reason         VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (kontra_bon_id),
  UNIQUE KEY uq_ap_kontra_bon_no (kontra_bon_no),
  KEY idx_supplier (supplier_id),
  KEY idx_status (status),
  KEY idx_due (due_date),
  CONSTRAINT fk_apkb_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id),
  CONSTRAINT fk_apkb_user FOREIGN KEY (created_by) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 5. ALTER ap_invoice: link ke kontra bon (setelah ap_kontra_bon ada)
-- ------------------------------------------------------------
ALTER TABLE ap_invoice
  ADD CONSTRAINT fk_ap_invoice_kontra_bon FOREIGN KEY (kontra_bon_id) REFERENCES ap_kontra_bon(kontra_bon_id);

-- ------------------------------------------------------------
-- 6. ap_kontra_bon_payment (pembayaran terhadap kontra bon hutang)
-- ------------------------------------------------------------
CREATE TABLE ap_kontra_bon_payment (
  kontra_bon_payment_id INT NOT NULL AUTO_INCREMENT,
  payment_no            VARCHAR(30) NOT NULL,
  kontra_bon_id         INT NOT NULL,
  payment_date          DATE NOT NULL,
  amount                INT NOT NULL,
  payment_method        ENUM('cash','transfer','qris','debit') NOT NULL,
  notes                 VARCHAR(255) DEFAULT NULL,
  paid_by               INT NOT NULL,
  journal_id            INT DEFAULT NULL,
  is_void               TINYINT(1) NOT NULL DEFAULT 0,
  voided_at             DATETIME DEFAULT NULL,
  voided_by             INT DEFAULT NULL,
  void_reason           VARCHAR(255) DEFAULT NULL,
  created_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (kontra_bon_payment_id),
  UNIQUE KEY uq_apkb_payment_no (payment_no),
  KEY idx_kontra_bon (kontra_bon_id),
  CONSTRAINT fk_apkbp_kontra_bon FOREIGN KEY (kontra_bon_id) REFERENCES ap_kontra_bon(kontra_bon_id),
  CONSTRAINT fk_apkbp_user FOREIGN KEY (paid_by) REFERENCES user(user_id),
  CONSTRAINT fk_apkbp_journal FOREIGN KEY (journal_id) REFERENCES finance_journal(journal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 7. ap_kontra_bon_payment_detail (jejak distribusi FIFO per invoice)
-- ------------------------------------------------------------
CREATE TABLE ap_kontra_bon_payment_detail (
  id                     INT NOT NULL AUTO_INCREMENT,
  kontra_bon_payment_id  INT NOT NULL,
  ap_invoice_id          INT NOT NULL,
  amount_allocated       INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_apkb_payment (kontra_bon_payment_id),
  KEY idx_ap_invoice (ap_invoice_id),
  CONSTRAINT fk_apkbpd_payment FOREIGN KEY (kontra_bon_payment_id) REFERENCES ap_kontra_bon_payment(kontra_bon_payment_id),
  CONSTRAINT fk_apkbpd_invoice FOREIGN KEY (ap_invoice_id) REFERENCES ap_invoice(ap_invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
