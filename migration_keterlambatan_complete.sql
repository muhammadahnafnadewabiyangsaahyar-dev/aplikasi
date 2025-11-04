-- ========================================================
-- MIGRATION: COMPLETE FIX LOGIKA KETERLAMBATAN
-- Date: 2025-01-03
-- Purpose: Update enum status_keterlambatan dan add potongan_tunjangan
-- ========================================================

USE aplikasi;

-- ========================================================
-- Step 1: Add potongan_tunjangan column (if not exists)
-- ========================================================
ALTER TABLE absensi 
ADD COLUMN IF NOT EXISTS potongan_tunjangan VARCHAR(50) DEFAULT 'tidak ada' 
COMMENT 'Tracking potongan tunjangan berdasarkan keterlambatan'
AFTER status_keterlambatan;

-- ========================================================
-- Step 2: Modify enum for status_keterlambatan
-- ========================================================
-- Hapus constraint enum lama dan set ke VARCHAR sementara
ALTER TABLE absensi 
MODIFY COLUMN status_keterlambatan VARCHAR(50) DEFAULT 'tepat waktu';

-- ========================================================
-- Step 3: Update existing records based on menit_terlambat
-- ========================================================

-- Tepat waktu (0 menit atau NULL)
UPDATE absensi 
SET status_keterlambatan = 'tepat waktu',
    potongan_tunjangan = 'tidak ada'
WHERE menit_terlambat IS NULL OR menit_terlambat = 0;

-- Level 1: Terlambat 1-19 menit (status terlambat, tidak ada potongan)
UPDATE absensi 
SET status_keterlambatan = 'terlambat',
    potongan_tunjangan = 'tidak ada'
WHERE menit_terlambat > 0 AND menit_terlambat < 20;

-- Level 2: Terlambat 20-39 menit (potong tunjangan makan)
UPDATE absensi 
SET status_keterlambatan = 'terlambat 20-40 menit',
    potongan_tunjangan = 'tunjangan makan'
WHERE menit_terlambat >= 20 AND menit_terlambat < 40;

-- Level 3: Terlambat 40+ menit (potong tunjangan makan + transport)
UPDATE absensi 
SET status_keterlambatan = 'terlambat lebih dari 40 menit',
    potongan_tunjangan = 'tunjangan makan dan transport'
WHERE menit_terlambat >= 40;

-- ========================================================
-- Step 4: Set back to ENUM with new values (optional)
-- ========================================================
-- Uncomment jika ingin paksa menggunakan ENUM (not recommended karena kurang fleksibel)
-- ALTER TABLE absensi 
-- MODIFY COLUMN status_keterlambatan ENUM(
--     'tepat waktu', 
--     'terlambat', 
--     'terlambat 20-40 menit', 
--     'terlambat lebih dari 40 menit'
-- ) DEFAULT 'tepat waktu';

-- ========================================================
-- Verification Queries
-- ========================================================

-- Check column exists
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'aplikasi' 
  AND TABLE_NAME = 'absensi' 
  AND COLUMN_NAME IN ('status_keterlambatan', 'potongan_tunjangan');

-- Show distribution of keterlambatan status
SELECT 
    status_keterlambatan,
    potongan_tunjangan,
    COUNT(*) as jumlah,
    MIN(menit_terlambat) as min_menit,
    MAX(menit_terlambat) as max_menit,
    ROUND(AVG(menit_terlambat), 2) as avg_menit
FROM absensi
GROUP BY status_keterlambatan, potongan_tunjangan
ORDER BY min_menit;

-- Sample records dengan keterlambatan
SELECT 
    id,
    user_id,
    tanggal_absensi,
    waktu_masuk,
    menit_terlambat,
    status_keterlambatan,
    potongan_tunjangan
FROM absensi
WHERE menit_terlambat > 0
ORDER BY menit_terlambat DESC
LIMIT 10;

-- ========================================================
-- LOGIKA KETERLAMBATAN BARU (3 LEVEL):
-- ========================================================
-- Level 0: Tepat waktu (0 menit)
--   Status: 'tepat waktu'
--   Potongan: 'tidak ada'
--
-- Level 1: Terlambat 1-19 menit
--   Status: 'terlambat'
--   Potongan: 'tidak ada'
--   Catatan: Hanya status terlambat, TIDAK ADA hukuman/potongan
--
-- Level 2: Terlambat 20-39 menit
--   Status: 'terlambat 20-40 menit'
--   Potongan: 'tunjangan makan'
--
-- Level 3: Terlambat 40+ menit
--   Status: 'terlambat lebih dari 40 menit'
--   Potongan: 'tunjangan makan dan transport'
-- ========================================================
