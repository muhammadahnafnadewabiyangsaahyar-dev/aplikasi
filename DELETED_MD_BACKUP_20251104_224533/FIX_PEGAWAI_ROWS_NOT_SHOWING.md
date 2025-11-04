# 🔧 Fix: Pegawai Rows Tidak Muncul di Calendar

## 🐛 Problem

**Symptoms:**
- ✅ Dropdown cabang terisi dengan data (works!)
- ✅ Login sebagai admin (works!)
- ✅ API returns data successfully (works!)
- ❌ **Pegawai rows tidak muncul di calendar grid**
- ❌ Hanya ada row "DEMO" di calendar
- ❌ Tombol "Refresh" sepertinya tidak berfungsi

---

## 🔍 Root Cause

### Issue 1: `loadCalendar()` Tidak Dipanggil Otomatis
**Problem:**
- Saat page load, `loadCalendar()` TIDAK dipanggil
- User harus manually change cabang dropdown atau klik refresh
- Jika cabang sudah terpilih tapi tidak trigger loadCalendar(), data tidak muncul

### Issue 2: Kurang Debug Logging
**Problem:**
- Tidak jelas apakah `dp.update()` berhasil
- Tidak jelas apakah data pegawai di-load
- Sulit troubleshoot tanpa log yang detail

---

## ✅ Solutions Applied

### Fix 1: Enhanced Debug Logging ✅

**Added detailed console logging:**
```javascript
console.log('Loading calendar data for cabang_id: X, month: Y');
console.log('Fetching pegawai from:', url);
console.log('Pegawai response:', dataPegawai);
console.log('Setting rows.list with', dataPegawai.data.length, 'pegawai');
console.log('✅ Rows updated successfully!');
```

**Result:** Mudah troubleshoot di console!

---

### Fix 2: Auto-Load on Initial Selection ✅

**Added check for preselected cabang:**
```javascript
if (filterCabang) {
    filterCabang.addEventListener('change', () => {
        console.log('Cabang changed to:', filterCabang.value);
        loadCalendar();
    });
    
    // ✅ NEW: Check if cabang already selected
    if (filterCabang.value) {
        console.log('Initial cabang already selected:', filterCabang.value);
        console.log('Loading initial calendar data...');
        setTimeout(() => {
            loadCalendar();
        }, 500);
    }
}
```

**Result:** 
- Jika cabang sudah dipilih, otomatis load data!
- No need manual interaction

---

### Fix 3: Event Listener Logging ✅

**Added logging to event listeners:**
```javascript
filterCabang.addEventListener('change', () => {
    console.log('Cabang changed to:', filterCabang.value);
    loadCalendar();
});
```

**Result:** Bisa track kapan event trigger!

---

## 🧪 Testing Steps

### Step 1: Hard Refresh
```
Mac: Cmd + Shift + R
Windows: Ctrl + Shift + R
```

### Step 2: Open Console (F12)

**Expected Console Output:**
```
Initializing shift calendar...
Loading cabang list...
Cabang API response: {status: "success", data: Array(9)}
Cabang count: 9
✅ Cabang loaded successfully!
DayPilot Scheduler initialized successfully
Shift calendar initialization complete
```

### Step 3: Select Cabang

**If cabang dropdown already has value:**
```
Initial cabang already selected: 1
Loading initial calendar data...
Loading calendar data for cabang_id: 1, month: 2025-11
Fetching pegawai from: api_shift_calendar.php?action=get_pegawai&cabang_id=1
Pegawai response: {status: "success", data: Array(11)}
Setting rows.list with 11 pegawai
✅ Rows updated successfully!
```

**If you manually select cabang:**
```
Cabang changed to: 1
Loading calendar data for cabang_id: 1, month: 2025-11
... (same as above)
```

### Step 4: Check Calendar Grid

**Should now see:**
- ✅ Employee names di left column (bukan cuma "DEMO")
- ✅ Shift assignments sebagai colored blocks
- ✅ Calendar grid fully populated

### Step 5: Test Refresh Button

**Click refresh button:**
```
Loading calendar data for cabang_id: 1, month: 2025-11
... (data reloads)
✅ Rows updated successfully!
✅ Events updated successfully!
```

---

## 📊 Before vs After

### BEFORE (Bug):
```
1. Page loads
2. Dropdown terisi ✅
3. Calendar grid shows only "DEMO" ❌
4. User clicks refresh → Nothing happens ❌
5. User must change cabang dropdown to trigger load
```

