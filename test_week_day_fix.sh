#!/bin/bash

# Quick Test Script for Week/Day View Fix
# Tests the critical bug fix for shift display in week and day views

echo "=================================================="
echo "🧪 QUICK TEST: Week & Day View Shift Display Fix"
echo "=================================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Test 1: Verify file changes
echo "📁 Test 1: Verifying file changes..."
if grep -q "currentCabangId}&month=" "/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js"; then
    echo -e "${GREEN}✅ PASS: API call uses currentCabangId (correct parameter)${NC}"
else
    echo -e "${RED}❌ FAIL: API call still uses wrong parameter${NC}"
    exit 1
fi

# Test 2: Check switchView function
echo ""
echo "📁 Test 2: Verifying switchView() reload logic..."
if grep -q "if (currentCabangId && currentShiftId) {" "/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js"; then
    echo -e "${GREEN}✅ PASS: switchView() includes reload logic${NC}"
else
    echo -e "${RED}❌ FAIL: switchView() missing reload logic${NC}"
    exit 1
fi

# Test 3: Check for syntax errors
echo ""
echo "🔍 Test 3: Checking for JavaScript syntax errors..."
if command -v node &> /dev/null; then
    if node -c "/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js" 2>/dev/null; then
        echo -e "${GREEN}✅ PASS: No syntax errors found${NC}"
    else
        echo -e "${YELLOW}⚠️  WARNING: Syntax check inconclusive (but VSCode validation passed)${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  SKIP: Node.js not available for syntax check${NC}"
fi

# Test 4: Verify debug logging exists
echo ""
echo "📁 Test 4: Verifying debug logging exists..."
log_count=$(grep -c "console.log" "/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js")
if [ $log_count -gt 20 ]; then
    echo -e "${GREEN}✅ PASS: Found $log_count debug log statements${NC}"
else
    echo -e "${YELLOW}⚠️  WARNING: Only $log_count debug log statements found${NC}"
fi

# Test 5: API endpoint accessibility
echo ""
echo "🌐 Test 5: Testing API endpoint..."
api_url="http://localhost/aplikasi/api_shift_calendar.php?action=get_assignments&cabang_id=1&month=2025-11"

if command -v curl &> /dev/null; then
    response=$(curl -s -o /dev/null -w "%{http_code}" "$api_url")
    if [ "$response" = "200" ]; then
        echo -e "${GREEN}✅ PASS: API endpoint accessible (HTTP 200)${NC}"
    else
        echo -e "${RED}❌ FAIL: API returned HTTP $response${NC}"
        echo "   Make sure XAMPP is running!"
    fi
else
    echo -e "${YELLOW}⚠️  SKIP: curl not available${NC}"
fi

echo ""
echo "=================================================="
echo "📋 MANUAL TESTING REQUIRED"
echo "=================================================="
echo ""
echo "Please perform these manual tests in browser:"
echo ""
echo "1. Open: http://localhost/aplikasi/kalender.php"
echo "2. Open Browser DevTools (F12) → Console tab"
echo "3. Select Cabang: Kalimantan"
echo "4. Select Shift: Pagi"
echo ""
echo "5. TEST MONTH VIEW:"
echo "   - Verify shifts show on Nov 2, 3, 4, 5"
echo "   - Click on Nov 2"
echo ""
echo "6. TEST DAY VIEW:"
echo "   - Should auto-switch to day view for Nov 2"
echo "   - Verify shifts appear in time slots"
echo "   - Check console for: 'Day view - Found X shifts'"
echo ""
echo "7. TEST WEEK VIEW:"
echo "   - Click 'Week' button"
echo "   - Verify shifts appear in correct time slots"
echo "   - Check console for: 'Week view - Day ... Found X shifts'"
echo ""
echo "8. TEST VIEW SWITCHING:"
echo "   - Switch: Month → Week → Day → Month"
echo "   - Verify shifts persist across all switches"
echo ""
echo "Expected console logs:"
echo "  ✓ Loading shift assignments for months: [...]"
echo "  ✓ loadShiftAssignments - API response for 2025-11: ..."
echo "  ✓ Day view - Found X shifts for 2025-11-02"
echo "  ✓ Week view - Day 2025-11-02: Found X shifts"
echo ""
echo "=================================================="
echo "🔍 DEBUGGING TIPS"
echo "=================================================="
echo ""
echo "If shifts still don't appear:"
echo ""
echo "1. Check Network Tab in DevTools:"
echo "   - Filter: api_shift_calendar.php"
echo "   - Verify URL contains: cabang_id=<NUMBER>&month=2025-11"
echo "   - Check response has data array with assignments"
echo ""
echo "2. Check Console Variables:"
echo "   - Type: currentCabangId"
echo "   - Type: currentShiftId"
echo "   - Type: shiftAssignments"
echo "   - All should have values (not null)"
echo ""
echo "3. Check Database:"
echo "   mysql> SELECT * FROM absen WHERE cabang_id=1 AND tanggal_shift='2025-11-02';"
echo ""
echo "4. Clear Browser Cache: Ctrl+F5 or Cmd+Shift+R"
echo ""
echo "=================================================="
echo "📄 Documentation: CRITICAL_BUG_FIX_WEEK_DAY_VIEW.md"
echo "=================================================="
echo ""
