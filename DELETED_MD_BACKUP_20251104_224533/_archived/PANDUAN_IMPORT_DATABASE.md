# 🚨 PANDUAN IMPORT DATABASE - PERINGATAN KERAS!

## ⚠️ PERINGATAN UTAMA

File `aplikasi.sql` adalah **BACKUP DATABASE** yang akan **MENGHAPUS SEMUA DATA BARU** jika di-import!

**JANGAN IMPORT DATABASE SAAT:**
- ✖️ Testing registrasi user baru
- ✖️ Development aktif
- ✖️ Ada data penting yang belum di-backup
- ✖️ Masih ada user yang sedang bekerja di sistem

---

## 📋 Kapan Boleh Import Database?

✅ **Setup awal** (first time installation)
✅ **Restore database** setelah corrupt/error
✅ **Pindah server/komputer** (migrasi)
✅ **Rollback** ke kondisi backup tertentu
✅ **Testing** dengan data bersih (setelah backup data aktual)

---

## 🛡️ Fitur Keamanan Baru

### 1. **Halaman Import Khusus** (`import_database.php`)

Halaman ini dilengkapi dengan:

- **⚠️ Peringatan Besar** - Visual yang jelas tentang bahaya import
- **📊 Statistik Data** - Menampilkan jumlah data yang akan terhapus
- **💬 Konfirmasi Teks** - Harus ketik "HAPUS SEMUA DATA" untuk melanjutkan
- **🔒 Double Confirmation** - JavaScript popup konfirmasi sekali lagi
- **📝 Logging** - Semua aktivitas import dicatat di error log
- **🔗 Link Backup** - Reminder untuk backup database dulu

### 2. **Akses Terbatas**

- Hanya **ADMIN** yang bisa mengakses halaman import
- Link di navbar ditandai dengan **warna merah** dan icon **⚠️**
- Session check untuk mencegah akses tidak sah

### 3. **Logging Aktivitas**

Setiap import akan dicatat:
```
🚨 IMPORT DATABASE DIMULAI oleh user: superadmin
📅 Waktu: 2025-11-02 23:54:00
✅ IMPORT SELESAI - Sukses: 150 query, Error: 0
```

---

## 📖 Cara Menggunakan

### **Metode 1: Via Web Interface (RECOMMENDED)**

1. **Login sebagai Admin**
   ```
   http://localhost/aplikasi/login.php
   ```

2. **Klik menu "⚠️ Import DB"** di navbar (warna merah)

3. **Baca peringatan** dengan seksama:
   - Lihat statistik data yang akan terhapus
   - Pastikan sudah backup jika perlu

4. **Ketik konfirmasi**: `HAPUS SEMUA DATA`
   - Harus huruf besar semua
   - Tombol "Import Database" akan aktif

5. **Klik "Import Database"**
   - Akan muncul popup konfirmasi terakhir
   - Klik OK untuk melanjutkan

6. **Tunggu proses selesai**
   - Halaman akan menampilkan hasil import
   - Cek jumlah query yang berhasil/error

### **Metode 2: Via Terminal (Manual)**

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi

