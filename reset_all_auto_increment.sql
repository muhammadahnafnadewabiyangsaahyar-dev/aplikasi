-- ============================================================
-- RESET AUTO INCREMENT & REORDER IDs
-- Script untuk reset ulang semua ID dan auto_increment
-- ============================================================
-- PERINGATAN: Script ini akan mengubah semua ID di database!
-- Backup database terlebih dahulu sebelum menjalankan!
-- ============================================================

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. RESET TABLE: register (users)
-- ============================================================
-- Backup data
CREATE TEMPORARY TABLE temp_register AS SELECT * FROM register ORDER BY id;

-- Truncate and reset
TRUNCATE TABLE register;
ALTER TABLE register AUTO_INCREMENT = 1;

-- Re-insert with new IDs
INSERT INTO register (
    username, password, role, outlet_id, status, 
    no_hp, tgl_lahir, alamat, foto_profil, email, 
    created_at, updated_at
)
SELECT 
    username, password, role, outlet_id, status, 
    no_hp, tgl_lahir, alamat, foto_profil, email, 
    created_at, updated_at
FROM temp_register
ORDER BY id;

DROP TEMPORARY TABLE temp_register;

-- ============================================================
-- 2. RESET TABLE: cabang (branches/outlets)
-- ============================================================
CREATE TEMPORARY TABLE temp_cabang AS SELECT * FROM cabang ORDER BY id;

TRUNCATE TABLE cabang;
ALTER TABLE cabang AUTO_INCREMENT = 1;

INSERT INTO cabang (
    nama_cabang, nama_shift, latitude, longitude, 
    radius_meter, jam_masuk, jam_keluar
)
SELECT 
    nama_cabang, nama_shift, latitude, longitude, 
    radius_meter, jam_masuk, jam_keluar
FROM temp_cabang
ORDER BY id;

DROP TEMPORARY TABLE temp_cabang;

-- ============================================================
-- 3. RESET TABLE: absensi (attendance records)
-- ============================================================
CREATE TEMPORARY TABLE temp_absensi AS SELECT * FROM absensi ORDER BY id;

TRUNCATE TABLE absensi;
ALTER TABLE absensi AUTO_INCREMENT = 1;

INSERT INTO absensi (
    user_id, tanggal_absensi, waktu_masuk, waktu_keluar,
    latitude_absen_masuk, longitude_absen_masuk,
    latitude_absen_keluar, longitude_absen_keluar,
    foto_absen_masuk, foto_absen_keluar,
    status_lokasi, menit_terlambat, status_keterlambatan,
    potongan_tunjangan, status_lembur, durasi_lembur,
    keterangan_lembur, cabang_id, jam_masuk_shift, jam_keluar_shift
)
SELECT 
    user_id, tanggal_absensi, waktu_masuk, waktu_keluar,
    latitude_absen_masuk, longitude_absen_masuk,
    latitude_absen_keluar, longitude_absen_keluar,
    foto_absen_masuk, foto_absen_keluar,
    status_lokasi, menit_terlambat, status_keterlambatan,
    potongan_tunjangan, status_lembur, durasi_lembur,
    keterangan_lembur, cabang_id, jam_masuk_shift, jam_keluar_shift
FROM temp_absensi
ORDER BY id;

DROP TEMPORARY TABLE temp_absensi;

-- ============================================================
-- 4. RESET TABLE: izin_sakit (leave/sick requests)
-- ============================================================
CREATE TEMPORARY TABLE temp_izin_sakit AS SELECT * FROM izin_sakit ORDER BY id;

TRUNCATE TABLE izin_sakit;
ALTER TABLE izin_sakit AUTO_INCREMENT = 1;

INSERT INTO izin_sakit (
    user_id, tanggal_mulai, tanggal_selesai, jenis,
    alasan, file_surat, status, keterangan_reject,
    decline_reason, created_at
)
SELECT 
    user_id, tanggal_mulai, tanggal_selesai, jenis,
    alasan, file_surat, status, keterangan_reject,
    decline_reason, created_at
FROM temp_izin_sakit
ORDER BY id;

DROP TEMPORARY TABLE temp_izin_sakit;

-- ============================================================
-- 5. RESET TABLE: gaji (salary records)
-- ============================================================
CREATE TEMPORARY TABLE temp_gaji AS SELECT * FROM gaji ORDER BY id;

TRUNCATE TABLE gaji;
ALTER TABLE gaji AUTO_INCREMENT = 1;

INSERT INTO gaji (
    user_id, bulan, tahun, gaji_pokok, tunjangan,
    potongan, total_gaji, status, created_at
)
SELECT 
    user_id, bulan, tahun, gaji_pokok, tunjangan,
    potongan, total_gaji, status, created_at
FROM temp_gaji
ORDER BY id;

DROP TEMPORARY TABLE temp_gaji;

-- ============================================================
-- 6. RESET TABLE: shift_assignments (if exists)
-- ============================================================
CREATE TEMPORARY TABLE temp_shift_assignments AS SELECT * FROM shift_assignments ORDER BY id;

TRUNCATE TABLE shift_assignments;
ALTER TABLE shift_assignments AUTO_INCREMENT = 1;

INSERT INTO shift_assignments (
    user_id, cabang_id, tanggal_shift, status_konfirmasi,
    konfirmasi_user_at, email_sent, created_at
)
SELECT 
    user_id, cabang_id, tanggal_shift, status_konfirmasi,
    konfirmasi_user_at, email_sent, created_at
FROM temp_shift_assignments
ORDER BY id;

DROP TEMPORARY TABLE temp_shift_assignments;

-- ============================================================
-- 7. RESET TABLE: absensi_error_log (if exists)
-- ============================================================
CREATE TEMPORARY TABLE temp_error_log AS SELECT * FROM absensi_error_log ORDER BY id;

TRUNCATE TABLE absensi_error_log;
ALTER TABLE absensi_error_log AUTO_INCREMENT = 1;

INSERT INTO absensi_error_log (
    user_id, error_type, error_message, error_details,
    ip_address, created_at
)
SELECT 
    user_id, error_type, error_message, error_details,
    ip_address, created_at
FROM temp_error_log
ORDER BY id;

DROP TEMPORARY TABLE temp_error_log;

-- ============================================================
-- 8. RESET TABLE: security_logs (if exists)
-- ============================================================
CREATE TEMPORARY TABLE temp_security_logs AS SELECT * FROM security_logs ORDER BY id;

TRUNCATE TABLE security_logs;
ALTER TABLE security_logs AUTO_INCREMENT = 1;

INSERT INTO security_logs (
    user_id, activity_type, activity_details, ip_address,
    user_agent, risk_level, created_at
)
SELECT 
    user_id, activity_type, activity_details, ip_address,
    user_agent, risk_level, created_at
FROM temp_security_logs
ORDER BY id;

DROP TEMPORARY TABLE temp_security_logs;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION: Check current auto_increment values
-- ============================================================
SELECT 
    TABLE_NAME,
    AUTO_INCREMENT,
    CONCAT('Next ID will be: ', AUTO_INCREMENT) as STATUS
FROM 
    information_schema.TABLES
WHERE 
    TABLE_SCHEMA = DATABASE()
    AND AUTO_INCREMENT IS NOT NULL
ORDER BY 
    TABLE_NAME;

-- ============================================================
-- DONE!
-- ============================================================
SELECT '✅ All auto_increment counters have been reset!' as RESULT;
