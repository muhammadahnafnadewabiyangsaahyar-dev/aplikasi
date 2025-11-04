-- ============================================
-- DATABASE SCHEMA: SHIFT MANAGEMENT SYSTEM
-- ============================================
-- Created: 2025-11-03
-- Purpose: Comprehensive shift, attendance, and payroll system
-- ============================================

-- --------------------------------------------
-- 1. TABEL SHIFT SCHEDULE
-- --------------------------------------------
-- Menyimpan jadwal shift per cabang per hari
CREATE TABLE IF NOT EXISTS shift_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    shift_type ENUM('shift_1', 'shift_2', 'shift_3') NOT NULL,
    jam_mulai TIME DEFAULT '08:00:00',
    jam_selesai TIME DEFAULT '17:00:00',
    kuota_pegawai INT DEFAULT 5,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_locked BOOLEAN DEFAULT FALSE,
    notes TEXT,
    UNIQUE KEY unique_shift (cabang, tanggal, shift_type),
    FOREIGN KEY (created_by) REFERENCES register(id),
    INDEX idx_cabang_tanggal (cabang, tanggal),
    INDEX idx_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------
-- 2. TABEL SHIFT ASSIGNMENTS
-- --------------------------------------------
-- Menyimpan assignment pegawai ke shift
CREATE TABLE IF NOT EXISTS shift_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_schedule_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('draft', 'notified', 'confirmed', 'rejected', 'sakit', 'izin') DEFAULT 'draft',
    confirmation_status ENUM('pending', 'approved', 'rejected_sakit', 'rejected_izin', 'tukar_shift') DEFAULT 'pending',
    confirmed_at TIMESTAMP NULL,
    rejection_reason TEXT,
    notified_at TIMESTAMP NULL,
    email_sent BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE COMMENT 'TRUE jika user sudah approve, tidak bisa diubah',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_assignment (shift_schedule_id, user_id),
    FOREIGN KEY (shift_schedule_id) REFERENCES shift_schedule(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES register(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_shift_status (shift_schedule_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------
-- 3. TABEL SHIFT CONFIRMATIONS
-- --------------------------------------------
-- Log konfirmasi shift dari user (via email atau web)
CREATE TABLE IF NOT EXISTS shift_confirmations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    user_id INT NOT NULL,
    confirmation_method ENUM('email_reply', 'web_form') NOT NULL,
    response ENUM('approve', 'reject_sakit', 'reject_izin', 'tukar_shift') NOT NULL,
    response_text TEXT COMMENT 'Raw email reply atau form text',
    alasan TEXT,
    confirmed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    email_message_id VARCHAR(255) COMMENT 'Email Message-ID untuk tracking reply',
    FOREIGN KEY (assignment_id) REFERENCES shift_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES register(id),
    INDEX idx_assignment (assignment_id),
    INDEX idx_user_confirmed (user_id, confirmed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------
-- 4. TABEL KOMPONEN GAJI
-- --------------------------------------------
-- Master komponen gaji (gaji pokok, tunjangan, potongan, dll)
CREATE TABLE IF NOT EXISTS komponen_gaji (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_komponen VARCHAR(100) NOT NULL,
    tipe ENUM('tetap', 'variabel') DEFAULT 'tetap' COMMENT 'tetap=gaji pokok/tunjangan tetap, variabel=bonus/kasbon/dll',
    kategori ENUM('pendapatan', 'potongan') DEFAULT 'pendapatan',
    is_active BOOLEAN DEFAULT TRUE,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_komponen (nama_komponen),
    INDEX idx_tipe_kategori (tipe, kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default komponen gaji
INSERT INTO komponen_gaji (nama_komponen, tipe, kategori, keterangan) VALUES
('Gaji Pokok', 'tetap', 'pendapatan', 'Gaji pokok bulanan dari register.gaji_pokok'),
('Tunjangan Transport', 'tetap', 'pendapatan', 'Tunjangan transport dari register.tunjangan_transport'),
('Tunjangan Makan', 'tetap', 'pendapatan', 'Tunjangan makan dari register.tunjangan_makan'),
('Overwork', 'variabel', 'pendapatan', 'Upah lembur/overwork diluar shift (Rp 6.250/jam)'),
('Bonus Marketing', 'variabel', 'pendapatan', 'Bonus pencapaian marketing/sales'),
('Insentif Omset', 'variabel', 'pendapatan', 'Insentif berdasarkan omset toko'),
('Bonus Lainnya', 'variabel', 'pendapatan', 'Bonus tambahan lainnya'),
('Potongan Alpha', 'variabel', 'potongan', 'Potongan tidak hadir tanpa keterangan'),
('Potongan Shift', 'variabel', 'potongan', 'Potongan tidak hadir di jadwal shift (Rp 50.000/hari)'),
('Potongan Keterlambatan', 'variabel', 'potongan', 'Potongan tunjangan akibat terlambat'),
('Kasbon', 'variabel', 'potongan', 'Kasbon/pinjaman yang dipotong dari gaji'),
('Piutang Toko', 'variabel', 'potongan', 'Piutang toko yang dipotong dari gaji'),
('Potongan Lainnya', 'variabel', 'potongan', 'Potongan lainnya')
ON DUPLICATE KEY UPDATE nama_komponen=nama_komponen;

-- --------------------------------------------
-- 5. TABEL SLIP GAJI HISTORY
-- --------------------------------------------
-- Riwayat slip gaji per periode (auto-generate tanggal 28)
CREATE TABLE IF NOT EXISTS slip_gaji_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    periode_mulai DATE NOT NULL COMMENT 'Tanggal 28 bulan sebelumnya',
    periode_selesai DATE NOT NULL COMMENT 'Tanggal 28 bulan ini',
    bulan INT NOT NULL COMMENT 'Bulan untuk display (1-12)',
    tahun INT NOT NULL,
    
    -- Gaji & Tunjangan (dari register)
    gaji_pokok DECIMAL(15,2) DEFAULT 0,
    tunjangan_transport DECIMAL(15,2) DEFAULT 0,
    tunjangan_makan DECIMAL(15,2) DEFAULT 0,
    
    -- Statistik Kehadiran
    total_hari_kerja INT DEFAULT 26,
    total_hadir INT DEFAULT 0,
    total_sakit INT DEFAULT 0,
    total_izin INT DEFAULT 0,
    total_alpha INT DEFAULT 0,
    total_shift_missed INT DEFAULT 0 COMMENT 'Total tidak hadir di jadwal shift',
    total_overwork_hours DECIMAL(5,2) DEFAULT 0,
    
    -- Perhitungan Overwork
    upah_overwork_per_jam DECIMAL(15,2) DEFAULT 6250 COMMENT 'Rp 6.250/jam (50.000/8 jam)',
    total_overwork_pay DECIMAL(15,2) DEFAULT 0,
    
    -- Potongan
    potongan_keterlambatan DECIMAL(15,2) DEFAULT 0,
    potongan_shift_missed DECIMAL(15,2) DEFAULT 0 COMMENT 'Rp 50.000 per hari tidak hadir shift',
    potongan_kasbon DECIMAL(15,2) DEFAULT 0,
    potongan_piutang DECIMAL(15,2) DEFAULT 0,
    potongan_lainnya DECIMAL(15,2) DEFAULT 0,
    
    -- Bonus & Insentif
    bonus_marketing DECIMAL(15,2) DEFAULT 0,
    insentif_omset DECIMAL(15,2) DEFAULT 0,
    bonus_lainnya DECIMAL(15,2) DEFAULT 0,
    
    -- Total
    total_pendapatan DECIMAL(15,2) DEFAULT 0,
    total_potongan DECIMAL(15,2) DEFAULT 0,
    gaji_bersih DECIMAL(15,2) DEFAULT 0,
    
    -- Status & Tracking
    status ENUM('draft', 'finalized', 'sent') DEFAULT 'draft',
    is_editable BOOLEAN DEFAULT TRUE,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finalized_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    sent_by INT NULL,
    
    -- File
    file_pdf VARCHAR(255) NULL COMMENT 'Path ke file PDF slip gaji',
    
    notes TEXT,
    
    UNIQUE KEY unique_user_periode (user_id, periode_mulai, periode_selesai),
    FOREIGN KEY (user_id) REFERENCES register(id),
    FOREIGN KEY (sent_by) REFERENCES register(id),
    INDEX idx_user_bulan_tahun (user_id, bulan, tahun),
    INDEX idx_periode (periode_mulai, periode_selesai),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------
-- 6. TABEL KOMPONEN GAJI DETAIL
-- --------------------------------------------
-- Detail breakdown komponen gaji per slip gaji
CREATE TABLE IF NOT EXISTS komponen_gaji_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slip_gaji_id INT NOT NULL,
    komponen_id INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (slip_gaji_id) REFERENCES slip_gaji_history(id) ON DELETE CASCADE,
    FOREIGN KEY (komponen_id) REFERENCES komponen_gaji(id),
    INDEX idx_slip (slip_gaji_id),
    INDEX idx_komponen (komponen_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------
-- 7. TABEL LIBUR NASIONAL
-- --------------------------------------------
-- Untuk tracking hari libur nasional
CREATE TABLE IF NOT EXISTS libur_nasional (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    nama_libur VARCHAR(255) NOT NULL,
    is_cuti_bersama BOOLEAN DEFAULT FALSE,
    tahun INT NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tanggal (tanggal),
    INDEX idx_tahun (tahun),
    INDEX idx_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------
-- 8. UPDATE TABEL ABSENSI (Add Shift Columns)
-- --------------------------------------------
-- Tambah kolom untuk tracking shift
ALTER TABLE absensi 
ADD COLUMN IF NOT EXISTS shift_schedule_id INT NULL COMMENT 'Link ke shift_schedule jika hadir di shift',
ADD COLUMN IF NOT EXISTS is_overwork BOOLEAN DEFAULT FALSE COMMENT 'TRUE jika hadir diluar jadwal shift',
ADD COLUMN IF NOT EXISTS overwork_hours DECIMAL(5,2) DEFAULT 0 COMMENT 'Jumlah jam overwork',
ADD COLUMN IF NOT EXISTS overwork_pay DECIMAL(15,2) DEFAULT 0 COMMENT 'Upah overwork (Rp 6.250/jam)',
ADD COLUMN IF NOT EXISTS potongan_overwork DECIMAL(15,2) DEFAULT 0 COMMENT 'Potongan dari overwork jika terlambat',
ADD INDEX idx_shift (shift_schedule_id),
ADD INDEX idx_overwork (is_overwork),
ADD FOREIGN KEY fk_shift (shift_schedule_id) REFERENCES shift_schedule(id) ON DELETE SET NULL;

-- --------------------------------------------
-- 9. UPDATE TABEL PENGAJUAN_IZIN (Add Shift Link)
-- --------------------------------------------
-- Link izin dengan shift schedule
ALTER TABLE pengajuan_izin
ADD COLUMN IF NOT EXISTS shift_schedule_id INT NULL COMMENT 'Link ke shift jika izin untuk shift',
ADD COLUMN IF NOT EXISTS affects_salary BOOLEAN DEFAULT FALSE COMMENT 'TRUE jika izin mempengaruhi gaji (potong Rp 50.000)',
ADD INDEX idx_shift_izin (shift_schedule_id),
ADD FOREIGN KEY fk_shift_izin (shift_schedule_id) REFERENCES shift_schedule(id) ON DELETE SET NULL;

-- ============================================
-- VIEWS FOR REPORTING
-- ============================================

-- View: Shift Schedule dengan Assignment Count
CREATE OR REPLACE VIEW v_shift_schedule_summary AS
SELECT 
    ss.id,
    ss.cabang,
    ss.tanggal,
    ss.shift_type,
    ss.jam_mulai,
    ss.jam_selesai,
    ss.kuota_pegawai,
    COUNT(sa.id) as total_assigned,
    SUM(CASE WHEN sa.confirmation_status = 'approved' THEN 1 ELSE 0 END) as total_confirmed,
    SUM(CASE WHEN sa.confirmation_status = 'pending' THEN 1 ELSE 0 END) as total_pending,
    SUM(CASE WHEN sa.confirmation_status LIKE 'rejected%' THEN 1 ELSE 0 END) as total_rejected,
    ss.is_locked,
    ss.created_at
FROM shift_schedule ss
LEFT JOIN shift_assignments sa ON ss.id = sa.shift_schedule_id
GROUP BY ss.id;

-- View: User Shift Summary per Bulan
CREATE OR REPLACE VIEW v_user_shift_monthly AS
SELECT 
    r.id as user_id,
    r.nama_lengkap,
    r.posisi,
    r.outlet,
    DATE_FORMAT(ss.tanggal, '%Y-%m') as periode,
    COUNT(sa.id) as total_shift,
    SUM(CASE WHEN sa.confirmation_status = 'approved' THEN 1 ELSE 0 END) as shift_hadir,
    SUM(CASE WHEN sa.confirmation_status = 'rejected_sakit' THEN 1 ELSE 0 END) as shift_sakit,
    SUM(CASE WHEN sa.confirmation_status IN ('rejected_izin', 'rejected') AND sa.status = 'izin' THEN 1 ELSE 0 END) as shift_izin,
    SUM(CASE WHEN sa.confirmation_status = 'pending' OR sa.status IN ('draft', 'notified') THEN 1 ELSE 0 END) as shift_tidak_hadir
FROM register r
LEFT JOIN shift_assignments sa ON r.id = sa.user_id
LEFT JOIN shift_schedule ss ON sa.shift_schedule_id = ss.id
GROUP BY r.id, DATE_FORMAT(ss.tanggal, '%Y-%m');

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================

-- Additional composite indexes
CREATE INDEX idx_slip_user_periode ON slip_gaji_history(user_id, periode_mulai, periode_selesai);
CREATE INDEX idx_assignment_user_date ON shift_assignments(user_id, shift_schedule_id);
CREATE INDEX idx_schedule_cabang_date ON shift_schedule(cabang, tanggal, shift_type);

-- ============================================
-- NOTES & DOCUMENTATION
-- ============================================

/*
LOGIKA BISNIS PENTING:

1. SIKLUS ABSENSI: 28 bulan X → 28 bulan Y (26 hari kerja)

2. OVERWORK LOGIC:
   - Bukan jadwal shift + hadir = Overwork otomatis
   - Upah: Rp 50.000/8 jam = Rp 6.250/jam
   - Terlambat = Potong dari upah overwork

3. SHIFT LOGIC:
   - Jadwal shift + tidak hadir = Potong Rp 50.000/hari
   - Jadwal shift + sakit = Tidak potong
   - Jadwal shift + izin approved = Potong Rp 50.000/hari
   - Jadwal shift + izin rejected = Tidak hadir, potong Rp 50.000/hari

4. SLIP GAJI AUTO-GENERATE:
   - Setiap tanggal 28
   - Breakdown lengkap dari komponen_gaji_detail
   - Editable untuk komponen variabel

5. EMAIL NOTIFICATION SHIFT:
   - Delay 30 detik per email
   - Parse reply: approve/reject(sakit/izin)/tukar_shift
   - Lock assignment jika approved

6. HARI LIBUR NASIONAL:
   - TODO: Perlu logika khusus (TBD)
   - Data di tabel libur_nasional
*/

-- ============================================
-- END OF SCHEMA
-- ============================================
