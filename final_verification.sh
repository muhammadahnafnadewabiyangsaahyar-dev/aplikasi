#!/bin/bash

echo "========================================="
echo "🔍 FINAL VERIFICATION TEST"
echo "========================================="
echo ""
echo "Test ini akan memverifikasi:"
echo "1. Tidak ada output tak diinginkan"
echo "2. Tidak ada BOM atau karakter tersembunyi"
echo "3. Syntax PHP valid"
echo "4. File structure correct"
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counter
PASS=0
FAIL=0

echo "========================================="
echo "TEST 1: File Include Output Check"
echo "========================================="
files_to_check=("connect.php" "calculate_status_kehadiran.php" "absen_helper.php")
for file in "${files_to_check[@]}"; do
    if [ -f "$file" ]; then
        echo -n "Checking $file... "
        output=$(php -r "ob_start(); include '$file'; \$out = ob_get_clean(); if (!empty(\$out)) echo \$out;" 2>&1)
        if [ -z "$output" ]; then
            echo -e "${GREEN}✅ PASS${NC}"
            ((PASS++))
        else
            echo -e "${RED}❌ FAIL - Has output${NC}"
            echo "Output: $output"
            ((FAIL++))
        fi
    fi
done
echo ""

echo "========================================="
echo "TEST 2: BOM Check"
echo "========================================="
all_php_files=("connect.php" "calculate_status_kehadiran.php" "absen_helper.php" "navbar.php" "index.php" "rekapabsen.php")
for file in "${all_php_files[@]}"; do
    if [ -f "$file" ]; then
        first_bytes=$(head -c 3 "$file" | od -An -tx1 | tr -d ' ')
        echo -n "Checking $file... "
        if [ "$first_bytes" != "efbbbf" ]; then
            echo -e "${GREEN}✅ PASS (No BOM)${NC}"
            ((PASS++))
        else
            echo -e "${RED}❌ FAIL (Has BOM)${NC}"
            ((FAIL++))
        fi
    fi
done
echo ""

echo "========================================="
echo "TEST 3: PHP Syntax Check"
echo "========================================="
php_pages=("index.php" "rekapabsen.php" "view_absensi.php" "absen.php" "connect.php" "calculate_status_kehadiran.php")
for file in "${php_pages[@]}"; do
    if [ -f "$file" ]; then
        echo -n "Checking $file... "
        if php -l "$file" > /dev/null 2>&1; then
            echo -e "${GREEN}✅ PASS${NC}"
            ((PASS++))
        else
            echo -e "${RED}❌ FAIL${NC}"
            php -l "$file"
            ((FAIL++))
        fi
    fi
done
echo ""

echo "========================================="
echo "TEST 4: Closing Tag Check"
echo "========================================="
include_files=("connect.php" "calculate_status_kehadiran.php" "absen_helper.php")
for file in "${include_files[@]}"; do
    if [ -f "$file" ]; then
        echo -n "Checking $file... "
        # Check if file ends with ?>
        last_line=$(tail -n 1 "$file")
        if [[ "$last_line" == *"?>"* ]]; then
            echo -e "${RED}❌ FAIL (Has closing tag ?>)${NC}"
            ((FAIL++))
        else
            echo -e "${GREEN}✅ PASS (No closing tag)${NC}"
            ((PASS++))
        fi
    fi
done
echo ""

echo "========================================="
echo "TEST 5: Output Buffering in rekapabsen.php"
echo "========================================="
if [ -f "rekapabsen.php" ]; then
    echo -n "Checking for ob_start()... "
    if grep -q "ob_start()" rekapabsen.php; then
        echo -e "${GREEN}✅ PASS${NC}"
        ((PASS++))
    else
        echo -e "${RED}❌ FAIL (Missing ob_start)${NC}"
        ((FAIL++))
    fi
    
    echo -n "Checking for ob_end_flush()... "
    if grep -q "ob_end_flush()" rekapabsen.php; then
        echo -e "${GREEN}✅ PASS${NC}"
        ((PASS++))
    else
        echo -e "${RED}❌ FAIL (Missing ob_end_flush)${NC}"
        ((FAIL++))
    fi
fi
echo ""

echo "========================================="
echo "TEST 6: Font Awesome Link Check"
echo "========================================="
html_pages=("index.php" "rekapabsen.php" "view_absensi.php")
for file in "${html_pages[@]}"; do
    if [ -f "$file" ]; then
        echo -n "Checking $file... "
        if grep -q "font-awesome" "$file"; then
            echo -e "${GREEN}✅ PASS (Has Font Awesome link)${NC}"
            ((PASS++))
        else
            echo -e "${YELLOW}⚠️  WARNING (No Font Awesome link)${NC}"
        fi
    fi
done
echo ""

echo "========================================="
echo "📊 FINAL RESULTS"
echo "========================================="
echo -e "Tests Passed: ${GREEN}$PASS${NC}"
echo -e "Tests Failed: ${RED}$FAIL${NC}"
echo ""

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}🎉 ALL TESTS PASSED!${NC}"
    echo ""
    echo "✅ File structure is correct"
    echo "✅ No unwanted output"
    echo "✅ No BOM detected"
    echo "✅ PHP syntax valid"
    echo "✅ Output buffering configured"
    echo ""
    echo "📋 NEXT STEPS:"
    echo "1. Open browser and navigate to: http://localhost/aplikasi/"
    echo "2. Test files available:"
    echo "   - http://localhost/aplikasi/test_clean_output.html (Test Font Awesome)"
    echo "   - http://localhost/aplikasi/test_index_clean.php (Test index.php output)"
    echo "   - http://localhost/aplikasi/index.php (Main page)"
    echo "3. Check developer console (F12) for any JavaScript errors"
    echo "4. Verify Font Awesome icons are displaying correctly"
    echo ""
else
    echo -e "${RED}❌ SOME TESTS FAILED${NC}"
    echo ""
    echo "Please fix the failed tests above before proceeding."
    echo "Check the error messages for details."
    echo ""
fi

echo "========================================="
echo "📝 For detailed documentation, see:"
echo "   - FIX_OUTPUT_DAN_ICON_FINAL.md"
echo "========================================="
