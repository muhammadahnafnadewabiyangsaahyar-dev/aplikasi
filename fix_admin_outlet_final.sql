-- ========================================================
-- FIX ADMIN OUTLET ID - FINAL VERSION
-- ========================================================
-- Update semua admin users untuk menggunakan Kaori HQ outlet
-- Support kedua field: outlet_id dan id_cabang
-- ========================================================

-- Step 1: Cek struktur tabel register
-- (Jalankan ini dulu untuk tahu field mana yang ada)
SHOW COLUMNS FROM register LIKE '%cabang%';
SHOW COLUMNS FROM register LIKE '%outlet%';

-- Step 2: Update admin outlet (pilih salah satu sesuai field yang ada)

-- Jika pakai field 'outlet_id':
UPDATE register 
SET outlet_id = (SELECT id FROM cabang WHERE nama_cabang LIKE '%Kaori HQ%' LIMIT 1)
WHERE role = 'admin';

-- Jika pakai field 'id_cabang':
UPDATE register 
SET id_cabang = (SELECT id FROM cabang WHERE nama_cabang LIKE '%Kaori HQ%' LIMIT 1)
WHERE role = 'admin';

-- Step 3: Verifikasi hasil
SELECT 
    id, 
    username, 
    role, 
    outlet_id,
    id_cabang,
    CASE 
        WHEN outlet_id IS NOT NULL THEN (SELECT nama_cabang FROM cabang WHERE id = outlet_id)
        WHEN id_cabang IS NOT NULL THEN (SELECT nama_cabang FROM cabang WHERE id = id_cabang)
        ELSE 'NO OUTLET ASSIGNED'
    END as outlet_name
FROM register 
WHERE role = 'admin';

-- ========================================================
-- QUICK FIX: Update specific admin user (ID = 1)
-- ========================================================
-- Jika query di atas tidak work, jalankan ini:
UPDATE register SET id_cabang = 10 WHERE id = 1 AND role = 'admin';
UPDATE register SET outlet_id = 10 WHERE id = 1 AND role = 'admin';

-- Verifikasi admin user ID 1:
SELECT * FROM register WHERE id = 1;
