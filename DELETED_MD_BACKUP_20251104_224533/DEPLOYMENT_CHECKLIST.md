# 🚀 Deployment Checklist - Shift Calendar System

## ✅ Pre-Deployment Checks

### 1. Database Structure
- [ ] Tabel `cabang` sudah ada dengan kolom:
  - `id`, `nama_cabang`, `nama_shift`, `jam_masuk`, `jam_keluar`
- [ ] Tabel `shift_assignments` sudah ada dengan kolom:
  - `id`, `user_id`, `cabang_id`, `tanggal_shift`, `status_konfirmasi`, `waktu_konfirmasi`, `created_by`, `created_at`, `updated_at`
- [ ] Foreign keys sudah di-set dengan benar
- [ ] Data cabang sudah terisi (minimal 1 cabang)

### 2. File Checks
- [ ] File `shift_calendar.php` sudah di-upload
- [ ] File `api_shift_calendar.php` sudah di-upload
- [ ] File `navbar.php` sudah di-update
- [ ] Folder `js/daypilot/` ada dan berisi `daypilot-all.min.js`
- [ ] File lama `shift_management.php` di-backup (optional: rename jadi `shift_management.php.old`)

### 3. Configuration
- [ ] File `connect.php` sudah benar (koneksi database)
- [ ] Session sudah aktif di semua file
- [ ] Role checking sudah benar (admin vs user)

### 4. Permissions
- [ ] Web server bisa read file PHP
- [ ] Web server bisa read file JS/CSS
- [ ] Upload folder (jika ada) bisa write

---

## 🧪 Testing Checklist

### A. Calendar View Testing

#### 1. Load Page
- [ ] Halaman `shift_calendar.php` bisa dibuka tanpa error
- [ ] Calendar view aktif by default
- [ ] No JavaScript errors di console

#### 2. Load Data
- [ ] Dropdown cabang terisi dengan data dari database
- [ ] Pilih cabang → pegawai muncul di calendar rows
- [ ] Legend warna cabang muncul dengan benar

#### 3. Create Assignment
- [ ] Pilih cabang dari dropdown
- [ ] Klik dan drag pada pegawai di tanggal tertentu
- [ ] Konfirmasi → shift assignment dibuat
- [ ] Shift muncul di calendar dengan warna sesuai cabang
- [ ] Shift menampilkan nama shift dan jam kerja
- [ ] Cek database: record baru di `shift_assignments`

#### 4. Move Assignment
- [ ] Drag shift ke pegawai lain → berhasil dipindah
- [ ] Drag shift ke tanggal lain → berhasil dipindah
- [ ] Cek database: `user_id` atau `tanggal_shift` ter-update
- [ ] `cabang_id` tidak berubah

#### 5. Delete Assignment
- [ ] Klik tombol X merah di shift
- [ ] Konfirmasi → shift hilang
- [ ] Cek database: record terhapus

#### 6. Error Handling
- [ ] Coba assign shift untuk pegawai yang sudah punya shift di tanggal sama
  - Harusnya muncul error: "Pegawai sudah memiliki shift pada tanggal ini"
- [ ] Coba assign tanpa pilih cabang
  - Harusnya muncul alert: "Silakan pilih cabang terlebih dahulu"

### B. Table View Testing

#### 1. Switch View
- [ ] Klik tombol "📋 Table View"
- [ ] Table view muncul, calendar view hidden
- [ ] Klik tombol "📅 Calendar View" → balik ke calendar

#### 2. Form Assignment
- [ ] Isi form: pilih pegawai, cabang, tanggal
- [ ] Submit → success message muncul
- [ ] Page reload → assignment muncul di tabel
- [ ] Cek database: record baru ada

#### 3. Table Display
- [ ] Tabel menampilkan semua assignments bulan ini
- [ ] Kolom tanggal, pegawai, cabang, shift, jam, status tampil benar
- [ ] Badge status warna benar:
  - Pending → kuning
  - Confirmed → hijau
  - Declined → merah

#### 4. Delete from Table
- [ ] Klik tombol "Hapus" di tabel
- [ ] Konfirmasi → record terhapus
- [ ] Page reload → data hilang dari tabel

### C. Multi-User Testing

#### Test Scenario 1: Different Cabang
```
Cabang A: Shift Pagi (08:00-16:00)
Cabang B: Shift Sore (14:00-22:00)

1. Assign User 1 ke Cabang A tanggal 10 Feb
2. Assign User 1 ke Cabang B tanggal 11 Feb
3. Assign User 2 ke Cabang A tanggal 10 Feb

Expected:
- User 1 punya 2 shift (tanggal berbeda, cabang berbeda)
- User 2 punya 1 shift
- Warna shift User 1 tanggal 10 ≠ tanggal 11
- Warna shift User 1 tanggal 10 = warna shift User 2 tanggal 10
```

#### Test Scenario 2: Conflict Prevention
```
1. Assign User 1 ke Cabang A tanggal 10 Feb
2. Coba assign User 1 ke Cabang B tanggal 10 Feb (same date)

Expected:
- Error: "Pegawai sudah memiliki shift pada tanggal ini"
- Assignment ke-2 tidak dibuat
```

#### Test Scenario 3: Drag & Drop
```
1. Assign User 1 ke Cabang A tanggal 10 Feb
2. Drag shift ke User 2 (same date)

Expected:
- Shift pindah dari User 1 ke User 2
- Tanggal tetap 10 Feb
- Cabang tetap Cabang A
- Database: user_id berubah dari 1 ke 2
```

