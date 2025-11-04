#!/bin/bash
# ========================================================
# QUICK TEST: KETERLAMBATAN & ABSEN KELUAR BERULANG
# ========================================================

echo "=========================================="
echo "QUICK TEST - SISTEM ABSENSI"
echo "Date: $(date)"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql -u root aplikasi"

echo -e "${BLUE}=== TEST 1: Verifikasi Struktur Database ===${NC}"
echo ""

echo "Cek kolom potongan_tunjangan..."
KOLOM_POTONGAN=$($MYSQL -sN -e "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='aplikasi' AND TABLE_NAME='absensi' AND COLUMN_NAME='potongan_tunjangan'")
if [ "$KOLOM_POTONGAN" = "1" ]; then
    echo -e "${GREEN}✓${NC} Kolom potongan_tunjangan exists"
else
    echo -e "${RED}✗${NC} Kolom potongan_tunjangan NOT FOUND!"
fi

echo "Cek tipe kolom status_keterlambatan..."
TIPE_STATUS=$($MYSQL -sN -e "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='aplikasi' AND TABLE_NAME='absensi' AND COLUMN_NAME='status_keterlambatan'")
echo "   Type: $TIPE_STATUS"
if [[ "$TIPE_STATUS" == *"varchar"* ]]; then
    echo -e "${GREEN}✓${NC} status_keterlambatan is VARCHAR (flexible)"
else
    echo -e "${YELLOW}⚠${NC}  status_keterlambatan is ENUM (consider changing to VARCHAR)"
fi

echo ""
echo -e "${BLUE}=== TEST 2: Distribusi Data Keterlambatan ===${NC}"
echo ""

$MYSQL -e "
SELECT 
    status_keterlambatan,
    potongan_tunjangan,
    COUNT(*) as jumlah,
    MIN(menit_terlambat) as min_menit,
    MAX(menit_terlambat) as max_menit
FROM absensi
GROUP BY status_keterlambatan, potongan_tunjangan
ORDER BY min_menit;
"

echo ""
echo -e "${BLUE}=== TEST 3: Sample Record Terlambat ===${NC}"
echo ""

$MYSQL -e "
SELECT 
    id,
    user_id,
    DATE(tanggal_absensi) as tanggal,
    TIME(waktu_masuk) as jam_masuk,
    menit_terlambat,
    status_keterlambatan,
    potongan_tunjangan
FROM absensi
WHERE menit_terlambat > 0
ORDER BY menit_terlambat DESC
LIMIT 5;
"

echo ""
echo -e "${BLUE}=== TEST 4: Cek File PHP ===${NC}"
echo ""

FILES=(
    "proses_absensi.php"
    "absen.php"
    "script_absen.js"
    "view_absensi.php"
    "rekapabsen.php"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file exists"
    else
        echo -e "${RED}✗${NC} $file NOT FOUND!"
    fi
done

echo ""
echo -e "${BLUE}=== TEST 5: Cek Keyword di Kode ===${NC}"
echo ""

echo "Cek 'potongan_tunjangan' di proses_absensi.php..."
if grep -q "potongan_tunjangan" proses_absensi.php 2>/dev/null; then
    COUNT=$(grep -c "potongan_tunjangan" proses_absensi.php)
    echo -e "${GREEN}✓${NC} Found $COUNT occurrences"
else
    echo -e "${RED}✗${NC} Not found!"
fi

echo "Cek 'ALLOW MULTIPLE ABSEN KELUAR' di proses_absensi.php..."
if grep -q "ALLOW MULTIPLE ABSEN KELUAR" proses_absensi.php 2>/dev/null; then
    echo -e "${GREEN}✓${NC} New logic implemented"
else
    echo -e "${RED}✗${NC} Not found!"
fi

echo "Cek 'Update Absen Keluar' di script_absen.js..."
if grep -q "Update Absen Keluar" script_absen.js 2>/dev/null; then
    echo -e "${GREEN}✓${NC} UI update implemented"
else
    echo -e "${RED}✗${NC} Not found!"
fi

echo ""
echo "=========================================="
echo "TEST SUMMARY"
echo "=========================================="
echo ""
echo -e "${GREEN}Database Structure:${NC} Check logs above"
echo -e "${GREEN}Code Implementation:${NC} Check logs above"
echo ""
echo -e "${YELLOW}NEXT STEP: Manual Browser Testing${NC}"
echo ""
echo "1. Login ke aplikasi"
echo "2. Absen masuk di luar jam shift (test keterlambatan)"
echo "3. Absen keluar"
echo "4. Reload halaman → tombol 'Update Absen Keluar' harus muncul"
echo "5. Klik update → cek waktu_keluar di database berubah"
echo "6. Cek view_absensi.php → kolom keterlambatan & potongan tampil"
echo ""
echo "=========================================="
