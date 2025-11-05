#!/bin/bash
# Script untuk testing shift status dan lock feature

echo "=================================================="
echo "🧪 SHIFT STATUS AND LOCK FEATURE TESTING"
echo "=================================================="
echo ""

echo "📋 Test 1: Checking shift_assignments table structure..."
mysql -u root -p << EOF
USE absensi_app;
DESCRIBE shift_assignments;
EOF

echo ""
echo "📋 Test 2: Checking current shift assignments with status..."
mysql -u root -p << EOF
USE absensi_app;
SELECT 
    sa.id,
    sa.tanggal_shift as tanggal,
    r.nama_lengkap as pegawai,
    c.nama_cabang as cabang,
    c.nama_shift as shift,
    sa.status_konfirmasi as status,
    DATE_FORMAT(sa.created_at, '%Y-%m-%d %H:%i') as created
FROM shift_assignments sa
JOIN register r ON sa.user_id = r.id
JOIN cabang c ON sa.cabang_id = c.id
WHERE MONTH(sa.tanggal_shift) = MONTH(CURRENT_DATE)
ORDER BY sa.tanggal_shift DESC, sa.id DESC
LIMIT 10;
EOF

echo ""
echo "📋 Test 3: Creating test shift with pending status..."
read -p "Enter user_id for test: " test_user_id
read -p "Enter cabang_id (shift) for test: " test_cabang_id
read -p "Enter test date (YYYY-MM-DD): " test_date

mysql -u root -p << EOF
USE absensi_app;
INSERT INTO shift_assignments (user_id, cabang_id, tanggal_shift, status_konfirmasi, created_at)
VALUES ($test_user_id, $test_cabang_id, '$test_date', 'pending', NOW());
SELECT LAST_INSERT_ID() as new_shift_id;
EOF

echo ""
read -p "Enter the new shift ID from above: " new_shift_id

echo ""
echo "📋 Test 4: Testing status update to approved..."
mysql -u root -p << EOF
USE absensi_app;
UPDATE shift_assignments 
SET status_konfirmasi = 'approved', waktu_konfirmasi = NOW() 
WHERE id = $new_shift_id;

SELECT 
    id, 
    status_konfirmasi, 
    DATE_FORMAT(waktu_konfirmasi, '%Y-%m-%d %H:%i:%s') as waktu_konfirmasi
FROM shift_assignments 
WHERE id = $new_shift_id;
EOF

echo ""
echo "📋 Test 5: Verifying approved status..."
mysql -u root -p << EOF
USE absensi_app;
SELECT 
    COUNT(*) as total_approved
FROM shift_assignments 
WHERE status_konfirmasi = 'approved' 
AND MONTH(tanggal_shift) = MONTH(CURRENT_DATE);
EOF

echo ""
echo "📋 Test 6: Status distribution..."
mysql -u root -p << EOF
USE absensi_app;
SELECT 
    status_konfirmasi as status,
    COUNT(*) as jumlah
FROM shift_assignments 
WHERE MONTH(tanggal_shift) = MONTH(CURRENT_DATE)
GROUP BY status_konfirmasi
ORDER BY status_konfirmasi;
EOF

echo ""
echo "=================================================="
echo "✅ Testing Complete!"
echo "=================================================="
echo ""
echo "Manual Tests to Perform:"
echo "1. Open http://localhost/aplikasi/kalender.php"
echo "   - Select cabang and shift"
echo "   - Verify status badges (✓, ✗, ⏱) in month view"
echo "   - Switch to day view, check color coding"
echo "   - Switch to week view, check shift summary"
echo ""
echo "2. Open http://localhost/aplikasi/shift_management.php"
echo "   - Check status badges in table"
echo "   - Try to delete an approved shift (should be locked)"
echo "   - Try to delete a pending shift (should work)"
echo ""
echo "3. API Testing:"
echo "   - Try DELETE via Postman/curl for approved shift"
echo "   - Expected: Error 'Shift yang sudah approved tidak dapat dihapus'"
echo ""
echo "=================================================="