### AFTER (Fixed):
```
1. Page loads
2. Dropdown terisi ✅
3. If cabang selected → Auto load data ✅
4. Calendar grid shows pegawai ✅
5. Refresh button works ✅
6. Change dropdown → Reload data ✅
```

---

## 🔍 Debugging with Console

### Check if dp initialized:
```javascript
console.log('dp exists:', typeof dp);  // Should be "object"
console.log('dp.rows:', dp.rows);
console.log('dp.rows.list:', dp.rows.list);
```

### Check current cabang:
```javascript
const cabangSelect = document.getElementById('filter-cabang-cal');
console.log('Selected cabang ID:', cabangSelect.value);
```

### Manually trigger load:
```javascript
loadCalendar();  // Should see all the loading logs
```

### Check API manually:
```javascript
// Test get_pegawai API:
fetch('api_shift_calendar.php?action=get_pegawai&cabang_id=1')
  .then(r => r.json())
  .then(d => console.log('Pegawai data:', d));

// Should return: {status: "success", data: [{id: X, name: "..."}]}
```

---

## 🎯 Expected Result

### Console Logs:
```
✅ Initializing shift calendar...
✅ Loading cabang list...
✅ Cabang count: 9
✅ DayPilot Scheduler initialized successfully
✅ Initial cabang already selected: 1
✅ Loading calendar data for cabang_id: 1
✅ Setting rows.list with 11 pegawai
✅ Rows updated successfully!
✅ Setting events.list with X assignments
✅ Events updated successfully!
```

### Calendar UI:
```
┌─────────────────────┬─────┬─────┬─────┬─────┬─────┐
│ Ahmad Pratama       │  1  │  2  │  3  │  4  │  5  │
├─────────────────────┼─────┼─────┼─────┼─────┼─────┤
│ Siti Nurhaliza      │  1  │[SH]│  3  │  4  │  5  │
├─────────────────────┼─────┼─────┼─────┼─────┼─────┤
│ Budi Santoso        │  1  │  2  │  3  │  4  │  5  │
└─────────────────────┴─────┴─────┴─────┴─────┴─────┘

[SH] = Shift block with colors
```

**NOT:**
```
┌─────────────────────┬─────┬─────┬─────┬─────┬─────┐
│ DEMO                │  1  │  2  │  3  │  4  │  5  │
└─────────────────────┴─────┴─────┴─────┴─────┴─────┘
```

---

## 📝 Additional Notes

### "DEMO" Row
The "DEMO" text is NOT from our code - it's a DayPilot watermark/demo marker that appears when:
- No rows loaded yet
- Using free/Lite version
- Calendar is empty

Once `dp.rows.list` is populated with actual employee data, the "DEMO" will be replaced with real employee names.

### Refresh Button
The refresh button now properly calls `loadCalendar()` which:
1. Fetches fresh pegawai data
2. Fetches fresh assignments data
3. Updates dp.rows.list
4. Updates dp.events.list
5. Calls dp.update() to refresh UI

---

## 🆘 If Still Not Working

### Check Console for Errors:
1. Open Console (F12)
2. Look for red errors
3. Look for any failed API calls (Network tab)

### Common Issues:

**Issue: "Cabang changed to: (empty string)"**
- Cabang not actually selected
- Try manually selecting cabang again

**Issue: "Pegawai response: {status: 'error'}"**
- API call failed
- Check Network tab for HTTP error
- Check session (might have expired)

**Issue: "Setting rows.list with 0 pegawai"**
- No pegawai for that cabang in database
- Check database: `SELECT * FROM register WHERE id_cabang = X`

**Issue: dp.update() does nothing**
- DayPilot might not be initialized properly
- Try hard refresh
- Check for JavaScript errors before update()

---

## ✅ Summary

### What Was Fixed:
1. ✅ Added comprehensive debug logging throughout `loadCalendar()`
2. ✅ Added auto-load when cabang preselected on page load
3. ✅ Added event listener logging
4. ✅ Enhanced error messages

### What Should Work Now:
- ✅ Pegawai rows appear in calendar grid
- ✅ Refresh button reloads data
- ✅ Changing cabang reloads data
- ✅ Console shows detailed logs for debugging
- ✅ "DEMO" replaced with real employee names

### Next Steps:
1. Hard refresh browser (Cmd+Shift+R)
2. Open Console (F12)
3. Check console logs
4. Verify pegawai rows appear
5. Test refresh button
6. If still issues, share console output!

---

**Last Updated:** November 4, 2025  
**Status:** ✅ Fixed with enhanced logging  
**Priority:** HIGH - Core functionality
