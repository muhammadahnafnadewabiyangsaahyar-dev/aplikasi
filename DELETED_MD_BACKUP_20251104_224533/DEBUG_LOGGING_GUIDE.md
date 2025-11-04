# Panduan Debug Logging - posisi_jabatan.php

## Lokasi Log File

Log akan tersimpan di file error log XAMPP:
- **macOS**: `/Applications/XAMPP/xamppfiles/logs/php_error_log`
- Atau cek di `phpinfo()` untuk lokasi `error_log`

## Cara Melihat Log Real-time

Buka terminal dan jalankan:
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log
```

Atau untuk filter hanya log posisi_jabatan:
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log | grep "POSISI"
```

## Struktur Log yang Diterapkan

### 1. **PAGE LOAD (Setiap kali halaman dibuka)**
```
=== PAGE LOAD: posisi_jabatan.php ===
Request Method: GET/POST
Request URI: /aplikasi/posisi_jabatan.php?edit=1
GET params: Array(...)
Session ID: ...
User ID: ...
User Role: ...
✅ ACCESS GRANTED / ❌ ACCESS DENIED
```

### 2. **FETCH EDIT DATA (Saat tombol Edit diklik)**
```
=== FETCHING EDIT DATA FOR POSISI ID: 1 ===
Edit Data: Array(
    [id] => 1
    [nama_posisi] => Manager
    [role_posisi] => admin
)
=== END FETCHING EDIT DATA ===
```

### 3. **FORM EDIT VALUES (Nilai yang ditampilkan di form)**
```
=== FORM EDIT VALUES ===
ID Posisi: 1
Nama Posisi: Manager
Role Posisi: admin
=== END FORM EDIT VALUES ===
```

### 4. **POST OPERATION (Saat form di-submit)**
```
=== POSISI JABATAN POST START ===
POST Data: Array(
    [csrf_token] => ...
    [id_posisi] => 1
    [nama_posisi] => Manager Updated
    [role_posisi] => admin
)
Session Token: abc123...
POST Token: abc123...
✅ CSRF TOKEN VALID / ❌ CSRF TOKEN VALIDATION FAILED!
Nama Posisi: Manager Updated
Role Posisi: admin
ID Posisi: 1
Old Posisi Data: Array(...)
✅ Validasi input passed
Duplicate check count: 0
✅ No duplicate, proceeding...
```

### 5. **UPDATE OPERATION**
```
🔄 MODE: UPDATE
Preparing UPDATE query...
Query: UPDATE posisi_jabatan SET nama_posisi=?, role_posisi=? WHERE id=?
Params: ['Manager Updated', 'admin', 1]
✅ UPDATE posisi_jabatan SUCCESS (affected rows: 1)
📊 VERIFY UPDATE - Data after update: Array(
    [id] => 1
    [nama_posisi] => Manager Updated
    [role_posisi] => admin
)
Updating pegawai_whitelist...
✅ UPDATE pegawai_whitelist: 3 rows affected
Updating register...
✅ UPDATE register: 2 rows affected
🔄 REDIRECTING to posisi_jabatan.php?success=update
=== POSISI JABATAN POST END ===
```

### 6. **INSERT OPERATION**
```
➕ MODE: INSERT
Preparing INSERT query...
Query: INSERT INTO posisi_jabatan (nama_posisi, role_posisi) VALUES (?, ?)
Params: ['New Position', 'user']
✅ INSERT posisi_jabatan SUCCESS (ID: 5)
📊 VERIFY INSERT - Data after insert: Array(
    [id] => 5
    [nama_posisi] => New Position
    [role_posisi] => user
)
🔄 REDIRECTING to posisi_jabatan.php?success=add
=== POSISI JABATAN POST END ===
```

### 7. **DELETE OPERATION**
```
=== POSISI JABATAN DELETE START ===
Delete ID: 3
Posisi to delete: Old Position
✅ Proceeding with delete...
Default position exists: YES
✅ Moved 2 employees in whitelist to default position
✅ Moved 1 employees in register to default position
Preparing DELETE query...
Query: DELETE FROM posisi_jabatan WHERE id=?
Params: [3]
✅ DELETE SUCCESS (affected rows: 1)
📊 VERIFY DELETE - Data after delete (should be empty): 
🔄 REDIRECTING to posisi_jabatan.php?success=delete&name=Old Position
=== POSISI JABATAN DELETE END ===
```

### 8. **FETCH DATA FROM DATABASE (Sebelum render tabel)**
```
=== FETCHING POSISI DATA FROM DATABASE ===
Query: SELECT * FROM posisi_jabatan ORDER BY nama_posisi ASC
Total rows fetched: 4
Data: Array(
    [0] => Array([id] => 1, [nama_posisi] => Manager, [role_posisi] => admin)
    [1] => Array([id] => 2, [nama_posisi] => Staff, [role_posisi] => user)
    ...
)
=== END FETCHING POSISI DATA ===
```

