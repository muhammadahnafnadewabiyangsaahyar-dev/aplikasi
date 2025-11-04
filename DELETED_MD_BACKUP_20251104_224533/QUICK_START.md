# 🚀 QUICK START GUIDE
## Shift Management Enhancement - Get Started in 15 Minutes

### ⚡ Prerequisites Check
```bash
# Check if you have everything needed
php -v        # Should be 7.4+
mysql --version  # Should be MariaDB 10.4+ or MySQL 8+
```

---

## 📋 Step-by-Step Implementation

### STEP 1: BACKUP (2 minutes) ⚠️ WAJIB!

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi

# Backup database
mysqldump -u root -p aplikasi > backup_pre_migration_$(date +%Y%m%d_%H%M%S).sql

# Verify backup file exists
ls -lh backup_*.sql
```

✅ **Success indicator:** You should see a .sql file with today's date

---

### STEP 2: RUN MIGRATION (3 minutes)

**Option A: Command Line (Recommended)**
```bash
mysql -u root -p aplikasi < migration_shift_enhancement.sql
```

**Option B: phpMyAdmin**
1. Open http://localhost/phpmyadmin
2. Select database `aplikasi`
3. Click "SQL" tab
4. Click "Choose File" and select `migration_shift_enhancement.sql`
5. Click "Go"

✅ **Success indicator:** You should see "Query OK" messages, no errors

---

### STEP 3: VERIFY MIGRATION (2 minutes)

```sql
-- Open MySQL client
mysql -u root -p aplikasi

-- Check new tables (should show 4 new tables)
SHOW TABLES LIKE '%shift%';
SHOW TABLES LIKE '%libur%';
SHOW TABLES LIKE '%komponen_gaji_detail%';

-- Check holidays (should show 16 records)
SELECT COUNT(*) as total_holidays FROM libur_nasional;

-- Check views (should show 3 views)
SHOW FULL TABLES WHERE Table_type = 'VIEW';

-- Check procedures (should show 3 procedures)
SHOW PROCEDURE STATUS WHERE Db = 'aplikasi';

-- Exit
exit;
```

✅ **Success indicators:**
- [ ] 4 new tables exist
- [ ] 16 holidays in libur_nasional
- [ ] 3 views created
- [ ] 3 stored procedures created

---

### STEP 4: POPULATE INITIAL DATA (3 minutes)

```sql
mysql -u root -p aplikasi

-- Set salary for all existing users
-- ADJUST THESE VALUES according to your company policy!
UPDATE register 
SET 
    gaji_pokok = 3500000,
    tunjangan_transport = 500000,
    tunjangan_makan = 300000,
    tunjangan_jabatan = 0,
    tarif_overwork_per_jam = 20000
WHERE role = 'user';

-- Set higher salary for admin
UPDATE register 
SET 
    gaji_pokok = 5000000,
    tunjangan_transport = 700000,
    tunjangan_makan = 500000,
    tunjangan_jabatan = 1000000,
    tarif_overwork_per_jam = 30000
WHERE role = 'admin';

-- Verify
SELECT id, nama_lengkap, posisi, gaji_pokok, tunjangan_transport, tarif_overwork_per_jam 
FROM register 
WHERE gaji_pokok > 0;

exit;
```

✅ **Success indicator:** All users should have salary components set

---

### STEP 5: TEST DATABASE FUNCTIONS (3 minutes)

```sql
mysql -u root -p aplikasi

-- Test view jadwal shift
SELECT * FROM v_jadwal_shift_harian LIMIT 5;

-- Test view absensi
SELECT * FROM v_absensi_dengan_shift LIMIT 5;

-- Test assign shift procedure
CALL sp_assign_shift(
    1,              -- user_id (use valid user_id from register)
    10,             -- cabang_id (use valid cabang_id from cabang)
    '2025-01-20',   -- tanggal_shift
    1               -- created_by (admin user_id)
);

-- Check if assignment created
SELECT * FROM shift_assignments ORDER BY id DESC LIMIT 1;

