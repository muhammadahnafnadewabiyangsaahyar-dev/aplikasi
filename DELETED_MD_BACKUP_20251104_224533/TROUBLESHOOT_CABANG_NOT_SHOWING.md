# 🔍 Troubleshooting: Data Cabang Tidak Muncul

## 🐛 Problem

Data cabang tidak muncul di shift calendar, padahal:
- ✅ Data ada di database
- ✅ Ada shift assignments yang sudah confirmed
- ❌ Dropdown cabang kosong di frontend

---

## 🔍 Root Causes (Kemungkinan)

### 1. **Session/Login Issue** (PALING UMUM!)
**Masalah:** User belum login atau session expired

**Cek:**
```
- Buka: http://localhost/aplikasi/test_shift_data.html
- Lihat "Session Check"
- Jika ❌ "NOT LOGGED IN" → Harus login dulu!
```

**Solusi:**
1. Login sebagai admin di: `http://localhost/aplikasi/login.php`
2. Username: `superadmin`
3. Password: `password`
4. Setelah login, buka kembali shift calendar

---

### 2. **API Authorization Error**
**Masalah:** API memerlukan admin session, tapi session tidak valid

**Error di Console:**
```javascript
{
  "status": "error",
  "message": "Unauthorized"
}
```

**Solusi:**
- Pastikan sudah login sebagai admin (role = 'admin')
- Check console (F12) untuk error API
- Test API manual: `http://localhost/aplikasi/api_shift_calendar.php?action=get_cabang`

---

### 3. **JavaScript Error**
**Masalah:** Ada error di JavaScript yang mencegah loadCabang() berjalan

**Cek Console (F12):**
```
- Cari error merah
- Cari "Error loading cabang"
- Cari "Element not found"
```

**Solusi:**
- Hard refresh: Cmd+Shift+R (Mac) atau Ctrl+Shift+R (Windows)
- Clear browser cache
- Check file terbaru ter-load

---

### 4. **Element Not Found**
**Masalah:** Element HTML `filter-cabang-cal` atau `legend-colors` tidak ditemukan

**Cek Console:**
```javascript
Element filter-cabang-cal not found!
Element legend-colors not found!
```

**Solusi:**
- Pastikan Anda di halaman yang benar (shift_calendar.php)
- Check view mode (Calendar View vs Table View)
- Element mungkin hidden karena view mode

---

## ✅ Diagnostic Steps

### Step 1: Test Session & APIs
```
1. Buka: http://localhost/aplikasi/test_shift_data.html
2. Lihat hasil semua tests:
   - Session Check → Harus ✅ LOGGED IN AS ADMIN
   - Get Cabang → Harus ✅ SUCCESS dengan data
   - Get Pegawai → Harus ✅ SUCCESS dengan data
   - Get Assignments → Harus ✅ SUCCESS dengan data
```

### Step 2: Check Database
```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root aplikasi -e "
SELECT COUNT(*) as total FROM cabang;
"
```
**Expected:** total > 0

### Step 3: Check Console
```
1. Buka shift_calendar.php
2. Open Console (F12)
3. Look for:
   - "Initializing shift calendar..."
   - "Loading cabang list..."
   - "Cabang API response:" dengan data
   - "Cabang count:" dengan angka
```

### Step 4: Manual API Test
```
1. Login terlebih dahulu
2. Buka di tab baru: 
   http://localhost/aplikasi/api_shift_calendar.php?action=get_cabang
3. Should see JSON with cabang data
```

---

## 🔧 Solutions

### Solution 1: Login Issue (Most Common!)
```
✅ STEPS:
1. Go to: http://localhost/aplikasi/login.php
2. Login as: superadmin / password
3. After login, go to: http://localhost/aplikasi/shift_calendar.php
4. Data should now appear!
```

---

### Solution 2: Hard Refresh Browser
```
✅ STEPS:
1. Clear browser cache
2. Hard refresh: Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)
3. Check Console for updated logs
```

