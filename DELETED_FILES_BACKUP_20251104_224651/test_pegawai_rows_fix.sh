#!/bin/bash

echo "🔧 Quick Fix Test - Pegawai Rows Issue"
echo "========================================"
echo ""

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}Checking fixes applied...${NC}"
echo ""

# Check 1: Enhanced logging
echo "1. Checking enhanced logging..."
if grep -q "Loading calendar data for cabang_id" shift_calendar.php; then
    echo -e "${GREEN}✅ Enhanced logging added${NC}"
else
    echo -e "${YELLOW}⚠️  Enhanced logging not found${NC}"
fi

# Check 2: Auto-load on initial selection
echo ""
echo "2. Checking auto-load feature..."
if grep -q "Initial cabang already selected" shift_calendar.php; then
    echo -e "${GREEN}✅ Auto-load feature added${NC}"
else
    echo -e "${YELLOW}⚠️  Auto-load feature not found${NC}"
fi

# Check 3: Event listener logging
echo ""
echo "3. Checking event listener logging..."
if grep -q "Cabang changed to:" shift_calendar.php; then
    echo -e "${GREEN}✅ Event listener logging added${NC}"
else
    echo -e "${YELLOW}⚠️  Event listener logging not found${NC}"
fi

echo ""
echo -e "${GREEN}========================================"
echo "Next Steps:"
echo "========================================${NC}"
echo ""
echo "1. Hard refresh your browser:"
echo "   Mac: Cmd + Shift + R"
echo "   Windows: Ctrl + Shift + R"
echo ""
echo "2. Open Console (F12)"
echo ""
echo "3. Look for these messages:"
echo "   - 'Initializing shift calendar...'"
echo "   - 'Loading calendar data for cabang_id: X'"
echo "   - 'Setting rows.list with X pegawai'"
echo "   - '✅ Rows updated successfully!'"
echo ""
echo "4. Calendar should now show pegawai names!"
echo ""
echo -e "${BLUE}Opening shift calendar in browser...${NC}"

open "http://localhost/aplikasi/shift_calendar.php"

echo ""
echo -e "${GREEN}✅ Browser opened!${NC}"
echo ""
echo "Check Console (F12) for detailed logs!"
echo ""
