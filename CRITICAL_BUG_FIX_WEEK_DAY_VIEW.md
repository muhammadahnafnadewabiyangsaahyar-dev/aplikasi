# 🐛 Critical Bug Fix: Week & Day View Shift Display

## Tanggal: $(date)

## 🔴 MASALAH YANG DITEMUKAN

### Gejala
1. **Month View**: Shift tampil dengan benar di tanggal 2, 3, 4, 5 November 2025
2. **Day View**: Tanggal 2 November 2025 KOSONG - tidak ada shift yang tampil
3. **Week View**: Minggu 27 Okt - 2 Nov tidak menampilkan shift di slot waktu yang seharusnya

### Root Cause
**BUG KRITIS di `script_kalender_database.js` line 262:**

```javascript
// ❌ WRONG - Menggunakan currentShiftId (ID shift)
const response = await fetch(`api_shift_calendar.php?action=get_assignments&cabang_id=${currentShiftId}&month=${month}`);

// ✅ CORRECT - Harus menggunakan currentCabangId (ID cabang)
const response = await fetch(`api_shift_calendar.php?action=get_assignments&cabang_id=${currentCabangId}&month=${month}`);
```

**Penjelasan:**
- API `get_assignments` membutuhkan **ID Cabang** (cabang_id), bukan ID Shift
- Variable `currentShiftId` berisi ID dari shift (misalnya ID dari shift "Pagi", "Siang", dll)
- Variable `currentCabangId` berisi ID dari cabang/outlet yang dipilih
- Karena menggunakan parameter yang salah, API tidak mengembalikan data shift apapun
- Month view masih tampil karena menggunakan data yang sudah di-load sebelumnya
- Week dan Day view gagal karena meload data baru dengan parameter yang salah

### Bug Kedua
**`switchView()` tidak meload ulang shift assignments:**
- Saat switch dari Month → Week atau Month → Day, data shift tidak direload
- Menyebabkan view baru menggunakan data lama atau kosong

## ✅ PERBAIKAN YANG DILAKUKAN

### 1. Fix API Parameter di `loadShiftAssignments()`

**File**: `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js`  
**Line**: ~262

```javascript
// Load data for each month
for (const month of monthsToLoad) {
    // ✅ FIXED: Menggunakan currentCabangId (ID cabang)
    const response = await fetch(`api_shift_calendar.php?action=get_assignments&cabang_id=${currentCabangId}&month=${month}`);
    const result = await response.json();
    
    console.log(`loadShiftAssignments - API response for ${month}:`, result);
    
    if (result.status === 'success' && result.data) {
        console.log(`loadShiftAssignments - Processing ${result.data.length} assignments for ${month}`);
        result.data.forEach(assignment => {
            const key = `${assignment.tanggal_shift}-${assignment.user_id}`;
            shiftAssignments[key] = {
                id: assignment.id,
                user_id: assignment.user_id,
                cabang_id: assignment.cabang_id,
                shift_date: assignment.tanggal_shift,
                shift_type: assignment.nama_shift,
                pegawai_name: assignment.nama_lengkap,
                jam_masuk: assignment.jam_masuk,
                jam_keluar: assignment.jam_keluar,
                status_konfirmasi: assignment.status_konfirmasi || 'pending'
            };
            console.log(`loadShiftAssignments - Added assignment: ${key}`, shiftAssignments[key]);
        });
    }
}
```

### 2. Fix `switchView()` untuk Reload Data

**File**: `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js`  
**Line**: ~943

```javascript
function switchView(view) {
    currentView = view;
    
    // Update active button
    document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`view-${view}`)?.classList.add('active');
    
    // ✅ FIXED: Reload shift assignments for the new view context
    if (currentCabangId && currentShiftId) {
        loadShiftAssignments(); // This will call generateCalendar() after loading
    } else {
        // Generate appropriate view if no data needs to be loaded
        if (view === 'month') {
            generateMonthView(currentMonth, currentYear);
        } else if (view === 'week') {
            generateWeekView(currentDate);
        } else if (view === 'day') {
            generateDayView(currentDate);
        } else if (view === 'year') {
            generateYearView(currentYear);
        }
    }
    
    updateNavigationLabels();
}
```

## 🧪 CARA TESTING

### Test Case 1: Day View
1. Buka `kalender.php`
2. Pilih cabang: **Kalimantan**
3. Pilih shift: **Pagi (08:00 - 16:00)**
4. Klik tanggal **2 November 2025** di month view (atau klik "Day" untuk hari ini)
5. **Expected**: Shift harus tampil di time slot 08:00 (atau sesuai jam masuk shift)
6. Periksa browser console untuk log:
   ```
   Day view - Looking for shifts on: 2025-11-02
   Day view - Found X shifts for 2025-11-02
   ```

### Test Case 2: Week View
1. Pastikan masih di cabang **Kalimantan** dan shift **Pagi**
2. Klik tombol "Week" di navigation
3. **Expected**: Week view menampilkan shift di time slot yang sesuai (08:00)
4. Periksa browser console untuk log:
   ```
   Loading shift assignments for months: ["2025-10", "2025-11"]
   Week view - Day 2025-11-02: Found X shifts
   ```

