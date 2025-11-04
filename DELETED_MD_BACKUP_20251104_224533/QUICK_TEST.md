# 🚀 Quick Test Guide - Shift Calendar v2.0

## 🎯 5-Minute Test

### 1. Open Calendar
```
URL: http://localhost/aplikasi/shift_calendar.php
Login: admin account
```

### 2. Select Cabang
```
1. Choose any cabang from dropdown
2. Employees should appear
```

### 3. Create Shift
```
1. Click & drag on employee row at any date
2. Confirm
3. Block appears with shift name + hours
```

### 4. Test Drag & Drop
```
- Drag to another employee ✓
- Drag to another date ✓
```

### 5. Delete Shift
```
Click red X button → Confirm
```

---

## ✅ Success = All 5 Steps Work!

## 🐛 If Issues:
1. Check console (F12) for errors
2. Verify: `SELECT * FROM cabang;` returns data
3. Check: `js/daypilot/daypilot-all.min.js` exists

---

## 📚 Full Docs:
- `SHIFT_CALENDAR_GUIDE.md` - Complete guide
- `DEPLOYMENT_CHECKLIST.md` - Testing checklist
- `CHANGE_SUMMARY.md` - What changed
