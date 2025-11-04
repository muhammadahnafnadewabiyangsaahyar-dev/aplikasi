#!/bin/bash

echo "🧪 Testing Shift Calendar Fix"
echo "=============================="
echo ""

# Test 1: DayPilot Library Access
echo "📦 Test 1: DayPilot Library Access"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/aplikasi/daypilot-all.min.js)
if [ "$STATUS" = "200" ]; then
    echo "   ✅ daypilot-all.min.js accessible (HTTP $STATUS)"
else
    echo "   ❌ daypilot-all.min.js NOT accessible (HTTP $STATUS)"
fi
echo ""

# Test 2: Shift Calendar Page
echo "📄 Test 2: Shift Calendar Page"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/aplikasi/shift_calendar.php)
if [ "$STATUS" = "200" ]; then
    echo "   ✅ shift_calendar.php accessible (HTTP $STATUS)"
else
    echo "   ❌ shift_calendar.php NOT accessible (HTTP $STATUS)"
fi
echo ""

# Test 3: API Endpoints
echo "🔌 Test 3: API Endpoints"
RESPONSE=$(curl -s "http://localhost/aplikasi/api_shift_calendar.php?action=get_cabang")
if echo "$RESPONSE" | grep -q "success"; then
    COUNT=$(echo "$RESPONSE" | grep -o "id" | wc -l)
    echo "   ✅ API get_cabang working ($COUNT cabang found)"
else
    echo "   ❌ API get_cabang failed"
fi
echo ""

# Test 4: Check if dummy data exists
echo "📊 Test 4: Dummy Data Status"
RESPONSE=$(curl -s "http://localhost/aplikasi/api_shift_calendar.php?action=get_cabang")
COUNT=$(echo "$RESPONSE" | grep -o "\"id\"" | wc -l)
if [ "$COUNT" -gt 0 ]; then
    echo "   ✅ Dummy data found ($COUNT cabang)"
else
    echo "   ⚠️  No dummy data - run: ./install_dummy_data.sh"
fi
echo ""

echo "=============================="
echo "✅ READY TO TEST!"
echo ""
echo "Next steps:"
echo "1. Open: http://localhost/aplikasi/shift_calendar.php"
echo "2. Login as: superadmin / password"
echo "3. Select cabang from dropdown"
echo "4. Calendar should now display!"
echo ""
