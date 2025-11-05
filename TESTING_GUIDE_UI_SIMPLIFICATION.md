# 🧪 TESTING GUIDE - UI SIMPLIFICATION

**Tanggal:** 6 November 2025  
**Tester:** _____________________  
**Browser:** _____________________

---

## ⚡ QUICK TEST PROCEDURES

### Test 1: Verifikasi UI Bersih ✅
**Expected:** Hanya 5 tombol utama yang terlihat

**Steps:**
1. Buka `kalender.php` di browser
2. Login (jika perlu)
3. Perhatikan UI

**Checklist:**
- [ ] ❌ Tidak ada dropdown "Pilih Shift" di main UI
- [ ] ✅ Ada tombol "Backup Data"
- [ ] ✅ Ada tombol "Restore Data"
- [ ] ✅ Ada tombol "Kelola Shift (Tabel)"
- [ ] ✅ Ada tombol "Ekspor CSV"
- [ ] ✅ Ada tombol "Tampilkan Ringkasan"
- [ ] ❌ Tidak ada tombol "Assign Shift", "Bulk Assign", dll

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 2: Auto-Load Shift per Cabang ✅
**Expected:** Shift otomatis dimuat saat pilih cabang

**Steps:**
1. Pilih cabang dari dropdown
2. Tunggu loading
3. Lihat kalender

**Checklist:**
- [ ] Shift muncul di kalender tanpa perlu klik tombol lain
- [ ] Semua shift type terlihat (pagi, siang, malam, off)
- [ ] Tidak ada error di console (F12 → Console)

**Console Expected:**
```
✅ Loaded shifts for outlet: [nama_cabang] - Count: X
```

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 3: Sinkronisasi Ringkasan - Day View 📅
**Expected:** Ringkasan menampilkan data hari yang dipilih

**Steps:**
1. Pilih cabang
2. Klik tombol "Day" view
3. Pilih tanggal tertentu
4. Klik "Tampilkan Ringkasan"
5. Perhatikan header ringkasan

**Checklist:**
- [ ] Header menampilkan: "Hari: [DD MMMM YYYY]"
- [ ] Tabel pegawai menampilkan data shift hari tersebut
- [ ] Tabel shift menampilkan jumlah per jenis shift hari tersebut
- [ ] Data kosong jika tidak ada shift di hari tersebut

**Example Header:**
```
Ringkasan Hari: 6 November 2025 - [Nama Cabang]
```

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 4: Sinkronisasi Ringkasan - Week View 📅
**Expected:** Ringkasan menampilkan data minggu yang dipilih

**Steps:**
1. Pilih cabang
2. Klik tombol "Week" view
3. Klik "Tampilkan Ringkasan"
4. Perhatikan header ringkasan
5. Klik "Previous" atau "Next"
6. Perhatikan ringkasan berubah

**Checklist:**
- [ ] Header menampilkan: "Minggu: [DD MMM YYYY] - [DD MMM YYYY]"
- [ ] Data mencakup 7 hari (Senin-Minggu)
- [ ] Ringkasan auto-update saat navigasi prev/next
- [ ] Rentang tanggal benar (Senin sebagai hari pertama)

**Example Header:**
```
Ringkasan Minggu: 3 November 2025 - 9 November 2025 - [Nama Cabang]
```

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 5: Sinkronisasi Ringkasan - Month View 📅
**Expected:** Ringkasan menampilkan data bulan yang dipilih

**Steps:**
1. Pilih cabang
2. Klik tombol "Month" view
3. Klik "Tampilkan Ringkasan"
4. Perhatikan header ringkasan
5. Klik "Previous" atau "Next" month
6. Perhatikan ringkasan berubah

**Checklist:**
- [ ] Header menampilkan: "Bulan: [MMMM YYYY]"
- [ ] Data mencakup seluruh bulan (1 - akhir bulan)
- [ ] Ringkasan auto-update saat navigasi prev/next
- [ ] Total shift sesuai dengan yang terlihat di kalender

