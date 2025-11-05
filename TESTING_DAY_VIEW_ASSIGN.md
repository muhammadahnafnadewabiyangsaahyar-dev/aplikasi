# 🧪 Testing Guide - Day View Assign Shift Feature

## 🎯 Tujuan Testing
Memastikan fitur assign shift di Day View berfungsi dengan baik dan sesuai dengan requirements.

## 🔧 Setup Prerequisites

### 1. Database
Pastikan tabel berikut sudah ada dan terisi:
```sql
-- Cek tabel cabang
SELECT * FROM cabang;

-- Cek tabel pegawai
SELECT * FROM pegawai;

-- Cek tabel shift_assignments
SELECT * FROM shift_assignments;
```

### 2. Server
- ✅ XAMPP/Apache running
- ✅ MySQL running
- ✅ PHP 7.4+

### 3. User Account
- ✅ Login sebagai admin
- ✅ Session aktif

## 📋 Test Scenarios

### ✅ Test 1: Dropdown Cabang Simplification
**Objective**: Memastikan dropdown hanya menampilkan "Pilih Cabang"

**Steps**:
1. Buka browser dan akses `http://localhost/aplikasi/kalender.php`
2. Login sebagai admin jika belum
3. Lihat dropdown di bagian atas

**Expected Result**:
- ✅ Label: "Pilih Cabang:" (bukan "Pilih Cabang & Shift:")
- ✅ Placeholder: "-- Pilih Cabang --"
- ✅ Daftar cabang muncul saat diklik

**Screenshot Area**: Top controls section

---

### ✅ Test 2: Day View Navigation
**Objective**: Memastikan Day View dapat diakses

**Steps**:
1. Dari halaman `kalender.php`
2. Klik tombol **"Day"** di bagian view controls

**Expected Result**:
- ✅ View berubah ke Day View
- ✅ Month/Week/Year view hidden
- ✅ Time column (00:00 - 23:00) muncul di sebelah kiri
- ✅ Day content area muncul di sebelah kanan
- ✅ Tanggal hari ini ditampilkan

**Console Check**:
```javascript
// Buka Console (F12)
console.log(currentView); // Should be 'day'
```

---

### ✅ Test 3: Time Slot Hover Effect
**Objective**: Memastikan time slot responsif terhadap hover

**Steps**:
1. Masuk ke Day View
2. Pilih cabang dari dropdown
3. Hover mouse di atas time slot (contoh: 08:00)