### D. Browser Compatibility
- [ ] Chrome - OK
- [ ] Firefox - OK
- [ ] Safari - OK
- [ ] Edge - OK
- [ ] Mobile browser - OK (responsive)

---

## 🔍 Database Queries for Verification

### Check Cabang Data
```sql
SELECT * FROM cabang;
```
**Expected:** Minimal 1 row dengan semua kolom terisi

### Check Assignments
```sql
SELECT 
    sa.id,
    sa.tanggal_shift,
    r.nama_lengkap,
    c.nama_cabang,
    c.nama_shift,
    c.jam_masuk,
    c.jam_keluar,
    sa.status_konfirmasi
FROM shift_assignments sa
JOIN register r ON sa.user_id = r.id
JOIN cabang c ON sa.cabang_id = c.id
ORDER BY sa.tanggal_shift DESC
LIMIT 10;
```
**Expected:** Data tampil lengkap dengan JOIN yang benar

### Check User's Shifts
```sql
SELECT 
    r.nama_lengkap,
    COUNT(sa.id) as total_shifts,
    MIN(sa.tanggal_shift) as first_shift,
    MAX(sa.tanggal_shift) as last_shift
FROM register r
LEFT JOIN shift_assignments sa ON r.id = sa.user_id
WHERE r.role = 'user'
GROUP BY r.id
ORDER BY r.nama_lengkap;
```
**Expected:** List pegawai dengan jumlah shift masing-masing

---

## 🐛 Common Issues & Solutions

### Issue 1: Calendar tidak muncul (blank)
**Symptoms:** Div `#dp` kosong, tidak ada calendar
**Causes:**
- DayPilot library tidak load
- JavaScript error

**Solutions:**
1. Cek browser console untuk error
2. Pastikan file `js/daypilot/daypilot-all.min.js` ada
3. Cek path script src di HTML
4. Clear browser cache

### Issue 2: Data tidak muncul di calendar
**Symptoms:** Calendar muncul tapi kosong (no rows/events)
**Causes:**
- Cabang tidak dipilih
- API error
- No data in database

**Solutions:**
1. Pilih cabang dari dropdown
2. Cek network tab: pastikan API calls success
3. Cek response API: `api_shift_calendar.php?action=get_pegawai&cabang_id=1`
4. Cek database: pastikan ada pegawai dengan `id_cabang` yang sesuai

### Issue 3: Error saat create assignment
**Symptoms:** Alert error muncul saat assign shift
**Causes:**
- Duplicate assignment
- Invalid data
- Database constraint

**Solutions:**
1. Cek error message di alert
2. Cek PHP error log
3. Cek database constraint (foreign keys, unique keys)
4. Pastikan pegawai belum punya shift di tanggal tersebut

### Issue 4: Warna shift tidak konsisten
**Symptoms:** Shift untuk cabang yang sama punya warna berbeda
**Causes:**
- `cabang_id` tidak di-pass ke event data
- Color function error

**Solutions:**
1. Cek event data di browser console: `dp.events.list`
2. Pastikan setiap event punya property `cabang_id`
3. Cek function `getCabangColor()`

### Issue 5: Drag & drop tidak work
**Symptoms:** Tidak bisa drag shift
**Causes:**
- DayPilot config error
- Event handler not set

**Solutions:**
1. Cek config: `eventMoveHandling: "Update"`
2. Cek `onEventMove` handler ada
3. Cek console untuk error

---

## 📊 Performance Monitoring

### Metrics to Track
- [ ] Page load time < 2 seconds
- [ ] API response time < 500ms
- [ ] Calendar render time < 1 second
- [ ] No memory leaks on long usage

### Database Optimization
- [ ] Index on `shift_assignments.tanggal_shift`
- [ ] Index on `shift_assignments.user_id`
- [ ] Index on `shift_assignments.cabang_id`
- [ ] Index on `register.id_cabang`

### Queries to Add Indexes
```sql
CREATE INDEX idx_tanggal ON shift_assignments(tanggal_shift);
CREATE INDEX idx_user ON shift_assignments(user_id);
CREATE INDEX idx_cabang ON shift_assignments(cabang_id);
CREATE INDEX idx_user_cabang ON register(id_cabang);
```

---

## 🔄 Rollback Plan

If something goes wrong:

1. **Restore old files:**
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi/
   cp shift_management.php.old shift_management.php
   cp navbar.php.backup navbar.php
   ```

2. **Update navbar:**
   - Point back to `shift_management.php`

3. **Notify users:**
   - Inform admin that old system is back temporarily

4. **Debug offline:**
   - Copy files to dev environment
   - Fix issues
   - Re-deploy when ready

---

## ✅ Post-Deployment

### Immediate (Day 1)
- [ ] Monitor error logs
- [ ] Check admin feedback
- [ ] Verify data integrity
- [ ] Backup database

### Short-term (Week 1)
- [ ] Collect user feedback
- [ ] Fix critical bugs
- [ ] Performance tuning
- [ ] Documentation updates

### Long-term (Month 1)
- [ ] Feature enhancements
- [ ] UI/UX improvements
- [ ] Mobile optimization
- [ ] Advanced features (recurring shifts, templates, etc.)

---

## 📞 Emergency Contacts

**Developer:** [Your Name]  
**Phone:** [Your Phone]  
**Email:** [Your Email]  

**Database Admin:** [DB Admin]  
**Phone:** [Phone]  

**System Admin:** [Sys Admin]  
**Phone:** [Phone]  

---

**Date:** 2025-02-07  
**Version:** 2.0  
**Deployment Status:** ⏳ Ready for Testing
