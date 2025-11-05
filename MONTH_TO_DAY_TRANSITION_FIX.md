# FIX: Month to Day/Week View Transition Bug

## Tanggal
4 November 2024

## Deskripsi Bug
Data shift pada tanggal 2 November 2025 **hilang** ketika user:
1. Melihat data di **month view** (data muncul normal)
2. **Klik tanggal 2 November** untuk berpindah ke **day view**
3. Data shift **tidak muncul** di day view atau week view

Namun jika user berpindah mode menggunakan **tombol Week/Day**, data muncul normal.

## Root Cause Analysis

### Problem 1: `switchView()` Function
**Lokasi**: Line ~943 di `script_kalender_database.js`

**Kode Lama (Salah)**:
```javascript
if (currentCabangId && currentShiftId) {
    loadShiftAssignments();
}
```

**Masalah**: 
- Fungsi `loadShiftAssignments()` hanya dipanggil jika **KEDUA** `currentCabangId` DAN `currentShiftId` terisi
- Ketika user klik tanggal dari month view, sering kali `currentShiftId` **kosong/null**
- Akibatnya data shift tidak dimuat ulang

### Problem 2: `loadShiftAssignments()` Function
**Lokasi**: Line ~220 di `script_kalender_database.js`

**Kode Lama (Salah)**:
```javascript
async function loadShiftAssignments() {
    if (!currentCabangId || !currentShiftId) return;
    // ...
}
```

**Masalah**:
- Fungsi langsung return jika `currentShiftId` kosong
- Padahal API **tidak memerlukan** `shift_id` untuk memuat data
- API hanya butuh `cabang_id` dan `month` parameter

### Problem 3: Shift Selector Event Listener
**Lokasi**: Line ~88 di `script_kalender_database.js`

**Kode Lama (Salah)**:
```javascript
if (currentCabangId && currentShiftId) {
    loadShiftAssignments();
}
```

**Masalah**: 
- Sama seperti Problem 1, terlalu ketat dalam pengecekan kondisi

## Solution Implemented

### Fix 1: `switchView()` Function
**Kode Baru (Benar)**:
```javascript
// FIX: Only require currentCabangId, not currentShiftId
// This allows loading all shifts for the cabang when switching from month view
if (currentCabangId) {
    console.log(`Switching to ${view} view, reloading shift assignments for cabang ${currentCabangId}`);
    loadShiftAssignments();
}
```

**Improvement**:
- ✅ Hanya cek `currentCabangId` (yang pasti terisi jika user sudah pilih cabang)
- ✅ Menambahkan debug logging untuk tracking
- ✅ Memuat data shift untuk semua shift di cabang tersebut

### Fix 2: `loadShiftAssignments()` Function
**Kode Baru (Benar)**:
```javascript
async function loadShiftAssignments() {
    // FIX: Only require currentCabangId, API doesn't need currentShiftId
    if (!currentCabangId) {
        console.log('loadShiftAssignments - No cabang selected, skipping');
        return;
    }
    
    console.log('loadShiftAssignments - Loading for cabang:', currentCabangId, 'shift:', currentShiftId || 'ALL');
    
    // Determine which months to load based on current view
    // ...
}
```

**Improvement**:
- ✅ Hanya cek `currentCabangId` (tidak cek `currentShiftId`)
- ✅ Menambahkan debug logging yang menunjukkan apakah memuat semua shift atau shift spesifik
- ✅ API call tetap berjalan tanpa `shift_id` parameter

### Fix 3: Shift Selector Event Listener
**Kode Baru (Benar)**:
```javascript
// FIX: Load assignments whenever cabang is selected, regardless of shift selection
if (currentCabangId) {
    loadShiftAssignments();
}
```

**Improvement**:
- ✅ Konsisten dengan fix lainnya
- ✅ Memuat data segera setelah user pilih shift (atau tidak pilih)

## API Behavior

### API Endpoint
`api_shift_calendar.php?action=get_assignments&cabang_id=X&month=YYYY-MM`

### Parameter Requirements
- **REQUIRED**: `cabang_id` - ID cabang yang dipilih
- **REQUIRED**: `month` - Bulan dalam format YYYY-MM
- **OPTIONAL**: `shift_id` - TIDAK diperlukan, API akan return semua shift untuk cabang tersebut

### Response
API mengembalikan **semua shift assignments** untuk cabang pada bulan tersebut, regardless of shift type.

## Testing

### Automated Test
Run script:
```bash
./test_month_to_day_fix.sh
```

### Manual Test Steps
1. Buka `kalender_database.php` di browser
2. **Pilih Cabang** (contoh: Jember)
3. **JANGAN pilih Shift** (biarkan kosong)
4. Month view harus menampilkan semua shift
5. **Klik tanggal 2 November** di month view
6. Day view harus menampilkan shift untuk tanggal tersebut
7. **Klik tombol "Week"**
8. Week view harus menampilkan shift di slot waktu yang benar

### Expected Results
✅ Data shift muncul di day view setelah klik tanggal dari month view
✅ Data shift muncul di week view dengan slot waktu yang benar
✅ Tidak ada data yang hilang saat transisi antar view
✅ Bekerja dengan atau tanpa pemilihan shift spesifik

### Debug Console
Buka browser console untuk melihat log:
```
Switching to day view, reloading shift assignments for cabang 2
loadShiftAssignments - Loading for cabang: 2 shift: ALL
Loading shift assignments for months: ['2025-11']
loadShiftAssignments - API response for 2025-11: {...}
```

## Files Modified
- `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js`
  - `switchView()` function - Line ~943
  - `loadShiftAssignments()` function - Line ~220
  - Shift selector event listener - Line ~88

## Files Created
- `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/test_month_to_day_fix.sh` - Test script
- `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/MONTH_TO_DAY_TRANSITION_FIX.md` - This documentation

## Impact Analysis

### Before Fix
- ❌ Data hilang ketika klik tanggal dari month view
- ❌ User harus menggunakan tombol Week/Day untuk melihat data
- ❌ Pengalaman user tidak konsisten
- ❌ Bug hanya terjadi jika `currentShiftId` kosong

### After Fix
- ✅ Data tetap muncul saat klik tanggal dari month view
- ✅ User bisa berpindah view dengan cara apapun
- ✅ Pengalaman user konsisten
- ✅ Bekerja dengan atau tanpa pemilihan shift

## Related Issues
- Original bug report: Week/Day view tidak menampilkan data shift
- Previous fix: Parameter API call di `loadShiftAssignments()` dari `currentShiftId` ke `currentCabangId`
- Previous fix: Multi-month loading untuk week view

## Status
✅ **RESOLVED** - All tests passing, ready for production

## Recommendation
Deploy immediately to production. This fix resolves a critical UX bug that affects the primary workflow of switching between calendar views.
