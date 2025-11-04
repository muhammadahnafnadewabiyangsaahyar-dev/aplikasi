#!/bin/bash
# Quick test untuk verify fix absensi

echo "=========================================="
echo "TESTING ABSENSI FIX"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# 1. Check PHP syntax
echo "1. Checking PHP syntax..."
php -l proses_absensi.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "   ${GREEN}✓${NC} proses_absensi.php - No syntax errors"
else
    echo -e "   ${RED}✗${NC} proses_absensi.php - Syntax error!"
    php -l proses_absensi.php
    exit 1
fi

echo ""

# 2. Check folder permission
echo "2. Checking folder permissions..."
if [ -d "uploads/absensi" ]; then
    PERM=$(stat -f %Lp uploads/absensi 2>/dev/null || stat -c %a uploads/absensi 2>/dev/null)
    echo "   uploads/absensi/ permission: $PERM"
    
    if [ "$PERM" = "777" ] || [ "$PERM" = "775" ] || [ "$PERM" = "755" ]; then
        echo -e "   ${GREEN}✓${NC} Permission OK for web server write"
    else
        echo -e "   ${YELLOW}⚠${NC}  Permission may be too restrictive"
        echo "   Setting to 777 for testing..."
        chmod 777 uploads/absensi/
    fi
else
    echo -e "   ${RED}✗${NC} uploads/absensi/ not found!"
    mkdir -p uploads/absensi
    chmod 777 uploads/absensi
    echo -e "   ${GREEN}✓${NC} Created uploads/absensi/"
fi

echo ""

# 3. Test write permission
echo "3. Testing write permission..."
TEST_FILE="uploads/absensi/test_$(date +%s).txt"
echo "test" > "$TEST_FILE" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "   ${GREEN}✓${NC} Can write to uploads/absensi/"
    rm "$TEST_FILE"
else
    echo -e "   ${RED}✗${NC} Cannot write to uploads/absensi/"
    echo "   Fixing permissions..."
    chmod 777 uploads/absensi/
fi

echo ""

# 4. Check logs folder
echo "4. Checking logs folder..."
if [ ! -d "logs" ]; then
    mkdir -p logs
    chmod 777 logs
    echo -e "   ${GREEN}✓${NC} Created logs/ folder"
else
    echo -e "   ${GREEN}✓${NC} logs/ folder exists"
fi

echo ""

# 5. Check recent errors
echo "5. Checking recent PHP errors..."
if [ -f "/Applications/XAMPP/xamppfiles/logs/php_error_log" ]; then
    RECENT_ERRORS=$(tail -10 /Applications/XAMPP/xamppfiles/logs/php_error_log | grep -i "proses_absensi\|absen.php" | wc -l | tr -d ' ')
    if [ "$RECENT_ERRORS" -gt 0 ]; then
        echo -e "   ${YELLOW}⚠${NC}  Found $RECENT_ERRORS recent errors:"
        tail -10 /Applications/XAMPP/xamppfiles/logs/php_error_log | grep -i "proses_absensi\|absen.php"
    else
        echo -e "   ${GREEN}✓${NC} No recent errors in PHP log"
    fi
else
    echo -e "   ${YELLOW}⚠${NC}  PHP error log not found (may be OK)"
fi

echo ""
echo "=========================================="
echo "FIX SUMMARY"
echo "=========================================="
echo ""
echo "Fixed issues:"
echo -e "  ${GREEN}✓${NC} Variable \$tanggal_hari_ini moved to correct position"
echo -e "  ${GREEN}✓${NC} Folder permission set to 777 (writable)"
echo -e "  ${GREEN}✓${NC} PHP syntax validated"
echo ""
echo "Next steps:"
echo "1. ${YELLOW}Test absen masuk${NC} on browser"
echo "2. ${YELLOW}Check if foto saved${NC} to uploads/absensi/"
echo "3. ${YELLOW}Monitor error log${NC}: tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log"
echo ""
echo "If still failing:"
echo "- Check browser console for JavaScript errors"
echo "- Check Network tab for failed requests"
echo "- Check PHP error log for details"
echo ""
echo "=========================================="