**Expected Result**:
- ✅ Background berubah menjadi light blue (#e3f2fd)
- ✅ Cursor berubah menjadi pointer
- ✅ Saat mouse leave, background kembali normal

---

### ✅ Test 4: Modal Assign Shift - Tanpa Cabang
**Objective**: Validasi saat cabang belum dipilih

**Steps**:
1. Masuk ke Day View
2. **Jangan pilih cabang** (biarkan kosong)
3. Klik pada time slot (contoh: 08:00)

**Expected Result**:
- ✅ Alert muncul: "❌ Pilih cabang terlebih dahulu!"
- ✅ Modal **TIDAK** muncul

---

### ✅ Test 5: Modal Assign Shift - Dengan Cabang
**Objective**: Memastikan modal muncul dengan data yang benar

**Steps**:
1. Pilih cabang dari dropdown (contoh: "Cabang Pusat")
2. Masuk ke Day View
3. Klik pada time slot (contoh: 08:00)

**Expected Result**:
- ✅ Modal muncul dengan smooth animation
- ✅ Title: "Assign Shift - [Tanggal Lengkap]"
- ✅ Waktu: "08:00 - 09:00" (atau sesuai jam yang diklik)
- ✅ Dropdown pegawai terisi dengan daftar pegawai dari cabang
- ✅ Dropdown shift menampilkan 4 opsi: Pagi, Siang, Malam, Off

**Console Check**:
```javascript
// Cek data modal
const modal = document.getElementById('day-assign-modal');
console.log(modal.dataset.date); // Format: YYYY-MM-DD
console.log(modal.dataset.hour); // Format: number (0-23)
```

---

### ✅ Test 6: Pegawai List Loading
**Objective**: Memastikan daftar pegawai dimuat dengan benar

**Steps**:
1. Pilih cabang: "Cabang Pusat"
2. Buka modal assign shift (klik time slot)
3. Lihat dropdown pegawai

**Expected Result**:
- ✅ Dropdown berisi pegawai dari cabang yang dipilih
- ✅ Format: "Nama Pegawai (Jabatan)"
- ✅ Jika tidak ada pegawai, hanya ada placeholder

**Console Check**:
```javascript
// Open Console
// Setelah modal terbuka, cek:
const pegawaiSelect = document.getElementById('day-modal-pegawai');
console.log(pegawaiSelect.options.length); // Should be > 1
```

**API Check**:
```
http://localhost/aplikasi/api_shift_calendar.php?action=get_pegawai&cabang_id=1
```

---

### ✅ Test 7: Save Shift - Tanpa Pegawai
**Objective**: Validasi saat pegawai belum dipilih

**Steps**:
1. Buka modal assign shift
2. Pilih shift type (contoh: Shift Pagi)
3. **Jangan pilih pegawai**
4. Klik tombol "💾 Simpan Shift"

**Expected Result**:
- ✅ Alert muncul: "❌ Pilih pegawai terlebih dahulu!"
- ✅ Modal **TIDAK** tertutup
- ✅ Data **TIDAK** disimpan ke database

---

### ✅ Test 8: Save Shift - Data Lengkap
**Objective**: Menyimpan shift assignment dengan sukses

**Steps**:
1. Pilih cabang: "Cabang Pusat"
2. Masuk ke Day View
3. Klik time slot (contoh: 08:00)
4. Modal muncul
5. Pilih pegawai: "John Doe"
6. Pilih shift: "🌅 Shift Pagi"
7. Klik "💾 Simpan Shift"

**Expected Result**:
- ✅ Alert sukses: "✅ Shift berhasil di-assign!"
- ✅ Modal tertutup otomatis
- ✅ Day View di-refresh
- ✅ Shift baru muncul di Day View dengan styling
- ✅ Info: Nama pegawai, Shift type, Jam kerja

**Database Check**:
```sql
SELECT * FROM shift_assignments 
WHERE cabang_id = 1 
  AND pegawai_id = (SELECT id FROM pegawai WHERE nama = 'John Doe')
  AND shift_date = CURDATE()
  AND shift_type = 'pagi';
```

**Console Check**:
```javascript
// Setelah save, cek:
console.log(shiftAssignments);
// Should contain the new assignment
```

---

### ✅ Test 9: Modal Close Buttons
**Objective**: Memastikan modal dapat ditutup dengan berbagai cara

**Steps**:
1. Buka modal (klik time slot)

**Test 9a - Close dengan X**:
2. Klik tombol "×" (close) di pojok kanan atas
3. **Expected**: Modal tertutup, form ter-reset

**Test 9b - Close dengan Batal**:
2. Klik tombol "❌ Batal"
3. **Expected**: Modal tertutup, form ter-reset

**Test 9c - Close dengan Click Outside** (Optional):
2. Klik di luar modal (background)
3. **Expected**: (Tergantung implementasi)

---

### ✅ Test 10: Display Shifts in Day View
**Objective**: Memastikan shift yang sudah di-assign ditampilkan dengan benar

**Setup**:
- Assign beberapa shift terlebih dahulu (via test 8)

**Steps**:
1. Pilih cabang
2. Masuk ke Day View
3. Lihat konten area

**Expected Result**:
- ✅ Shift cards muncul dengan styling:
  - Background: #f0f8ff
  - Border left: 4px solid #2196F3
  - Border radius: 4px
- ✅ Menampilkan:
  - Nama pegawai (bold, 16px)
  - Shift type (blue, bold)
  - Jam kerja (gray, with clock icon)

---

### ✅ Test 11: No Shift Info Message
**Objective**: Memastikan info message muncul saat belum ada shift

**Steps**:
1. Pilih cabang yang tidak punya shift assignment hari ini
2. Masuk ke Day View

**Expected Result**:
- ✅ Info box muncul:
  - Background: #f5f5f5
  - Text: "📅 Belum ada shift yang di-assign untuk hari ini"
  - Subtext: "Klik pada jam di sebelah kiri untuk assign shift"

---

### ✅ Test 12: Instruction Message
**Objective**: Memastikan instruksi selalu muncul

**Steps**:
1. Masuk ke Day View (dengan atau tanpa shift)
2. Scroll ke bawah

**Expected Result**:
- ✅ Info box muncul di bawah:
  - Background: #e8f5e9 (light green)
  - Text: "💡 Tip: Klik pada waktu di sebelah kiri untuk assign shift ke pegawai"

---

### ✅ Test 13: Multiple Shifts Same Day
**Objective**: Memastikan multiple shifts dapat di-assign ke hari yang sama

**Steps**:
1. Assign shift 1: John Doe - Shift Pagi - 08:00
2. Assign shift 2: Jane Smith - Shift Siang - 08:00
3. Assign shift 3: Bob Johnson - Shift Malam - 08:00

**Expected Result**:
- ✅ Semua shift tersimpan di database
- ✅ Semua shift muncul di Day View
- ✅ Tidak ada duplikasi atau konflik

**Database Check**:
```sql
SELECT * FROM shift_assignments 
WHERE shift_date = CURDATE()
ORDER BY pegawai_id;
```

---

### ✅ Test 14: Navigation with Assigned Shifts
**Objective**: Memastikan shift tetap muncul saat navigasi

**Steps**:
1. Assign shift untuk hari ini
2. Klik "Next" (hari berikutnya)
3. Klik "Previous" (kembali ke hari ini)

**Expected Result**:
- ✅ Shift yang sudah di-assign tetap muncul
- ✅ Tidak hilang setelah navigasi

---

### ✅ Test 15: Cross-Browser Testing
**Objective**: Memastikan fitur berfungsi di berbagai browser

**Browsers to Test**:
- ✅ Google Chrome (latest)
- ✅ Mozilla Firefox (latest)
- ✅ Safari (latest)
- ✅ Microsoft Edge (latest)

**Test**:
- Lakukan Test 1-14 di masing-masing browser

---

## 🐛 Common Issues & Solutions

### Issue 1: Modal tidak muncul
**Symptom**: Click time slot, tidak ada respon

**Debug**:
```javascript
// Console check
console.log(currentCabangId); // Should not be null
console.log(document.getElementById('day-assign-modal')); // Should exist
```

**Solution**:
- Pastikan cabang sudah dipilih
- Refresh browser (Ctrl+F5)
- Clear cache

---

### Issue 2: Dropdown pegawai kosong
**Symptom**: Modal muncul tapi dropdown pegawai hanya placeholder

**Debug**:
```javascript
// Console check
fetch('api_shift_calendar.php?action=get_pegawai&cabang_id=1')
  .then(r => r.json())
  .then(console.log);
```

**Solution**:
- Cek API response
- Pastikan pegawai ada di database untuk cabang tersebut
- Cek koneksi database

---

### Issue 3: Shift tidak muncul setelah save
**Symptom**: Alert sukses muncul tapi shift tidak tampil

**Debug**:
```javascript
// Console check
console.log(shiftAssignments);
// Setelah save, klik time slot lagi dan cek
```

**Solution**:
- Refresh manual (F5)
- Cek database apakah data tersimpan
- Cek fungsi `loadShiftAssignments()`

---

### Issue 4: Error 500 saat save
**Symptom**: Alert error muncul

**Debug**:
- Buka Console → Network tab
- Cek response dari API
- Buka `api_shift_calendar.php` dan tambahkan error logging

**Solution**:
- Cek error log di Console
- Validasi data yang dikirim
- Cek syntax di API

---

## 📊 Test Results Template

```
Date: [YYYY-MM-DD]
Tester: [Your Name]
Browser: [Chrome/Firefox/Safari/Edge]
OS: [Windows/MacOS/Linux]

Test Results:
✅ Test 1: Pass
✅ Test 2: Pass
✅ Test 3: Pass
✅ Test 4: Pass
❌ Test 5: Fail - [Reason]
✅ Test 6: Pass
✅ Test 7: Pass
✅ Test 8: Pass
✅ Test 9: Pass
✅ Test 10: Pass
✅ Test 11: Pass
✅ Test 12: Pass
✅ Test 13: Pass
✅ Test 14: Pass
✅ Test 15: Pass

Issues Found:
1. [Description]
2. [Description]

Notes:
[Additional notes]
```

---

## 🎯 Success Criteria

### Must Have (P0):
- ✅ Modal muncul saat time slot diklik
- ✅ Pegawai list dimuat dari cabang yang dipilih
- ✅ Shift dapat disimpan ke database
- ✅ Shift muncul di Day View setelah disave
- ✅ Validasi input berfungsi

### Should Have (P1):
- ✅ Hover effect pada time slot
- ✅ Info messages ditampilkan
- ✅ Modal dapat ditutup dengan berbagai cara
- ✅ Error handling yang baik

### Nice to Have (P2):
- ✅ Smooth animations
- ✅ Icons/emojis di UI
- ✅ Color coding untuk shifts
- ✅ Responsive design

---

## 📝 Final Checklist

Before going to production:
- [ ] All tests passed (Test 1-15)
- [ ] Cross-browser testing done
- [ ] Database schema validated
- [ ] API endpoints tested
- [ ] Error handling validated
- [ ] Security validated (SQL injection, XSS, CSRF)
- [ ] Performance tested (load time, query time)
- [ ] Documentation updated
- [ ] User guide created
- [ ] Admin trained

---

**Testing Status**: 🟡 In Progress
**Last Updated**: 2024
**Next Review**: After first production deployment
