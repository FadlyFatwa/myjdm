-- ============================================================
-- Uang Makan Karyawan Berbasis Kehadiran v1
-- Jalankan manual: mysql -uroot myjdm < uang_makan_v1.sql
-- ============================================================

-- COA baru: child dari BEBAN OPERASIONAL (coa_id 24)
INSERT INTO finance_coa (coa_code, coa_name, coa_type, coa_subtype, parent_id, is_postable, is_active)
VALUES ('6105', 'Beban Uang Makan Karyawan', 'beban', 'beban_uang_makan', 24, 1, 1);

-- Master karyawan (terpisah dari tabel user, karena tidak semua karyawan punya akun login)
CREATE TABLE karyawan (
  karyawan_id  INT NOT NULL AUTO_INCREMENT,
  nama         VARCHAR(60) NOT NULL,
  user_id      INT DEFAULT NULL COMMENT 'Opsional: penghubung ke akun login jika karyawan ini juga staf sistem',
  is_active    TINYINT(1) NOT NULL DEFAULT 1,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (karyawan_id),
  KEY idx_user (user_id),
  CONSTRAINT fk_karyawan_user FOREIGN KEY (user_id) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Absensi harian: satu baris per karyawan yang HADIR pada tanggal itu
CREATE TABLE absensi_harian (
  absensi_id   INT NOT NULL AUTO_INCREMENT,
  tanggal      DATE NOT NULL,
  karyawan_id  INT NOT NULL,
  created_by   INT NOT NULL,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (absensi_id),
  UNIQUE KEY uq_tanggal_karyawan (tanggal, karyawan_id),
  KEY idx_tanggal (tanggal),
  CONSTRAINT fk_absensi_karyawan FOREIGN KEY (karyawan_id) REFERENCES karyawan(karyawan_id),
  CONSTRAINT fk_absensi_user FOREIGN KEY (created_by) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Setting tarif flat uang makan (tabel singleton, 1 baris)
CREATE TABLE uang_makan_setting (
  id          TINYINT NOT NULL,
  tarif       INT NOT NULL,
  updated_by  INT DEFAULT NULL,
  updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_ums_user FOREIGN KEY (updated_by) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO uang_makan_setting (id, tarif) VALUES (1, 20000);

-- Riwayat proses uang makan harian, link balik ke Beban Operasional (mirror pola po_receipt.ongkir_expense_id)
CREATE TABLE uang_makan (
  uang_makan_id    INT NOT NULL AUTO_INCREMENT,
  tanggal          DATE NOT NULL,
  jumlah_karyawan  INT NOT NULL,
  tarif            INT NOT NULL,
  total_amount     INT NOT NULL,
  expense_id       INT DEFAULT NULL,
  journal_id       INT DEFAULT NULL,
  created_by       INT NOT NULL,
  created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
  is_void          TINYINT(1) NOT NULL DEFAULT 0,
  voided_at        DATETIME DEFAULT NULL,
  voided_by        INT DEFAULT NULL,
  void_reason      VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (uang_makan_id),
  KEY idx_tanggal (tanggal),
  CONSTRAINT fk_um_expense FOREIGN KEY (expense_id) REFERENCES beban_operasional(expense_id),
  CONSTRAINT fk_um_journal FOREIGN KEY (journal_id) REFERENCES finance_journal(journal_id),
  CONSTRAINT fk_um_user FOREIGN KEY (created_by) REFERENCES user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
