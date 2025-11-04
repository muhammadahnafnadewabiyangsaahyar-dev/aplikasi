# 🔧 Fix: DayPilot Scheduler Initialization Error

## 🐛 Error Yang Terjadi

```
Error initializing DayPilot Scheduler: TypeError: H.rows.Mr is not a function
at DayPilot.Scheduler.$m
at DayPilot.Scheduler.sl
at DayPilot.Scheduler.Cf
at DayPilot.Scheduler.dn
at DayPilot.Scheduler.Ev
at DayPilot.Scheduler.init
at initCalendar
```

```
Error loading calendar: Error: You are trying to update a DayPilot.Scheduler 
object that hasn't been initialized.
```

---

## 🔍 Root Cause

### Problem 1: Invalid `rowHeaderColumns` Configuration
**Issue:**
```javascript
rowHeaderColumns: [
    {name: "Pegawai", display: "name", width: 150},  // ❌ "display" not supported in Lite
    {name: "Shift", width: 50}
]
```

DayPilot Lite (free version) tidak mendukung `rowHeaderColumns` dengan property `display`. Fitur ini hanya tersedia di DayPilot Pro.

### Problem 2: Update Called Before Init Complete
**Issue:**
- `loadCalendar()` dipanggil sebelum `dp.init()` selesai
- `dp.update()` dipanggil pada object yang belum fully initialized

---

## ✅ Solutions Applied

### Fix 1: Remove `rowHeaderColumns` Configuration
**Before:**
```javascript
dp = new DayPilot.Scheduler("dp", {
    // ...other config...
    rowHeaderColumns: [
        {name: "Pegawai", display: "name", width: 150},
        {name: "Shift", width: 50}
    ],
    onBeforeRowHeaderRender: (args) => {
        // Custom column logic
    },
    // ...
});
```

**After:**
```javascript
dp = new DayPilot.Scheduler("dp", {
    // ...other config...
    // ✅ Removed rowHeaderColumns - not supported in Lite version
    // Default row header will show the 'name' property from rows data
    // ...
});
```

**Result:**
- Menggunakan default row header (hanya menampilkan nama pegawai)
- Tidak perlu custom columns yang memerlukan Pro version
- Lebih simple dan kompatibel dengan Lite version

---

### Fix 2: Add Initialization Check in `loadCalendar()`
**Before:**
```javascript
async function loadCalendar() {
    const cabangSelect = document.getElementById('filter-cabang-cal');
    // ... langsung akses dp.rows tanpa cek
}
```

**After:**
```javascript
async function loadCalendar() {
    // Check if dp is initialized
    if (!dp) {
        console.error('DayPilot Scheduler not initialized yet');
        return;
    }
    
    const cabangSelect = document.getElementById('filter-cabang-cal');
    // ... lanjut jika dp sudah ada
}
```

**Result:**
- Mencegah akses ke `dp` sebelum initialized
- Menghindari error "object hasn't been initialized"

---

### Fix 3: Add Delay When Month Changes
**Before:**
```javascript
monthSelector.addEventListener('change', () => {
    initCalendar();
    loadCalendar();  // ❌ Called immediately, might be too fast
});
```

**After:**
```javascript
monthSelector.addEventListener('change', () => {
    console.log('Month changed, re-initializing calendar...');
    initCalendar();
    // Wait a bit for init to complete before loading data
    setTimeout(() => {
        loadCalendar();
    }, 100);
});
```

**Result:**
- Memberikan waktu untuk `dp.init()` selesai
- Menghindari race condition antara init dan update

---

## 🎯 What Changed

### Removed Features (Not Supported in Lite):
- ❌ `rowHeaderColumns` - Multiple columns in row header
- ❌ `onBeforeRowHeaderRender` - Custom column rendering
- ❌ Custom "Shift Count" column

### What Still Works:
- ✅ Basic row header dengan nama pegawai
- ✅ Calendar grid dengan days of month
- ✅ Shift assignments sebagai colored events
- ✅ Drag & drop untuk move shifts
- ✅ Click untuk create shifts
- ✅ Delete button pada events
- ✅ Color coding by cabang
- ✅ Shift time display dalam event

---

## 🧪 Testing Steps

