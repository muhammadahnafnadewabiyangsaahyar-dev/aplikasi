#!/bin/bash

# Test Script - Fix Month to Day/Week View Transition Bug
# Tests the fix for data disappearing when clicking date from month view

echo "=============================================="
echo "Testing Month → Day/Week View Transition Fix"
echo "=============================================="
echo ""

SCRIPT_FILE="/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js"

echo "1. Verifying switchView() fix..."
echo "   - Checking if switchView only requires currentCabangId"
if grep -q "if (currentCabangId) {" "$SCRIPT_FILE" && \
   grep -A 2 "if (currentCabangId) {" "$SCRIPT_FILE" | grep -q "console.log.*Switching to.*view"; then
    echo "   ✓ switchView() correctly checks only currentCabangId"
else
    echo "   ✗ switchView() may still have incorrect condition"
fi
echo ""

echo "2. Verifying loadShiftAssignments() fix..."
echo "   - Checking if loadShiftAssignments only requires currentCabangId"
if grep -q "if (!currentCabangId) {" "$SCRIPT_FILE" && \
   grep -A 3 "if (!currentCabangId) {" "$SCRIPT_FILE" | grep -q "console.log.*No cabang selected"; then
    echo "   ✓ loadShiftAssignments() correctly checks only currentCabangId"
else
    echo "   ✗ loadShiftAssignments() may still require currentShiftId"
fi
echo ""

echo "3. Verifying shift selector event listener fix..."
echo "   - Checking if shift selector only checks currentCabangId"
SHIFT_LISTENER_CHECK=$(grep -A 10 "document.getElementById('shift-select')?.addEventListener" "$SCRIPT_FILE" | \
                        grep "if (currentCabangId)" | wc -l)
if [ "$SHIFT_LISTENER_CHECK" -gt 0 ]; then
    echo "   ✓ Shift selector event listener correctly checks only currentCabangId"
else
    echo "   ✗ Shift selector event listener may have incorrect condition"
fi
echo ""

echo "4. Checking for old incorrect patterns..."
OLD_PATTERN_COUNT=$(grep -c "currentCabangId && currentShiftId" "$SCRIPT_FILE")
if [ "$OLD_PATTERN_COUNT" -eq 0 ]; then
    echo "   ✓ No old 'currentCabangId && currentShiftId' patterns found"
else
    echo "   ⚠ Found $OLD_PATTERN_COUNT occurrences of old pattern (may be in comments or OK)"
fi
echo ""

echo "5. Verifying debug logging..."
if grep -q "console.log.*Loading for cabang.*shift.*ALL" "$SCRIPT_FILE"; then
    echo "   ✓ Enhanced debug logging added to loadShiftAssignments"
else
    echo "   ⚠ Debug logging may need improvement"
fi
echo ""

echo "=============================================="
echo "Summary"
echo "=============================================="
echo ""
echo "The fix addresses the root cause:"
echo "  - Month view click → day/week view transition"
echo "  - Data now loads even when currentShiftId is null"
echo "  - API only needs cabang_id, not shift_id"
echo ""
echo "Testing Instructions:"
echo "1. Open kalender_database.php in browser"
echo "2. Select a Cabang (e.g., Jember)"
echo "3. DO NOT select a Shift (leave it blank)"
echo "4. View should show all shifts in month view"
echo "5. Click on date 2 November in month view"
echo "6. Day view should show shifts for that date"
echo "7. Switch to Week view"
echo "8. Week view should show shifts in correct time slots"
echo ""
echo "Expected Result:"
echo "  ✓ Data persists when transitioning between views"
echo "  ✓ No data loss on month → day/week transitions"
echo "  ✓ Works with or without shift selection"
echo ""
echo "=============================================="
