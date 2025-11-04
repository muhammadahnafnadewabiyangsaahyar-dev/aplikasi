#!/bin/bash

# Verify all debug tools are present and accessible

echo "================================================"
echo "  CSRF Debug Tools Verification"
echo "================================================"
echo ""

APP_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi"
BASE_URL="http://localhost/aplikasi"

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "Checking debug tools in: $APP_DIR"
echo ""

# Check files
files=(
    "test_logging.php:Logging Test"
    "debug_import_forms.php:Form Debugger (★ PRIMARY TOOL)"
    "diagnostic_import.php:Full Diagnostic"
    "debug_csrf.php:CSRF Debug Tool"
    "fix_csrf_tokens.php:Token Regenerator"
    "whitelist.php:Main Import Page"
    "monitor_csrf_logs.sh:Log Monitor Script"
    "DIAGNOSTIC_STEPS.md:Troubleshooting Guide"
    "DEBUG_TOOLKIT_SUMMARY.md:Complete Toolkit Guide"
    "QUICK_DEBUG_CARD_V2.md:Quick Reference Card"
)

echo "📁 File Check:"
echo "----------------------------------------"
for item in "${files[@]}"; do
    IFS=':' read -r file desc <<< "$item"
    if [ -f "$APP_DIR/$file" ]; then
        echo -e "${GREEN}✓${NC} $file - $desc"
    else
        echo -e "${RED}✗${NC} $file - $desc (MISSING)"
    fi
done

echo ""
echo "🔗 URLs to Test:"
echo "----------------------------------------"
echo "1. ${BLUE}$BASE_URL/test_logging.php${NC}"
echo "   → Verify error logging works"
echo ""
echo "2. ${BLUE}$BASE_URL/debug_import_forms.php${NC} ⭐ START HERE"
echo "   → Test forms with console logging"
echo ""
echo "3. ${BLUE}$BASE_URL/diagnostic_import.php${NC}"
echo "   → Full CSRF diagnostic"
echo ""
echo "4. ${BLUE}$BASE_URL/debug_csrf.php${NC}"
echo "   → View session tokens"
echo ""
echo "5. ${BLUE}$BASE_URL/whitelist.php${NC}"
echo "   → Actual import page (now with debug logging)"
echo ""

echo "🔧 Scripts to Run:"
echo "----------------------------------------"
echo "Monitor logs in real-time:"
echo "  cd $APP_DIR"
echo "  ./monitor_csrf_logs.sh"
echo ""
echo "Or manually:"
echo "  tail -f /Applications/XAMPP/xamppfiles/logs/error_log | grep -i csrf"
echo ""

echo "📖 Documentation:"
echo "----------------------------------------"
echo "Complete guide: $APP_DIR/DEBUG_TOOLKIT_SUMMARY.md"
echo "Quick reference: $APP_DIR/QUICK_DEBUG_CARD_V2.md"
echo "Step-by-step: $APP_DIR/DIAGNOSTIC_STEPS.md"
echo ""

# Check if XAMPP is running
if curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/test_logging.php" | grep -q "200"; then
    echo -e "${GREEN}✓${NC} XAMPP is running and accessible"
else
    echo -e "${YELLOW}⚠${NC} Cannot reach $BASE_URL - make sure XAMPP is running"
fi

echo ""
echo "================================================"
echo "🚀 QUICK START:"
echo "================================================"
echo ""
echo "1. Open Browser Console (F12)"
echo "2. Visit: $BASE_URL/debug_import_forms.php"
echo "3. Test Method 2 with a CSV file"
echo "4. Watch console for debug output"
echo ""
echo "Expected console output:"
echo "  ✅ Form submitting: POST"
echo "  ✅ CSRF token present: 9f3a7b2c..."
echo "  ✅ Form data being sent:"
echo "     csrf_token: 9f3a7b2c..."
echo "     test_file: [File: yourfile.csv]"
echo ""
echo "This will tell us if CSRF tokens are working!"
echo ""
echo "================================================"
