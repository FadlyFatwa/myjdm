-- ============================================================
-- Kontra Bon v1: Konsolidasi tagihan piutang (gabung beberapa nota
-- dalam satu periode jadi satu tagihan dengan satu jatuh tempo)
-- Jalankan manual: mysql -uroot myjdm2 < kontra_bon_v1.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1. ar_kontra_bon (header tagihan gabungan)
-- ------------------------------------------------------------
CREATE TABLE ar_kontra_bon (
  kontra_bon_id       INT NOT NULL AUTO_INCREMENT,
  kontra_bon_no       VARCHAR(30) NOT NULL,
  customer_id         INT NOT NULL,
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
  UNIQUE KEY uq_kontra_bon_no (kontra_bon_no),
  KEY idx_customer (customer_id),
  KEY idx_status (status),
  KEY idx_due (due_date),
  CONSTRAINT fk_kb_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
  CONSTRAINT fk_kb_user FOREIGN KEY (created_by) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 2. ALTER ar_invoice: tambah link ke kontra bon
-- ------------------------------------------------------------
ALTER TABLE ar_invoice
  ADD COLUMN kontra_bon_id INT NULL AFTER sale_id,
  ADD KEY idx_kontra_bon (kontra_bon_id),
  ADD CONSTRAINT fk_ar_invoice_kontra_bon FOREIGN KEY (kontra_bon_id) REFERENCES ar_kontra_bon(kontra_bon_id);

-- ------------------------------------------------------------
-- 3. ar_kontra_bon_payment (pembayaran terhadap kontra bon)
-- ------------------------------------------------------------
CREATE TABLE ar_kontra_bon_payment (
  kontra_bon_payment_id INT NOT NULL AUTO_INCREMENT,
  payment_no            VARCHAR(30) NOT NULL,
  kontra_bon_id         INT NOT NULL,
  payment_date          DATE NOT NULL,
  amount                INT NOT NULL,
  payment_method        ENUM('cash','transfer','qris','debit') NOT NULL,
  notes                 VARCHAR(255) DEFAULT NULL,
  received_by           INT NOT NULL,
  journal_id            INT DEFAULT NULL,
  is_void               TINYINT(1) NOT NULL DEFAULT 0,
  voided_at             DATETIME DEFAULT NULL,
  voided_by             INT DEFAULT NULL,
  void_reason           VARCHAR(255) DEFAULT NULL,
  created_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (kontra_bon_payment_id),
  UNIQUE KEY uq_kb_payment_no (payment_no),
  KEY idx_kontra_bon (kontra_bon_id),
  CONSTRAINT fk_kbp_kontra_bon FOREIGN KEY (kontra_bon_id) REFERENCES ar_kontra_bon(kontra_bon_id),
  CONSTRAINT fk_kbp_user FOREIGN KEY (received_by) REFERENCES user(user_id),
  CONSTRAINT fk_kbp_journal FOREIGN KEY (journal_id) REFERENCES finance_journal(journal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 4. ar_kontra_bon_payment_detail (jejak distribusi FIFO per nota)
-- ------------------------------------------------------------
CREATE TABLE ar_kontra_bon_payment_detail (
  id                     INT NOT NULL AUTO_INCREMENT,
  kontra_bon_payment_id  INT NOT NULL,
  ar_invoice_id          INT NOT NULL,
  amount_allocated       INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_kb_payment (kontra_bon_payment_id),
  KEY idx_ar_invoice (ar_invoice_id),
  CONSTRAINT fk_kbpd_payment FOREIGN KEY (kontra_bon_payment_id) REFERENCES ar_kontra_bon_payment(kontra_bon_payment_id),
  CONSTRAINT fk_kbpd_invoice FOREIGN KEY (ar_invoice_id) REFERENCES ar_invoice(ar_invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
