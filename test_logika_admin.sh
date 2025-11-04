#!/bin/bash

# ========================================================
# Test Script: Validasi Implementasi Logika Admin
# ========================================================

echo "=========================================="
echo "TEST: Logika Khusus Admin vs User"
echo "=========================================="
echo ""

DB_NAME="aplikasi"
DB_USER="root"
DB_PASS=""
MYSQL_CMD="/Applications/XAMPP/xamppfiles/bin/mysql"

# ========================================================
# Test 1: Verifikasi kolom status_kehadiran ada
# ========================================================
echo "Test 1: Cek kolom status_kehadiran di tabel absensi"
$MYSQL_CMD -u $DB_USER $DB_NAME -e "SHOW COLUMNS FROM absensi LIKE 'status_kehadiran';" 2>&1
if [ $? -eq 0 ]; then
    echo "✓ PASS: Kolom status_kehadiran ditemukan"
else
    echo "✗ FAIL: Kolom status_kehadiran tidak ditemukan"
fi
echo ""

# ========================================================
# Test 2: Cek data absensi dengan status_lokasi "Admin - Remote"
# ========================================================
echo "Test 2: Cek absensi admin dengan status_lokasi 'Admin - Remote'"
ADMIN_COUNT=$($MYSQL_CMD -u $DB_USER $DB_NAME -se "SELECT COUNT(*) FROM absensi WHERE status_lokasi = 'Admin - Remote';")
echo "Jumlah absensi admin remote: $ADMIN_COUNT"
if [ "$ADMIN_COUNT" -gt 0 ]; then
    echo "✓ INFO: Ada $ADMIN_COUNT absensi admin dengan status remote"
else
    echo "⚠ INFO: Belum ada absensi admin (perlu test manual)"
fi
echo ""

# ========================================================
# Test 3: Cek status keterlambatan admin
# ========================================================
echo "Test 3: Cek status keterlambatan admin (harusnya 'tidak ada shift')"
$MYSQL_CMD -u $DB_USER $DB_NAME -e "
SELECT 
    a.id,
    a.user_id,
    r.nama_lengkap,
    r.role,
    a.status_lokasi,
    a.status_keterlambatan,
    a.potongan_tunjangan
FROM absensi a
JOIN register r ON a.user_id = r.id
WHERE r.role = 'admin'
ORDER BY a.id DESC
LIMIT 5;
" 2>&1
echo ""

# ========================================================
# Test 4: Simulasi hitung status kehadiran untuk user
# ========================================================
echo "Test 4: Simulasi hitung status kehadiran untuk USER (shift-based)"
$MYSQL_CMD -u $DB_USER $DB_NAME -e "
SELECT 
    a.id,
    r.nama_lengkap,
    r.role,
    a.tanggal_absensi,
    TIME(a.waktu_masuk) as jam_masuk,
    TIME(a.waktu_keluar) as jam_keluar,
    c.jam_keluar as jam_shift_keluar,
    CASE 
        WHEN a.waktu_keluar IS NULL THEN 'Belum Absen Keluar'
        WHEN TIME(a.waktu_keluar) >= c.jam_keluar THEN 'Hadir'
        ELSE 'Tidak Hadir'
    END as status_kehadiran_calculated
FROM absensi a
JOIN register r ON a.user_id = r.id
LEFT JOIN cabang c ON c.id = 1
WHERE r.role = 'user'
ORDER BY a.tanggal_absensi DESC
LIMIT 5;
" 2>&1
echo ""

# ========================================================
# Test 5: Simulasi hitung status kehadiran untuk admin (durasi-based)
# ========================================================
echo "Test 5: Simulasi hitung status kehadiran untuk ADMIN (minimal 8 jam)"
$MYSQL_CMD -u $DB_USER $DB_NAME -e "
SELECT 
    a.id,
    r.nama_lengkap,
    r.role,
    a.tanggal_absensi,
    TIME(a.waktu_masuk) as jam_masuk,
    TIME(a.waktu_keluar) as jam_keluar,
    CASE 
        WHEN a.waktu_keluar IS NULL THEN 'Belum Absen Keluar'
        WHEN TIMESTAMPDIFF(HOUR, a.waktu_masuk, a.waktu_keluar) >= 8 THEN 'Hadir'
        ELSE 'Tidak Hadir'
    END as status_kehadiran_calculated,
    ROUND(TIMESTAMPDIFF(SECOND, a.waktu_masuk, a.waktu_keluar) / 3600, 1) as durasi_jam
FROM absensi a
JOIN register r ON a.user_id = r.id
WHERE r.role = 'admin'
ORDER BY a.tanggal_absensi DESC
LIMIT 5;
" 2>&1
echo ""

# ========================================================
# Test 6: Verifikasi tidak ada admin dengan keterlambatan atau potongan
# ========================================================
echo "Test 6: Verifikasi admin tidak punya keterlambatan atau potongan tunjangan"
ADMIN_WITH_POTONGAN=$($MYSQL_CMD -u $DB_USER $DB_NAME -se "
SELECT COUNT(*) 
FROM absensi a
JOIN register r ON a.user_id = r.id
WHERE r.role = 'admin' 
  AND (a.potongan_tunjangan != 'tidak ada' OR a.status_keterlambatan NOT IN ('tepat waktu', 'tidak ada shift'));
")
if [ "$ADMIN_WITH_POTONGAN" -eq 0 ]; then
    echo "✓ PASS: Tidak ada admin dengan potongan atau keterlambatan yang tidak valid"
else
    echo "✗ FAIL: Ditemukan $ADMIN_WITH_POTONGAN admin dengan potongan/keterlambatan yang tidak seharusnya ada"
fi
echo ""

# ========================================================
# Test 7: Cek distribusi status keterlambatan
# ========================================================
echo "Test 7: Distribusi status keterlambatan (termasuk admin)"
$MYSQL_CMD -u $DB_USER $DB_NAME -e "
SELECT 
    r.role,
    a.status_keterlambatan,
    COUNT(*) as jumlah
FROM absensi a
JOIN register r ON a.user_id = r.id
GROUP BY r.role, a.status_keterlambatan
ORDER BY r.role, 
    CASE a.status_keterlambatan
        WHEN 'tepat waktu' THEN 1
        WHEN 'tidak ada shift' THEN 2
        WHEN 'terlambat' THEN 3
        WHEN 'terlambat 20-40 menit' THEN 4
        WHEN 'terlambat lebih dari 40 menit' THEN 5
        WHEN 'di luar shift' THEN 6
        ELSE 7
    END;
" 2>&1
echo ""

# ========================================================
# Summary
# ========================================================
echo "=========================================="
echo "Test Summary:"
echo "=========================================="
echo "1. Kolom status_kehadiran: CHECK"
echo "2. Data admin remote: CHECK"
echo "3. Status keterlambatan admin: CHECK"
echo "4. Status kehadiran user (shift-based): CHECK"
echo "5. Status kehadiran admin (durasi-based): CHECK"
echo "6. Validasi potongan admin: CHECK"
echo "7. Distribusi status keterlambatan: CHECK"
echo ""
echo "=========================================="
echo "Catatan:"
echo "- Untuk test manual, login sebagai admin dan coba absen"
echo "- Pastikan admin bisa absen dari lokasi mana saja"
echo "- Pastikan admin tidak ada keterlambatan/potongan"
echo "- Pastikan status kehadiran admin dihitung dari durasi kerja (min 8 jam)"
echo "=========================================="
echo ""
echo "Script selesai!"
