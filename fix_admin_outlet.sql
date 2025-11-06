-- ========================================================
-- QUICK FIX: Update Admin outlet_id ke Kaori HQ
-- ========================================================

-- Update superadmin (user_id = 1) ke Kaori HQ (id = 10)
UPDATE register SET outlet_id = 10 WHERE id = 1;

-- Pastikan outlet_id sudah terupdate
SELECT id, username, role, outlet_id, outlet, id_cabang 
FROM register 
WHERE id = 1;

-- Cek semua admin users
SELECT r.id, r.username, r.role, r.outlet_id, r.id_cabang, c.nama_cabang
FROM register r
LEFT JOIN cabang c ON r.outlet_id = c.id
WHERE r.role = 'admin';
