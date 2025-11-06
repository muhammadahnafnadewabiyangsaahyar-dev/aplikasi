-- ========================================================
-- RESET AUTO_INCREMENT UNTUK SEMUA TABEL
-- ========================================================
-- Script ini akan mereset auto_increment ke nilai setelah ID tertinggi
-- Data TIDAK akan dihapus, hanya counter auto_increment yang direset
-- ========================================================
-- PENTING: Script ini mencakup SEMUA tabel yang ada di database
-- ========================================================

-- 1. Tabel absensi
ALTER TABLE absensi AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM absensi);
SET @sql = CONCAT('ALTER TABLE absensi AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Tabel absensi_duplicates_backup
ALTER TABLE absensi_duplicates_backup AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM absensi_duplicates_backup);
SET @sql = CONCAT('ALTER TABLE absensi_duplicates_backup AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Tabel absensi_error_log
ALTER TABLE absensi_error_log AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM absensi_error_log);
SET @sql = CONCAT('ALTER TABLE absensi_error_log AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Tabel absensi_paths_backup
ALTER TABLE absensi_paths_backup AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM absensi_paths_backup);
SET @sql = CONCAT('ALTER TABLE absensi_paths_backup AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Tabel absensi_rate_limit_log
ALTER TABLE absensi_rate_limit_log AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM absensi_rate_limit_log);
SET @sql = CONCAT('ALTER TABLE absensi_rate_limit_log AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Tabel cabang
ALTER TABLE cabang AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM cabang);
SET @sql = CONCAT('ALTER TABLE cabang AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. Tabel cabang_outlet
ALTER TABLE cabang_outlet AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM cabang_outlet);
SET @sql = CONCAT('ALTER TABLE cabang_outlet AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. Tabel hari_libur_nasional
ALTER TABLE hari_libur_nasional AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM hari_libur_nasional);
SET @sql = CONCAT('ALTER TABLE hari_libur_nasional AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 9. Tabel komponen_gaji
ALTER TABLE komponen_gaji AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM komponen_gaji);
SET @sql = CONCAT('ALTER TABLE komponen_gaji AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 10. Tabel komponen_gaji_detail
ALTER TABLE komponen_gaji_detail AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM komponen_gaji_detail);
SET @sql = CONCAT('ALTER TABLE komponen_gaji_detail AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 11. Tabel komponen_gaji_tambahan
ALTER TABLE komponen_gaji_tambahan AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM komponen_gaji_tambahan);
SET @sql = CONCAT('ALTER TABLE komponen_gaji_tambahan AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 12. Tabel libur_nasional
ALTER TABLE libur_nasional AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM libur_nasional);
SET @sql = CONCAT('ALTER TABLE libur_nasional AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 13. Tabel pegawai_whitelist
ALTER TABLE pegawai_whitelist AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM pegawai_whitelist);
SET @sql = CONCAT('ALTER TABLE pegawai_whitelist AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 14. Tabel pengajuan_izin
ALTER TABLE pengajuan_izin AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM pengajuan_izin);
SET @sql = CONCAT('ALTER TABLE pengajuan_izin AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 15. Tabel posisi_jabatan
ALTER TABLE posisi_jabatan AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM posisi_jabatan);
SET @sql = CONCAT('ALTER TABLE posisi_jabatan AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 16. Tabel register (Users)
ALTER TABLE register AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM register);
SET @sql = CONCAT('ALTER TABLE register AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 17. Tabel reset_password
ALTER TABLE reset_password AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM reset_password);
SET @sql = CONCAT('ALTER TABLE reset_password AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 18. Tabel riwayat_gaji
ALTER TABLE riwayat_gaji AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM riwayat_gaji);
SET @sql = CONCAT('ALTER TABLE riwayat_gaji AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 19. Tabel shift_assignments
ALTER TABLE shift_assignments AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM shift_assignments);
SET @sql = CONCAT('ALTER TABLE shift_assignments AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 20. Tabel slip_gaji_batch
ALTER TABLE slip_gaji_batch AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM slip_gaji_batch);
SET @sql = CONCAT('ALTER TABLE slip_gaji_batch AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 21. Tabel slip_gaji_history
ALTER TABLE slip_gaji_history AUTO_INCREMENT = 1;
SET @max_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM slip_gaji_history);
SET @sql = CONCAT('ALTER TABLE slip_gaji_history AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================================
-- VERIFIKASI: Tampilkan status auto_increment semua tabel
-- ========================================================
SELECT 
    TABLE_NAME,
    AUTO_INCREMENT,
    CONCAT('Next ID: ', IFNULL(AUTO_INCREMENT, 'N/A')) as STATUS
FROM 
    information_schema.TABLES
WHERE 
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN (
        'absensi', 'absensi_duplicates_backup', 'absensi_error_log',
        'absensi_paths_backup', 'absensi_rate_limit_log', 'cabang',
        'cabang_outlet', 'hari_libur_nasional', 'komponen_gaji',
        'komponen_gaji_detail', 'komponen_gaji_tambahan', 'libur_nasional',
        'pegawai_whitelist', 'pengajuan_izin', 'posisi_jabatan',
        'register', 'reset_password', 'riwayat_gaji',
        'shift_assignments', 'slip_gaji_batch', 'slip_gaji_history'
    )
ORDER BY 
    TABLE_NAME;

-- ========================================================
-- SELESAI
-- ========================================================
SELECT '✅ Auto-increment reset completed for ALL 21 tables!' as status;
