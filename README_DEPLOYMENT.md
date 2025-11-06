# 🚀 QUICK START: DEPLOYMENT GUIDE

## File yang Harus Di-Upload ke Hosting:

1. **📦 kaori_hr_deployment_20251106_164847.zip** (2.3 MB)
   - Semua file aplikasi (PHP, CSS, JS)
   - Siap extract di hosting

2. **🗄️ aplikasi_production_20251106_164755.sql.gz** (15 KB)
   - Database production (sudah bersih dari dummy data)
   - Siap import ke hosting

## Langkah Cepat:

### 1. Upload & Extract Aplikasi
```
1. Login cPanel
2. File Manager → public_html/
3. Upload kaori_hr_deployment_*.zip
4. Klik kanan → Extract
5. Buat folder: uploads/ (chmod 755)
6. Buat subfolder: uploads/tanda_tangan, uploads/surat_izin, uploads/foto_absensi
7. Buat folder: logs/ (chmod 755)
```

### 2. Setup Database
```
1. cPanel → MySQL Databases
2. Buat database: username_aplikasi
3. Buat user: username_appuser
4. Assign user ke database (ALL PRIVILEGES)
5. cPanel → phpMyAdmin
6. Pilih database → Import
7. Upload aplikasi_production_*.sql.gz
8. Klik "Go"
```

### 3. Konfigurasi connect.php
```php
// Edit file connect.php di hosting
$db_host = 'localhost';
$db_name = 'username_aplikasi';  // Sesuaikan
$db_user = 'username_appuser';   // Sesuaikan
$db_pass = 'your_strong_password'; // Sesuaikan
```

### 4. Test Aplikasi
```
1. Akses: https://namadomain.com/aplikasi/
2. Login dengan akun admin atau user whitelist
3. Test absensi, izin/sakit, dll
4. Verifikasi semua fitur bekerja
```

## ⚠️ PENTING!

- ✅ Database sudah dibersihkan (hanya 36 user whitelist)
- ✅ Security features aktif (anti-mock location, time manipulation, dll)
- ✅ Session management secure
- ✅ CSRF protection enabled
- ✅ Rate limiting active

## 📚 Dokumentasi Lengkap:

Baca file **PANDUAN_DEPLOYMENT_HOSTING.md** untuk:
- Pilihan hosting yang cocok
- Cara install SSL certificate
- Konfigurasi .htaccess
- Troubleshooting lengkap
- Backup & maintenance guide

## 🆘 Need Help?

Check:
1. PANDUAN_DEPLOYMENT_HOSTING.md - Panduan lengkap deployment
2. DOKUMENTASI_KEAMANAN_SISTEM.md - Security features & config
3. Error logs di hosting (cPanel → Error Log)

---

**✨ Selamat Deploy! Semoga lancar! ✨**
