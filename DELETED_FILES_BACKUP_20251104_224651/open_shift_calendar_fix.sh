#!/bin/bash

# Shift Calendar Fix - Quick Access Script
# This script opens all relevant testing and documentation files

echo "🔧 Shift Calendar Fix - Quick Access"
echo "===================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# URLs
TEST_PAGE="http://localhost/aplikasi/test_shift_calendar_fix.html"
SHIFT_CALENDAR="http://localhost/aplikasi/shift_calendar.php"
LOGIN_PAGE="http://localhost/aplikasi/login.php"

echo -e "${BLUE}📋 Opening documentation...${NC}"
echo ""
echo "1. Comprehensive Fix Documentation"
open "/Applications/XAMPP/xamppfiles/htdocs/aplikasi/SHIFT_CALENDAR_FIX_COMPREHENSIVE.md"

echo ""
echo -e "${BLUE}🧪 Opening test pages in browser...${NC}"
echo ""
echo "2. Test Suite"
open "$TEST_PAGE"

sleep 2

echo "3. Shift Calendar (Main Page)"
open "$SHIFT_CALENDAR"

sleep 1

echo ""
echo -e "${GREEN}✅ Files opened!${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Review the fix documentation"
echo "2. Run the test suite (test_shift_calendar_fix.html)"
echo "3. Login as admin and test shift_calendar.php"
echo ""
echo "Login credentials:"
echo "  Username: superadmin"
echo "  Password: password"
echo ""
echo "Check browser console (F12) for any errors!"
echo ""
echo -e "${GREEN}Happy testing! 🚀${NC}"