### 1. Hard Refresh Browser
```
Mac: Cmd + Shift + R
Windows: Ctrl + Shift + R
```

### 2. Open Console (F12)
Expected output:
```
Initializing shift calendar...
DayPilot Scheduler initialized successfully
Shift calendar initialization complete
```

**Should NOT see:**
- ❌ "H.rows.Mr is not a function"
- ❌ "object hasn't been initialized"

### 3. Test Calendar View
1. Click "Calendar View" button
2. Select a cabang from dropdown
3. Should see:
   - ✅ Calendar grid with dates
   - ✅ Employee names on the left
   - ✅ Shift blocks (if any assignments exist)
   - ✅ NO errors in console

### 4. Test Interactions
- ✅ Click empty cell → Create shift prompt
- ✅ Drag shift block → Move to different date
- ✅ Click "X" on shift → Delete confirmation
- ✅ Change month → Re-initialize and reload
- ✅ Change cabang → Reload with new data

---

## 📊 Visual Changes

### Before (With Error):
```
[Calendar Header]
[❌ ERROR: rows.Mr is not a function]
[Empty/broken view]
```

### After (Fixed):
```
[Calendar Header]
┌─────────────────┬─────┬─────┬─────┬─────┬─────┐
│ Pegawai Name 1  │  1  │  2  │  3  │  4  │  5  │
├─────────────────┼─────┼─────┼─────┼─────┼─────┤
│ Pegawai Name 2  │  1  │[SH1]│  3  │  4  │  5  │
├─────────────────┼─────┼─────┼─────┼─────┼─────┤
│ Pegawai Name 3  │  1  │  2  │[SH2]│[SH3]│  5  │
└─────────────────┴─────┴─────┴─────┴─────┴─────┘

[SH1] = Shift block with time (colored by cabang)
```

**Note:** 
- Tidak ada lagi kolom "Shift Count" di row header
- Row header hanya menampilkan nama pegawai
- Ini adalah limitation dari DayPilot Lite version
- Untuk multiple columns, perlu upgrade ke Pro version

---

## 🔍 Debug Commands

### Check if dp initialized:
```javascript
console.log(typeof dp);  // Should be: "object"
console.log(dp);  // Should show DayPilot.Scheduler object
```

### Check rows and events:
```javascript
console.log(dp.rows.list);  // Should be: [] or array with employee data
console.log(dp.events.list);  // Should be: [] or array with shift data
```

### Manual test init:
```javascript
// In console, try:
dp.init();  // Should not throw error
```

---

## 🆘 If Still Having Issues

### Issue: "rows.Mr is not a function" persists
**Action:**
1. Clear browser cache completely
2. Hard refresh (Cmd+Shift+R)
3. Check file was updated (view source)
4. Verify DayPilot library is loaded

### Issue: "object hasn't been initialized"
**Check:**
1. Is `dp` variable defined? (`typeof dp`)
2. Was `dp.init()` called without error?
3. Console log in `initCalendar()` shows?

### Issue: Calendar shows but no data
**Check:**
1. Is cabang selected in dropdown?
2. API endpoints returning data? (Network tab)
3. Console shows data being loaded?

---

## 📝 Alternative: Upgrade to DayPilot Pro

If you need advanced features like:
- Multiple row header columns
- Custom column rendering
- Modal dialogs
- Advanced event rendering

Consider upgrading to DayPilot Pro:
- https://javascript.daypilot.org/

**Note:** Most core features work fine with Lite version for basic shift management!

---

## ✅ Summary

**What was fixed:**
1. ✅ Removed unsupported `rowHeaderColumns` config
2. ✅ Removed `onBeforeRowHeaderRender` handler
3. ✅ Added initialization check in `loadCalendar()`
4. ✅ Added delay when month changes (re-init timing)
5. ✅ All core calendar functionality still works

**What was removed:**
- Shift count column in row header (Pro feature)
- Custom multi-column row headers (Pro feature)

**What still works:**
- All shift management features (create, move, delete)
- Color coding by cabang
- Employee rows with shift assignments
- Full calendar interaction

---

**Last Updated:** November 4, 2025
**Status:** ✅ Fixed - Ready for testing
**Compatibility:** DayPilot Lite (Free version)
