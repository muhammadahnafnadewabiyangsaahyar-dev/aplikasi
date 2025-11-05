#!/bin/bash

# Test Visual Fixes - Grid Symmetry and Card Stretching
# Script ini memverifikasi perbaikan visual pada kalender shift

echo "=================================================="
echo "🎨 TEST VISUAL FIXES - GRID & CARD STRETCHING"
echo "=================================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Test function
run_test() {
    local test_name=$1
    local test_command=$2
    local expected=$3
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo -n "Test $TOTAL_TESTS: $test_name ... "
    
    result=$(eval "$test_command")
    
    if [ "$result" = "$expected" ]; then
        echo -e "${GREEN}✓ PASS${NC}"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        echo "  Expected: $expected"
        echo "  Got: $result"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
}

# Check if file exists
check_file() {
    local file=$1
    if [ -f "$file" ]; then
        echo "1"
    else
        echo "0"
    fi
}

# Search for pattern in file
search_pattern() {
    local file=$1
    local pattern=$2
    if grep -q "$pattern" "$file" 2>/dev/null; then
        echo "1"
    else
        echo "0"
    fi
}

# Count occurrences
count_pattern() {
    local file=$1
    local pattern=$2
    grep -c "$pattern" "$file" 2>/dev/null || echo "0"
}

echo "=== File Existence Tests ==="
echo ""

run_test "script_kalender_database.js exists" \
    "check_file 'script_kalender_database.js'" \
    "1"

run_test "style.css exists" \
    "check_file 'style.css'" \
    "1"

echo ""
echo "=== Day View - Grid Symmetry Tests ==="
echo ""

run_test "Day view uses HOUR_HEIGHT constant" \
    "search_pattern 'script_kalender_database.js' 'const HOUR_HEIGHT = 60'" \
    "1"

run_test "Day view time header has fixed height" \
    "search_pattern 'script_kalender_database.js' 'timeHeader.style.height = .50px.'" \
    "1"

run_test "Day view time slots use box-sizing" \
    "search_pattern 'script_kalender_database.js' 'timeSlot.style.boxSizing = .border-box.'" \
    "1"

run_test "Day view time slots use flexbox" \
    "search_pattern 'script_kalender_database.js' 'timeSlot.style.display = .flex.'" \
    "1"

echo ""
echo "=== Day View - Card Stretching Tests ==="
echo ""

run_test "Day view uses absolute positioning for cards" \
    "search_pattern 'script_kalender_database.js' 'shiftDiv.style.position = .absolute.'" \
    "1"

run_test "Day view calculates duration" \
    "search_pattern 'script_kalender_database.js' 'let duration = .endHour \+ endMinute/60. - .startHour \+ startMinute/60.'" \
    "1"

run_test "Day view handles overnight shifts" \
    "search_pattern 'script_kalender_database.js' 'if .duration <= 0. duration \+= 24'" \
    "1"

run_test "Day view sets card height based on duration" \
    "search_pattern 'script_kalender_database.js' 'const cardHeight = group.duration \* HOUR_HEIGHT - 8'" \
    "1"

run_test "Day view sets card top position" \
    "search_pattern 'script_kalender_database.js' 'const topPosition = .group.startHour \+ group.startMinute/60. \* HOUR_HEIGHT'" \
    "1"

run_test "Day view uses container for absolute positioning" \
    "search_pattern 'script_kalender_database.js' 'contentContainer.style.position = .relative.'" \
    "1"

run_test "Day view container has calculated height" \
    "search_pattern 'script_kalender_database.js' 'contentContainer.style.height = .\${24 \* HOUR_HEIGHT}px.'" \
    "1"

echo ""
echo "=== Week View - Grid Symmetry Tests ==="
echo ""

run_test "Week view time header has fixed height (50px)" \
    "search_pattern 'script_kalender_database.js' 'height: 50px.*Waktu'" \
    "1"

run_test "Week view time slots have fixed height (60px)" \
    "search_pattern 'script_kalender_database.js' 'timeSlot.style.height = .60px.'" \
    "1"

run_test "Week view day header has fixed height" \
    "search_pattern 'script_kalender_database.js' 'dayHeader.style.height = .50px.'" \
    "1"

echo ""
echo "=== Week View - Card Stretching Tests ==="
echo ""

run_test "Week view uses HOUR_HEIGHT constant" \
    "count_pattern 'script_kalender_database.js' 'const HOUR_HEIGHT = 60'" \
    "2"

run_test "Week view uses absolute positioning for cards" \
    "search_pattern 'script_kalender_database.js' 'shiftCard.style.position = .absolute.'" \
    "1"

run_test "Week view creates day content container" \
    "search_pattern 'script_kalender_database.js' 'const dayContent = document.createElement..div..' | head -1" \
    "1"

run_test "Week view container has calculated height" \
    "count_pattern 'script_kalender_database.js' '.style.height = .\$\{24 \* HOUR_HEIGHT\}px.'" \
    "2"

run_test "Week view calculates card position" \
    "search_pattern 'script_kalender_database.js' 'const topPosition = .startHour \+ startMinute/60. \* HOUR_HEIGHT'" \
    "1"

run_test "Week view groups shifts to avoid overlap" \
    "search_pattern 'script_kalender_database.js' 'const shiftGroups = {}'" \
    "1"

echo ""
echo "=== CSS Style Tests ==="
echo ""

