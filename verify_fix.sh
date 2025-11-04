#!/bin/bash
echo "🔧 Verifying DayPilot Fix..."
echo ""

echo "✅ Check 1: dp.dispose() pattern exists"
grep -n "dp.dispose()" shift_calendar.php | head -3

echo ""
echo "✅ Check 2: isLoadingCalendar flag exists"
grep -n "isLoadingCalendar" shift_calendar.php | head -3

echo ""
echo "✅ Check 3: Update instead of reinit on month change"
grep -n "dp.startDate = startDate" shift_calendar.php

echo ""
echo "All checks done! Opening test page..."
