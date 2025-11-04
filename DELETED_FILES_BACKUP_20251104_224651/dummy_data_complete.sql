-- ============================================
-- DUMMY DATA untuk Testing Sistem Absensi & Shift
-- Created: 2025-11-04
-- WARNING: Hanya untuk testing/development!
-- ============================================

-- Pastikan Anda sudah backup database!
-- mysqldump -u root aplikasi > backup_$(date +%Y%m%d).sql

SET FOREIGN_KEY_CHECKS=0;

-- ============================================
-- 1. DUMMY ADMIN USERS
-- ============================================

INSERT INTO register (username, password, nama_lengkap, email, role, no_hp, posisi, outlet, id_cabang, created_at) VALUES
('superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'galihganji@gmail.com', 'admin', '081234567890', 'Super Admin', 'Head Office', 1, NOW()),
('admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Jakarta', 'galihganji@gmail.com', 'admin', '081234567891', 'Admin', 'Jakarta', 1, NOW()),
('admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Bandung', 'pilaraforismacinta@gmail.com', 'admin', '081234567892', 'Admin', 'Bandung', 2, NOW()),
('admin3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Surabaya', 'dotpikir@gmail.com', 'admin', '081234567893', 'Admin', 'Surabaya', 3, NOW()),
('hradmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR Manager', 'katahnaf@gmail.com', 'admin', '081234567894', 'HR', 'Head Office', 1, NOW());

-- ============================================
-- 2. DUMMY USER/PEGAWAI
-- ============================================

INSERT INTO register (username, password, nama_lengkap, email, role, no_hp, posisi, outlet, id_cabang, created_at) VALUES
('pegawai1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'budi@example.com', 'user', '085234567801', 'Kasir', 'Citraland Gowa', 1, NOW()),
('pegawai2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Nurhaliza', 'siti@example.com', 'user', '085234567802', 'SPG', 'Citraland Gowa', 1, NOW()),
('pegawai3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Rifai', 'ahmad@example.com', 'user', '085234567803', 'Kasir', 'Adhyaksa', 2, NOW()),
('pegawai4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dewi Lestari', 'dewi@example.com', 'user', '085234567804', 'SPG', 'Adhyaksa', 2, NOW()),
('pegawai5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eko Prasetyo', 'eko@example.com', 'user', '085234567805', 'Kasir', 'BTP', 3, NOW()),
('pegawai6', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fitri Handayani', 'fitri@example.com', 'user', '085234567806', 'SPG', 'BTP', 3, NOW()),
('pegawai7', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gunawan Wijaya', 'gunawan@example.com', 'user', '085234567807', 'Security', 'Citraland Gowa', 1, NOW()),
('pegawai8', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hendra Kurniawan', 'hendra@example.com', 'user', '085234567808', 'Cleaning Service', 'Adhyaksa', 2, NOW());

-- NOTE: Password untuk semua user adalah: password
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- ============================================
-- 3. SHIFT ASSIGNMENTS untuk November 2025
-- ============================================

-- Get user IDs (assuming they're auto-incremented from last existing ID)
SET @pegawai1_id = (SELECT id FROM register WHERE username = 'pegawai1');
SET @pegawai2_id = (SELECT id FROM register WHERE username = 'pegawai2');
SET @pegawai3_id = (SELECT id FROM register WHERE username = 'pegawai3');
SET @pegawai4_id = (SELECT id FROM register WHERE username = 'pegawai4');
SET @pegawai5_id = (SELECT id FROM register WHERE username = 'pegawai5');
SET @pegawai6_id = (SELECT id FROM register WHERE username = 'pegawai6');
SET @pegawai7_id = (SELECT id FROM register WHERE username = 'pegawai7');
SET @pegawai8_id = (SELECT id FROM register WHERE username = 'pegawai8');
SET @admin_id = (SELECT id FROM register WHERE username = 'superadmin');

-- Citraland Gowa (cabang_id=1) - Tanggal 5-10 November
INSERT INTO shift_assignments (user_id, cabang_id, tanggal_shift, status_konfirmasi, created_by, created_at) VALUES
(@pegawai1_id, 1, '2025-11-05', 'confirmed', @admin_id, NOW()),
(@pegawai1_id, 1, '2025-11-06', 'confirmed', @admin_id, NOW()),
(@pegawai1_id, 1, '2025-11-07', 'pending', @admin_id, NOW()),
(@pegawai2_id, 1, '2025-11-05', 'confirmed', @admin_id, NOW()),
(@pegawai2_id, 1, '2025-11-06', 'confirmed', @admin_id, NOW()),
(@pegawai2_id, 1, '2025-11-08', 'pending', @admin_id, NOW()),
(@pegawai7_id, 1, '2025-11-05', 'confirmed', @admin_id, NOW()),
(@pegawai7_id, 1, '2025-11-06', 'confirmed', @admin_id, NOW());

-- Adhyaksa (cabang_id=2) - Tanggal 5-10 November
INSERT INTO shift_assignments (user_id, cabang_id, tanggal_shift, status_konfirmasi, created_by, created_at) VALUES
(@pegawai3_id, 2, '2025-11-05', 'confirmed', @admin_id, NOW()),
(@pegawai3_id, 2, '2025-11-06', 'confirmed', @admin_id, NOW()),
(@pegawai3_id, 2, '2025-11-07', 'pending', @admin_id, NOW()),
(@pegawai4_id, 2, '2025-11-05', 'confirmed', @admin_id, NOW()),
(@pegawai4_id, 2, '2025-11-06', 'confirmed', @admin_id, NOW()),
(@pegawai4_id, 2, '2025-11-09', 'pending', @admin_id, NOW()),
(@pegawai8_id, 2, '2025-11-05', 'confirmed', @admin_id, NOW()),
(@pegawai8_id, 2, '2025-11-06', 'confirmed', @admin_id, NOW());

-- BTP (cabang_id=3) - Tanggal 5-10 November
INSERT INTO shift_assignments (user_id, cabang_id, tanggal_shift, status_konfirmasi, created_by, created_at) VALUES
(@pegawai5_id, 3, '2025-11-05', 'confirmed', @admin_id, NOW()),
(@pegawai5_id, 3, '2025-11-06', 'confirmed', @admin_id, NOW()),
(@pegawai5_id, 3, '2025-11-07', 'pending', @admin_id, NOW()),
(@pegawai6_id, 3, '2025-11-05', 'confirmed', @admin_id, NOW()),
(@pegawai6_id, 3, '2025-11-06', 'confirmed', @admin_id, NOW()),
(@pegawai6_id, 3, '2025-11-10', 'pending', @admin_id, NOW());

-- ============================================
-- 4. DUMMY ABSENSI DATA
-- ============================================

-- Absensi untuk tanggal 5 November (tepat waktu)
INSERT INTO absen (user_id, tanggal_absen, jam_masuk, jam_keluar, status_kehadiran, keterlambatan, foto_masuk, foto_keluar, lokasi_masuk, lokasi_keluar) VALUES
(@pegawai1_id, '2025-11-05', '07:00:00', '15:05:00', 'Hadir', 0, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'Citraland Gowa', 'Citraland Gowa'),
(@pegawai2_id, '2025-11-05', '06:58:00', '15:03:00', 'Hadir', 0, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'Citraland Gowa', 'Citraland Gowa'),
(@pegawai3_id, '2025-11-05', '07:02:00', '15:10:00', 'Hadir', 0, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'Adhyaksa', 'Adhyaksa'),
(@pegawai4_id, '2025-11-05', '06:55:00', '15:00:00', 'Hadir', 0, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'Adhyaksa', 'Adhyaksa');

-- Absensi untuk tanggal 6 November (ada yang terlambat)
INSERT INTO absen (user_id, tanggal_absen, jam_masuk, jam_keluar, status_kehadiran, keterlambatan, foto_masuk, foto_keluar, lokasi_masuk, lokasi_keluar) VALUES
(@pegawai1_id, '2025-11-06', '07:15:00', '15:20:00', 'Hadir', 15, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'Citraland Gowa', 'Citraland Gowa'),
(@pegawai2_id, '2025-11-06', '07:30:00', '15:35:00', 'Hadir', 30, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'Citraland Gowa', 'Citraland Gowa'),
(@pegawai3_id, '2025-11-06', '07:05:00', '15:08:00', 'Hadir', 5, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'Adhyaksa', 'Adhyaksa'),
(@pegawai4_id, '2025-11-06', '07:00:00', NULL, 'Lupa Absen Pulang', 0, 'uploads/absen/dummy_foto.jpg', NULL, 'Adhyaksa', NULL);

-- Absensi BTP (jam masuk 08:00)
INSERT INTO absen (user_id, tanggal_absen, jam_masuk, jam_keluar, status_kehadiran, keterlambatan, foto_masuk, foto_keluar, lokasi_masuk, lokasi_keluar) VALUES
(@pegawai5_id, '2025-11-05', '08:00:00', '15:05:00', 'Hadir', 0, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'BTP', 'BTP'),
(@pegawai5_id, '2025-11-06', '08:10:00', '15:15:00', 'Hadir', 10, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'BTP', 'BTP'),
(@pegawai6_id, '2025-11-05', '08:02:00', '15:10:00', 'Hadir', 0, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'BTP', 'BTP'),
(@pegawai6_id, '2025-11-06', '08:00:00', '16:00:00', 'Hadir + Lembur', 0, 'uploads/absen/dummy_foto.jpg', 'uploads/absen/dummy_foto.jpg', 'BTP', 'BTP');

-- ============================================
-- 5. WHITELIST DATA (Komponen Gaji)
-- ============================================

INSERT INTO whitelist (user_id, nama, posisi, cabang, gaji_pokok, tunjangan_transport, tunjangan_makan, bpjs, potongan_bpjs, total_gaji) VALUES
(@pegawai1_id, 'Budi Santoso', 'Kasir', 'Citraland Gowa', 3500000, 500000, 400000, 200000, 100000, 4500000),
(@pegawai2_id, 'Siti Nurhaliza', 'SPG', 'Citraland Gowa', 3000000, 500000, 400000, 200000, 100000, 4000000),
(@pegawai3_id, 'Ahmad Rifai', 'Kasir', 'Adhyaksa', 3500000, 500000, 400000, 200000, 100000, 4500000),
(@pegawai4_id, 'Dewi Lestari', 'SPG', 'Adhyaksa', 3000000, 500000, 400000, 200000, 100000, 4000000),
(@pegawai5_id, 'Eko Prasetyo', 'Kasir', 'BTP', 3500000, 500000, 400000, 200000, 100000, 4500000),
(@pegawai6_id, 'Fitri Handayani', 'SPG', 'BTP', 3000000, 500000, 400000, 200000, 100000, 4000000),
(@pegawai7_id, 'Gunawan Wijaya', 'Security', 'Citraland Gowa', 3200000, 500000, 400000, 200000, 100000, 4200000),
(@pegawai8_id, 'Hendra Kurniawan', 'Cleaning Service', 'Adhyaksa', 3000000, 500000, 400000, 200000, 100000, 4000000);

SET FOREIGN_KEY_CHECKS=1;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Cek jumlah users
SELECT 
    role, 
    COUNT(*) as total 
FROM register 
GROUP BY role;

-- Cek shift assignments
SELECT 
    COUNT(*) as total_assignments,
    COUNT(CASE WHEN status_konfirmasi = 'confirmed' THEN 1 END) as confirmed,
    COUNT(CASE WHEN status_konfirmasi = 'pending' THEN 1 END) as pending
FROM shift_assignments
WHERE tanggal_shift >= '2025-11-01';

-- Cek absensi
SELECT 
    COUNT(*) as total_absensi,
    COUNT(CASE WHEN status_kehadiran = 'Hadir' THEN 1 END) as hadir,
    COUNT(CASE WHEN keterlambatan > 0 THEN 1 END) as terlambat,
    COUNT(CASE WHEN jam_keluar IS NULL THEN 1 END) as lupa_absen_pulang
FROM absen
WHERE tanggal_absen >= '2025-11-01';

-- Cek whitelist
SELECT COUNT(*) as total_whitelist FROM whitelist;

-- ============================================
-- LOGIN CREDENTIALS
-- ============================================

/*
ADMIN ACCOUNTS:
- Username: superadmin | Password: password
- Username: admin1     | Password: password
- Username: admin2     | Password: password
- Username: admin3     | Password: password
- Username: hradmin    | Password: password

USER ACCOUNTS:
- Username: pegawai1   | Password: password
- Username: pegawai2   | Password: password
- Username: pegawai3   | Password: password
- Username: pegawai4   | Password: password
- Username: pegawai5   | Password: password
- Username: pegawai6   | Password: password
- Username: pegawai7   | Password: password
- Username: pegawai8   | Password: password

Total: 13 users (5 admin, 8 pegawai)
*/

-- ============================================
-- DONE!
-- ============================================

SELECT '✅ Dummy data berhasil di-import!' as status;
SELECT 'Silakan login dengan username: superadmin, password: password' as info;
