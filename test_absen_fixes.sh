#!/bin/bash
# ========================================================
# QUICK TEST SCRIPT - ABSENSI FIXES
# Purpose: Test critical functionality after fixes
# ========================================================

echo "=========================================="
echo "TESTING ABSENSI CRITICAL FIXES"
echo "Date: $(date)"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql"
DB="aplikasi"

echo "1. Testing Database Schema..."
echo "   - Checking foto_absen_keluar column..."

RESULT=$($MYSQL -u root -s -N -e "
SELECT COUNT(*) 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = '$DB' 
AND TABLE_NAME = 'absensi' 
AND COLUMN_NAME = 'foto_absen_keluar';
")

if [ "$RESULT" -eq "1" ]; then
    echo -e "   ${GREEN}✓${NC} foto_absen_keluar column EXISTS"
else
    echo -e "   ${RED}✗${NC} foto_absen_keluar column NOT FOUND"
fi

echo ""
echo "2. Testing Error Log Tables..."

RESULT=$($MYSQL -u root -s -N -e "
SELECT COUNT(*) 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = '$DB' 
AND TABLE_NAME = 'absensi_error_log';
")

if [ "$RESULT" -eq "1" ]; then
    echo -e "   ${GREEN}✓${NC} absensi_error_log table EXISTS"
else
    echo -e "   ${RED}✗${NC} absensi_error_log table NOT FOUND"
fi

RESULT=$($MYSQL -u root -s -N -e "
SELECT COUNT(*) 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = '$DB' 
AND TABLE_NAME = 'absensi_rate_limit_log';
")

if [ "$RESULT" -eq "1" ]; then
    echo -e "   ${GREEN}✓${NC} absensi_rate_limit_log table EXISTS"
else
    echo -e "   ${RED}✗${NC} absensi_rate_limit_log table NOT FOUND"
fi

echo ""
echo "3. Checking Duplicate Records..."

DUPLICATES=$($MYSQL -u root -s -N -e "
SELECT COUNT(*) 
FROM (
    SELECT user_id, tanggal_absensi 
    FROM $DB.absensi 
    GROUP BY user_id, tanggal_absensi 
    HAVING COUNT(*) > 1
) as dup;
")

if [ "$DUPLICATES" -eq "0" ]; then
    echo -e "   ${GREEN}✓${NC} No duplicate records found"
else
    echo -e "   ${YELLOW}⚠${NC}  $DUPLICATES duplicate records found (need cleanup)"
    echo ""
    echo "   Run: mysql -u root aplikasi < cleanup_duplicate_absen.sql"
fi

echo ""
echo "4. Testing File System..."

if [ -d "uploads/absensi" ]; then
    echo -e "   ${GREEN}✓${NC} uploads/absensi/ directory EXISTS"
else
    echo -e "   ${RED}✗${NC} uploads/absensi/ directory NOT FOUND"
    mkdir -p uploads/absensi
    echo -e "   ${GREEN}✓${NC} Created uploads/absensi/"
fi

if [ -d "logs" ]; then
    echo -e "   ${GREEN}✓${NC} logs/ directory EXISTS"
else
    echo -e "   ${RED}✗${NC} logs/ directory NOT FOUND"
    mkdir -p logs
    echo -e "   ${GREEN}✓${NC} Created logs/"
fi

echo ""
echo "5. Testing Backup Files..."

BACKUP_COUNT=$(ls -1 *.backup_* 2>/dev/null | wc -l)
echo "   Found $BACKUP_COUNT backup files"

if [ $BACKUP_COUNT -gt 0 ]; then
    echo -e "   ${GREEN}✓${NC} Backup files present"
    ls -lh *.backup_* 2>/dev/null | tail -5
else
    echo -e "   ${YELLOW}⚠${NC}  No backup files found"
fi

echo ""
echo "6. Testing PHP Syntax..."

php -l proses_absensi.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "   ${GREEN}✓${NC} proses_absensi.php - No syntax errors"
else
    echo -e "   ${RED}✗${NC} proses_absensi.php - Syntax error detected!"
fi

php -l absen.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "   ${GREEN}✓${NC} absen.php - No syntax errors"
else
    echo -e "   ${RED}✗${NC} absen.php - Syntax error detected!"
fi

echo ""
echo "=========================================="
echo "TEST SUMMARY"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. ${YELLOW}Review duplicate records${NC} (if any)"
echo "2. ${YELLOW}Run cleanup script${NC}: mysql -u root aplikasi < cleanup_duplicate_absen.sql"
echo "3. ${YELLOW}Test absensi manually${NC} on browser"
echo "4. ${YELLOW}Monitor error logs${NC}: tail -f logs/absensi_errors.log"
echo ""
echo "Documentation:"
echo "- IMPLEMENTASI_ABSEN_FIXES.md"
echo "- ANALISIS_ABSEN_PHP.md"
echo ""
echo "=========================================="
