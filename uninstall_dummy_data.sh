#!/bin/bash

# ============================================
# Uninstall Dummy Data
# ============================================

echo "🗑️  UNINSTALL DUMMY DATA"
echo "======================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Database config
DB_NAME="aplikasi"
DB_USER="root"
DB_PASS=""

echo -e "${YELLOW}⚠️  WARNING:${NC}"
echo "Script ini akan menghapus semua dummy data yang telah di-import."
echo ""
echo "Data yang akan dihapus:"
echo "  - Dummy users (dengan username: superadmin, admin*, pegawai*)"
echo "  - Shift assignments yang dibuat oleh dummy admin"
echo "  - Absensi dari dummy pegawai"
echo "  - Whitelist dari dummy pegawai"
echo ""

read -p "Apakah Anda yakin ingin menghapus dummy data? (y/n): " confirm
if [ "$confirm" != "y" ]; then
    echo "Uninstall dibatalkan."
    exit 0
fi

echo ""
echo -e "${YELLOW}🗑️  Menghapus dummy data...${NC}"

# SQL untuk hapus dummy data
mysql -u $DB_USER $DB_NAME <<EOF

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS=0;

-- Hapus whitelist untuk dummy users
DELETE FROM whitelist 
WHERE user_id IN (
    SELECT id FROM register 
    WHERE username IN ('superadmin', 'admin1', 'admin2', 'admin3', 'hradmin', 
                       'pegawai1', 'pegawai2', 'pegawai3', 'pegawai4', 
                       'pegawai5', 'pegawai6', 'pegawai7', 'pegawai8')
);

-- Hapus absensi untuk dummy users
DELETE FROM absen 
WHERE user_id IN (
    SELECT id FROM register 
    WHERE username IN ('pegawai1', 'pegawai2', 'pegawai3', 'pegawai4', 
                       'pegawai5', 'pegawai6', 'pegawai7', 'pegawai8')
);

-- Hapus shift assignments untuk dummy users
DELETE FROM shift_assignments 
WHERE user_id IN (
    SELECT id FROM register 
    WHERE username IN ('pegawai1', 'pegawai2', 'pegawai3', 'pegawai4', 
                       'pegawai5', 'pegawai6', 'pegawai7', 'pegawai8')
);

-- Hapus dummy users
DELETE FROM register 
WHERE username IN ('superadmin', 'admin1', 'admin2', 'admin3', 'hradmin', 
                   'pegawai1', 'pegawai2', 'pegawai3', 'pegawai4', 
                   'pegawai5', 'pegawai6', 'pegawai7', 'pegawai8');

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

SELECT '✅ Dummy data berhasil dihapus!' as status;

EOF

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ DUMMY DATA BERHASIL DIHAPUS!${NC}"
    echo ""
    echo "Database Anda kembali ke kondisi sebelum import dummy data."
    echo ""
else
    echo ""
    echo -e "${RED}❌ Uninstall gagal!${NC}"
    echo "Cek error message di atas."
    exit 1
fi