---

### Solution 3: Check View Mode
```
✅ STEPS:
1. On shift_calendar.php page
2. Click "Calendar View" button (bukan Table View)
3. Dropdown cabang ada di Calendar View, bukan Table View
```

---

### Solution 4: Reinstall Dummy Data
```bash
# If data is missing or corrupted:
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi

# Uninstall old data
./uninstall_dummy_data.sh

# Reinstall fresh data
./install_dummy_data.sh
```

---

## 📊 Expected Behavior

### After Login as Admin:

**Console Output:**
```
Initializing shift calendar...
Loading cabang list...
Cabang API response: {status: "success", data: Array(8)}
Cabang count: 8
✅ Cabang loaded successfully!
DayPilot Scheduler initialized successfully
Shift calendar initialization complete
```

**UI:**
```
- Dropdown "Pilih Cabang" terisi dengan:
  ✅ Citraland Gowa - pagi (07:00 - 15:00)
  ✅ Adhyaksa - pagi (07:00 - 15:00)
  ✅ BTP - pagi (08:00 - 15:00)
  ... (dan seterusnya)

- Legend muncul dengan color boxes:
  ✅ Citraland Gowa (pagi) [warna hijau]
  ✅ Adhyaksa (pagi) [warna biru]
  ... (dan seterusnya)
```

---

## 🧪 Quick Tests

### Test 1: Are you logged in?
```javascript
// In Console (F12):
fetch('api_shift_calendar.php?action=get_cabang')
  .then(r => r.json())
  .then(d => console.log(d));

// ✅ Should show: {status: "success", data: [...]}
// ❌ If shows: {status: "error", message: "Unauthorized"}
//    → You're NOT logged in!
```

### Test 2: Is loadCabang() called?
```javascript
// In Console (F12):
// Should see in logs:
"Loading cabang list..."
"Cabang API response:"
"Cabang count: X"
"✅ Cabang loaded successfully!"
```

### Test 3: Does element exist?
```javascript
// In Console (F12):
console.log(document.getElementById('filter-cabang-cal'));
// ✅ Should show: <select id="filter-cabang-cal">...</select>
// ❌ If shows: null → Element not found!
```

---

## 📝 Files Created for Debugging

### 1. `test_shift_data.html`
**Purpose:** Test all API endpoints
**URL:** http://localhost/aplikasi/test_shift_data.html
**Usage:** Run automatic tests to check:
- Session status
- Cabang API
- Pegawai API  
- Assignments API

### 2. `debug_shift_calendar.php`
**Purpose:** Show session info and database data
**URL:** http://localhost/aplikasi/debug_shift_calendar.php
**Usage:** Check if:
- You're logged in
- Data exists in database
- API links work

---

## 🎯 Most Likely Solution

### **90% of the time, it's a LOGIN issue!**

```
🔑 SOLUTION:
1. Make sure you're logged in as ADMIN
2. Go to: http://localhost/aplikasi/login.php
3. Username: superadmin
4. Password: password
5. After login, go to shift_calendar.php
6. Data should appear!
```

If still not working after login:
- Check Console (F12) for errors
- Use test_shift_data.html to diagnose
- Check debug_shift_calendar.php for session info

---

## 🆘 Still Not Working?

**Collect this info:**

1. **Session Status:**
   - Visit: debug_shift_calendar.php
   - Screenshot the session info

2. **Console Errors:**
   - Open shift_calendar.php
   - Press F12
   - Go to Console tab
   - Screenshot any red errors

3. **API Response:**
   - Visit: test_shift_data.html
   - Screenshot all test results

4. **Browser:**
   - Which browser? (Chrome, Firefox, Safari?)
   - Tried different browser?

---

**Last Updated:** November 4, 2025  
**Status:** ✅ Diagnostic tools created  
**Next:** Check test_shift_data.html results
