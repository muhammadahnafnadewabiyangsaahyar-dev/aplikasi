-- ============================================================
-- POPULATE SALARY DATA
-- ============================================================
-- This script populates salary components for existing employees
-- Adjust the values according to your company's salary structure
-- ============================================================

START TRANSACTION;

-- Update salary data for existing employees
-- Contoh: Gaji untuk posisi "Kasir"
UPDATE register SET 
    gaji_pokok = 4500000,
    tunjangan_transport = 390000,      -- Total untuk 26 hari (15000 x 26)
    tunjangan_makan = 260000,          -- Total untuk 26 hari (10000 x 26)
    tunjangan_jabatan = 0,
    upah_overwork_per_8jam = 50000     -- Upah lembur untuk 8 jam
WHERE posisi LIKE '%kasir%' 
  AND (gaji_pokok = 0 OR gaji_pokok IS NULL);

-- Contoh: Gaji untuk posisi "Staff"
UPDATE register SET 
    gaji_pokok = 4000000,
    tunjangan_transport = 390000,      -- Total untuk 26 hari
    tunjangan_makan = 260000,          -- Total untuk 26 hari
    tunjangan_jabatan = 0,
    upah_overwork_per_8jam = 45000
WHERE posisi LIKE '%staff%' 
  AND (gaji_pokok = 0 OR gaji_pokok IS NULL);

-- Contoh: Gaji untuk posisi "Manager"
UPDATE register SET 
    gaji_pokok = 7000000,
    tunjangan_transport = 520000,      -- Total untuk 26 hari (20000 x 26)
    tunjangan_makan = 390000,          -- Total untuk 26 hari (15000 x 26)
    tunjangan_jabatan = 1000000,
    upah_overwork_per_8jam = 75000
WHERE posisi LIKE '%manager%' 
  AND (gaji_pokok = 0 OR gaji_pokok IS NULL);

-- Contoh: Gaji untuk posisi "Supervisor"
UPDATE register SET 
    gaji_pokok = 5500000,
    tunjangan_transport = 455000,      -- Total untuk 26 hari (17500 x 26)
    tunjangan_makan = 325000,          -- Total untuk 26 hari (12500 x 26)
    tunjangan_jabatan = 500000,
    upah_overwork_per_8jam = 60000
WHERE posisi LIKE '%supervisor%' 
  AND (gaji_pokok = 0 OR gaji_pokok IS NULL);

-- Default salary untuk role yang belum di-set
UPDATE register SET 
    gaji_pokok = 4000000,
    tunjangan_transport = 390000,
    tunjangan_makan = 260000,
    tunjangan_jabatan = 0,
    upah_overwork_per_8jam = 45000
WHERE role = 'user' 
  AND (gaji_pokok = 0 OR gaji_pokok IS NULL);

COMMIT;

-- ============================================================
-- VERIFICATION QUERIES
-- ============================================================
-- Check salary data for all employees
SELECT 
    id,
    nama_lengkap,
    posisi,
    outlet,
    gaji_pokok,
    tunjangan_transport,
    tunjangan_makan,
    tunjangan_jabatan,
    upah_overwork_per_8jam,
    (gaji_pokok + tunjangan_transport + tunjangan_makan + tunjangan_jabatan) AS total_gaji_kotor
FROM register
WHERE role = 'user'
ORDER BY posisi, nama_lengkap;

-- ============================================================
-- NOTES:
-- ============================================================
-- PENTING: Sesuaikan nilai salary sesuai dengan struktur gaji perusahaan Anda!
-- 
-- Penjelasan Kolom:
-- - gaji_pokok: Gaji pokok per bulan
-- - tunjangan_transport: TOTAL untuk 26 hari kerja (akan dibagi proporsional)
-- - tunjangan_makan: TOTAL untuk 26 hari kerja (akan dibagi proporsional)
-- - tunjangan_jabatan: Tunjangan jabatan per bulan (tetap)
-- - upah_overwork_per_8jam: Upah lembur untuk 8 jam (akan dibagi per jam saat kalkulasi)
--
-- Contoh Kalkulasi:
-- Jika pegawai hadir 22 hari:
-- - Tunjangan makan aktual = (260000 / 26) * 22 = 220,000
-- - Tunjangan transport aktual = (390000 / 26) * 22 = 330,000
--
-- Jika pegawai lembur 1.5 jam:
-- - Upah lembur = (50000 / 8) * 1.5 = 9,375
-- ============================================================
