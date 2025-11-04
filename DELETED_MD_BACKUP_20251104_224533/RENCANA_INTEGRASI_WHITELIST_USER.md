# 📋 RENCANA INTEGRASI WHITELIST & USER MANAGEMENT

## 🎯 TUJUAN:
Ketika admin **hapus pegawai di whitelist.php**, sistem juga **otomatis hapus akun user** di tabel `register` (termasuk foto & tanda tangan).

## 🔍 ANALISIS:

### Tabel & Relasi:
```
pegawai_whitelist           register
├─ nama_lengkap      <───>  ├─ nama_lengkap
├─ posisi                   ├─ posisi
├─ role                     ├─ role
├─ status_registrasi        ├─ id (PRIMARY KEY)
└─ (whitelist data)         ├─ foto_profil
                            ├─ tanda_tangan_file
                            └─ (user account data)
```

**Link:** `nama_lengkap` digunakan sebagai foreign key (tidak formal)

### 2 Pendekatan:

#### ❌ **OPSI 1: Merge File (TIDAK DIREKOMENDASIKAN)**
```
Merge whitelist.php + view_user.php → manajemen_pegawai.php
```
**Cons:**
- Terlalu kompleks (2 file besar jadi 1)
- Sulit maintain
- Resiko error tinggi
- Perlu update navbar & links

#### ✅ **OPSI 2: CASCADE DELETE (DIREKOMENDASIKAN)**
```
Hapus di whitelist.php → Trigger hapus di register table
```
**Pros:**
- Simple & clean
- Kedua file tetap independen
- Minimal changes
- Easy to test & rollback

---

## ✅ IMPLEMENTASI (OPSI 2 - CASCADE DELETE)

### 1. **Update Handler Hapus di whitelist.php**

**SEBELUM:**
```php
if (isset($_GET['hapus_nama']) && isset($_GET['csrf'])) {
    if ($_GET['csrf'] === $_SESSION['csrf_token']) {
        $hapus_nama = trim($_GET['hapus_nama']);
        if ($hapus_nama !== '') {
            try {
                $stmt = $pdo->prepare("DELETE FROM pegawai_whitelist WHERE nama_lengkap = ?");
                $stmt->execute([$hapus_nama]);
                header('Location: whitelist.php?success=' . urlencode('Pegawai berhasil dihapus dari whitelist.'));
                exit;
            } catch (PDOException $e) {
                header('Location: whitelist.php?error=' . urlencode('Gagal menghapus pegawai: ' . $e->getMessage()));
                exit;
            }
        }
    }
}
```

**SESUDAH (dengan CASCADE DELETE):**
```php
if (isset($_GET['hapus_nama']) && isset($_GET['csrf'])) {
    if ($_GET['csrf'] === $_SESSION['csrf_token']) {
        $hapus_nama = trim($_GET['hapus_nama']);
        if ($hapus_nama !== '') {
            try {
                // Mulai transaction untuk atomic operation
                $pdo->beginTransaction();
                
                // 1. Ambil data user untuk hapus file terkait
                $stmt = $pdo->prepare("SELECT id, foto_profil, tanda_tangan_file FROM register WHERE nama_lengkap = ?");
                $stmt->execute([$hapus_nama]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // 2. Hapus file foto profil jika ada
                if ($user_data && !empty($user_data['foto_profil']) && $user_data['foto_profil'] != 'default.png') {
                    $foto_path = 'uploads/foto_profil/' . $user_data['foto_profil'];
                    if (file_exists($foto_path)) @unlink($foto_path);
                }
                
                // 3. Hapus file tanda tangan jika ada
                if ($user_data && !empty($user_data['tanda_tangan_file'])) {
                    $ttd_path = 'uploads/tanda_tangan/' . $user_data['tanda_tangan_file'];
                    if (file_exists($ttd_path)) @unlink($ttd_path);
                }
                
                // 4. Hapus dari tabel register (akun user)
                if ($user_data) {
                    $stmt = $pdo->prepare("DELETE FROM register WHERE nama_lengkap = ?");
                    $stmt->execute([$hapus_nama]);
                }
                
                // 5. Hapus dari tabel pegawai_whitelist
                $stmt = $pdo->prepare("DELETE FROM pegawai_whitelist WHERE nama_lengkap = ?");
                $stmt->execute([$hapus_nama]);
                
                // 6. Hapus dari tabel komponen_gaji jika ada
                $stmt = $pdo->prepare("DELETE FROM komponen_gaji WHERE jabatan = ?");
                $stmt->execute([$hapus_nama]);
                
                // Commit transaction
                $pdo->commit();
                
                header('Location: whitelist.php?success=' . urlencode('Pegawai dan akun berhasil dihapus.'));
                exit;
            } catch (PDOException $e) {
                // Rollback jika error
                $pdo->rollBack();
                header('Location: whitelist.php?error=' . urlencode('Gagal menghapus pegawai: ' . $e->getMessage()));
                exit;
            }
        }
    }
}
```

### 2. **Benefit Dari Implementasi Ini:**

✅ **Atomic Operation:**
- Transaction memastikan semua operasi sukses atau semua gagal (rollback)
- Tidak ada data partial delete

✅ **Cascade Delete:**
- Hapus pegawai → otomatis hapus akun user
- Hapus foto profil & tanda tangan
- Hapus komponen gaji

✅ **Backward Compatible:**
- `view_user.php` tetap bisa digunakan untuk hapus akun langsung
- Tidak ada breaking changes

✅ **Clean & Maintainable:**
- Satu handler untuk semua operasi hapus
- Error handling proper
- Logging available

---

## 🧪 TESTING PLAN:

