# 🎯 Quick Reference: Shift Status & Lock Feature

## Status Types

| Status | Display | Color | Can Edit? | Can Delete? |
|--------|---------|-------|-----------|-------------|
| **pending** | ⏱ Pending | 🟠 Orange | ✅ Yes | ✅ Yes |
| **approved** | ✓ Approved | 🟢 Green | ❌ No (Locked) | ❌ No (Locked) |
| **declined** | ✗ Declined | 🔴 Red | ✅ Yes | ✅ Yes |

## How It Looks

### 📅 Month View (kalender.php)
```
┌────────────────────┐
│  5                 │
│  ✓ John: Pagi      │  ← Green background (approved)
│  ⏱ Sarah: Siang    │  ← Orange border (pending)
└────────────────────┘
```

### 📆 Day View (kalender.php)
```
┌──────────────────────────────────┐
│ [✓ Approved]                     │ ← Badge in top right
│ John Doe                         │
│ Shift Pagi                       │
│ ⏰ 08:00 - 16:00                 │
│ 🔒 Locked (cannot edit/delete)   │
└──────────────────────────────────┘
```

### 📊 Table View (shift_management.php)
```
Approved shift row:
┌─────────────────────────────────────────────┐
│ 05 Nov 2024 | John | Outlet A | ✓ Approved | 🔒 Locked │ ← Gray background
└─────────────────────────────────────────────┘

Pending shift row:
┌─────────────────────────────────────────────┐
│ 05 Nov 2024 | Sarah | Outlet B | ⏱ Pending | [Hapus] │ ← White background
└─────────────────────────────────────────────┘
```

## API Responses

### ✅ Success: Create shift
```json
{
  "status": "success",
  "message": "Shift berhasil di-assign",
  "data": {
    "id": 123,
    "nama_cabang": "Outlet A",
    "nama_shift": "pagi",
    "jam_masuk": "08:00:00",
    "jam_keluar": "16:00:00"
  }
}
```

### ❌ Error: Try to update approved shift
```json
{
  "status": "error",
  "message": "Shift yang sudah approved tidak dapat diubah"
}
```

### ❌ Error: Try to delete approved shift
```json
{
  "status": "error",
  "message": "Shift yang sudah approved tidak dapat dihapus"
}
```

## Database Queries

### Check shift status
```sql
SELECT id, user_id, tanggal_shift, status_konfirmasi 
FROM shift_assignments 
WHERE id = ?;
```

### Manually approve a shift (for testing)
```sql
UPDATE shift_assignments 
SET status_konfirmasi = 'approved', 
    waktu_konfirmasi = NOW() 
WHERE id = ?;
```

### Count by status
```sql
SELECT 
    status_konfirmasi, 
    COUNT(*) as total 
FROM shift_assignments 
WHERE MONTH(tanggal_shift) = MONTH(CURRENT_DATE)
GROUP BY status_konfirmasi;
```

## Testing Checklist

### Visual Testing
- [ ] Month view: See badges (✓, ✗, ⏱)
- [ ] Day view: See color coding (green/red/orange)
- [ ] Week view: See status in summary boxes
- [ ] Table: See status badges and locked buttons

### Functional Testing
- [ ] Create new shift → Status = pending
- [ ] Approve shift → Status = approved
- [ ] Try to delete approved shift → Should fail
- [ ] Try to delete pending shift → Should work
- [ ] Try to update approved shift → Should fail
- [ ] Try to update pending shift → Should work

### API Testing
```bash
# Try to delete approved shift
curl -X POST http://localhost/aplikasi/api_shift_calendar.php \
  -H "Content-Type: application/json" \
  -d '{"action":"delete","id":123}'

# Expected response:
# {"status":"error","message":"Shift yang sudah approved tidak dapat dihapus"}
```

## Common Use Cases

### 1. Admin assigns shift
```
1. Admin goes to kalender.php or shift_management.php
2. Selects employee, cabang/shift, date
3. Clicks assign
4. Status automatically set to "pending" ⏱
```

### 2. Admin approves shift
```
1. Go to database or create approval UI
2. UPDATE shift_assignments SET status_konfirmasi = 'approved'
3. Shift becomes locked 🔒
4. Badge changes to ✓ Approved (green)
5. Delete button becomes disabled
```

### 3. Admin tries to delete approved shift
```
1. Admin clicks delete on approved shift
2. Button is disabled (grayed out)
3. Tooltip shows: "Shift yang sudah approved tidak dapat dihapus"
4. No action taken
```

### 4. Admin deletes pending shift
```
1. Admin clicks delete on pending shift
2. Confirmation dialog appears
3. Click OK → Shift deleted
4. Calendar refreshes
```

## Color Codes

```css
/* Approved (Green) */
background: #4CAF50;
border-color: #4CAF50;

/* Declined (Red) */
background: #f44336;
border-color: #f44336;

/* Pending (Orange) */
background: #ff9800;
border-color: #ff9800;
```

## Files Modified

1. ✅ `api_shift_calendar.php` - Backend validation
2. ✅ `script_kalender_database.js` - Frontend display
3. ✅ `style.css` - Status styling
4. ✅ `shift_management.php` - Table view lock

## Quick Commands

```bash
# View this quick reference
cat SHIFT_STATUS_QUICK_REFERENCE.md

# Run test script
./test_shift_status.sh

# Check logs
tail -f /Applications/XAMPP/xamppfiles/logs/error_log

# Restart Apache
sudo /Applications/XAMPP/xamppfiles/bin/apachectl restart
```

## Support

If something doesn't work:
1. Check browser console for JS errors
2. Check PHP error logs
3. Verify database has status_konfirmasi column
4. Clear browser cache
5. Restart Apache/MySQL

---
**Quick Access**: Bookmark this file for instant reference! 📚
