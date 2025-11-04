-- ============================================================
-- MIGRATION: SHIFT MANAGEMENT ENHANCEMENT
-- ============================================================
-- This migration adds shift assignment, confirmation, and 
-- payroll enhancement features while keeping existing structure
-- Date: 2025-01-XX
-- ============================================================

-- ============================================================
-- BACKUP CURRENT DATA (OPTIONAL - for safety)
-- ============================================================
-- Run this first to backup current data
-- CREATE TABLE cabang_backup AS SELECT * FROM cabang;
-- CREATE TABLE absensi_backup AS SELECT * FROM absensi;
-- CREATE TABLE register_backup AS SELECT * FROM register;

START TRANSACTION;

-- ============================================================
-- STEP 1: CREATE NEW TABLES FOR SHIFT MANAGEMENT
-- ============================================================

-- Table for shift assignments (who works which shift when)
CREATE TABLE IF NOT EXISTS `shift_assignments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `cabang_id` INT(11) NOT NULL,
  `tanggal_shift` DATE NOT NULL,
  `status_konfirmasi` ENUM('pending', 'confirmed', 'declined') DEFAULT 'pending',
  `waktu_konfirmasi` DATETIME DEFAULT NULL,
  `catatan_pegawai` TEXT DEFAULT NULL,
  `created_by` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`, `tanggal_shift`),
  KEY `idx_tanggal` (`tanggal_shift`),
  KEY `idx_cabang` (`cabang_id`),
  KEY `idx_status` (`status_konfirmasi`),
  FOREIGN KEY (`user_id`) REFERENCES `register`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`cabang_id`) REFERENCES `cabang`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Assigns users to specific shifts on specific dates';

-- Table for national holidays (to exclude from attendance requirements)
CREATE TABLE IF NOT EXISTS `libur_nasional` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tanggal` DATE NOT NULL,
  `nama_libur` VARCHAR(255) NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tanggal` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='National holidays and special non-working days';

-- Table for detailed payroll components (per employee per month)
CREATE TABLE IF NOT EXISTS `komponen_gaji_detail` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `periode_bulan` TINYINT(2) UNSIGNED NOT NULL CHECK (`periode_bulan` >= 1 AND `periode_bulan` <= 12),
  `periode_tahun` YEAR(4) NOT NULL,
  
  -- Fixed components
  `gaji_pokok` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `tunjangan_transport` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `tunjangan_makan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `tunjangan_jabatan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  
  -- Variable components (editable)
  `bonus_kehadiran` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `bonus_marketing` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `insentif_omset` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `overwork_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `overwork_hours` DECIMAL(5,2) NOT NULL DEFAULT 0,
  
  -- Deductions
  `potongan_telat_berat` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Terlambat > 20 menit',
  `potongan_telat_ringan` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Terlambat < 20 menit',
  `potongan_alfa` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Tidak hadir tanpa izin',
  `kasbon` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `piutang_toko` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `potongan_lainnya` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `keterangan_potongan` TEXT DEFAULT NULL,
  
  -- Summary
  `total_pendapatan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `total_potongan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `gaji_bersih` DECIMAL(15,2) NOT NULL DEFAULT 0,
  
  -- Attendance summary
  `jumlah_hadir` INT(11) NOT NULL DEFAULT 0,
  `jumlah_telat_ringan` INT(11) NOT NULL DEFAULT 0,
  `jumlah_telat_berat` INT(11) NOT NULL DEFAULT 0,
  `jumlah_alfa` INT(11) NOT NULL DEFAULT 0,
  `jumlah_izin` INT(11) NOT NULL DEFAULT 0,
  
  -- Status and timestamps
  `status_slip` ENUM('draft', 'generated', 'sent', 'revised') DEFAULT 'draft',
  `file_slip_gaji` VARCHAR(255) DEFAULT NULL,
  `generated_at` DATETIME DEFAULT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_period` (`user_id`, `periode_bulan`, `periode_tahun`),
  KEY `idx_periode` (`periode_bulan`, `periode_tahun`),
  KEY `idx_status` (`status_slip`),
  FOREIGN KEY (`user_id`) REFERENCES `register`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Detailed payroll components per employee per month';