**Example Header:**
```
Ringkasan Bulan: November 2025 - [Nama Cabang]
```

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 6: Sinkronisasi Ringkasan - Year View 📅
**Expected:** Ringkasan menampilkan data tahun yang dipilih

**Steps:**
1. Pilih cabang
2. Klik tombol "Year" view
3. Klik "Tampilkan Ringkasan"
4. Perhatikan header ringkasan
5. Klik "Previous" atau "Next" year
6. Perhatikan ringkasan berubah

**Checklist:**
- [ ] Header menampilkan: "Tahun: [YYYY]"
- [ ] Data mencakup seluruh tahun (Jan - Des)
- [ ] Ringkasan auto-update saat navigasi prev/next
- [ ] Total jam dan shift akumulasi seluruh tahun

**Example Header:**
```
Ringkasan Tahun: 2025 - [Nama Cabang]
```

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 7: Filter Nama Pegawai 🔍
**Expected:** Tabel terfilter real-time saat ketik nama

**Steps:**
1. Buka ringkasan (dengan data yang ada)
2. Ketik nama pegawai di input filter
3. Lihat tabel pegawai

**Test Cases:**
| Input | Expected Result |
|-------|----------------|
| "budi" | Hanya baris dengan nama mengandung "budi" |
| "BUDI" | Same as above (case-insensitive) |
| "bu" | Semua nama mengandung "bu" (partial match) |
| "" (kosong) | Semua baris muncul kembali |

**Checklist:**
- [ ] Filter bekerja real-time (tanpa klik tombol)
- [ ] Case-insensitive
- [ ] Partial match bekerja
- [ ] Clear input menampilkan semua data kembali

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 8: Download Ringkasan - CSV Format 💾
**Expected:** File CSV terdownload dengan data lengkap

**Steps:**
1. Buka ringkasan (pastikan ada data)
2. Pilih format "CSV" di dropdown
3. Klik tombol "Download"
4. Buka file CSV yang terdownload

**Checklist:**
- [ ] File terdownload otomatis
- [ ] Filename format: `Ringkasan_Shift_[CabangName]_[YYYY-MM-DD].csv`
- [ ] File berisi header: cabang, periode, timestamp
- [ ] File berisi tabel pegawai (nama, total shift, jam, hari kerja, off)
- [ ] File berisi tabel shift (nama shift, jumlah)
- [ ] Data di CSV sesuai dengan yang terlihat di UI
- [ ] Format CSV bisa dibuka di Excel/Google Sheets

**Example Content:**
```csv
"Ringkasan Shift - Cabang A"
"Periode: Bulan: November 2025"
"Tanggal Download: 6/11/2025, 10:30:45"

"RINGKASAN PER PEGAWAI"
"Nama Pegawai","Total Shift","Total Jam","Hari Kerja","Hari Off"
"Budi Santoso","20","160","18","2"
...
```

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 9: Download Ringkasan - TXT Format 💾
**Expected:** File TXT terdownload dengan format tabel

**Steps:**
1. Buka ringkasan (pastikan ada data)
2. Pilih format "TXT" di dropdown
3. Klik tombol "Download"
4. Buka file TXT yang terdownload

**Checklist:**
- [ ] File terdownload otomatis
- [ ] Filename format: `Ringkasan_Shift_[CabangName]_[YYYY-MM-DD].txt`
- [ ] File berisi header dengan garis separator (====)
- [ ] Tabel pegawai dengan kolom aligned (printf-style)
- [ ] Tabel shift dengan kolom aligned
- [ ] File readable di text editor biasa
- [ ] Format rapi dan terstruktur

**Example Content:**
```
RINGKASAN SHIFT - Cabang A
Periode: Bulan: November 2025
Tanggal Download: 6/11/2025, 10:30:45
================================================================================

RINGKASAN PER PEGAWAI
────────────────────────────────────────────────────────────────────────────────
Nama Pegawai                              Total Shift   Total Jam  Hari Kerja  Hari Off
────────────────────────────────────────────────────────────────────────────────
Budi Santoso                                       20         160          18         2
...
```

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 10: Tombol Utama Masih Berfungsi ✅
**Expected:** Semua 5 tombol utama berfungsi normal

