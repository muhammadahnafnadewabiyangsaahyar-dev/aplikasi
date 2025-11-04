# 🧪 TESTING GUIDE - CASCADE DELETE WHITELIST

## ✅ IMPLEMENTASI SELESAI!

Sistem sekarang sudah diupdate dengan **CASCADE DELETE**:
- Hapus pegawai di whitelist → otomatis hapus akun + foto + TTD + gaji

## 📋 PANDUAN TESTING

### 🔧 PERSIAPAN:

1. **Backup sudah dibuat:**
   ```
   /Applications/XAMPP/xamppfiles/htdocs/aplikasi/whitelist.php.backup_before_cascade
   ```

2. **File dimodifikasi:**
   - `whitelist.php` - handler hapus dengan cascade delete

3. **Tidak ada file yang dihapus:**
   - `view_user.php` - tetap ada & berfungsi
   - `delete_user.php` - tetap ada & berfungsi

---

## 🧪 TEST CASE 1: Hapus Pegawai Dengan Akun

### Langkah:
1. Login sebagai admin
2. Buka http://localhost/aplikasi/whitelist.php
3. Pilih pegawai yang **status_registrasi = "terdaftar"** (sudah punya akun)
4. Klik link "Hapus"
5. Confirm dialog: klik OK

### Expected Result:
```
✅ URL: http://localhost/aplikasi/whitelist.php?success=Pegawai+dan+akun+berhasil+dihapus.
✅ Notifikasi sukses muncul
✅ Pegawai hilang dari tabel whitelist
✅ Data terhapus dari database:
   - pegawai_whitelist: DELETED
   - register: DELETED
   - komponen_gaji: DELETED
✅ File terhapus:
   - uploads/foto_profil/[nama]_*.png/jpg: DELETED
   - uploads/tanda_tangan/[nama]_*.png/jpg: DELETED
```

### Verifikasi Database:
```sql
-- Cek pegawai tidak ada di whitelist
SELECT * FROM pegawai_whitelist WHERE nama_lengkap = 'NAMA_PEGAWAI';
-- Result: Empty set

-- Cek akun tidak ada di register
SELECT * FROM register WHERE nama_lengkap = 'NAMA_PEGAWAI';
-- Result: Empty set

-- Cek komponen gaji tidak ada
SELECT * FROM komponen_gaji WHERE jabatan = 'NAMA_PEGAWAI';
-- Result: Empty set
```

### Verifikasi File:
```bash
ls -la uploads/foto_profil/ | grep "NAMA_PEGAWAI"
ls -la uploads/tanda_tangan/ | grep "NAMA_PEGAWAI"
# Result: No such file (sudah terhapus)
```

---

## 🧪 TEST CASE 2: Hapus Pegawai Tanpa Akun

### Langkah:
1. Login sebagai admin
2. Buka http://localhost/aplikasi/whitelist.php
3. Tambah pegawai baru (status: "pending" - belum registrasi)
   - Nama: "Test User Pending"
   - Posisi: "Staff"
4. Klik link "Hapus" pada pegawai tersebut
5. Confirm dialog: klik OK

### Expected Result:
```
✅ URL: http://localhost/aplikasi/whitelist.php?success=Pegawai+berhasil+dihapus+dari+whitelist.
✅ Notifikasi sukses muncul
✅ Pegawai hilang dari tabel whitelist
✅ Tidak ada error (meskipun akun tidak ditemukan)
✅ Data terhapus dari database:
   - pegawai_whitelist: DELETED
   - register: TIDAK ADA (OK - karena belum registrasi)
   - komponen_gaji: TIDAK ADA (OK)
```

### Verifikasi Database:
```sql
SELECT * FROM pegawai_whitelist WHERE nama_lengkap = 'Test User Pending';
-- Result: Empty set (sudah terhapus)
```

---

## 🧪 TEST CASE 3: CSRF Protection

### Langkah:
1. Login sebagai admin
2. Buka http://localhost/aplikasi/whitelist.php
3. Inspect element pada link "Hapus"
4. Copy URL hapus (dengan CSRF token):
   ```
   http://localhost/aplikasi/whitelist.php?hapus_nama=John+Doe&csrf=abc123xyz
   ```
