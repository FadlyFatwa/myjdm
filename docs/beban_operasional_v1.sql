-- ============================================================
-- Kelola Beban Operasional v1
-- Jalankan manual: mysql -uroot myjdm2 < beban_operasional_v1.sql
-- ============================================================

CREATE TABLE beban_operasional (
  expense_id      INT NOT NULL AUTO_INCREMENT,
  expense_no      VARCHAR(30) NOT NULL,
  expense_date    DATE NOT NULL,
  coa_id          INT NOT NULL COMMENT 'Kategori beban (akun COA bertipe beban)',
  amount          INT NOT NULL,
  payment_method  ENUM('cash','transfer') NOT NULL,
  description     VARCHAR(255) NOT NULL,
  journal_id      INT DEFAULT NULL,
  created_by      INT NOT NULL,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  is_void         TINYINT(1) NOT NULL DEFAULT 0,
  voided_at       DATETIME DEFAULT NULL,
  voided_by       INT DEFAULT NULL,
  void_reason     VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (expense_id),
  UNIQUE KEY uq_expense_no (expense_no),
  KEY idx_coa (coa_id),
  KEY idx_date (expense_date),
  CONSTRAINT fk_beban_coa FOREIGN KEY (coa_id) REFERENCES finance_coa(coa_id),
  CONSTRAINT fk_beban_journal FOREIGN KEY (journal_id) REFERENCES finance_journal(journal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