-- Table for payroll generation history and logs
CREATE TABLE IF NOT EXISTS `slip_gaji_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `batch_id` VARCHAR(50) NOT NULL COMMENT 'Unique ID for each generation batch',
  `periode_bulan` TINYINT(2) UNSIGNED NOT NULL,
  `periode_tahun` YEAR(4) NOT NULL,
  `jumlah_pegawai` INT(11) NOT NULL DEFAULT 0,
  `total_gaji_dibayarkan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `status_batch` ENUM('processing', 'completed', 'failed', 'cancelled') DEFAULT 'processing',
  `email_sent_count` INT(11) NOT NULL DEFAULT 0,
  `generated_by` INT(11) NOT NULL,
  `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  `error_log` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_id` (`batch_id`),
  KEY `idx_periode` (`periode_bulan`, `periode_tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='History of payroll generation batches';

-- ============================================================
-- STEP 2: ALTER EXISTING TABLES
-- ============================================================

-- Add shift reference to absensi table (skip if already exists)
-- Columns already added, skip this step

-- Add salary component reference to register table (skip if already exists)
-- Columns already added, skip this step

-- Add notes to pengajuan_izin for shift integration (skip if already exists)
-- Columns already added, skip this step

-- ============================================================
-- STEP 3: INSERT SAMPLE NATIONAL HOLIDAYS FOR 2025
-- ============================================================

INSERT INTO `libur_nasional` (`tanggal`, `nama_libur`, `keterangan`) VALUES
('2025-01-01', 'Tahun Baru 2025', 'Tahun Baru Masehi'),
('2025-01-29', 'Tahun Baru Imlek 2576', 'Tahun Baru Imlek'),
('2025-03-29', 'Hari Raya Nyepi 1947', 'Tahun Baru Saka'),
('2025-03-30', 'Wafat Isa Al-Masih', 'Hari Suci Kristen'),
('2025-03-31', 'Isra Mikraj Nabi Muhammad SAW', 'Hari Besar Islam'),
('2025-04-01', 'Hari Raya Idul Fitri 1446 H', 'Hari Raya Islam (Est)'),
('2025-04-02', 'Hari Raya Idul Fitri 1446 H', 'Hari Raya Islam (Est)'),
('2025-04-03', 'Cuti Bersama Idul Fitri', 'Cuti Bersama'),
('2025-04-04', 'Cuti Bersama Idul Fitri', 'Cuti Bersama'),
('2025-05-01', 'Hari Buruh Internasional', 'Hari Buruh'),
('2025-05-29', 'Kenaikan Isa Al-Masih', 'Hari Suci Kristen'),
('2025-06-07', 'Hari Raya Idul Adha 1446 H', 'Hari Raya Islam (Est)'),
('2025-06-28', 'Tahun Baru Islam 1447 H', 'Tahun Baru Hijriah (Est)'),
('2025-08-17', 'Hari Kemerdekaan RI', 'HUT Kemerdekaan RI ke-80'),
('2025-09-06', 'Maulid Nabi Muhammad SAW', 'Hari Besar Islam (Est)'),
('2025-12-25', 'Hari Raya Natal', 'Hari Raya Natal')
ON DUPLICATE KEY UPDATE nama_libur = VALUES(nama_libur);

-- ============================================================
-- STEP 4: CREATE VIEWS FOR EASIER DATA ACCESS
-- ============================================================

-- View for daily shift schedule with employee details
CREATE OR REPLACE VIEW `v_jadwal_shift_harian` AS
SELECT 
    sa.id,
    sa.tanggal_shift,
    sa.user_id,
    r.nama_lengkap,
    r.posisi,
    r.no_whatsapp,
    r.email,
    sa.cabang_id,
    co.nama_cabang AS outlet,
    c.nama_shift,
    c.jam_masuk,
    c.jam_keluar,
    sa.status_konfirmasi,
    sa.waktu_konfirmasi,
    sa.catatan_pegawai,
    sa.created_at,
    CASE 
        WHEN sa.tanggal_shift IN (SELECT tanggal FROM libur_nasional) THEN 'Libur Nasional'
        WHEN DAYOFWEEK(sa.tanggal_shift) = 1 THEN 'Minggu'
        ELSE 'Hari Kerja'
    END AS status_hari
FROM shift_assignments sa
JOIN register r ON sa.user_id = r.id
JOIN cabang c ON sa.cabang_id = c.id
JOIN cabang_outlet co ON c.nama_cabang = co.nama_cabang
ORDER BY sa.tanggal_shift DESC, c.jam_masuk ASC;

-- View for attendance with shift information
CREATE OR REPLACE VIEW `v_absensi_dengan_shift` AS
SELECT 
    a.id,
    a.user_id,
    r.nama_lengkap,
    r.posisi,
    a.tanggal_absensi,
    a.waktu_masuk,
    a.waktu_keluar,
    a.jam_masuk_shift,
    a.jam_keluar_shift,
    a.durasi_kerja_menit,
    a.durasi_overwork_menit,
    ROUND(a.durasi_overwork_menit / 60.0, 2) AS jam_overwork,
    a.menit_terlambat,
    a.status_keterlambatan,
    a.status_lembur,
    a.is_overwork_approved,
    a.status_lokasi,
    c.nama_cabang AS outlet,
    c.nama_shift,
    CASE 
        WHEN a.waktu_masuk IS NULL AND a.waktu_keluar IS NULL THEN 'Alfa'
        WHEN a.waktu_masuk IS NOT NULL AND a.waktu_keluar IS NULL THEN 'Belum Absen Keluar'
        WHEN a.waktu_masuk IS NOT NULL AND a.waktu_keluar IS NOT NULL THEN 'Lengkap'
        ELSE 'Unknown'
    END AS status_absensi
FROM absensi a
JOIN register r ON a.user_id = r.id
LEFT JOIN cabang c ON a.cabang_id = c.id
ORDER BY a.tanggal_absensi DESC, a.waktu_masuk DESC;

-- View for payroll summary
CREATE OR REPLACE VIEW `v_ringkasan_gaji` AS
SELECT 
    kg.id,
    kg.user_id,
    r.nama_lengkap,
    r.posisi,
    r.outlet,
    kg.periode_bulan,
    kg.periode_tahun,
    kg.gaji_pokok,
    kg.tunjangan_transport,
    kg.tunjangan_makan,
    kg.tunjangan_jabatan,
    kg.bonus_kehadiran,
    kg.bonus_marketing,
    kg.insentif_omset,
    kg.overwork_amount,
    kg.overwork_hours,
    kg.total_pendapatan,
    kg.potongan_telat_berat,
    kg.potongan_telat_ringan,
    kg.potongan_alfa,
    kg.kasbon,
    kg.piutang_toko,
    kg.potongan_lainnya,
    kg.total_potongan,
    kg.gaji_bersih,
    kg.jumlah_hadir,
    kg.jumlah_telat_ringan,
    kg.jumlah_telat_berat,
    kg.jumlah_alfa,
    kg.jumlah_izin,
    kg.status_slip,
    kg.file_slip_gaji,
    kg.generated_at,
    kg.sent_at
FROM komponen_gaji_detail kg
JOIN register r ON kg.user_id = r.id
ORDER BY kg.periode_tahun DESC, kg.periode_bulan DESC, r.nama_lengkap ASC;

-- ============================================================
-- STEP 5: CREATE STORED PROCEDURES FOR COMMON OPERATIONS
-- ============================================================

-- Procedure to assign shift to user
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `sp_assign_shift`(
    IN p_user_id INT,
    IN p_cabang_id INT,
    IN p_tanggal_shift DATE,
    IN p_created_by INT
)
BEGIN
    INSERT INTO shift_assignments (user_id, cabang_id, tanggal_shift, created_by)
    VALUES (p_user_id, p_cabang_id, p_tanggal_shift, p_created_by)
    ON DUPLICATE KEY UPDATE 
        cabang_id = p_cabang_id,
        status_konfirmasi = 'pending',
        updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- Procedure to confirm shift
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `sp_konfirmasi_shift`(
    IN p_assignment_id INT,
    IN p_status VARCHAR(20),
    IN p_catatan TEXT
)
BEGIN
    UPDATE shift_assignments 
    SET 
        status_konfirmasi = p_status,
        waktu_konfirmasi = NOW(),
        catatan_pegawai = p_catatan,
        updated_at = NOW()
    WHERE id = p_assignment_id;
END$$
DELIMITER ;

-- Procedure to calculate attendance for a period
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `sp_hitung_kehadiran_periode`(
    IN p_user_id INT,
    IN p_bulan INT,
    IN p_tahun INT,
    OUT o_hadir INT,
    OUT o_telat_ringan INT,
    OUT o_telat_berat INT,
    OUT o_alfa INT,
    OUT o_izin INT,
    OUT o_total_overwork_jam DECIMAL(10,2)
)
BEGIN
    DECLARE v_start_date DATE;
    DECLARE v_end_date DATE;
    DECLARE v_total_hari_kerja INT;
    
    -- Calculate period dates (28 prev month to 27 current month)
    IF p_bulan = 1 THEN
        SET v_start_date = DATE(CONCAT(p_tahun - 1, '-12-28'));
        SET v_end_date = DATE(CONCAT(p_tahun, '-', LPAD(p_bulan, 2, '0'), '-27'));
    ELSE
        SET v_start_date = DATE(CONCAT(p_tahun, '-', LPAD(p_bulan - 1, 2, '0'), '-28'));
        SET v_end_date = DATE(CONCAT(p_tahun, '-', LPAD(p_bulan, 2, '0'), '-27'));
    END IF;
    
    -- Count attendance
    SELECT 
        COUNT(*) INTO o_hadir
    FROM absensi 
    WHERE user_id = p_user_id 
        AND tanggal_absensi BETWEEN v_start_date AND v_end_date
        AND waktu_masuk IS NOT NULL;
    
    -- Count late arrivals (light)
    SELECT 
        COUNT(*) INTO o_telat_ringan
    FROM absensi 
    WHERE user_id = p_user_id 
        AND tanggal_absensi BETWEEN v_start_date AND v_end_date
        AND status_keterlambatan = 'terlambat kurang dari 20 menit';
    
    -- Count late arrivals (heavy)
    SELECT 
        COUNT(*) INTO o_telat_berat
    FROM absensi 
    WHERE user_id = p_user_id 
        AND tanggal_absensi BETWEEN v_start_date AND v_end_date
        AND status_keterlambatan = 'terlambat lebih dari 20 menit';
    
    -- Count approved leaves
    SELECT 
        COALESCE(SUM(lama_izin), 0) INTO o_izin
    FROM pengajuan_izin 
    WHERE user_id = p_user_id 
        AND status = 'Diterima'
        AND ((tanggal_mulai BETWEEN v_start_date AND v_end_date)
            OR (tanggal_selesai BETWEEN v_start_date AND v_end_date));
    
    -- Calculate total work days
    SET v_total_hari_kerja = DATEDIFF(v_end_date, v_start_date) + 1 
        - (SELECT COUNT(*) FROM libur_nasional WHERE tanggal BETWEEN v_start_date AND v_end_date);
    
    -- Calculate alfa (absent without permission)
    SET o_alfa = v_total_hari_kerja - o_hadir - o_izin;
    IF o_alfa < 0 THEN SET o_alfa = 0; END IF;
    
    -- Calculate total overwork hours
    SELECT 
        COALESCE(SUM(durasi_overwork_menit) / 60.0, 0) INTO o_total_overwork_jam
    FROM absensi 
    WHERE user_id = p_user_id 
        AND tanggal_absensi BETWEEN v_start_date AND v_end_date
        AND is_overwork_approved = TRUE;
END$$
DELIMITER ;

-- ============================================================
-- STEP 6: CREATE TRIGGERS FOR AUTOMATIC CALCULATIONS
-- ============================================================

-- Trigger to calculate work duration and overwork after absensi insert/update
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `tr_absensi_calculate_duration`
BEFORE UPDATE ON `absensi`
FOR EACH ROW
BEGIN
    IF NEW.waktu_masuk IS NOT NULL AND NEW.waktu_keluar IS NOT NULL THEN
        -- Calculate actual work duration in minutes
        SET NEW.durasi_kerja_menit = TIMESTAMPDIFF(MINUTE, NEW.waktu_masuk, NEW.waktu_keluar);
        
        -- If shift times are set, calculate overwork
        IF NEW.jam_masuk_shift IS NOT NULL AND NEW.jam_keluar_shift IS NOT NULL THEN
            DECLARE v_expected_duration INT;
            DECLARE v_shift_end DATETIME;
            
            -- Expected shift duration in minutes
            SET v_expected_duration = TIMESTAMPDIFF(MINUTE, 
                CONCAT(NEW.tanggal_absensi, ' ', NEW.jam_masuk_shift),
                CONCAT(NEW.tanggal_absensi, ' ', NEW.jam_keluar_shift));
            
            -- Calculate overwork (work beyond expected shift end time)
            SET v_shift_end = CONCAT(NEW.tanggal_absensi, ' ', NEW.jam_keluar_shift);
            IF NEW.waktu_keluar > v_shift_end THEN
                SET NEW.durasi_overwork_menit = TIMESTAMPDIFF(MINUTE, v_shift_end, NEW.waktu_keluar);
                IF NEW.durasi_overwork_menit > 30 THEN
                    SET NEW.status_lembur = 'Pending';
                END IF;
            ELSE
                SET NEW.durasi_overwork_menit = 0;
            END IF;
        END IF;
    END IF;
END$$
DELIMITER ;

COMMIT;

-- ============================================================
-- VERIFICATION QUERIES
-- ============================================================
-- Run these after migration to verify:
-- SELECT * FROM shift_assignments LIMIT 5;
-- SELECT * FROM libur_nasional;
-- SELECT * FROM komponen_gaji_detail LIMIT 5;
-- SELECT * FROM v_jadwal_shift_harian WHERE tanggal_shift >= CURDATE() LIMIT 10;
-- SELECT * FROM v_absensi_dengan_shift WHERE tanggal_absensi >= CURDATE() - INTERVAL 7 DAY;

-- ============================================================
-- ROLLBACK INSTRUCTIONS (if needed)
-- ============================================================
-- To rollback this migration:
/*
DROP TRIGGER IF EXISTS tr_absensi_calculate_duration;
DROP PROCEDURE IF EXISTS sp_hitung_kehadiran_periode;
DROP PROCEDURE IF EXISTS sp_konfirmasi_shift;
DROP PROCEDURE IF EXISTS sp_assign_shift;
DROP VIEW IF EXISTS v_ringkasan_gaji;
DROP VIEW IF EXISTS v_absensi_dengan_shift;
DROP VIEW IF EXISTS v_jadwal_shift_harian;
ALTER TABLE pengajuan_izin DROP COLUMN IF EXISTS shift_diganti;
ALTER TABLE pengajuan_izin DROP COLUMN IF EXISTS mempengaruhi_shift;
ALTER TABLE register DROP COLUMN IF EXISTS tarif_overwork_per_jam;
ALTER TABLE register DROP COLUMN IF EXISTS tunjangan_jabatan;
ALTER TABLE register DROP COLUMN IF EXISTS tunjangan_makan;
ALTER TABLE register DROP COLUMN IF EXISTS tunjangan_transport;
ALTER TABLE register DROP COLUMN IF EXISTS gaji_pokok;
ALTER TABLE absensi DROP KEY IF EXISTS idx_tanggal;
ALTER TABLE absensi DROP KEY IF EXISTS idx_cabang;
ALTER TABLE absensi DROP COLUMN IF EXISTS is_overwork_approved;
ALTER TABLE absensi DROP COLUMN IF EXISTS durasi_overwork_menit;
ALTER TABLE absensi DROP COLUMN IF EXISTS durasi_kerja_menit;
ALTER TABLE absensi DROP COLUMN IF EXISTS jam_keluar_shift;
ALTER TABLE absensi DROP COLUMN IF EXISTS jam_masuk_shift;
ALTER TABLE absensi DROP COLUMN IF EXISTS cabang_id;
DROP TABLE IF EXISTS slip_gaji_history;
DROP TABLE IF EXISTS komponen_gaji_detail;
DROP TABLE IF EXISTS libur_nasional;
DROP TABLE IF EXISTS shift_assignments;
*/

-- ============================================================
-- END OF MIGRATION
-- ============================================================