5. **Logout** dari admin
6. **Login lagi** (CSRF token berubah)
7. Paste URL lama di browser
8. Enter

### Expected Result:
```
❌ URL: http://localhost/aplikasi/whitelist.php?error=Invalid+CSRF+token.
❌ Error muncul: "Invalid CSRF token"
✅ Pegawai TIDAK terhapus (protected)
✅ Data tetap ada di database
```

---

## 🧪 TEST CASE 4: Transaction Rollback (Error Handling)

### Langkah:
1. Simulasi error database (disconnect MySQL)
   ```bash
   sudo /Applications/XAMPP/xamppfiles/bin/mysql.server stop
   ```
2. Login sebagai admin (sebelum MySQL stop)
3. Buka http://localhost/aplikasi/whitelist.php
4. Coba hapus pegawai
5. Start MySQL lagi:
   ```bash
   sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start
   ```

### Expected Result:
```
❌ Error message muncul (connection lost)
✅ Transaction rollback otomatis
✅ Data TIDAK terhapus sebagian
✅ Database integrity terjaga
```

---

## 🧪 TEST CASE 5: Hapus File (Foto & TTD)

### Langkah:
1. Login sebagai user yang punya foto profil & tanda tangan
2. Upload foto profil di profile.php
3. Upload tanda tangan di profile.php
4. Logout
5. Login sebagai admin
6. Buka whitelist.php
7. Hapus user tersebut

### Expected Result:
```
✅ Pegawai & akun terhapus
✅ File foto profil terhapus dari uploads/foto_profil/
✅ File tanda tangan terhapus dari uploads/tanda_tangan/
✅ Tidak ada orphan files (file tanpa owner)
```

### Verifikasi:
```bash
# Cek file sebelum hapus
ls -la uploads/foto_profil/ | grep "user_id"
ls -la uploads/tanda_tangan/ | grep "user_id"

# Hapus user via whitelist

# Cek file sesudah hapus (harus hilang)
ls -la uploads/foto_profil/ | grep "user_id"
ls -la uploads/tanda_tangan/ | grep "user_id"
# Result: No such file
```

---

## 🧪 TEST CASE 6: Backward Compatibility (view_user.php)

### Langkah:
1. Login sebagai admin
2. Buka http://localhost/aplikasi/view_user.php
3. Hapus user via "Hapus" button di view_user
4. Confirm

### Expected Result:
```
✅ User terhapus dari register table
✅ Foto & TTD terhapus (via delete_user.php)
✅ Fungsi tetap bekerja normal
✅ Tidak ada breaking changes
```

**Note:** Hapus via `view_user.php` TIDAK hapus dari `pegawai_whitelist` (by design - untuk edge cases).

---

## 📊 COMPARISON TABLE:

| Aksi | Sebelum Update | Sesudah Update |
|------|---------------|----------------|
| Hapus di whitelist.php | ❌ Hanya hapus whitelist<br>❌ Akun masih ada<br>❌ File masih ada | ✅ Hapus whitelist<br>✅ Hapus akun<br>✅ Hapus file<br>✅ Hapus gaji |
| Hapus di view_user.php | ✅ Hapus akun<br>✅ Hapus file<br>❌ Whitelist masih ada | ✅ Hapus akun<br>✅ Hapus file<br>⚠️ Whitelist masih ada (by design) |
| Data Consistency | ❌ Bisa inconsistent | ✅ Always consistent |

---

## 🔄 ROLLBACK (Jika Ada Masalah):

