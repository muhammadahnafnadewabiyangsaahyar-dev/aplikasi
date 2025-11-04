# 📊 ANALISIS DATABASE: Existing vs Shift System Schema

## 🔍 Overview

Dokumen ini membandingkan struktur database existing (`aplikasi.sql`) dengan schema shift system baru (`database_schema_shift_system.sql`) dan memberikan rekomendasi migration strategy.

---

## ✅ **TABEL YANG SUDAH ADA**

### 1. **Tabel `cabang`**
**Existing Structure:**
```sql
- id, nama_cabang, latitude, longitude, radius_meter
- nama_shift: 'pagi', 'middle', 'sore'
- jam_masuk, jam_keluar
```

**Fungsi Existing:**
- Master data cabang/outlet
- Template shift types per cabang
- **NAMUN:** Tidak ada jadwal harian, hanya template

**Mapping ke Shift System Baru:**
- `nama_shift` ('pagi', 'middle', 'sore') → `shift_type` ('shift_1', 'shift_2', 'shift_3')
- `jam_masuk` → `shift_schedule.jam_mulai`
- `jam_keluar` → `shift_schedule.jam_selesai`

**Rekomendasi:**
✅ **KEEP** tabel `cabang` sebagai master data  
✅ **ADD** tabel `shift_schedule` untuk jadwal harian dinamis  
✅ Create UI untuk admin assign jadwal shift berdasarkan template di `cabang`

---

### 2. **Tabel `absensi`**
**Existing Structure:**
```sql
- waktu_masuk, waktu_keluar, tanggal_absensi
- menit_terlambat, status_keterlambatan
- status_lembur: 'Pending', 'Approved', 'Rejected', 'Not Applicable'
- status_lokasi: 'Valid', 'Tidak Valid'
```

**Yang Perlu Ditambahkan:**
```sql
+ shift_schedule_id INT (link ke shift jika hadir di shift)
+ is_overwork BOOLEAN (TRUE jika hadir diluar shift)
+ overwork_hours DECIMAL (jumlah jam overwork)
+ overwork_pay DECIMAL (upah overwork Rp 6.250/jam)
+ potongan_overwork DECIMAL (potongan jika terlambat)
+ potongan_tunjangan ENUM (kategori potongan keterlambatan)
```

**Rekomendasi:**
✅ **ALTER TABLE** untuk add kolom baru  
✅ Kolom existing tetap dipertahankan  
✅ Backward compatible

---

### 3. **Tabel `komponen_gaji`**
**Existing Structure:**
```sql
CREATE TABLE komponen_gaji (
    id, register_id (FK), jabatan,
    gaji_pokok, tunjangan_transport, tunjangan_makan,
    overwork, tunjangan_jabatan, bonus_kehadiran,
    bonus_marketing, insentif_omset
)
```

**Masalah:**
❌ Data per user (1 row per user)  
❌ Tidak ada breakdown detail  
❌ Sulit untuk track komponen variabel (kasbon, piutang, dll)

**Schema Baru:**
```sql
-- Master data komponen
komponen_gaji: (id, nama_komponen, tipe, kategori)

-- Breakdown detail per slip gaji
komponen_gaji_detail: (slip_gaji_id, komponen_id, jumlah, keterangan)
```

**Keuntungan Schema Baru:**
✅ Flexible untuk add komponen baru tanpa ALTER TABLE  
✅ Detail breakdown per slip gaji  
✅ Bisa track historical changes  
✅ Support komponen variabel (kasbon, bonus, piutang)

**Rekomendasi:**
✅ **RENAME** `komponen_gaji` → `komponen_gaji_old` (backup)  
✅ **CREATE** struktur baru  
✅ **MIGRATE** data gaji pokok ke `register` table  
✅ Create view compatibility layer

---

### 4. **Tabel `riwayat_gaji`**
**Existing Structure:**
```sql
CREATE TABLE riwayat_gaji (
    register_id, periode_bulan, periode_tahun,
    gaji_pokok_aktual, tunjangan_makan, tunjangan_transportasi,
    overwork, kasbon, piutang_toko,
    potongan_absen, potongan_telat_*,
    gaji_bersih, jumlah_hadir, jumlah_terlambat, jumlah_absen,
    file_slip_gaji, tanggal_dibuat
)
```