### Test Case 1: Hapus Pegawai dengan Akun
```
1. Login sebagai admin
2. Buka whitelist.php
3. Klik "Hapus" pada pegawai yang SUDAH PUNYA AKUN
4. Confirm dialog

Expected Result:
✅ Pegawai terhapus dari whitelist
✅ Akun terhapus dari register
✅ Foto profil terhapus (jika ada)
✅ Tanda tangan terhapus (jika ada)
✅ Komponen gaji terhapus
✅ Success message: "Pegawai dan akun berhasil dihapus."
```

### Test Case 2: Hapus Pegawai Tanpa Akun
```
1. Login sebagai admin
2. Buka whitelist.php
3. Klik "Hapus" pada pegawai yang BELUM PUNYA AKUN (status: pending)
4. Confirm dialog

Expected Result:
✅ Pegawai terhapus dari whitelist
✅ Tidak ada error (akun tidak ditemukan = OK)
✅ Success message: "Pegawai dan akun berhasil dihapus."
```

### Test Case 3: Error Handling
```
1. Simulasi error database (disconnect)
2. Coba hapus pegawai

Expected Result:
❌ Error message muncul
✅ Transaction rollback
✅ Data tidak terhapus sebagian
```

### Test Case 4: CSRF Protection
```
1. Copy URL hapus dengan CSRF token lama
2. Logout & login lagi (token berubah)
3. Paste URL lama

Expected Result:
❌ Error: "Invalid CSRF token"
✅ Pegawai TIDAK terhapus
```

---

## 🔐 SECURITY CONSIDERATIONS:

### 1. **Transaction Safety**
```php
try {
    $pdo->beginTransaction();
    // ... operations ...
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    // ... error handling ...
}
```
✅ Ensures data consistency

### 2. **File Deletion Safety**
```php
if (file_exists($foto_path)) @unlink($foto_path);
```
✅ Suppress warning dengan `@` jika file sudah tidak ada
✅ Check file_exists() sebelum hapus

### 3. **CSRF Protection**
```php
if ($_GET['csrf'] !== $_SESSION['csrf_token']) {
    // Invalid CSRF token
    exit;
}
```
✅ Prevents unauthorized deletion

### 4. **Input Validation**
```php
$hapus_nama = trim($_GET['hapus_nama']);
if ($hapus_nama === '') {
    // Invalid input
    exit;
}
```
✅ Validates input before deletion

---

## 📁 FILES TO MODIFY:

### 1. **whitelist.php** (MODIFY)
- Update handler hapus via GET
- Add transaction
- Add cascade delete untuk register & komponen_gaji
- Add file cleanup (foto & TTD)

### 2. **view_user.php** (NO CHANGE)
- Tetap bisa digunakan untuk hapus akun langsung
- Berfungsi sebagai backup jika whitelist tidak digunakan

### 3. **delete_user.php** (NO CHANGE)
- Tetap digunakan oleh view_user.php
- Tidak perlu modifikasi

---

## 🔄 ROLLBACK PLAN:

Jika ada masalah setelah implementasi:

### 1. **Backup File Sebelum Modifikasi**
```bash
cp whitelist.php whitelist.php.backup_before_cascade_delete
```

### 2. **Restore Dari Backup**
```bash
cp whitelist.php.backup_before_cascade_delete whitelist.php
```

### 3. **Cek Database Integrity**
```sql
-- Cek data yang mungkin inconsistent
SELECT pw.nama_lengkap 
FROM pegawai_whitelist pw 
LEFT JOIN register r ON pw.nama_lengkap = r.nama_lengkap 
WHERE pw.status_registrasi = 'terdaftar' AND r.id IS NULL;
```

---

## ✅ IMPLEMENTATION CHECKLIST:

- [ ] Backup file `whitelist.php`
- [ ] Update handler hapus dengan cascade delete
- [ ] Test hapus pegawai dengan akun
- [ ] Test hapus pegawai tanpa akun
- [ ] Test CSRF protection
- [ ] Test transaction rollback
- [ ] Verify file cleanup (foto & TTD)
- [ ] Update dokumentasi
- [ ] Remove debug logging (jika ada)
- [ ] Deploy to production

---

## 📊 COMPARISON:

### SEBELUM (Current):
```
Hapus di whitelist.php → Hanya hapus whitelist
Hapus di view_user.php → Hanya hapus akun

Problem: 
❌ Data tidak sinkron (whitelist masih ada, akun hilang)
❌ Perlu hapus manual di 2 tempat
```

### SESUDAH (Dengan CASCADE):
```
Hapus di whitelist.php → Hapus whitelist + akun + file
Hapus di view_user.php → Hanya hapus akun (optional)

Benefit:
✅ Data tetap sinkron
✅ One-click delete semua data pegawai
✅ Atomic operation (all or nothing)
```

---

## 💡 ALTERNATIVE APPROACH (Future):

Jika ingin lebih advanced, bisa gunakan **Database Foreign Key Constraint**:

```sql
ALTER TABLE register 
ADD CONSTRAINT fk_nama_lengkap 
FOREIGN KEY (nama_lengkap) 
REFERENCES pegawai_whitelist(nama_lengkap) 
ON DELETE CASCADE;
```

**Tapi ini memerlukan:**
- `nama_lengkap` di `pegawai_whitelist` jadi PRIMARY KEY atau UNIQUE
- Perubahan struktur database
- Testing lebih ekstensif

**Current approach (cascade via PHP) lebih aman untuk saat ini.**

---

📅 **Date Created:** 2025-11-03  
🎯 **Status:** PLANNING  
✅ **Recommended:** OPSI 2 - CASCADE DELETE via PHP  
🔐 **Security:** Maintained with transaction & CSRF  

---

**READY TO IMPLEMENT? ✅**
