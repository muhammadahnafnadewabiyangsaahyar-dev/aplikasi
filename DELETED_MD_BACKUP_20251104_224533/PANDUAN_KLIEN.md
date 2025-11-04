# 📋 PANDUAN UNTUK KLIEN - APLIKASI ABSENSI

## ✅ SISTEM SUDAH AMAN & SIAP DIGUNAKAN

### 🎯 Yang Sudah Dilakukan:

1. **✅ Fitur Import Database DIHAPUS**
   - Mencegah admin non-teknis salah klik
   - Mencegah data hilang tidak sengaja
   - Menu "⚠️ Import DB" sudah dihapus dari navbar

2. **✅ Auto-Import DINONAKTIFKAN**
   - Cron job berbahaya sudah dihapus
   - Data registrasi tidak akan hilang lagi
   - Database aman dari reset otomatis

3. **✅ Backup Otomatis SIAP DIGUNAKAN** (Opsional)
   - Script backup aman sudah tersedia
   - Hanya export, tidak menghapus data
   - Bisa diaktifkan kapan saja

4. **✅ Bug Reset Password DIPERBAIKI**
   - Token tidak lagi kadaluarsa prematur
   - Timezone PHP dan MySQL sudah sinkron
   - Email reset password berfungsi normal

5. **✅ Bug Hapus User Whitelist DIPERBAIKI**
   - Error "Nama tidak boleh kosong" sudah teratasi
   - Error "Invalid request" sudah teratasi
   - Fitur hapus user sekarang menggunakan GET method yang lebih reliable

6. **✅ CASCADE DELETE di Whitelist**
   - Hapus pegawai di whitelist → otomatis hapus akun user
   - Hapus foto profil & tanda tangan otomatis
   - Hapus komponen gaji otomatis
   - One-click operation (tidak perlu hapus manual di 2 tempat)
   - Aman dengan transaction & rollback

---

## 📦 FITUR BACKUP OTOMATIS (OPSIONAL)

### Untuk Apa Backup?

Backup database berguna untuk:
- 🔄 Recovery jika terjadi error
- 📊 Menyimpan history data
- 🛡️ Proteksi dari kehilangan data
- 📅 Audit dan compliance

### Cara Aktifkan Backup Otomatis:

**HANYA jika Anda mengerti teknis atau ada IT support!**

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
./setup_backup_auto.sh
```

Pilih interval backup:
- **Setiap 1 jam** (Recommended untuk kantor aktif)
- **Setiap 4 jam** (Recommended untuk kantor normal)
- **Setiap 1 hari** (Recommended untuk kantor kecil)

**Backup akan tersimpan di folder:** `backups/`

---

## 🚫 YANG SUDAH DIHAPUS/DINONAKTIFKAN

### 1. **Import Database via Web** (DIHAPUS)
   - File: `import_database.php` ❌ DIHAPUS
   - Menu di navbar ❌ DIHAPUS
   - **Alasan:** Berbahaya untuk non-teknis

### 2. **Auto-Import Script** (DISABLED)
   - File: `import_auto.sh` ✅ DIARSIPKAN
   - Cron job ❌ DIHAPUS
   - **Alasan:** Menghapus data setiap menit!

### 3. **Dokumentasi Teknis** (DIARSIPKAN)
   - `PANDUAN_IMPORT_DATABASE.md` ✅ Dipindah ke `_archived/`
   - `FITUR_IMPORT_SUMMARY.md` ✅ Dipindah ke `_archived/`
   - **Alasan:** Tidak relevan lagi

---

## ✅ YANG TETAP TERSEDIA (AMAN)

### 1. **Aplikasi Utama**
   - ✅ Registrasi user baru
   - ✅ Absensi masuk/keluar
   - ✅ Rekap absensi
   - ✅ Pengajuan izin
   - ✅ Slip gaji
   - ✅ Whitelist pegawai
   - ✅ Approve lembur
   - ✅ Manajemen user

### 2. **Backup Manual** (Jika Diperlukan)
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
   ./backup_database.sh
   ```
   - File backup: `backups/aplikasi_backup_YYYYMMDD_HHMMSS.sql`
   - **Kapan digunakan:** Sebelum update besar, atau berkala

### 3. **Monitoring Database**
   ```bash
   ./check_register.sh          # Quick check
   ./watch_check_register.sh    # Real-time monitoring
   ```

---

## 📊 MENU ADMIN (SETELAH CLEANUP)

**Menu yang tersedia untuk admin:**

```
Navbar Admin:
├── Home
├── Profile
├── Surat Izin
├── Absensi
├── Rekap Absensi
├── Slip Gaji
├── Jadwal Shift
├── Approve Surat
├── Daftar Pengguna
├── Daftar Absensi
├── Approve Lembur
├── Whitelist
└── Logout
```

**Menu "Import DB" sudah DIHAPUS** untuk keamanan!

---

## 🆘 JIKA BUTUH RESTORE DATABASE

**HANYA dilakukan oleh IT Support atau yang paham teknis!**

### Scenario: Database Corrupt/Error