**Masalah:**
⚠️ Periode bulan/tahun, tapi sistem sekarang: **28 bulan X → 28 bulan Y**  
⚠️ Tidak ada link ke detail breakdown komponen  
⚠️ Tidak ada tracking shift missed

**Schema Baru (`slip_gaji_history`):**
```sql
+ periode_mulai DATE (28 bulan sebelumnya)
+ periode_selesai DATE (28 bulan ini)
+ total_shift_missed INT
+ total_overwork_hours DECIMAL
+ potongan_shift_missed DECIMAL (Rp 50.000/hari)
+ status: 'draft', 'finalized', 'sent'
+ is_editable BOOLEAN
+ sent_at, sent_by (tracking)
```

**Rekomendasi:**
✅ **RENAME** `riwayat_gaji` → `riwayat_gaji_old` (backup)  
✅ **CREATE** `slip_gaji_history` dengan struktur baru  
✅ Support periode 28-28 (bukan bulan kalender)  
✅ Link ke `komponen_gaji_detail` untuk breakdown

---

## ❌ **TABEL YANG BELUM ADA (Perlu Ditambahkan)**

### 1. **`shift_schedule`** ⭐ CRITICAL
Jadwal shift per hari per cabang

**Kenapa Penting:**
- Existing `cabang` hanya template, bukan jadwal harian
- Perlu tabel untuk store actual schedule per tanggal
- Support multiple shift types per hari

### 2. **`shift_assignments`** ⭐ CRITICAL
Assignment pegawai ke shift + konfirmasi status

**Kenapa Penting:**
- Track siapa yang di-assign ke shift mana
- Status konfirmasi: pending/approved/rejected
- Lock mechanism (tidak bisa diubah setelah approve)

### 3. **`shift_confirmations`** ⭐ PENTING
Log konfirmasi shift dari user (via email/web)

**Kenapa Penting:**
- Audit trail konfirmasi shift
- Support email reply parsing
- Track IP/user agent untuk keamanan

### 4. **`libur_nasional`** ⭐ PENTING
Data hari libur nasional

**Kenapa Penting:**
- Logic absensi berbeda untuk hari libur
- Slip gaji calculation perlu exclude libur
- Planning shift harus consider libur nasional

### 5. **`komponen_gaji_detail`** ⭐ PENTING
Detail breakdown komponen per slip gaji

**Kenapa Penting:**
- Flexible add komponen tanpa ALTER TABLE
- Track historical changes
- Editable untuk admin

---

## 🔄 **PERBANDINGAN SHIFT LOGIC**

| Aspect | Existing | New System |
|--------|----------|------------|
| **Shift Definition** | Static di `cabang` table | Dynamic di `shift_schedule` |
| **Jadwal Harian** | ❌ Tidak ada | ✅ Per tanggal + cabang |
| **Assignment Pegawai** | ❌ Tidak ada | ✅ `shift_assignments` |
| **Konfirmasi User** | ❌ Tidak ada | ✅ Via email/web + log |
| **Overwork Detection** | Manual (status_lembur) | ✅ Otomatis (`is_overwork`) |
| **Shift Missed Tracking** | ❌ Tidak ada | ✅ Track per slip gaji |

---

## 🎯 **MIGRATION STRATEGY**

### **✅ RECOMMENDED: Incremental Migration**

**Phase 1: Database Schema (Day 1-2)**
1. ✅ Run `migration_shift_system.sql`
2. ✅ Backup existing data (`*_old` tables)
3. ✅ Add new tables
4. ✅ ALTER existing tables
5. ✅ Create views for compatibility

**Phase 2: Core Logic (Day 3-5)**
1. ✅ Update `proses_absensi.php` untuk handle shift logic
2. ✅ Create shift schedule UI (calendar view)
3. ✅ Create shift assignment UI
4. ✅ Create email notification system

**Phase 3: Salary System (Day 6-8)**
1. ✅ Auto-generate slip gaji script (cron job)
2. ✅ Slip gaji riwayat page
3. ✅ Edit komponen gaji UI
4. ✅ Bulk email slip gaji

**Phase 4: Testing & Refinement (Day 9-10)**
1. ✅ End-to-end testing
2. ✅ User acceptance testing
3. ✅ Performance optimization
4. ✅ Documentation

---

## 📝 **STEP-BY-STEP MIGRATION**

### **Step 1: Backup Database**
```bash
# Backup existing database
mysqldump -u root aplikasi > aplikasi_backup_$(date +%Y%m%d).sql

# Or via phpMyAdmin: Export > SQL
```

