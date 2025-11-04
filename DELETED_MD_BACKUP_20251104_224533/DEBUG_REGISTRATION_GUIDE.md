# Debug Guide - Registrasi Tidak Menambah Data ke Tabel `register`

## Masalah
Proses registrasi tidak menambahkan data ke tabel `register` meskipun form sudah diisi lengkap dan di-submit.

## Debug Logging yang Ditambahkan

Saya telah menambahkan **logging lengkap** pada setiap tahap proses registrasi di `index.php`:

### 1. POST Submit Detection
```
=== REGISTRATION SUBMIT START ===
POST Data: Array(...)
Form data captured: Array(...)
```

### 2. Validation Stage
```
Validation errors: Array(...) // Kosong jika tidak ada error
✅ No validation errors, proceeding to database...
// ATAU
❌ Validation failed, cannot proceed to database
```

### 3. Whitelist Check
```
Checking whitelist for: [Nama]
Whitelist data: Array(...)
✅ Whitelist check passed, status: pending
// ATAU
❌ Name not found in whitelist
❌ Name already registered
```

### 4. Database Transaction
```
✅ Proceeding with registration...
Password hashed successfully
Role assigned: user/admin
Posisi assigned: [Posisi]
Starting transaction...
```

### 5. INSERT to register
```
INSERT Query: INSERT INTO register...
INSERT Params: Array(...)
✅ INSERT to register SUCCESS (New ID: 123)
// ATAU
❌ INSERT to register FAILED
```

### 6. UPDATE whitelist
```
UPDATE Query: UPDATE pegawai_whitelist...
✅ UPDATE pegawai_whitelist SUCCESS (Affected rows: 1)
```

### 7. Transaction Commit
```
✅ Transaction committed successfully
🔄 Redirecting to success page...
=== REGISTRATION SUBMIT END (SUCCESS) ===
```

### 8. Error Scenarios
```
❌ Registrasi INSERT Gagal: [Error Message]
Error Code: [Code]
Error Info: Array(...)
=== REGISTRATION SUBMIT END (FAILED - INSERT ERROR) ===
```

## Cara Menggunakan Debug

### Method 1: Real-time Monitoring (Recommended)

Terminal 1 - Monitor Log:
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
./watch_registration_log.sh
# Pilih option 1
```

Terminal 2 - Test Registrasi:
```
1. Buka browser: http://localhost/aplikasi/index.php
2. Klik "Daftar"
3. Masukkan nama yang ada di whitelist
4. Isi semua field
5. Submit
```

Terminal 1 - Lihat Log Real-time:
```
- Semua tahapan akan terlihat
- Cari ❌ untuk error
- Cari ✅ untuk sukses
```

### Method 2: Manual tail

```bash
tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log | grep "REGISTRATION"
```

### Method 3: Lihat Log Setelah Test

```bash
./watch_registration_log.sh
# Pilih option 2 (lihat 50 baris terakhir)
```

## Troubleshooting Berdasarkan Log

### Scenario 1: Tidak Ada Log "REGISTRATION SUBMIT START"

**Problem**: Form tidak di-submit dengan benar

**Cek**:
1. Apakah tombol "Daftar" diklik?
2. Apakah ada error JavaScript di browser console? (F12 → Console)
3. Apakah form method="POST" dan name="register"?

**Fix**:
```php
// Pastikan di form ada:
<form method="POST" action="index.php">
    ...
    <button type="submit" name="register" class="btn">Daftar</button>
</form>
```

### Scenario 2: Log Berhenti di "Validation errors"

**Problem**: Validasi gagal

**Log Contoh**:
```
Validation errors: Array(
    [no_wa] => No. WhatsApp harus diisi.
)
❌ Validation failed, cannot proceed to database
```

**Fix**:
- Pastikan semua field diisi dengan benar
- Cek format no_wa: `+62 81234567890` (harus ada spasi setelah +62)
- Cek email valid
- Cek password dan confirm password cocok

### Scenario 3: Log Berhenti di "Name not found in whitelist"

**Problem**: Nama tidak ada di tabel `pegawai_whitelist`

**Log Contoh**:
```
Checking whitelist for: John Doe
Whitelist data: 
❌ Name not found in whitelist
```

**Fix**:
```sql
-- Tambahkan nama ke whitelist
INSERT INTO pegawai_whitelist (nama_lengkap, posisi, role, status_registrasi) 
VALUES ('John Doe', 'Staff', 'user', 'pending');
```

### Scenario 4: Log Berhenti di "Name already registered"

**Problem**: Nama sudah terdaftar (status_registrasi = 'terdaftar')

**Log Contoh**:
```
Whitelist data: Array([status_registrasi] => terdaftar)
❌ Name already registered
```

**Fix**:
```sql
-- Reset status registrasi
UPDATE pegawai_whitelist SET status_registrasi = 'pending' WHERE nama_lengkap = 'John Doe';

