#!/bin/bash

# Quick test for DayPilot Scheduler fix

echo "🔧 Testing DayPilot Scheduler Fix"
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}1. Checking if shift_calendar.php was updated...${NC}"
if grep -q "rowHeaderColumns" "/Applications/XAMPP/xamppfiles/htdocs/aplikasi/shift_calendar.php"; then
    echo -e "${RED}❌ FAIL: rowHeaderColumns still exists (should be removed)${NC}"
else
    echo -e "${GREEN}✅ PASS: rowHeaderColumns removed${NC}"
fi

echo ""
echo -e "${BLUE}2. Checking for initialization check in loadCalendar...${NC}"
if grep -q "if (!dp)" "/Applications/XAMPP/xamppfiles/htdocs/aplikasi/shift_calendar.php"; then
    echo -e "${GREEN}✅ PASS: Initialization check added${NC}"
else
    echo -e "${RED}❌ FAIL: Initialization check not found${NC}"
fi

echo ""
echo -e "${BLUE}3. Checking for month change delay...${NC}"
if grep -q "setTimeout" "/Applications/XAMPP/xamppfiles/htdocs/aplikasi/shift_calendar.php" | grep -q "loadCalendar"; then
    echo -e "${GREEN}✅ PASS: Delay added for month change${NC}"
else
    echo -e "${YELLOW}⚠️  WARNING: setTimeout pattern not clearly found${NC}"
fi

echo ""
echo -e "${GREEN}=================================="
echo "Testing Summary"
echo "==================================${NC}"
echo ""
echo "Next steps:"
echo "1. Open shift_calendar.php in browser"
echo "2. Open Console (F12)"
echo "3. Check for these messages:"
echo "   - 'Initializing shift calendar...'"
echo "   - 'DayPilot Scheduler initialized successfully'"
echo "   - NO 'rows.Mr is not a function' error"
echo ""
echo "4. Select a cabang from dropdown"
echo "5. Verify calendar loads without errors"
echo ""
echo -e "${BLUE}Opening browser test page...${NC}"

# Open test page
open "http://localhost/aplikasi/shift_calendar.php"

echo ""
echo -e "${GREEN}✅ Test page opened!${NC}"
echo ""
echo "Check your browser console for any errors!"