### 9. **RENDERING TABLE (Saat tabel ditampilkan)**
```
=== RENDERING TABLE ===
Number of rows to display: 4
Row 0 - ID: 1, Nama: Manager, Role: admin
Row 1 - ID: 2, Nama: Staff, Role: user
Row 2 - ID: 3, Nama: Supervisor, Role: user
Row 3 - ID: 4, Nama: Tidak Ada Posisi, Role: user
=== END RENDERING TABLE ===
```

## Cara Debugging Issue "Data Kembali ke Data Lama"

### Langkah 1: Pastikan data sampai ke server
Cari di log:
```
=== POSISI JABATAN POST START ===
POST Data: Array(...)
```
**Cek**: Apakah `nama_posisi` dan `role_posisi` sesuai dengan yang Anda input?

### Langkah 2: Cek CSRF Token
```
✅ CSRF TOKEN VALID
```
**Jika gagal**: Token tidak cocok, refresh halaman dan coba lagi.

### Langkah 3: Cek Query Execution
```
✅ UPDATE posisi_jabatan SUCCESS (affected rows: 1)
```
**Jika affected rows = 0**: Data tidak berubah di database (mungkin duplikat atau WHERE clause tidak match).

### Langkah 4: Verifikasi Data Setelah Update
```
📊 VERIFY UPDATE - Data after update: Array(...)
```
**Cek**: Apakah data sudah benar setelah UPDATE? Jika ya, tapi di UI masih salah, berarti ada masalah cache.

### Langkah 5: Cek Data yang Di-fetch Untuk Render
```
=== FETCHING POSISI DATA FROM DATABASE ===
Data: Array(...)
```
**Cek**: Apakah data yang di-fetch dari database sudah sesuai dengan update terbaru?

### Langkah 6: Cek Data yang Ditampilkan di Tabel
```
Row 0 - ID: 1, Nama: Manager Updated, Role: admin
```
**Cek**: Apakah data yang di-render sesuai dengan data dari database?

## Troubleshooting Common Issues

### Issue: Data tidak berubah setelah submit

**Log yang perlu dicek**:
1. `POST Data` - Pastikan data dikirim dengan benar
2. `✅ CSRF TOKEN VALID` - Token harus valid
3. `affected rows: X` - Harus > 0 untuk UPDATE/DELETE
4. `📊 VERIFY UPDATE` - Data harus sudah berubah di database

**Solusi**:
- Jika affected rows = 0, cek apakah ID yang di-update benar
- Jika data tidak berubah di VERIFY, cek koneksi database atau transaksi
- Clear browser cache (Ctrl+F5 atau Cmd+Shift+R)

### Issue: Token CSRF gagal

**Log**: `❌ CSRF TOKEN VALIDATION FAILED!`

**Solusi**:
- Refresh halaman dan coba lagi
- Pastikan tidak ada multiple tab yang submit bersamaan
- Cek apakah session masih aktif

### Issue: Redirect tidak terjadi

**Log**: Tidak ada log `🔄 REDIRECTING`

**Solusi**:
- Cek apakah ada output sebelum `ob_start()`
- Cek apakah ada error yang mencegah redirect
- Pastikan `exit` dipanggil setelah `header()`

## Tips

1. **Filter log berdasarkan emoji**:
   ```bash
   tail -f /path/to/error.log | grep "✅"  # Lihat hanya operasi sukses
   tail -f /path/to/error.log | grep "❌"  # Lihat hanya error
   ```

2. **Simpan log untuk analisis**:
   ```bash
   grep "POSISI" /path/to/error.log > posisi_debug.log
   ```

3. **Monitor real-time dengan timestamp**:
   ```bash
   tail -f /path/to/error.log | awk '{print strftime("%H:%M:%S"), $0}'
   ```

## Contoh Skenario Lengkap

### Skenario: Edit posisi "Staff" menjadi "Staff Senior" dengan role "admin"

**Expected Log Flow**:
```
1. PAGE LOAD (GET /posisi_jabatan.php?edit=2)
2. FETCHING EDIT DATA FOR POSISI ID: 2
3. FORM EDIT VALUES (Staff, user)
4. [User mengubah form dan submit]
5. POST START (POST data diterima)
6. CSRF TOKEN VALID
7. Validasi passed
8. MODE: UPDATE
9. UPDATE SUCCESS (affected rows: 1)
10. VERIFY UPDATE (Staff Senior, admin) ✅ Data sudah benar
11. UPDATE pegawai_whitelist (X rows affected)
12. UPDATE register (Y rows affected)
13. REDIRECTING to success=update
14. PAGE LOAD (GET /posisi_jabatan.php?success=update)
15. FETCHING POSISI DATA (semua data terbaru)
16. RENDERING TABLE (Staff Senior ditampilkan dengan role admin) ✅
```

Jika di langkah 16 masih menampilkan "Staff" dengan role "user", cek log dari langkah 10 dan 15.

## Menonaktifkan Debug

Untuk production, comment out semua `error_log()` atau tambahkan di awal file:
```php
define('DEBUG_MODE', false);
if (DEBUG_MODE) {
    error_log(...);
}
```
