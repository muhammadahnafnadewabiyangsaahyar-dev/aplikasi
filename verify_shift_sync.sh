#!/bin/bash

# ============================================
# SHIFT SYNCHRONIZATION VERIFICATION SCRIPT
# ============================================
# Purpose: Verify that kalender.php and shift_management.php
#          are synchronized and using the same API backend
# ============================================

echo "🔍 SHIFT SYNCHRONIZATION VERIFICATION"
echo "======================================"
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Base directory
BASE_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi"

# Check 1: Verify kalender.php uses api_shift_calendar.php
echo "📋 Check 1: Verifying kalender.php + script_kalender_database.js API usage..."
# Check both PHP and JS files
KALENDER_PHP_API=$(grep -o "api_shift[^'\"]*\.php" "$BASE_DIR/kalender.php" 2>/dev/null | sort -u)
KALENDER_JS_API=$(grep -o "api_shift[^'\"]*\.php" "$BASE_DIR/script_kalender_database.js" 2>/dev/null | sort -u)

if [[ "$KALENDER_JS_API" == "api_shift_calendar.php" ]]; then
    echo -e "${GREEN}✅ PASS: kalender.php (via script_kalender_database.js) uses api_shift_calendar.php${NC}"
elif [[ -n "$KALENDER_PHP_API" ]] && [[ "$KALENDER_PHP_API" == "api_shift_calendar.php" ]]; then
    echo -e "${GREEN}✅ PASS: kalender.php uses api_shift_calendar.php${NC}"
else
    echo -e "${RED}❌ FAIL: kalender.php API not found or incorrect${NC}"
    echo "   PHP: $KALENDER_PHP_API"
    echo "   JS: $KALENDER_JS_API"
fi
echo ""

# Check 2: Verify shift_management.php uses api_shift_calendar.php
echo "📋 Check 2: Verifying shift_management.php API usage..."
SHIFT_MGMT_API=$(grep -o "api_shift[^'\"]*\.php" "$BASE_DIR/shift_management.php" 2>/dev/null | sort -u)
if [[ "$SHIFT_MGMT_API" == "api_shift_calendar.php" ]]; then
    echo -e "${GREEN}✅ PASS: shift_management.php uses api_shift_calendar.php${NC}"
else
    echo -e "${RED}❌ FAIL: shift_management.php API: $SHIFT_MGMT_API${NC}"
fi
echo ""

# Check 3: Verify no PHP files use api_shift_management.php
echo "📋 Check 3: Checking for deprecated api_shift_management.php usage..."
DEPRECATED_USAGE=$(grep -l "api_shift_management.php" "$BASE_DIR"/*.php 2>/dev/null)
if [[ -z "$DEPRECATED_USAGE" ]]; then
    echo -e "${GREEN}✅ PASS: No PHP files use deprecated api_shift_management.php${NC}"
else
    echo -e "${RED}❌ FAIL: Files still using api_shift_management.php:${NC}"
    echo "$DEPRECATED_USAGE"
fi
echo ""

# Check 4: Verify api_shift_calendar.php exists
echo "📋 Check 4: Verifying api_shift_calendar.php exists..."
if [[ -f "$BASE_DIR/api_shift_calendar.php" ]]; then
    echo -e "${GREEN}✅ PASS: api_shift_calendar.php exists${NC}"
    # Check for required actions
    ACTIONS=$(grep -o "case '[^']*':" "$BASE_DIR/api_shift_calendar.php" | sed "s/case '//;s/'://" | tr '\n' ', ')
    echo "   Available actions: $ACTIONS"
else
    echo -e "${RED}❌ FAIL: api_shift_calendar.php not found${NC}"
fi
echo ""

# Check 5: Verify database connection
echo "📋 Check 5: Verifying database connection..."
if [[ -f "$BASE_DIR/connect.php" ]]; then
    echo -e "${GREEN}✅ PASS: connect.php exists${NC}"
else
    echo -e "${RED}❌ FAIL: connect.php not found${NC}"
fi
echo ""

# Check 6: Verify shift_assignments table exists
echo "📋 Check 6: Checking shift_assignments table (requires MySQL running)..."
DB_USER="root"
DB_PASS=""
DB_NAME="absensi_pegawai"

mysql -u"$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME; DESCRIBE shift_assignments;" 2>/dev/null
if [[ $? -eq 0 ]]; then
    echo -e "${GREEN}✅ PASS: shift_assignments table exists${NC}"
else
    echo -e "${YELLOW}⚠️  WARNING: Could not verify table (MySQL may not be running)${NC}"
fi
echo ""

# Summary
echo "======================================"
echo "📊 VERIFICATION SUMMARY"
echo "======================================"
echo ""
echo "✅ Both views (kalender.php and shift_management.php) should now:"
echo "   1. Use the same API backend (api_shift_calendar.php)"
echo "   2. Share the same database table (shift_assignments)"
echo "   3. Synchronize changes in real-time"
echo ""
echo "🧪 MANUAL TESTING REQUIRED:"
echo "   1. Create shift in kalender.php → Check shift_management.php"
echo "   2. Create shift in shift_management.php → Check kalender.php"
echo "   3. Delete shift in either view → Verify removed in both"
echo ""
echo "📖 For detailed testing guide, see:"
echo "   $BASE_DIR/SHIFT_SYNCHRONIZATION_COMPLETE.md"
echo ""