-- Hapus data dari register (jika perlu re-register)
DELETE FROM register WHERE nama_lengkap = 'John Doe';
```

### Scenario 5: Log Berhenti di "INSERT to register FAILED"

**Problem**: Query INSERT gagal

**Log Contoh**:
```
INSERT Query: INSERT INTO register...
❌ INSERT to register FAILED
❌ Registrasi INSERT Gagal: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'role' in 'field list'
Error Code: 42S22
```

**Possible Causes**:

#### A. Column 'role' tidak ada di tabel register
```sql
-- Cek struktur tabel
DESC register;

-- Tambahkan kolom jika tidak ada
ALTER TABLE register ADD COLUMN role VARCHAR(50) DEFAULT 'user' AFTER username;
```

#### B. Tabel 'register' tidak ada
```sql
-- Cek apakah tabel ada
SHOW TABLES LIKE 'register';

-- Buat tabel jika tidak ada
CREATE TABLE register (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(255) NOT NULL,
    posisi VARCHAR(100),
    outlet VARCHAR(100),
    no_whatsapp VARCHAR(20) UNIQUE,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### C. Duplicate key error
```
Error Code: 1062
❌ Registrasi INSERT Gagal: Duplicate entry 'johndoe' for key 'username'
```

**Fix**: Username/email/no_wa sudah digunakan. Gunakan data lain.

### Scenario 6: INSERT SUCCESS tapi tidak ada di database

**Problem**: Transaction di-rollback setelah INSERT

**Log Contoh**:
```
✅ INSERT to register SUCCESS (New ID: 123)
UPDATE Query: UPDATE pegawai_whitelist...
❌ UPDATE pegawai_whitelist FAILED
Transaction rolled back
```

**Fix**:
```sql
-- Cek apakah nama ada di whitelist
SELECT * FROM pegawai_whitelist WHERE nama_lengkap = 'John Doe';

-- Jika tidak ada, tambahkan
INSERT INTO pegawai_whitelist (nama_lengkap, posisi, role, status_registrasi) 
VALUES ('John Doe', 'Staff', 'user', 'pending');
```

### Scenario 7: Semua SUCCESS tapi tidak redirect

**Problem**: Output sebelum header() atau exit() tidak dipanggil

**Log Contoh**:
```
✅ Transaction committed successfully
🔄 Redirecting to success page...
// Tidak ada log setelah ini, tapi halaman tidak redirect
```

**Cek**:
1. Apakah ada output/echo sebelum header()?
2. Apakah ada error di browser console?
3. Apakah ada pesan error di halaman?

**Fix**: Pastikan tidak ada output sebelum `header("Location: ...")` dan `exit()` dipanggil.

## Expected Normal Flow (Full Log)

Berikut adalah contoh log lengkap untuk registrasi yang berhasil:

```
=== REGISTRATION SUBMIT START ===
POST Data: Array(
    [nama_panjang] => John Doe
    [posisi] => Staff
    [outlet] => Jakarta Pusat
    [no_wa] => +62 81234567890
    [email] => john@example.com
    [username] => johndoe
    [password] => ********
    [confirm_password] => ********
    [register] => 
)
Form data captured: Array(
    [nama_panjang] => John Doe
    [posisi] => Staff
    ...
)
Validation errors: Array()
✅ No validation errors, proceeding to database...
Checking whitelist for: John Doe
Whitelist data: Array(
    [id] => 1
    [nama_lengkap] => John Doe
    [posisi] => Staff
    [role] => user
    [status_registrasi] => pending
)
✅ Whitelist check passed, status: pending
✅ Proceeding with registration...
Password hashed successfully
Role assigned: user
Posisi assigned: Staff
Starting transaction...
INSERT Query: INSERT INTO register (nama_lengkap, posisi, outlet, no_whatsapp, email, password, username, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
INSERT Params: Array(
    [0] => John Doe
    [1] => Staff
    [2] => Jakarta Pusat
    [3] => +62 81234567890
    [4] => john@example.com
    [5] => [HASHED]
    [6] => johndoe
    [7] => user
)
✅ INSERT to register SUCCESS (New ID: 123)
UPDATE Query: UPDATE pegawai_whitelist SET status_registrasi = 'terdaftar' WHERE nama_lengkap = ?
✅ UPDATE pegawai_whitelist SUCCESS (Affected rows: 1)
✅ Transaction committed successfully
🔄 Redirecting to success page...
=== REGISTRATION SUBMIT END (SUCCESS) ===
```

## Quick Commands

### Lihat Error Terakhir
```bash
grep "REGISTRATION" /Applications/XAMPP/xamppfiles/logs/php_error_log | grep "❌" | tail -5
```

### Lihat Registrasi Sukses Terakhir
```bash
grep "REGISTRATION SUBMIT END (SUCCESS)" /Applications/XAMPP/xamppfiles/logs/php_error_log | tail -5
```

### Count Registrasi Hari Ini
```bash
grep "$(date +%Y-%m-%d)" /Applications/XAMPP/xamppfiles/logs/php_error_log | grep "INSERT to register SUCCESS" | wc -l
```

### Export Full Log untuk Support
```bash
./watch_registration_log.sh
# Pilih option 5
# Kirim file registration_debug_[timestamp].log
```

## Common Fixes

### Fix 1: Reset Status Registrasi
```sql
UPDATE pegawai_whitelist SET status_registrasi = 'pending' WHERE nama_lengkap = 'John Doe';
```

### Fix 2: Delete Test Account
```sql
DELETE FROM register WHERE username = 'testuser';
UPDATE pegawai_whitelist SET status_registrasi = 'pending' WHERE nama_lengkap = 'Test User';
```

### Fix 3: Check Table Structure
```sql
-- Cek kolom yang ada
DESC register;

-- Expected columns:
-- id, nama_lengkap, posisi, outlet, no_whatsapp, email, password, username, role
```

### Fix 4: Verify Database Connection
```php
// Di index.php, cek apakah ada error koneksi
error_log("PDO Connection: " . ($pdo ? "OK" : "FAILED"));
```

## Testing Checklist

✅ **Step 1**: Clear log atau catat timestamp sekarang
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log | grep "$(date +%H:%M)"
```

✅ **Step 2**: Clear browser cache (Ctrl+Shift+Del)

✅ **Step 3**: Buka halaman registrasi

✅ **Step 4**: Monitor log di terminal

✅ **Step 5**: Isi form dan submit

✅ **Step 6**: Cek log untuk:
- `REGISTRATION SUBMIT START` ✅
- `No validation errors` ✅
- `Whitelist check passed` ✅
- `INSERT to register SUCCESS` ✅
- `Transaction committed successfully` ✅
- `Redirecting to success page` ✅

✅ **Step 7**: Verify di database
```sql
SELECT * FROM register ORDER BY id DESC LIMIT 1;
```

Jika semua ✅, registrasi berhasil! 🎉

## Next Steps Jika Masih Gagal

1. **Export log lengkap**:
   ```bash
   ./watch_registration_log.sh
   # Pilih option 5
   ```

2. **Cek database structure**:
   ```sql
   SHOW CREATE TABLE register;
   ```

3. **Test manual INSERT**:
   ```sql
   INSERT INTO register (nama_lengkap, posisi, outlet, no_whatsapp, email, password, username, role) 
   VALUES ('Test User', 'Staff', 'Jakarta', '+62 81234567890', 'test@test.com', 'hashed_password', 'testuser', 'user');
   ```
   
   Jika manual INSERT gagal, ada masalah dengan struktur tabel.

4. **Share exported log** dan error message untuk analisis lebih lanjut.
