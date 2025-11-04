#!/bin/bash

# ============================================
# Install Dummy Data untuk Testing
# ============================================

echo "🎭 INSTALL DUMMY DATA"
echo "===================="
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
echo "Script ini akan menambahkan dummy data ke database Anda."
echo ""
echo "Data yang akan ditambahkan:"
echo "  - 5 Admin users"
echo "  - 8 Pegawai users"
echo "  - 24 Shift assignments"
echo "  - 12 Absensi records"
echo "  - 8 Whitelist records"
echo ""

# Backup confirmation
read -p "Apakah Anda sudah backup database? (y/n): " backup_confirm
if [ "$backup_confirm" != "y" ]; then
    echo ""
    echo -e "${YELLOW}📦 Membuat backup otomatis...${NC}"
    mysqldump -u $DB_USER $DB_NAME > "backup_before_dummy_$(date +%Y%m%d_%H%M%S).sql"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Backup berhasil dibuat!${NC}"
    else
        echo -e "${RED}❌ Backup gagal! Batalkan instalasi.${NC}"
        exit 1
    fi
fi

echo ""
read -p "Lanjutkan install dummy data? (y/n): " confirm
if [ "$confirm" != "y" ]; then
    echo "Instalasi dibatalkan."
    exit 0
fi

echo ""
echo -e "${YELLOW}📥 Importing dummy data...${NC}"

# Import SQL file
mysql -u $DB_USER $DB_NAME < dummy_data_complete.sql

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ DUMMY DATA BERHASIL DI-INSTALL!${NC}"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "🔐 LOGIN CREDENTIALS"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo -e "${GREEN}ADMIN:${NC}"
    echo "  Username: superadmin"
    echo "  Password: password"
    echo ""
    echo -e "${GREEN}USER/PEGAWAI:${NC}"
    echo "  Username: pegawai1"
    echo "  Password: password"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "📚 Lihat DUMMY_USERS_INFO.md untuk daftar lengkap"
    echo ""
    echo -e "${YELLOW}🧪 Quick Test:${NC}"
    echo "  1. Login sebagai superadmin"
    echo "  2. Buka Shift Calendar"
    echo "  3. Pilih cabang 'Citraland Gowa'"
    echo "  4. Lihat shift assignments yang sudah ada"
    echo ""
else
    echo ""
    echo -e "${RED}❌ Import gagal!${NC}"
    echo "Cek error message di atas."
    exit 1
fi