### 1. Restore File Backup:
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
cp whitelist.php.backup_before_cascade whitelist.php
```

### 2. Restart Apache:
```bash
sudo /Applications/XAMPP/xamppfiles/bin/apachectl restart
```

### 3. Test Lagi:
```
http://localhost/aplikasi/whitelist.php
```

---

## 📋 CHECKLIST TESTING:

Setelah testing semua test case, centang checklist ini:

- [ ] Test Case 1: Hapus pegawai dengan akun ✅
- [ ] Test Case 2: Hapus pegawai tanpa akun ✅
- [ ] Test Case 3: CSRF protection ✅
- [ ] Test Case 4: Transaction rollback ✅
- [ ] Test Case 5: Hapus file (foto & TTD) ✅
- [ ] Test Case 6: Backward compatibility ✅
- [ ] Verifikasi database consistency ✅
- [ ] Verifikasi file cleanup ✅
- [ ] Error handling tested ✅
- [ ] No breaking changes ✅

---

## 🐛 TROUBLESHOOTING:

### Problem 1: Error "Cannot start transaction"
**Cause:** Previous transaction not closed
**Fix:**
```php
if ($pdo->inTransaction()) {
    $pdo->rollBack();
}
```
**Status:** ✅ Already handled in code

### Problem 2: File tidak terhapus
**Cause:** Path salah atau permission issue
**Check:**
```bash
ls -la uploads/foto_profil/
ls -la uploads/tanda_tangan/
# Check owner & permission
```
**Fix:**
```bash
chmod 755 uploads/foto_profil/
chmod 755 uploads/tanda_tangan/
```

### Problem 3: Akun tidak terhapus
**Cause:** nama_lengkap tidak match
**Check:**
```sql
SELECT nama_lengkap FROM pegawai_whitelist WHERE nama_lengkap LIKE '%John%';
SELECT nama_lengkap FROM register WHERE nama_lengkap LIKE '%John%';
-- Cek apakah nama exact match (case sensitive, spasi, dll)
```

---

## 📚 CODE EXPLANATION:

### Transaction Flow:
```
BEGIN TRANSACTION
  ↓
1. Query register untuk ambil data file
  ↓
2. Unlink foto_profil (jika ada)
  ↓
3. Unlink tanda_tangan (jika ada)
  ↓
4. DELETE FROM register
  ↓
5. DELETE FROM pegawai_whitelist
  ↓
6. DELETE FROM komponen_gaji
  ↓
COMMIT
```

### Error Handling:
```
TRY
  BEGIN TRANSACTION
    ... operations ...
  COMMIT
CATCH (PDOException)
  ROLLBACK (if in transaction)
  Show error message
```

---

## ✅ SUCCESS CRITERIA:

Implementasi dianggap **SUCCESS** jika:

1. ✅ Test Case 1-6 semua PASS
2. ✅ Tidak ada data inconsistency
3. ✅ Tidak ada orphan files
4. ✅ Error handling berfungsi (rollback)
5. ✅ CSRF protection berfungsi
6. ✅ Backward compatibility terjaga
7. ✅ Tidak ada breaking changes
8. ✅ Performance tidak menurun

---

## 🎯 NEXT STEPS (Setelah Testing):

### 1. **Cleanup Debug Logging** (Optional)
Hapus debug logging di whitelist.php:
```php
// HAPUS BARIS INI:
error_log("POST received: " . print_r($_POST, true));
error_log("HAPUS HANDLER: ...");
error_log("CATCH-ALL: ...");
```

### 2. **Update Dokumentasi**
Update PANDUAN_KLIEN.md dengan fitur baru:
```markdown
### 🆕 Fitur Baru: Cascade Delete
- Hapus pegawai di whitelist → otomatis hapus akun
- Data selalu sinkron
- Tidak perlu hapus manual di 2 tempat
```

### 3. **Notify Users**
Informasikan ke admin/user tentang perubahan:
```
"Ketika Anda hapus pegawai di Whitelist, 
akunnya juga akan otomatis terhapus."
```

---

📅 **Date Implemented:** 2025-11-03  
🎯 **Feature:** CASCADE DELETE on whitelist  
✅ **Status:** READY FOR TESTING  
🔐 **Security:** Transaction + CSRF + Rollback  
📁 **Backup:** whitelist.php.backup_before_cascade  

---

**SILAKAN TESTING! 🚀**

Jika semua test case PASS, maka implementasi SUKSES! ✅
Jika ada masalah, rollback ke backup dan report error.
