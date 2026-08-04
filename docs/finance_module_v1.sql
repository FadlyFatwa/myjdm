-- ============================================================
-- Finance Module v1: Chart of Accounts + Jurnal Umum + AR (Piutang)
-- Jalankan manual: mysql -uroot myjdm2 < finance_module_v1.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1. finance_coa (Chart of Accounts)
-- ------------------------------------------------------------
CREATE TABLE finance_coa (
  coa_id          INT NOT NULL AUTO_INCREMENT,
  coa_code        VARCHAR(20) NOT NULL,
  coa_name        VARCHAR(150) NOT NULL,
  coa_type        ENUM('aset','kewajiban','modal','pendapatan','beban') NOT NULL,
  coa_subtype     VARCHAR(50) DEFAULT NULL COMMENT 'kunci lookup program: piutang_usaha, kas, bank, hutang_usaha, ppn_keluaran, ppn_masukan, persediaan, hpp, pendapatan_penjualan, dst',
  parent_id       INT DEFAULT NULL,
  normal_balance  ENUM('debit','kredit') NOT NULL,
  is_postable     TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = akun header/grup, tidak boleh dipakai jurnal langsung',
  is_active       TINYINT(1) NOT NULL DEFAULT 1,
  is_system       TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = dipakai auto-posting program, tidak boleh dihapus dari UI',
  description     VARCHAR(255) DEFAULT NULL,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (coa_id),
  UNIQUE KEY uq_coa_code (coa_code),
  KEY idx_parent (parent_id),
  KEY idx_type (coa_type),
  KEY idx_subtype (coa_subtype),
  CONSTRAINT fk_coa_parent FOREIGN KEY (parent_id) REFERENCES finance_coa(coa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed default COA -----------------------------------------------------

-- ASET
INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('1000','ASET','aset',NULL,NULL,'debit',0,0);
SET @aset := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('1100','Kas & Bank','aset',NULL,@aset,'debit',0,0);
SET @kasbank := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('1101','Kas','aset','kas',@kasbank,'debit',1,1),
('1102','Bank','aset','bank',@kasbank,'debit',1,1);

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('1200','Piutang','aset',NULL,@aset,'debit',0,0);
SET @piutang := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('1201','Piutang Usaha','aset','piutang_usaha',@piutang,'debit',1,1);

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('1300','Persediaan','aset',NULL,@aset,'debit',0,0);
SET @persediaan := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('1301','Persediaan Barang Dagang','aset','persediaan',@persediaan,'debit',1,1),
('1401','PPN Masukan','aset','ppn_masukan',@aset,'debit',1,1);

-- KEWAJIBAN
INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('2000','KEWAJIBAN','kewajiban',NULL,NULL,'kredit',0,0);
SET @kewajiban := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('2100','Hutang Usaha','kewajiban',NULL,@kewajiban,'kredit',0,0);
SET @hutang := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('2101','Hutang Usaha - Supplier','kewajiban','hutang_usaha',@hutang,'kredit',1,1),
('2201','PPN Keluaran','kewajiban','ppn_keluaran',@kewajiban,'kredit',1,1);

-- MODAL
INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('3000','MODAL','modal',NULL,NULL,'kredit',0,0);
SET @modal := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('3101','Modal Pemilik','modal','modal_pemilik',@modal,'kredit',1,0),
('3102','Laba Ditahan','modal','laba_ditahan',@modal,'kredit',1,0);

-- PENDAPATAN
INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('4000','PENDAPATAN','pendapatan',NULL,NULL,'kredit',0,0);
SET @pendapatan := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('4101','Pendapatan Penjualan','pendapatan','pendapatan_penjualan',@pendapatan,'kredit',1,1),
('4102','Pendapatan Jasa Service','pendapatan','pendapatan_jasa',@pendapatan,'kredit',1,0),
('4103','Pendapatan Lain-lain','pendapatan','pendapatan_lain',@pendapatan,'kredit',1,0),
('4201','Retur Penjualan','pendapatan','retur_penjualan',@pendapatan,'debit',1,0);

-- HPP
INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('5000','HARGA POKOK PENJUALAN','beban',NULL,NULL,'debit',0,0);
SET @hpp := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('5101','HPP Barang Dagang','beban','hpp',@hpp,'debit',1,0);

-- BEBAN
INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('6000','BEBAN OPERASIONAL','beban',NULL,NULL,'debit',0,0);
SET @beban := LAST_INSERT_ID();

INSERT INTO finance_coa (coa_code,coa_name,coa_type,coa_subtype,parent_id,normal_balance,is_postable,is_system) VALUES
('6101','Beban Operasional','beban','beban_operasional',@beban,'debit',1,0),
('6102','Beban Administrasi & Umum','beban','beban_adm',@beban,'debit',1,0),
('6103','Beban Piutang Tak Tertagih','beban','beban_piutang_ragu',@beban,'debit',1,0);

-- ------------------------------------------------------------
-- 2. finance_journal + finance_journal_detail (Jurnal Umum)
-- ------------------------------------------------------------
CREATE TABLE finance_journal (
  journal_id     INT NOT NULL AUTO_INCREMENT,
  journal_no     VARCHAR(30) NOT NULL,
  journal_date   DATE NOT NULL,
  source_type    VARCHAR(30) NOT NULL COMMENT 'ar_invoice, ar_payment, ap_invoice(future), ap_payment(future), kas_bank(future), manual_adjustment',
  source_id      INT DEFAULT NULL COMMENT 'polymorphic ref ke tabel sumber sesuai source_type, tanpa FK constraint',
  description    VARCHAR(255) NOT NULL,
  total_debit    INT NOT NULL DEFAULT 0,
  total_kredit   INT NOT NULL DEFAULT 0,
  status         ENUM('posted','void') NOT NULL DEFAULT 'posted',
  created_by     INT NOT NULL,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  voided_at      DATETIME DEFAULT NULL,
  voided_by      INT DEFAULT NULL,
  void_reason    VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (journal_id),
  UNIQUE KEY uq_journal_no (journal_no),
  KEY idx_source (source_type, source_id),
  KEY idx_date (journal_date),
  CONSTRAINT fk_journal_user FOREIGN KEY (created_by) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE finance_journal_detail (
  journal_detail_id INT NOT NULL AUTO_INCREMENT,
  journal_id        INT NOT NULL,
  coa_id            INT NOT NULL,
  debit             INT NOT NULL DEFAULT 0,
  kredit            INT NOT NULL DEFAULT 0,
  notes             VARCHAR(255) DEFAULT NULL,
  created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (journal_detail_id),
  KEY idx_journal (journal_id),
  KEY idx_coa (coa_id),
  CONSTRAINT fk_jd_journal FOREIGN KEY (journal_id) REFERENCES finance_journal(journal_id) ON DELETE CASCADE,
  CONSTRAINT fk_jd_coa FOREIGN KEY (coa_id) REFERENCES finance_coa(coa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 3. v_general_ledger (Buku Besar - VIEW, bukan tabel fisik)
-- ------------------------------------------------------------
CREATE VIEW v_general_ledger AS
SELECT
  jd.journal_detail_id, j.journal_id, j.journal_no, j.journal_date,
  j.description AS journal_description, j.source_type, j.source_id, j.status,
  jd.coa_id, c.coa_code, c.coa_name, c.coa_type,
  jd.debit, jd.kredit, jd.notes
FROM finance_journal_detail jd
JOIN finance_journal j ON j.journal_id = jd.journal_id
JOIN finance_coa c ON c.coa_id = jd.coa_id
WHERE j.status = 'posted';

-- ------------------------------------------------------------
-- 4. ar_invoice (Piutang header)
-- ------------------------------------------------------------
CREATE TABLE ar_invoice (
  ar_invoice_id       INT NOT NULL AUTO_INCREMENT,
  ar_no               VARCHAR(30) NOT NULL,
  source              ENUM('sale','manual') NOT NULL,
  sale_id             INT DEFAULT NULL,
  customer_id         INT NOT NULL,
  invoice_date        DATE NOT NULL,
  due_date            DATE NOT NULL,
  description         VARCHAR(255) DEFAULT NULL,
  amount              INT NOT NULL,
  paid_amount         INT NOT NULL DEFAULT 0,
  outstanding_amount  INT NOT NULL,
  status              ENUM('outstanding','partial','paid','void') NOT NULL DEFAULT 'outstanding',
  created_by          INT NOT NULL,
  created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  voided_at           DATETIME DEFAULT NULL,
  voided_by           INT DEFAULT NULL,
  void_reason         VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (ar_invoice_id),
  UNIQUE KEY uq_ar_no (ar_no),
  UNIQUE KEY uq_sale_id (sale_id),
  KEY idx_customer (customer_id),
  KEY idx_status (status),
  KEY idx_due (due_date),
  CONSTRAINT fk_ar_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
  CONSTRAINT fk_ar_sale FOREIGN KEY (sale_id) REFERENCES t_sale(sale_id),
  CONSTRAINT fk_ar_user FOREIGN KEY (created_by) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 5. ar_payment (Histori pembayaran/cicilan)
-- ------------------------------------------------------------
CREATE TABLE ar_payment (
  ar_payment_id   INT NOT NULL AUTO_INCREMENT,
  payment_no      VARCHAR(30) NOT NULL,
  ar_invoice_id   INT NOT NULL,
  payment_date    DATE NOT NULL,
  amount          INT NOT NULL,
  payment_method  ENUM('cash','transfer','qris','debit') NOT NULL,
  notes           VARCHAR(255) DEFAULT NULL,
  received_by     INT NOT NULL,
  journal_id      INT DEFAULT NULL,
  is_void         TINYINT(1) NOT NULL DEFAULT 0,
  voided_at       DATETIME DEFAULT NULL,
  voided_by       INT DEFAULT NULL,
  void_reason     VARCHAR(255) DEFAULT NULL,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ar_payment_id),
  UNIQUE KEY uq_payment_no (payment_no),
  KEY idx_ar_invoice (ar_invoice_id),
  KEY idx_payment_date (payment_date),
  CONSTRAINT fk_arp_invoice FOREIGN KEY (ar_invoice_id) REFERENCES ar_invoice(ar_invoice_id),
  CONSTRAINT fk_arp_user FOREIGN KEY (received_by) REFERENCES user(user_id),
  CONSTRAINT fk_arp_journal FOREIGN KEY (journal_id) REFERENCES finance_journal(journal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 6. ALTER TABLE customer (tambahan field finance)
-- ------------------------------------------------------------
ALTER TABLE customer
  ADD COLUMN credit_limit      INT NOT NULL DEFAULT 0 AFTER alamat,
  ADD COLUMN payment_term_days INT NOT NULL DEFAULT 0 AFTER credit_limit,
  ADD COLUMN ar_balance        INT NOT NULL DEFAULT 0 AFTER payment_term_days;