### **Step 2: Run Migration**
```bash
# Via command line
mysql -u root aplikasi < migration_shift_system.sql

# Or via phpMyAdmin: Import > migration_shift_system.sql
```

### **Step 3: Verify Migration**
```sql
-- Check new tables
SHOW TABLES LIKE 'shift_%';
SHOW TABLES LIKE '%_old';

-- Check altered columns
DESCRIBE absensi;
DESCRIBE pengajuan_izin;

-- Check views
SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW';
```

### **Step 4: Test Data Integrity**
```sql
-- Count records before/after
SELECT 'absensi' as table_name, COUNT(*) as records FROM absensi
UNION ALL
SELECT 'register', COUNT(*) FROM register
UNION ALL
SELECT 'pengajuan_izin', COUNT(*) FROM pengajuan_izin;

-- Check backup tables
SELECT COUNT(*) FROM komponen_gaji_old;
SELECT COUNT(*) FROM riwayat_gaji_old;
```

---

## ⚠️ **POTENTIAL ISSUES & SOLUTIONS**

### Issue 1: Foreign Key Constraints
**Problem:** FK may fail if referenced data doesn't exist

**Solution:**
```sql
-- Check for orphaned records
SELECT a.* FROM absensi a 
LEFT JOIN register r ON a.user_id = r.id 
WHERE r.id IS NULL;

-- Clean up if needed
DELETE FROM absensi WHERE user_id NOT IN (SELECT id FROM register);
```

### Issue 2: Kolom Gaji di Register Belum Ada
**Problem:** Migration add gaji_pokok to register, tapi data kosong

**Solution:**
```sql
-- Populate from komponen_gaji_old
UPDATE register r
JOIN komponen_gaji_old kg ON r.id = kg.register_id
SET 
    r.gaji_pokok = kg.gaji_pokok,
    r.tunjangan_transport = kg.tunjangan_transport,
    r.tunjangan_makan = kg.tunjangan_makan
WHERE kg.register_id = r.id;
```

### Issue 3: Enum Values Conflict
**Problem:** Existing enum values may not match new schema

**Solution:**
```sql
-- Check existing values
SELECT DISTINCT status_lokasi FROM absensi;
SELECT DISTINCT status FROM pengajuan_izin;

-- Verify no data loss
SELECT * FROM absensi WHERE status_lokasi NOT IN ('Valid', 'Tidak Valid');
```

---

## 🔒 **ROLLBACK PLAN**

Jika migration gagal atau ada masalah:

```sql
-- 1. Restore from backup
mysql -u root aplikasi < aplikasi_backup_20251103.sql

-- 2. Or manual rollback (see migration_shift_system.sql footer)
```

---

## 📊 **POST-MIGRATION CHECKLIST**

- [ ] All new tables created successfully
- [ ] Existing tables altered without data loss
- [ ] Backup tables (`*_old`) exist
- [ ] Views created and functioning
- [ ] Foreign keys intact
- [ ] Record counts match pre-migration
- [ ] Sample queries work correctly
- [ ] No orphaned records
- [ ] Application still functions with old code (backward compatible)

---

## 🚀 **NEXT STEPS AFTER MIGRATION**

1. **Update PHP Code:**
   - [ ] Update `proses_absensi.php` untuk use shift_schedule_id
   - [ ] Add overwork detection logic
   - [ ] Add shift missed tracking

2. **Create New Features:**
   - [ ] Shift schedule calendar UI
   - [ ] Shift assignment interface
   - [ ] Email notification system
   - [ ] Slip gaji auto-generation

3. **Testing:**
   - [ ] Test overwork calculation
   - [ ] Test shift assignment workflow
   - [ ] Test email confirmations
   - [ ] Test slip gaji generation

4. **Documentation:**
   - [ ] Update user manual
   - [ ] Create admin guide for shift management
   - [ ] Document new business logic

---

## 📞 **Support**

Jika ada pertanyaan atau masalah selama migration:

1. **Check logs:**
   ```bash
   tail -f /Applications/XAMPP/xamppfiles/logs/error_log
   ```

2. **Verify database:**
   ```sql
   SHOW FULL TABLES;
   SHOW TABLE STATUS;
   ```

3. **Restore from backup** jika critical issue

---

**Last Updated:** November 3, 2025  
**Version:** 1.0.0  
**Status:** ✅ Ready for Migration