### Test Case 3: Switch Between Views
1. Mulai dari **Month View** dengan shift assignments
2. Switch ke **Week View** → shifts harus tampil
3. Switch ke **Day View** → shifts harus tampil
4. Switch kembali ke **Month View** → shifts tetap tampil
5. Pastikan tidak ada data yang hilang saat switch view

### Test Case 4: Cross-Month Week
1. Navigasi ke minggu yang melintasi 2 bulan (misal 27 Okt - 2 Nov)
2. **Expected**: Shifts dari kedua bulan harus tampil di week view
3. Console log harus menampilkan:
   ```
   Loading shift assignments for months: ["2025-10", "2025-11"]
   ```

## 📊 VERIFIKASI API RESPONSE

### API Endpoint Test
```bash
# Test API dengan parameter yang benar
curl "http://localhost/aplikasi/api_shift_calendar.php?action=get_assignments&cabang_id=1&month=2025-11"
```

**Expected Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": "1",
      "user_id": "123",
      "cabang_id": "1",
      "tanggal_shift": "2025-11-02",
      "nama_shift": "Pagi",
      "nama_lengkap": "John Doe",
      "jam_masuk": "08:00:00",
      "jam_keluar": "16:00:00",
      "status_konfirmasi": "approved"
    }
  ]
}
```

## 🔍 DEBUG CHECKLIST

Jika masih ada masalah setelah fix:

### 1. Check Browser Console
```javascript
// Cari log ini di console:
"Loading shift assignments for months: ..."
"loadShiftAssignments - API response for ..."
"Day view - Found X shifts for ..."
"Week view - Day 2025-11-02: Found X shifts"
```

### 2. Check Network Tab
- Buka DevTools → Network tab
- Filter: `api_shift_calendar.php`
- Verify request URL contains: `cabang_id=<ANGKA>&month=2025-11`
- Check response body for data

### 3. Check Variables
```javascript
// Add to console:
console.log('currentCabangId:', currentCabangId);
console.log('currentShiftId:', currentShiftId);
console.log('shiftAssignments:', shiftAssignments);
```

### 4. Check Database
```sql
-- Verify data exists
SELECT * FROM absen 
WHERE cabang_id = 1 
  AND tanggal_shift >= '2025-11-01' 
  AND tanggal_shift <= '2025-11-30';
```

## 🎯 EXPECTED BEHAVIOR AFTER FIX

### Month View
- ✅ Semua shift tampil di tanggal yang sesuai
- ✅ Status badge (pending/approved/declined) tampil
- ✅ Klik tanggal → switch ke day view dengan data

### Week View
- ✅ Shift tampil di time slot yang sesuai dengan jam_masuk
- ✅ Employee names tampil di badge dengan warna status
- ✅ Week yang span 2 bulan: load data dari kedua bulan
- ✅ Scroll vertical untuk melihat semua 24 jam

### Day View
- ✅ Shift tampil di time slot yang sesuai dengan jam_masuk
- ✅ Multiple employees per shift digabung dalam satu card
- ✅ Status badge dan lock indicator untuk approved shifts
- ✅ Klik time slot → open assign modal

## 📝 TECHNICAL NOTES

### API Contract
```javascript
// ✅ CORRECT API Call
GET api_shift_calendar.php?action=get_assignments&cabang_id={ID_CABANG}&month={YYYY-MM}

// Response structure:
{
    status: 'success',
    data: [
        {
            id: string,
            user_id: string,
            cabang_id: string,
            tanggal_shift: 'YYYY-MM-DD',
            nama_shift: string,
            nama_lengkap: string,
            jam_masuk: 'HH:MM:SS',
            jam_keluar: 'HH:MM:SS',
            status_konfirmasi: 'pending'|'approved'|'declined'
        }
    ]
}
```

### Data Flow
1. User selects **Cabang** → `currentCabangId` set
2. User selects **Shift** → `currentShiftId` set → `loadShiftAssignments()` called
3. `loadShiftAssignments()` determines which months to load based on `currentView`
4. API called with **`currentCabangId`** (not currentShiftId!)
5. Data stored in `shiftAssignments` object with key: `${date}-${userId}`
6. `generateCalendar()` called to render view
7. View functions loop through `shiftAssignments` and display matching shifts

### Key Variables
- `currentCabangId`: ID of selected outlet/branch (for API calls)
- `currentShiftId`: ID of selected shift template (for filtering display)
- `currentCabangName`: Name of selected outlet (for display)
- `currentShiftData`: Full shift object (for modal and display)
- `shiftAssignments`: Object storing all shift assignments for loaded months

## ✅ STATUS

- [x] Bug identified and root cause found
- [x] Fix applied to `loadShiftAssignments()`
- [x] Fix applied to `switchView()`
- [x] Syntax validation passed
- [ ] Manual testing required
- [ ] User verification required

## 🚀 NEXT STEPS

1. **Clear Browser Cache**: Ctrl+F5 atau Cmd+Shift+R
2. **Test All Views**: Month → Week → Day
3. **Verify Console Logs**: Check untuk API responses
4. **Report Results**: Capture screenshots if issues persist

---

**CRITICAL**: Ini adalah bug major yang mempengaruhi core functionality. Testing menyeluruh diperlukan sebelum deployment.