**Steps:**
Test setiap tombol satu per satu

**Checklist:**
- [ ] **Backup Data:** Klik → muncul dialog/download backup
- [ ] **Restore Data:** Klik → muncul dialog upload/restore
- [ ] **Kelola Shift (Tabel):** Klik → muncul modal tabel shift
- [ ] **Ekspor CSV:** Klik → download file CSV schedule
- [ ] **Tampilkan Ringkasan:** Klik → toggle show/hide ringkasan

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 11: Assign Shift Masih Berfungsi ✅
**Expected:** User masih bisa assign shift via klik kalender

**Steps:**
1. Di day/week view, klik pada jam tertentu
2. Modal "Assign Shift" muncul
3. Pilih shift dari dropdown (di modal)
4. Pilih pegawai
5. Klik "Save"

**Checklist:**
- [ ] Modal muncul saat klik waktu/tanggal
- [ ] Dropdown shift di modal terisi dengan benar
- [ ] List pegawai muncul
- [ ] Bisa assign shift ke pegawai
- [ ] Shift muncul di kalender setelah save
- [ ] Ringkasan auto-update setelah assign

**Result:** PASS / FAIL  
**Notes:** _____________________

---

### Test 12: Error Handling & Console 🐛
**Expected:** Tidak ada error di console, proper logging

**Steps:**
1. Buka Developer Tools (F12)
2. Pergi ke tab "Console"
3. Lakukan semua test di atas
4. Perhatikan log messages

**Checklist:**
- [ ] Tidak ada error merah di console
- [ ] Ada log sukses: "✅ Loaded shifts..."
- [ ] Ada log sukses: "✅ Summary downloaded..."
- [ ] Ada log sukses: "✅ Summary filtered by..."
- [ ] Ada log debug yang informatif
- [ ] Tidak ada warning kuning yang serius

**Result:** PASS / FAIL  
**Notes:** _____________________

---

## 📊 TEST SUMMARY

| Test # | Test Name | Status | Notes |
|--------|-----------|--------|-------|
| 1 | UI Bersih | ⬜ | |
| 2 | Auto-Load Shift | ⬜ | |
| 3 | Sync - Day View | ⬜ | |
| 4 | Sync - Week View | ⬜ | |
| 5 | Sync - Month View | ⬜ | |
| 6 | Sync - Year View | ⬜ | |
| 7 | Filter Nama | ⬜ | |
| 8 | Download CSV | ⬜ | |
| 9 | Download TXT | ⬜ | |
| 10 | Tombol Utama | ⬜ | |
| 11 | Assign Shift | ⬜ | |
| 12 | Error Handling | ⬜ | |

**Legend:**
- ⬜ Not Tested
- ✅ PASS
- ❌ FAIL
- ⚠️ PASS with Issues

---

## 🐛 BUG REPORT TEMPLATE

Jika menemukan bug, gunakan template ini:

```
BUG ID: [001]
SEVERITY: [High/Medium/Low]
TEST: [Test #X - Test Name]
BROWSER: [Chrome/Firefox/Safari]

DESCRIPTION:
[Deskripsi singkat bug]

STEPS TO REPRODUCE:
1. [Step 1]
2. [Step 2]
3. [Step 3]

EXPECTED RESULT:
[Apa yang seharusnya terjadi]

ACTUAL RESULT:
[Apa yang sebenarnya terjadi]

CONSOLE ERRORS:
[Copy error messages dari console]

SCREENSHOTS:
[Attach jika perlu]

WORKAROUND:
[Jika ada cara sementara]
```

---

## ✅ SIGN OFF

**All Tests Passed:** YES / NO  
**Ready for Production:** YES / NO  

**Tested by:** _____________________  
**Date:** _____________________  
**Signature:** _____________________

**Notes:**
```
[Any additional notes or observations]
```

---

**End of Testing Guide**