-- Test konfirmasi shift procedure
CALL sp_konfirmasi_shift(
    LAST_INSERT_ID(),  -- assignment_id from previous insert
    'confirmed',       -- status
    'Siap bekerja'     -- catatan
);

-- Verify confirmation
SELECT * FROM v_jadwal_shift_harian WHERE id = LAST_INSERT_ID();

exit;
```

✅ **Success indicators:**
- [ ] Views return data
- [ ] Shift assignment created successfully
- [ ] Shift confirmation works

---

### STEP 6: LINK EXISTING ABSENSI TO SHIFTS (2 minutes)

```sql
mysql -u root -p aplikasi

-- Update existing absensi with shift information
-- This query links absensi to the first matching "pagi" shift for each outlet
UPDATE absensi a
JOIN register r ON a.user_id = r.id
JOIN cabang c ON r.outlet = c.nama_cabang 
SET 
    a.cabang_id = c.id,
    a.jam_masuk_shift = c.jam_masuk,
    a.jam_keluar_shift = c.jam_keluar
WHERE a.cabang_id IS NULL
  AND c.nama_shift = 'pagi'
  AND c.id IN (
      SELECT MIN(id) 
      FROM cabang 
      WHERE nama_cabang = c.nama_cabang 
      AND nama_shift = 'pagi'
  );

-- Verify linked absensi
SELECT COUNT(*) as total_linked FROM absensi WHERE cabang_id IS NOT NULL;

exit;
```

✅ **Success indicator:** Existing absensi records now have cabang_id

---

## 🎉 MIGRATION COMPLETE!

You've successfully migrated your database! Here's what you have now:

### ✅ New Capabilities
- ✨ Shift assignment system
- ✨ Shift confirmation workflow
- ✨ Auto overwork detection
- ✨ Detailed payroll components
- ✨ National holiday tracking

### 📁 Files Created
1. `migration_shift_enhancement.sql` - Database migration script
2. `IMPLEMENTATION_GUIDE.md` - Complete implementation guide
3. `shift_management_quick_reference.html` - Visual reference guide
4. `system_flow_diagram.html` - System flow visualization
5. `SUMMARY.md` - Summary of changes
6. `QUICK_START.md` - This file
7. `README.md` - Updated with new features

---

## 🎯 NEXT STEPS

### Immediate (Required for basic functionality)
1. **Update proses_absensi.php** to link absensi to confirmed shifts
   - Check IMPLEMENTATION_GUIDE.md Phase 4 for code sample

2. **Create jadwal_shift.php** for admin to assign shifts
   - Check IMPLEMENTATION_GUIDE.md Phase 3 for complete code

3. **Create konfirmasi_shift.php** for users to confirm shifts
   - Check IMPLEMENTATION_GUIDE.md Phase 3 for complete code

### Short-term (1-2 weeks)
4. **Build payroll generation UI** (generate_payroll_batch.php)
   - Check IMPLEMENTATION_GUIDE.md Phase 5 for code sample

5. **Test end-to-end flow:**
   - Admin assigns shift
   - User confirms shift
   - User does absensi
   - System detects overwork
   - Admin approves overwork
   - Generate payroll

### Medium-term (1 month)
6. **Add email/WhatsApp notifications**
7. **Build reporting dashboard**
8. **Mobile-responsive UI improvements**

---

## 📖 Documentation Quick Links

### For Database Details
- Open `shift_management_quick_reference.html` in browser
- Visual guide with all tables, columns, and relationships

### For System Flow
- Open `system_flow_diagram.html` in browser
- Complete flow diagrams for all processes

### For Implementation
- Read `IMPLEMENTATION_GUIDE.md`
- Step-by-step with code samples

### For Overview
- Read `SUMMARY.md`
- High-level summary of changes

---

## 🆘 Troubleshooting Quick Fixes

### Problem: Migration fails with foreign key error
```sql
-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;
-- Re-run migration
-- Enable back
SET FOREIGN_KEY_CHECKS = 1;
```

### Problem: Duplicate entry error
```sql
-- Check for existing records
SELECT * FROM shift_assignments WHERE user_id = X AND tanggal_shift = 'YYYY-MM-DD';
-- Delete if needed
DELETE FROM shift_assignments WHERE id = X;
```

### Problem: Trigger not working
```sql
-- Drop and recreate
DROP TRIGGER IF EXISTS tr_absensi_calculate_duration;
-- Then re-run the CREATE TRIGGER part from migration script
```

### Problem: Need to rollback
```bash
# Restore from backup
mysql -u root -p aplikasi < backup_pre_migration_YYYYMMDD_HHMMSS.sql
```

---

## 🧪 Quick Test Scenarios

### Test 1: Assign & Confirm Shift
```sql
-- As admin: Assign shift
CALL sp_assign_shift(7, 10, '2025-01-25', 1);