```bash
# 1. Cek backup yang tersedia
ls -lh backups/

# 2. Pilih backup terakhir yang bagus
# Contoh: backups/aplikasi_backup_20251103_120000.sql

# 3. Restore database (HATI-HATI!)
mysql -u root aplikasi < backups/aplikasi_backup_20251103_120000.sql

# 4. Verify
./check_register.sh
```

**⚠️ PERINGATAN:** Restore akan menimpa data saat ini dengan data backup!

---

## 💡 BEST PRACTICES UNTUK KLIEN

### ✅ LAKUKAN:

1. **Backup Berkala (Manual)**
   ```bash
   ./backup_database.sh
   ```
   Frekuensi: Setiap minggu atau sebelum perubahan besar

2. **Monitor Registrasi**
   - Cek data user baru via menu "Daftar Pengguna"
   - Pastikan data tidak hilang

3. **Training Admin**
   - Admin harus tahu fitur-fitur yang ada
   - Admin TIDAK perlu tahu cara import/export database

4. **Hubungi IT Support**
   - Jika ada error database
   - Jika perlu restore backup
   - Jika perlu update aplikasi

### ❌ JANGAN:

1. **Jangan Import Manual**
   ```bash
   # JANGAN jalankan ini tanpa IT support!
   mysql -u root aplikasi < aplikasi.sql
   ```

2. **Jangan Edit Crontab**
   ```bash
   # JANGAN tambah cron job sendiri!
   crontab -e
   ```

3. **Jangan Hapus Folder Backup**
   ```bash
   # JANGAN hapus folder ini!
   backups/
   ```

4. **Jangan Edit File SQL**
   - File `.sql` berisi struktur database
   - Edit manual bisa merusak database

---

## 📞 KONTAK SUPPORT

**Jika terjadi masalah:**

1. **Cek Log:**
   ```bash
   tail -f /Applications/XAMPP/xamppfiles/logs/error_log
   ```

2. **Quick Check Database:**
   ```bash
   ./check_register.sh
   ```

3. **Hubungi IT Support:**
   - Email: [isi email IT support]
   - Phone: [isi nomor IT support]
   - Sertakan screenshot error

---

## 📁 STRUKTUR FOLDER

```
aplikasi/
├── index.php                    ← Halaman login/registrasi
├── mainpage.php                 ← Dashboard
├── absen.php                    ← Absensi
├── rekapabsen.php              ← Rekap absensi
├── whitelist.php               ← Manajemen whitelist
├── profile.php                  ← Profile user
├── backup_database.sh           ← Script backup manual (aman)
├── backup_auto.sh              ← Script backup otomatis (opsional)
├── setup_backup_auto.sh        ← Setup backup otomatis (opsional)
├── check_register.sh           ← Quick check database
├── watch_check_register.sh     ← Monitor database real-time
├── backups/                     ← Folder backup (JANGAN HAPUS!)
│   └── aplikasi_backup_*.sql
├── _archived/                   ← File lama/tidak dipakai
│   ├── import_auto.sh.DISABLED
│   └── *.md (dokumentasi lama)
└── ... (file aplikasi lainnya)
```

---

## ✅ CHECKLIST SISTEM AMAN

- [x] Fitur import database DIHAPUS
- [x] Menu import di navbar DIHAPUS
- [x] Auto-import cron job DIHAPUS
- [x] Script berbahaya DIARSIPKAN
- [x] Backup manual TERSEDIA
- [x] Backup otomatis READY (opsional)
- [x] Monitoring script TERSEDIA
- [x] Dokumentasi user-friendly DIBUAT
- [x] Bug reset password DIPERBAIKI (token expiry)
- [x] Bug whitelist hapus user DIPERBAIKI
- [x] CASCADE DELETE di whitelist (hapus pegawai → hapus akun)

---

## 🎯 KESIMPULAN

**Sistem sekarang:**
- ✅ **AMAN** untuk klien non-teknis
- ✅ **SIMPLE** - Tidak ada fitur berbahaya
- ✅ **PROTECTED** - Data tidak akan hilang lagi
- ✅ **BACKUP READY** - Bisa diaktifkan kapan saja
- ✅ **USER FRIENDLY** - Admin fokus ke bisnis, bukan teknis

**Yang dihapus:**
- ❌ Fitur import database (berbahaya untuk non-teknis)
- ❌ Auto-import cron job (penyebab data hilang)
- ❌ Menu dan dokumentasi teknis yang membingungkan

**Yang tersedia:**
- ✅ Semua fitur aplikasi utama
- ✅ Backup manual (jika diperlukan)
- ✅ Monitoring tools
- ✅ Dokumentasi user-friendly

---

📅 **Terakhir diupdate:** 2025-11-03  
✅ **Status:** PRODUCTION READY  
🎯 **Target User:** Admin non-teknis  
🛡️ **Keamanan:** Tinggi - Fitur berbahaya dihapus  
🐛 **Bug Fixes:** Reset password & Hapus user whitelist  
🆕 **New Feature:** CASCADE DELETE (hapus pegawai → hapus akun otomatis)

---

**SISTEM SIAP DIGUNAKAN! 🚀**

Untuk pertanyaan lebih lanjut, hubungi IT Support.
