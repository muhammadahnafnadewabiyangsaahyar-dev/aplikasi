-- ============================================================
-- MIGRATION: Satukan Tabel Absensi dengan Foto Masuk & Keluar
-- Date: 2025-11-05
-- Description: Menambahkan kolom foto_absen_masuk dan foto_absen_keluar
--              untuk memisahkan foto saat absen masuk dan keluar
-- ============================================================

-- Backup existing data (jika diperlukan rollback)
-- CREATE TABLE absensi_backup_20251105 AS SELECT * FROM absensi;

-- Step 1: Rename kolom foto_absen menjadi foto_absen_masuk
ALTER TABLE `absensi` 
CHANGE COLUMN `foto_absen` `foto_absen_masuk` VARCHAR(255) DEFAULT NULL 
COMMENT 'Foto saat absen masuk';

-- Step 2: Tambah kolom foto_absen_keluar
ALTER TABLE `absensi` 
ADD COLUMN `foto_absen_keluar` VARCHAR(255) DEFAULT NULL 
COMMENT 'Foto saat absen keluar' 
AFTER `foto_absen_masuk`;

-- Step 3: Tambah kolom latitude dan longitude untuk absen keluar
ALTER TABLE `absensi` 
ADD COLUMN `latitude_absen_keluar` DECIMAL(10,8) DEFAULT NULL 
COMMENT 'Latitude saat absen keluar' 
AFTER `foto_absen_keluar`;

ALTER TABLE `absensi` 
ADD COLUMN `longitude_absen_keluar` DECIMAL(11,8) DEFAULT NULL 
COMMENT 'Longitude saat absen keluar' 
AFTER `latitude_absen_keluar`;

-- Step 4: Rename kolom latitude_absen dan longitude_absen untuk lebih spesifik
ALTER TABLE `absensi` 
CHANGE COLUMN `latitude_absen` `latitude_absen_masuk` DECIMAL(10,8) DEFAULT NULL 
COMMENT 'Latitude saat absen masuk';

ALTER TABLE `absensi` 
CHANGE COLUMN `longitude_absen` `longitude_absen_masuk` DECIMAL(11,8) DEFAULT NULL 
COMMENT 'Longitude saat absen masuk';

-- Step 5: Migrasi data foto existing (jika ada pattern foto_keluar)
-- Update foto yang diambil saat absen keluar ke kolom foto_absen_keluar
UPDATE `absensi` 
SET 
    `foto_absen_keluar` = `foto_absen_masuk`,
    `foto_absen_masuk` = NULL
WHERE `foto_absen_masuk` LIKE '%absen_keluar%';

-- Step 6: Tambah index untuk performa query
CREATE INDEX idx_user_tanggal ON `absensi` (`user_id`, `tanggal_absensi`);
CREATE INDEX idx_tanggal_absensi ON `absensi` (`tanggal_absensi`);

-- ============================================================
-- STRUKTUR TABEL ABSENSI YANG BARU:
-- ============================================================
-- id                        INT AUTO_INCREMENT PRIMARY KEY
-- user_id                   INT NOT NULL
-- waktu_masuk              DATETIME (waktu absen masuk)
-- waktu_keluar             DATETIME (waktu absen keluar)
-- foto_absen_masuk         VARCHAR(255) (foto saat masuk)
-- foto_absen_keluar        VARCHAR(255) (foto saat keluar)
-- latitude_absen_masuk     DECIMAL(10,8) (koordinat masuk)
-- longitude_absen_masuk    DECIMAL(11,8) (koordinat masuk)
-- latitude_absen_keluar    DECIMAL(10,8) (koordinat keluar)
-- longitude_absen_keluar   DECIMAL(11,8) (koordinat keluar)
-- status_lokasi            ENUM('Valid','Tidak Valid')
-- tanggal_absensi          DATE
-- menit_terlambat          INT
-- status_keterlambatan     ENUM(...)
-- status_lembur            ENUM(...)
-- ============================================================

-- Verification Query
SELECT 
    id,
    user_id,
    DATE(tanggal_absensi) as tanggal,
    TIME(waktu_masuk) as jam_masuk,
    TIME(waktu_keluar) as jam_keluar,
    foto_absen_masuk,
    foto_absen_keluar,
    status_lokasi
FROM absensi 
ORDER BY tanggal_absensi DESC, waktu_masuk DESC
LIMIT 10;

-- ============================================================
-- ROLLBACK (jika diperlukan):
-- ============================================================
-- ALTER TABLE `absensi` DROP COLUMN `foto_absen_keluar`;
-- ALTER TABLE `absensi` DROP COLUMN `latitude_absen_keluar`;
-- ALTER TABLE `absensi` DROP COLUMN `longitude_absen_keluar`;
-- ALTER TABLE `absensi` CHANGE COLUMN `foto_absen_masuk` `foto_absen` VARCHAR(255) DEFAULT NULL;
-- ALTER TABLE `absensi` CHANGE COLUMN `latitude_absen_masuk` `latitude_absen` DECIMAL(10,8) DEFAULT NULL;
-- ALTER TABLE `absensi` CHANGE COLUMN `longitude_absen_masuk` `longitude_absen` DECIMAL(11,8) DEFAULT NULL;
-- DROP INDEX idx_user_tanggal ON `absensi`;
-- DROP INDEX idx_tanggal_absensi ON `absensi`;
-- ============================================================