# Import manual via MySQL
mysql -u root aplikasi < aplikasi.sql
```

⚠️ **CATATAN**: Metode ini tidak ada proteksi apapun!

---

## 🔄 Backup Database Sebelum Import

### **Script Backup Otomatis**

```bash
# Buat backup dulu
chmod +x backup_database.sh
./backup_database.sh
```

File backup akan tersimpan di:
```
backups/aplikasi_backup_YYYYMMDD_HHMMSS.sql
```

### **Manual Backup via phpMyAdmin**

1. Buka phpMyAdmin
2. Pilih database `aplikasi`
3. Tab "Export"
4. Klik "Go"
5. Simpan file SQL

---

## 📊 Yang Terjadi Saat Import

### **Sebelum Import:**
```
Total Users: 10 user
Total Absensi: 50 record
Total Whitelist: 38 pegawai
Total Izin: 5 izin
```

### **Setelah Import:**
```
Total Users: 3 user (ID 1, 4, 7)
Total Absensi: 24 record
Total Whitelist: 38 pegawai
Total Izin: 4 izin
```

⚠️ **7 user baru yang terdaftar akan HILANG!**

---

## 🎯 Skenario Penggunaan

### **Skenario 1: Testing Registrasi**

**❌ JANGAN:**
```
1. Register user baru
2. Cek di phpMyAdmin
3. Import aplikasi.sql  ← DATA HILANG!
4. User yang baru register hilang
```

**✅ LAKUKAN:**
```
1. Backup database dulu
2. Register user baru
3. Test fitur-fitur
4. Jika ada masalah, restore dari backup
5. JANGAN import aplikasi.sql!
```

### **Skenario 2: Production Reset**

**✅ BOLEH:**
```
1. Backup database production
2. Informasikan ke semua user
3. Pastikan tidak ada transaksi aktif
4. Import aplikasi.sql
5. Restore data penting dari backup jika perlu
```

---

## 🚨 Troubleshooting

### **Problem: Data Hilang Setelah Registrasi**

**Penyebab:**
- Import `aplikasi.sql` setelah registrasi
- File SQL berisi `DROP TABLE` yang menghapus semua data

**Solusi:**
1. ✅ Jangan import SQL saat testing
2. ✅ Gunakan script monitoring: `./watch_check_register.sh`
3. ✅ Backup rutin dengan `./backup_database.sh`
4. ✅ Cek log: `/Applications/XAMPP/xamppfiles/logs/error_log`

### **Problem: Ingin Rollback Setelah Import**

**Solusi:**
```bash
# Restore dari backup
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
mysql -u root aplikasi < backups/aplikasi_backup_YYYYMMDD_HHMMSS.sql
```

### **Problem: Import Error "Access Denied"**

**Solusi:**
- Pastikan login sebagai admin
- Clear browser cache
- Logout dan login ulang

---

## 📝 Checklist Sebelum Import

**Sebelum klik tombol "Import Database", pastikan:**

- [ ] Sudah backup database terbaru
- [ ] Semua user sudah logout/tidak aktif
- [ ] Tidak ada transaksi penting yang sedang berjalan
- [ ] Sudah informasikan ke tim (jika production)
- [ ] Sudah baca dan paham konsekuensinya
- [ ] Sudah ketik konfirmasi "HAPUS SEMUA DATA"
- [ ] Siap accept bahwa data baru akan hilang

---

## 🔗 File Terkait

| File | Fungsi |
|------|--------|
| `import_database.php` | Halaman import dengan proteksi |
| `aplikasi.sql` | File backup database |
| `backup_database.sh` | Script backup otomatis |
| `watch_check_register.sh` | Monitor database real-time |
| `check_register.sh` | Quick check database |

---

## 💡 Tips & Best Practices

1. **Backup Rutin**
   ```bash
   # Backup setiap hari
   ./backup_database.sh
   ```

2. **Monitor Real-time**
   ```bash
   # Saat testing
   ./watch_check_register.sh
   ```

3. **Check Log**
   ```bash
   # Lihat aktivitas import
   tail -f /Applications/XAMPP/xamppfiles/logs/error_log
   ```

4. **Version Control untuk SQL**
   - Buat folder `backups/` di `.gitignore`
   - Commit `aplikasi.sql` hanya saat stable
   - Tag versi backup yang penting

5. **Production Safety**
   - Jangan pernah import di production tanpa backup
   - Test dulu di development/staging
   - Jadwalkan import saat low traffic

---

## 🆘 Emergency Contact

Jika terjadi masalah setelah import:

1. **STOP** semua aktivitas
2. **JANGAN** import ulang
3. **RESTORE** dari backup terakhir
4. **CEK** log error
5. **CONTACT** developer/admin senior

---

## ✅ Summary

**INGAT:**
- ⚠️ Import database = **HAPUS DATA BARU**
- 📦 Backup dulu sebelum import
- 🔍 Monitor dengan script helper
- 🚫 Jangan import saat testing/development
- ✅ Gunakan web interface untuk safety

**File `aplikasi.sql` adalah untuk:**
- ✅ Setup awal
- ✅ Disaster recovery
- ✅ Migrasi server
- ❌ BUKAN untuk testing registrasi!

---

📅 **Terakhir diupdate:** 2025-11-02
👤 **Maintainer:** Development Team
🔗 **Dokumentasi:** PANDUAN_IMPORT_DATABASE.md