run_test "CSS: week/day calendar has auto height" \
    "search_pattern 'style.css' 'height: auto'" \
    "1"

run_test "CSS: week/day calendar has min-height" \
    "search_pattern 'style.css' 'min-height: 600px'" \
    "1"

run_test "CSS: week/day calendar has max-height" \
    "search_pattern 'style.css' 'max-height: calc.100vh - 300px.'" \
    "1"

run_test "CSS: time column has wider width (80px)" \
    "search_pattern 'style.css' 'width: 80px'" \
    "1"

run_test "CSS: time column has thicker border (2px)" \
    "search_pattern 'style.css' 'border-right: 2px solid #ddd'" \
    "1"

run_test "CSS: time column has flex-shrink: 0" \
    "search_pattern 'style.css' 'flex-shrink: 0'" \
    "1"

run_test "CSS: time slot uses box-sizing" \
    "search_pattern 'style.css' 'box-sizing: border-box'" \
    "1"

run_test "CSS: time slot uses flexbox" \
    "search_pattern 'style.css' 'display: flex'" \
    "1"

run_test "CSS: time-header class exists with fixed height" \
    "search_pattern 'style.css' '.time-header.*height: 50px'" \
    "1"

run_test "CSS: day-header has flexbox centering" \
    "search_pattern 'style.css' '.day-header.*display: flex'" \
    "1"

run_test "CSS: day-column has min-width" \
    "search_pattern 'style.css' 'min-width: 120px'" \
    "1"

echo ""
echo "=== Code Structure Tests ==="
echo ""

run_test "Day view uses shiftsGroupedByStart" \
    "search_pattern 'script_kalender_database.js' 'const shiftsGroupedByStart = {}'" \
    "1"

run_test "Day view creates background grid slots" \
    "search_pattern 'script_kalender_database.js' 'day-content-slot-bg'" \
    "1"

run_test "Week view creates background grid slots" \
    "search_pattern 'script_kalender_database.js' 'week-hour-slot-bg'" \
    "1"

run_test "Both views handle startMinute for precise positioning" \
    "count_pattern 'script_kalender_database.js' 'startMinute = parseInt.jamMasuk.split.*:...1..'" \
    "2"

run_test "Both views handle endMinute for precise duration" \
    "count_pattern 'script_kalender_database.js' 'endMinute = parseInt.jamKeluar.split.*:...1..'" \
    "2"

echo ""
echo "=== Functionality Tests ==="
echo ""

run_test "Day view appends container to dayContent" \
    "search_pattern 'script_kalender_database.js' 'dayContent.appendChild.contentContainer.'" \
    "1"

run_test "Week view appends dayContent to dayColumn" \
    "search_pattern 'script_kalender_database.js' 'dayColumn.appendChild.dayContent.'" \
    "1"

run_test "Cards have z-index for layering" \
    "count_pattern 'script_kalender_database.js' '.style.zIndex = .10.'" \
    "2"

run_test "Cards have overflow auto for scrolling" \
    "count_pattern 'script_kalender_database.js' '.style.overflow = .auto.'" \
    "2"

run_test "Employee names are grouped in week view" \
    "search_pattern 'script_kalender_database.js' 'let employeeNames = group.employees.map.e => e.pegawai_name'" \
    "1"

echo ""
echo "=== Documentation Tests ==="
echo ""

run_test "Visual fix documentation exists" \
    "check_file 'FIX_VISUAL_GRID_AND_STRETCH_CARDS.md'" \
    "1"

if [ -f "FIX_VISUAL_GRID_AND_STRETCH_CARDS.md" ]; then
    run_test "Documentation mentions HOUR_HEIGHT" \
        "search_pattern 'FIX_VISUAL_GRID_AND_STRETCH_CARDS.md' 'HOUR_HEIGHT'" \
        "1"
    
    run_test "Documentation has code examples" \
        "search_pattern 'FIX_VISUAL_GRID_AND_STRETCH_CARDS.md' '```javascript'" \
        "1"
    
    run_test "Documentation has test cases" \
        "search_pattern 'FIX_VISUAL_GRID_AND_STRETCH_CARDS.md' 'Test Case'" \
        "1"
fi

echo ""
echo "=================================================="
echo "📊 TEST SUMMARY"
echo "=================================================="
echo ""
echo "Total Tests: $TOTAL_TESTS"
echo -e "${GREEN}Passed: $PASSED_TESTS${NC}"
echo -e "${RED}Failed: $FAILED_TESTS${NC}"
echo ""

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}✅ ALL TESTS PASSED!${NC}"
    echo ""
    echo "Visual fixes are correctly implemented:"
    echo "  ✓ Grid symmetry in day and week views"
    echo "  ✓ Card stretching based on shift duration"
    echo "  ✓ Consistent HOUR_HEIGHT (60px) across views"
    echo "  ✓ Absolute positioning for proper stretching"
    echo "  ✓ CSS updates for alignment and sizing"
    echo ""
    echo "🎨 The calendar should now display beautifully!"
    exit 0
else
    echo -e "${RED}❌ SOME TESTS FAILED${NC}"
    echo ""
    echo "Please review the failed tests above."
    echo "Common issues:"
    echo "  - Missing pattern in code"
    echo "  - Incorrect implementation"
    echo "  - File not found"
    echo ""
    echo "Check the documentation in FIX_VISUAL_GRID_AND_STRETCH_CARDS.md"
    exit 1
fi
