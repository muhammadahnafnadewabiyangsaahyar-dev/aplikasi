# 🎯 Quick Reference - Import CSV System

## 🚀 3 Import Modes

### 1️⃣ SKIP Mode (Safest) - `import_csv_enhanced.php`
```
✅ Recommended untuk: Import pegawai baru
📌 Behavior:
   - Data existing → SKIP ❌
   - Data baru → INSERT ✅
🔒 Safety: ⭐⭐⭐⭐⭐
```

### 2️⃣ UPDATE Mode (Advanced) - `import_csv_enhanced.php`
```
⚠️ Recommended untuk: Bulk update data existing
📌 Behavior:
   - Data existing → UPDATE 🔄
   - Data baru → INSERT ✅
🔒 Safety: ⭐⭐⭐
```

### 3️⃣ SMART Mode (Intelligent) - `import_csv_smart.php`
```
🎯 Recommended untuk: Mixed data dengan conflict
📌 Behavior:
   - 100% match → Auto overwrite 🟢
   - Conflict → User decide 🟡
   - Data baru → Auto insert 🔵
🔒 Safety: ⭐⭐⭐⭐
```

---

## 📝 CSV Format

```csv
No;Nama Lengkap;Posisi
1;Ahmad Rifai;Barista
2;Siti Nurhaliza;Manager
3;Budi Santoso;HR
```

**Rules:**
- Delimiter: **;** (semicolon)
- Header: Required (akan di-skip)
- Column 2: Nama Lengkap (required)
- Column 3: Posisi (required)
- Encoding: UTF-8

---

## 🔐 Role Auto-Detection

| Posisi (Example) | Role | Level |
|------------------|------|-------|
| Owner, CEO, Director | admin | ⭐⭐⭐ |
| Manager, Supervisor, Team Lead | supervisor | ⭐⭐ |
| Barista, Kasir, Staff, HR | user | ⭐ |
| Unknown/Tidak ada | user | ⭐ (fallback) |

**Source:** Table `posisi_jabatan`
**Function:** `getRoleByPosisiFromDB($pdo, $posisi)`

---

## 🎨 Status Colors

| Color | Status | Meaning |
|-------|--------|---------|
| 🟢 Green | IMPORTED | Data baru berhasil diimport |
| 🟡 Yellow | UPDATED | Data existing berhasil diupdate |
| 🔴 Red | SKIPPED | Data existing di-skip |
| 🔵 Blue | NEW | Data baru akan diinsert |
| ⚫ Black | ERROR | Error database/validation |

---

## ⚡ Quick Commands

### Check Posisi Jabatan:
```sql
SELECT * FROM posisi_jabatan;
```

### Add New Posisi:
```sql
INSERT INTO posisi_jabatan (nama_posisi, role) 
VALUES ('Posisi Baru', 'user');
```

### Check Whitelist:
```sql
SELECT * FROM pegawai_whitelist;
```

### Check Duplicate:
```sql
SELECT nama_lengkap, COUNT(*) 
FROM pegawai_whitelist 
GROUP BY nama_lengkap 
HAVING COUNT(*) > 1;
```

---

## 🐛 Troubleshooting

| Error | Cause | Solution |
|-------|-------|----------|
| Invalid CSRF token | Token expired | Refresh page |
| Already exists | Duplicate nama | Use UPDATE or SMART mode |
| Role tidak ditemukan | Posisi baru | Add to posisi_jabatan |
| File not CSV | Wrong format | Use .csv or .txt |
| Upload error | File too large | Check php.ini upload_max_filesize |

---

## 📊 Files Quick Access

| File | Purpose | URL |
|------|---------|-----|
| `whitelist.php` | Main whitelist management | `/whitelist.php` |
| `import_csv_enhanced.php` | Mode 1 & 2 (SKIP/UPDATE) | `/import_csv_enhanced.php` |
| `import_csv_smart.php` | Mode 3 (Smart conflict) | `/import_csv_smart.php` |
| `functions_role.php` | Core role function | (include only) |

---

## 🎓 Usage Guide

### For New Pegawai Import:
1. Go to `import_csv_enhanced.php`
2. Select mode: **SKIP**
3. Upload CSV
4. ✅ Done!

### For Bulk Update:
1. Go to `import_csv_enhanced.php`
2. Select mode: **UPDATE**
3. Upload CSV
4. ⚠️ Careful! Data will be overwritten

### For Mixed Data:
1. Go to `import_csv_smart.php`
2. Upload CSV (Step 1: Analyze)
3. Review conflicts (Step 2: Decide)
4. Process (Step 3: Complete)

---

## 🔒 Security Checklist

- [x] CSRF token on all forms
- [x] SQL injection prevention (prepared statements)
- [x] File type validation (.csv, .txt only)
- [x] Admin-only access
- [x] Session-based authentication
- [x] Error logging (server-side)

---

## 📞 Support

**Error Logs:** Check `php_error.log`
**Documentation:** 
- `IMPORT_CSV_GUIDE.md` - User guide
- `FINAL_IMPORT_TESTING_GUIDE.md` - Testing
- `FINAL_SUMMARY.md` - Complete overview

**Contact:** Admin system

---

**Version:** 1.0.0 | **Status:** ✅ Production Ready
