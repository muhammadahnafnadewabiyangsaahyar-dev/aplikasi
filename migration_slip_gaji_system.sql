-- =====================================================
-- SLIP GAJI SYSTEM - Database Schema Update
-- =====================================================

-- 1. Add columns to riwayat_gaji for detailed breakdown
ALTER TABLE riwayat_gaji
ADD COLUMN IF NOT EXISTS bonus_marketing DECIMAL(12,2) DEFAULT 0.00 AFTER overwork,
ADD COLUMN IF NOT EXISTS insentif_omset DECIMAL(12,2) DEFAULT 0.00 AFTER bonus_marketing,
ADD COLUMN IF NOT EXISTS bonus_lainnya DECIMAL(12,2) DEFAULT 0.00 AFTER insentif_omset,
ADD COLUMN IF NOT EXISTS total_pendapatan DECIMAL(12,2) DEFAULT 0.00 AFTER bonus_lainnya,
ADD COLUMN IF NOT EXISTS total_potongan DECIMAL(12,2) DEFAULT 0.00 AFTER piutang_toko,
ADD COLUMN IF NOT EXISTS jumlah_overwork INT DEFAULT 0 AFTER jumlah_absen,
ADD COLUMN IF NOT EXISTS jumlah_sakit INT DEFAULT 0 AFTER jumlah_overwork,
ADD COLUMN IF NOT EXISTS jumlah_izin_approved INT DEFAULT 0 AFTER jumlah_sakit,
ADD COLUMN IF NOT EXISTS jumlah_izin_rejected INT DEFAULT 0 AFTER jumlah_izin_approved,
ADD COLUMN IF NOT EXISTS hari_tidak_hadir INT DEFAULT 0 AFTER jumlah_izin_rejected,
ADD COLUMN IF NOT EXISTS potongan_tidak_hadir DECIMAL(12,2) DEFAULT 0.00 AFTER hari_tidak_hadir,
ADD COLUMN IF NOT EXISTS periode_awal DATE NULL AFTER periode_tahun,
ADD COLUMN IF NOT EXISTS periode_akhir DATE NULL AFTER periode_awal,
ADD COLUMN IF NOT EXISTS is_editable TINYINT(1) DEFAULT 1 AFTER file_slip_gaji,
ADD COLUMN IF NOT EXISTS email_sent TINYINT(1) DEFAULT 0 AFTER is_editable,
ADD COLUMN IF NOT EXISTS email_sent_at DATETIME NULL AFTER email_sent,
ADD COLUMN IF NOT EXISTS generated_by INT NULL AFTER email_sent_at,
ADD COLUMN IF NOT EXISTS generated_at DATETIME NULL AFTER generated_by,
ADD COLUMN IF NOT EXISTS updated_by INT NULL AFTER generated_at;

-- 2. Create table for salary generation history
CREATE TABLE IF NOT EXISTS slip_gaji_batch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    periode_bulan INT NOT NULL,
    periode_tahun INT NOT NULL,
    periode_awal DATE NOT NULL,
    periode_akhir DATE NOT NULL,
    total_pegawai INT DEFAULT 0,
    total_generated INT DEFAULT 0,
    total_failed INT DEFAULT 0,
    generated_by INT NOT NULL,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    INDEX idx_periode (periode_bulan, periode_tahun),
    INDEX idx_generated_by (generated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create table for editable salary components (kasbon, bonus, etc)
CREATE TABLE IF NOT EXISTS komponen_gaji_tambahan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    riwayat_gaji_id INT NOT NULL,
    jenis_komponen ENUM('kasbon', 'bonus_marketing', 'insentif_omset', 'piutang_toko', 'bonus_lainnya') NOT NULL,
    nominal DECIMAL(12,2) NOT NULL,
    keterangan TEXT,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (riwayat_gaji_id) REFERENCES riwayat_gaji(id) ON DELETE CASCADE,
    INDEX idx_riwayat (riwayat_gaji_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create table for leave/absence requests
CREATE TABLE IF NOT EXISTS pengajuan_izin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    jenis_izin ENUM('izin', 'sakit', 'cuti') NOT NULL,
    keterangan TEXT,
    file_pendukung VARCHAR(255) NULL COMMENT 'Path to surat dokter, etc',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    rejection_reason TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id),
    INDEX idx_user_date (user_id, tanggal_mulai, tanggal_selesai),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Add overwork tracking to absensi table if not exists
ALTER TABLE absensi
ADD COLUMN IF NOT EXISTS is_overwork TINYINT(1) DEFAULT 0 AFTER status_lembur,
ADD COLUMN IF NOT EXISTS overwork_hours DECIMAL(4,2) DEFAULT 0.00 AFTER is_overwork,
ADD COLUMN IF NOT EXISTS overwork_amount DECIMAL(12,2) DEFAULT 0.00 AFTER overwork_hours,
ADD COLUMN IF NOT EXISTS is_holiday TINYINT(1) DEFAULT 0 COMMENT 'Hari libur nasional' AFTER overwork_amount;

-- 6. Create national holidays table
CREATE TABLE IF NOT EXISTS hari_libur_nasional (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL UNIQUE,
    nama_hari_libur VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Insert sample national holidays for 2025
INSERT IGNORE INTO hari_libur_nasional (tanggal, nama_hari_libur, keterangan) VALUES
('2025-01-01', 'Tahun Baru 2025', 'Hari Tahun Baru Masehi'),
('2025-03-31', 'Hari Raya Idul Fitri', 'Hari Raya Idul Fitri 1446 H'),
('2025-04-01', 'Hari Raya Idul Fitri', 'Hari Raya Idul Fitri 1446 H (Hari ke-2)'),
('2025-05-01', 'Hari Buruh Internasional', 'Hari Buruh Sedunia'),
('2025-06-05', 'Hari Raya Idul Adha', 'Hari Raya Idul Adha 1446 H'),
('2025-06-26', 'Tahun Baru Islam', 'Tahun Baru Islam 1447 H'),
('2025-08-17', 'Hari Kemerdekaan RI', 'HUT Kemerdekaan RI ke-80'),
('2025-09-05', 'Maulid Nabi Muhammad SAW', 'Maulid Nabi Muhammad SAW'),
('2025-12-25', 'Hari Raya Natal', 'Hari Raya Natal');

-- Show table structures
DESCRIBE riwayat_gaji;
DESCRIBE slip_gaji_batch;
DESCRIBE komponen_gaji_tambahan;
DESCRIBE pengajuan_izin;
DESCRIBE hari_libur_nasional;

SELECT 'Migration completed successfully!' as status;
