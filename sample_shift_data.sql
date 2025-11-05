-- ============================================================
-- Quick Setup: Sample Shift Assignments for Testing
-- ============================================================

-- Pastikan table shift_assignments sudah ada
-- Jika belum, jalankan migration dari migration_satukan_absensi.sql

-- Contoh: Assign shift untuk user_id = 2 (ganti sesuai user yang login)
-- Untuk bulan November 2025

SET @user_id = 2; -- GANTI dengan user_id yang sesuai
SET @cabang_id = 1; -- GANTI dengan cabang_id yang sesuai
SET @admin_id = 1; -- ID admin yang assign

-- Insert sample shift assignments untuk 1 minggu ke depan
INSERT INTO shift_assignments (user_id, cabang_id, tanggal_shift, status_konfirmasi, created_by, created_at)
VALUES
(@user_id, @cabang_id, '2025-11-05', 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, '2025-11-06', 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, '2025-11-07', 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, '2025-11-08', 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, '2025-11-09', 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, '2025-11-12', 'confirmed', @admin_id, NOW()),
(@user_id, @cabang_id, '2025-11-13', 'confirmed', @admin_id, NOW())
ON DUPLICATE KEY UPDATE status_konfirmasi = VALUES(status_konfirmasi);

-- Cek hasil
SELECT 
    sa.id,
    sa.tanggal_shift,
    r.nama_lengkap as pegawai,
    c.nama_cabang,
    c.nama_shift,
    c.jam_masuk,
    c.jam_keluar,
    sa.status_konfirmasi,
    sa.waktu_konfirmasi
FROM shift_assignments sa
JOIN register r ON sa.user_id = r.id
JOIN cabang c ON sa.cabang_id = c.id
WHERE sa.user_id = @user_id
ORDER BY sa.tanggal_shift DESC;

-- ============================================================
-- NOTE: Setelah insert, refresh halaman jadwal_shift.php
-- ============================================================