-- As user: Confirm shift
CALL sp_konfirmasi_shift(LAST_INSERT_ID(), 'confirmed', 'OK');

-- Verify
SELECT * FROM v_jadwal_shift_harian WHERE tanggal_shift = '2025-01-25';
```

### Test 2: Simulate Attendance with Overwork
```sql
-- Insert test absensi with overwork
INSERT INTO absensi (
    user_id, cabang_id, tanggal_absensi,
    waktu_masuk, waktu_keluar,
    jam_masuk_shift, jam_keluar_shift,
    status_lokasi, menit_terlambat, status_keterlambatan
) VALUES (
    7, 10, CURDATE(),
    CONCAT(CURDATE(), ' 07:00:00'),
    CONCAT(CURDATE(), ' 16:00:00'),  -- 1 hour overtime
    '07:00:00', '15:00:00',
    'Valid', 0, 'tepat waktu'
);

-- Check if overwork detected
SELECT * FROM v_absensi_dengan_shift 
WHERE tanggal_absensi = CURDATE() 
AND durasi_overwork_menit > 0;
```

### Test 3: Calculate Payroll for One User
```sql
-- Calculate attendance for user 7, January 2025
CALL sp_hitung_kehadiran_periode(7, 1, 2025, @h, @tr, @tb, @a, @i, @o);

-- See results
SELECT @h as hadir, @tr as telat_ringan, @tb as telat_berat, 
       @a as alfa, @i as izin, @o as overwork_jam;
```

---

## ✅ Success Checklist

Before moving to UI development, ensure:

- [ ] Database migration completed without errors
- [ ] All 4 new tables exist
- [ ] 16 holidays loaded
- [ ] 3 views working
- [ ] 3 stored procedures callable
- [ ] 1 trigger active
- [ ] Salary components set for all users
- [ ] Test shift assignment works
- [ ] Test shift confirmation works
- [ ] Test attendance calculation works
- [ ] Backup file saved safely

---

## 🎓 Learning Resources

### Understand the System
1. Read `SUMMARY.md` first for overview
2. View `system_flow_diagram.html` for visual flow
3. Check `shift_management_quick_reference.html` for database details
4. Read `IMPLEMENTATION_GUIDE.md` for complete guide

### SQL Reference
- Views: `SELECT * FROM v_jadwal_shift_harian`
- Procedures: `CALL sp_assign_shift(...)`
- Tables: `DESCRIBE shift_assignments`

---

## 📞 Need Help?

### Check These First
1. MySQL error log: `/var/log/mysql/error.log`
2. PHP error log: Check XAMPP control panel → Logs
3. Browser console: F12 → Console tab

### Common Issues Solved
- **"Table doesn't exist"** → Re-run migration
- **"Foreign key constraint fails"** → Check parent records exist
- **"Duplicate entry"** → Check unique constraints, delete old record
- **"Procedure not found"** → Re-run procedure creation part

---

**🎉 CONGRATULATIONS!** 

Your database is now ready for shift management! 

Next: Build the UI (see IMPLEMENTATION_GUIDE.md Phase 3-5)

---

**Last Updated:** 2025-01-XX  
**Estimated Setup Time:** 15 minutes  
**Difficulty:** ⭐⭐☆☆☆ (Medium)
