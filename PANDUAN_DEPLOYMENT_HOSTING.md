# PANDUAN DEPLOYMENT KE HOSTING
## Cara Memindahkan Aplikasi KAORI ke Server Production

**Tanggal:** November 6, 2025  
**Status:** Ready for Production  
**Versi:** 1.0

---

## 📋 DAFTAR ISI

1. [Persiapan Sebelum Deploy](#persiapan-sebelum-deploy)
2. [Pilihan Hosting](#pilihan-hosting)
3. [Langkah-Langkah Deployment](#langkah-langkah-deployment)
4. [Konfigurasi Database](#konfigurasi-database)
5. [Konfigurasi PHP & Apache](#konfigurasi-php--apache)
6. [Testing Production](#testing-production)
7. [Troubleshooting](#troubleshooting)
8. [Maintenance & Backup](#maintenance--backup)

---

## 🎯 PERSIAPAN SEBELUM DEPLOY

### 1. Checklist Pre-Deployment

- ✅ Database sudah dibersihkan dari dummy data
- ✅ Semua fitur sudah ditest di local
- ✅ Security features sudah aktif
- ✅ File `connect.php` siap dikonfigurasi untuk production
- ✅ Backup database local tersedia
- ✅ Dokumentasi lengkap tersedia

### 2. Files yang HARUS Di-upload

```
aplikasi/
├── *.php (semua file PHP)
├── *.css (style.css, dll)
├── *.js (script.js, script_absen.js, dll)
├── logo.png
├── datawhitelistpegawai.csv
├── uploads/ (folder kosong, akan diisi otomatis)
│   ├── tanda_tangan/
│   ├── surat_izin/
│   └── foto_absensi/
└── logs/ (folder kosong, akan diisi otomatis)
    └── security_*.log
```

### 3. Files yang TIDAK Perlu Di-upload

```
❌ .git/
❌ .gitignore
❌ *.md (file dokumentasi - opsional)
❌ composer.lock (kecuali pakai composer)
❌ node_modules/
❌ backup_*.sh (script local)
❌ test_*.php (file testing)
❌ debug_*.php (file debugging)
```

---

## 🌐 PILIHAN HOSTING

### Rekomendasi Hosting untuk Aplikasi KAORI:

#### 1. **Shared Hosting** (Budget: Rp 20-100rb/bulan)
**Cocok untuk:** Small-medium business (< 100 karyawan)

**Pilihan Provider:**
- ✅ **Niagahoster** (Rp 24rb/bulan)
  - PHP 7.4+
  - MySQL Database
  - SSL Gratis
  - Support 24/7
  - Link: https://www.niagahoster.co.id

- ✅ **Hostinger** (Rp 29rb/bulan)
  - PHP 8.0+
  - MySQL 5.7+
  - SSL Gratis
  - Support 24/7
  - Link: https://www.hostinger.co.id

- ✅ **Rumahweb** (Rp 40rb/bulan)
  - PHP 7.4+
  - MySQL Database
  - SSL Gratis
  - Support Indonesia
  - Link: https://www.rumahweb.com

**Kebutuhan Minimal:**
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.2+
- Storage: 1GB (untuk aplikasi + database)
- Bandwidth: Unlimited (recommended)
- SSL Certificate: Gratis (Let's Encrypt)

#### 2. **VPS (Virtual Private Server)** (Budget: Rp 100-500rb/bulan)
**Cocok untuk:** Medium-large business (100+ karyawan)

**Pilihan Provider:**
- ✅ **DigitalOcean** ($5-10/bulan)
- ✅ **Vultr** ($5-10/bulan)
- ✅ **Linode** ($5-10/bulan)
- ✅ **AWS Lightsail** ($3.5-10/bulan)

**Keunggulan VPS:**
- Full control server
- Bisa custom konfigurasi
- Performance lebih baik
- Scalable

#### 3. **Cloud Hosting** (Budget: Flexible)
**Cocok untuk:** Large enterprise

**Pilihan Provider:**
- AWS (Amazon Web Services)
- Google Cloud Platform
- Microsoft Azure

---

## 🚀 LANGKAH-LANGKAH DEPLOYMENT

### METODE 1: Upload via cPanel (Shared Hosting)

#### Step 1: Persiapan File

```bash
# 1. Compress aplikasi menjadi ZIP
cd /Applications/XAMPP/xamppfiles/htdocs/
zip -r aplikasi.zip aplikasi/ \
  -x "aplikasi/.git/*" \
  -x "aplikasi/*.md" \
  -x "aplikasi/backup_*.sh" \
  -x "aplikasi/test_*.php" \
  -x "aplikasi/debug_*.php"

# 2. File aplikasi.zip siap di-upload
```

#### Step 2: Login ke cPanel

1. Buka browser, akses: `https://namadomain.com/cpanel`
2. Login dengan username & password dari hosting
3. Cari menu **"File Manager"**

#### Step 3: Upload File

1. Di File Manager, masuk ke folder `public_html` (atau `www`, `htdocs`)
2. Klik **"Upload"**
3. Pilih file `aplikasi.zip` yang sudah dibuat
4. Tunggu sampai upload selesai (lihat progress bar)

#### Step 4: Extract ZIP

1. Kembali ke File Manager
2. Klik kanan file `aplikasi.zip`
3. Pilih **"Extract"**
4. Pilih lokasi extract: `/public_html/`
5. Klik "Extract Files"

Struktur folder akan jadi:
```
public_html/
└── aplikasi/
    ├── index.php
    ├── login.php
    ├── mainpage.php
    └── ...
```

**ATAU** jika ingin langsung di root (tanpa folder aplikasi):
```
public_html/
├── index.php
├── login.php
├── mainpage.php
└── ...
```

#### Step 5: Set Permissions (Chmod)

Di File Manager, set permission folder berikut:

```
uploads/ → 755 (rwxr-xr-x)
├── tanda_tangan/ → 755
├── surat_izin/ → 755
└── foto_absensi/ → 755

logs/ → 755 (rwxr-xr-x)
```

**Cara set permission:**
1. Klik kanan folder
2. Pilih "Change Permissions"
3. Set ke 755
4. Centang "Recurse into subdirectories"
5. Klik "Change Permissions"

---

### METODE 2: Upload via FTP (FileZilla)

#### Step 1: Install FileZilla

Download dari: https://filezilla-project.org/

#### Step 2: Koneksi FTP

1. Buka FileZilla
2. Isi koneksi:
   - **Host:** ftp.namadomain.com (atau IP server)
   - **Username:** username_ftp (dari hosting)
   - **Password:** password_ftp (dari hosting)
   - **Port:** 21 (FTP) atau 22 (SFTP)
3. Klik "Quickconnect"

#### Step 3: Upload Files

**Local Site (kiri):**
```
/Applications/XAMPP/xamppfiles/htdocs/aplikasi/
```

**Remote Site (kanan):**
```
/public_html/aplikasi/
```

1. Pilih semua file di Local Site
2. Klik kanan → "Upload"
3. Tunggu proses upload selesai

**Tips:** 
- Gunakan "Transfer Queue" untuk multiple files
- Resume otomatis jika koneksi putus

---

### METODE 3: Deploy via Git (VPS)

**Untuk Advanced Users dengan VPS/SSH Access**

#### Step 1: Push ke GitHub (Private Repo)

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi

# Init git (jika belum)
git init

# Tambahkan .gitignore
cat > .gitignore << EOF
*.md
*.log
uploads/
logs/
backup_*.sh
test_*.php
debug_*.php
.env
EOF

# Commit
git add .
git commit -m "Initial deployment - KAORI HR System"

# Push ke GitHub
git remote add origin https://github.com/yourusername/kaori-hr.git
git branch -M main
git push -u origin main
```

#### Step 2: Pull di Server VPS

```bash
# SSH ke server
ssh username@your-server-ip

# Install requirements
sudo apt update
sudo apt install apache2 php php-mysql php-mbstring php-xml git

# Clone repository
cd /var/www/html
sudo git clone https://github.com/yourusername/kaori-hr.git aplikasi
cd aplikasi

# Set ownership
sudo chown -R www-data:www-data /var/www/html/aplikasi
sudo chmod -R 755 /var/www/html/aplikasi

# Create upload & log folders
mkdir -p uploads/{tanda_tangan,surat_izin,foto_absensi}
mkdir -p logs
chmod -R 755 uploads/ logs/
```

---

## 🗄️ KONFIGURASI DATABASE

### Step 1: Export Database dari Local

```bash
# Via terminal
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
/Applications/XAMPP/xamppfiles/bin/mysqldump -u root aplikasi > aplikasi_production.sql

# File akan tersimpan di: aplikasi_production.sql
```

**ATAU** via phpMyAdmin:
1. Buka http://localhost/phpmyadmin
2. Pilih database `aplikasi`
3. Klik tab "Export"
4. Format: SQL
5. Klik "Go"
6. Save file `aplikasi.sql`

### Step 2: Buat Database di Hosting

**Via cPanel:**

1. Login cPanel
2. Cari **"MySQL Databases"**
3. Buat database baru:
   - Database Name: `username_aplikasi` (contoh: `mysite_aplikasi`)
   - Klik "Create Database"
4. Buat user database:
   - Username: `username_appuser` (contoh: `mysite_appuser`)
   - Password: **[BUAT PASSWORD KUAT]** (save password ini!)
   - Klik "Create User"
5. Assign user ke database:
   - User: `username_appuser`
   - Database: `username_aplikasi`
   - Privileges: **ALL PRIVILEGES**
   - Klik "Make Changes"

**PENTING:** Catat informasi ini:
```
Database Name: username_aplikasi
Database User: username_appuser
Database Password: [your_strong_password]
Database Host: localhost (biasanya)
```

### Step 3: Import Database

**Via cPanel phpMyAdmin:**

1. Kembali ke cPanel
2. Cari **"phpMyAdmin"**
3. Pilih database `username_aplikasi`
4. Klik tab "Import"
5. Klik "Choose File"
6. Pilih file `aplikasi_production.sql`
7. Klik "Go"
8. Tunggu proses import selesai

**ATAU via SSH (jika ada akses):**

```bash
mysql -u username_appuser -p username_aplikasi < aplikasi_production.sql
# Enter password saat diminta
```

### Step 4: Update File `connect.php`

**PENTING:** Jangan upload `connect.php` dari local! Edit di server.

Di cPanel File Manager atau FTP, buka file `connect.php` dan ubah:

```php
<?php
// PRODUCTION DATABASE CONNECTION
$db_host = 'localhost'; // Atau IP database server dari hosting
$db_name = 'username_aplikasi'; // Database name dari hosting
$db_user = 'username_appuser'; // Database user dari hosting
$db_pass = 'your_strong_password'; // Database password dari hosting

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    // PRODUCTION: Jangan tampilkan error detail
    error_log("Database Connection Failed: " . $e->getMessage());
    die("Database connection error. Please contact administrator.");
}
?>
```

**Tips Keamanan:**
- Gunakan password database yang KUAT (minimal 16 karakter, kombinasi huruf besar-kecil-angka-simbol)
- Jangan gunakan password default atau mudah ditebak
- Simpan backup password di tempat aman (password manager)

---

## ⚙️ KONFIGURASI PHP & APACHE

### 1. Update `php.ini` (jika ada akses)

**Via cPanel:**
1. Cari menu "Select PHP Version" atau "MultiPHP INI Editor"
2. Update settings:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
file_uploads = On
session.gc_maxlifetime = 7200
```

3. Save changes

### 2. Create `.htaccess` (di root aplikasi)

File: `/public_html/aplikasi/.htaccess`

```apache
# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# PHP Settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value memory_limit 256M

# Hide PHP Version
<IfModule mod_headers.c>
    Header unset X-Powered-By
</IfModule>

# Prevent Directory Listing
Options -Indexes

# Error Pages (optional)
ErrorDocument 404 /aplikasi/404.php
ErrorDocument 500 /aplikasi/500.php

# Force HTTPS (jika sudah install SSL)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# Protect sensitive files
<FilesMatch "^(connect\.php|security_helper\.php|.*\.log|.*\.csv)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Allow access to specific files if needed
<FilesMatch "^(index\.php|login\.php)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
```

### 3. Install SSL Certificate (HTTPS)

**Via cPanel (Let's Encrypt - GRATIS):**

1. Login cPanel
2. Cari menu **"SSL/TLS Status"** atau **"Let's Encrypt SSL"**
3. Pilih domain Anda
4. Klik "Issue" atau "Install"
5. Tunggu proses (biasanya 1-5 menit)
6. Setelah selesai, akses website dengan `https://`

**Manual (via Certbot) - untuk VPS:**

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Generate SSL
sudo certbot --apache -d namadomain.com -d www.namadomain.com

# Auto-renew (cron job)
sudo crontab -e
# Add line:
0 3 * * * certbot renew --quiet
```

---

## 🧪 TESTING PRODUCTION

### Checklist Testing:

#### 1. Basic Access
- [ ] ✅ https://namadomain.com/aplikasi/ terbuka
- [ ] ✅ Logo dan CSS tampil dengan benar
- [ ] ✅ Tidak ada error 404 atau 500

#### 2. Login & Session
- [ ] ✅ Login dengan akun admin berhasil
- [ ] ✅ Login dengan akun user berhasil
- [ ] ✅ Session tersimpan (refresh halaman tetap login)
- [ ] ✅ Logout berhasil
- [ ] ✅ Akses index.php saat sudah login → redirect ke mainpage

#### 3. Database Connection
- [ ] ✅ Data user tampil di mainpage
- [ ] ✅ Data absensi tampil
- [ ] ✅ Data shift tampil

#### 4. File Upload
- [ ] ✅ Upload foto absensi berhasil
- [ ] ✅ Upload surat izin berhasil
- [ ] ✅ Upload tanda tangan berhasil
- [ ] ✅ File tersimpan di folder `uploads/`

#### 5. Security Features
- [ ] ✅ CSRF token bekerja
- [ ] ✅ Rate limiting bekerja
- [ ] ✅ Session timeout bekerja (2 jam)
- [ ] ✅ File upload validation bekerja
- [ ] ✅ Security logs tertulis di `logs/`

#### 6. Mobile Access
- [ ] ✅ Website responsive di mobile
- [ ] ✅ Kamera akses berhasil
- [ ] ✅ GPS akses berhasil
- [ ] ✅ Form absensi bekerja di mobile

### Testing Commands:

```bash
# Test database connection
curl -I https://namadomain.com/aplikasi/mainpage.php

# Test SSL
curl -I https://namadomain.com/aplikasi/

# Check PHP version
# Buat file info.php (hapus setelah test!):
<?php phpinfo(); ?>
# Akses: https://namadomain.com/aplikasi/info.php
```

---

## 🔧 TROUBLESHOOTING

### Problem 1: Error "Database Connection Failed"

**Solusi:**
1. Check `connect.php`:
   - Database name correct?
   - Username correct?
   - Password correct?
   - Host correct? (usually `localhost`)

2. Check database user privileges:
   - Via cPanel → MySQL Databases → Check user has ALL PRIVILEGES

3. Check database exists:
   - Via phpMyAdmin → Database list

### Problem 2: Error "Internal Server Error 500"

**Solusi:**
1. Check PHP error log:
   - cPanel → Error Log
   - atau file: `/home/username/public_html/error_log`

2. Check file permissions:
   ```bash
   chmod 755 folder/
   chmod 644 file.php
   ```

3. Check `.htaccess` syntax:
   - Comment out all lines in `.htaccess`
   - Test if website works
   - Uncomment line by line to find issue

### Problem 3: Upload File Failed

**Solusi:**
1. Check folder permissions:
   ```bash
   chmod -R 755 uploads/
   chown -R www-data:www-data uploads/
   ```

2. Check PHP settings:
   - upload_max_filesize ≥ 10M
   - post_max_size ≥ 10M

3. Check folder exists:
   ```bash
   mkdir -p uploads/{tanda_tangan,surat_izin,foto_absensi}
   ```

### Problem 4: CSS/JS Not Loading

**Solusi:**
1. Check file paths:
   - Use absolute path: `/aplikasi/style.css`
   - Or relative path: `./style.css`

2. Check file permissions:
   ```bash
   chmod 644 *.css
   chmod 644 *.js
   ```

3. Clear browser cache:
   - Ctrl + Shift + R (hard refresh)

### Problem 5: Session Lost After Refresh

**Solusi:**
1. Check PHP session settings:
   ```ini
   session.save_path = /tmp
   session.gc_probability = 1
   session.gc_divisor = 100
   session.gc_maxlifetime = 7200
   ```

2. Check session folder writable:
   ```bash
   chmod 1777 /tmp
   ```

3. Update `security_helper.php`:
   ```php
   // Pastikan session_start() dipanggil
   SecurityHelper::secureSessionStart();
   ```

---

## 🔄 MAINTENANCE & BACKUP

### Daily Tasks:

#### 1. Monitor Error Logs
```bash
# Via cPanel File Manager
tail -f logs/security_*.log

# Via SSH
tail -f /var/www/html/aplikasi/logs/security_$(date +%Y-%m-%d).log
```

#### 2. Check Disk Usage
```bash
# Via cPanel → Disk Usage
# Or SSH:
du -sh /var/www/html/aplikasi/uploads/
```

### Weekly Tasks:

#### 1. Backup Database

**Via cPanel:**
1. cPanel → Backup Wizard
2. Full Backup atau Partial Backup (Database)
3. Download backup file

**Via SSH:**
```bash
#!/bin/bash
# backup_database.sh

DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="username_aplikasi"
DB_USER="username_appuser"
DB_PASS="your_password"
BACKUP_DIR="/home/username/backups"

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/aplikasi_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "aplikasi_*.sql.gz" -mtime +30 -delete

echo "Backup completed: aplikasi_$DATE.sql.gz"
```

**Cron job (auto backup every Sunday 2 AM):**
```bash
crontab -e
# Add line:
0 2 * * 0 /home/username/backup_database.sh
```

#### 2. Backup Files

```bash
#!/bin/bash
# backup_files.sh

DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/html/aplikasi"
BACKUP_DIR="/home/username/backups"

mkdir -p $BACKUP_DIR

tar -czf $BACKUP_DIR/aplikasi_files_$DATE.tar.gz \
  --exclude='*.log' \
  --exclude='*.md' \
  $APP_DIR

# Keep only last 7 days
find $BACKUP_DIR -name "aplikasi_files_*.tar.gz" -mtime +7 -delete

echo "File backup completed: aplikasi_files_$DATE.tar.gz"
```

### Monthly Tasks:

#### 1. Update System
```bash
# For VPS
sudo apt update && sudo apt upgrade -y
sudo service apache2 restart
```

#### 2. Security Audit
- Review security logs for suspicious activities
- Check for failed login attempts
- Update passwords if needed

#### 3. Database Optimization
```sql
-- Run in phpMyAdmin or MySQL console
OPTIMIZE TABLE register;
OPTIMIZE TABLE absensi;
OPTIMIZE TABLE shift_assignments;
OPTIMIZE TABLE pengajuan_izin;
OPTIMIZE TABLE riwayat_gaji;
```

---

## 📞 SUPPORT & RESOURCES

### Dokumentasi:
- Dokumentasi Keamanan: `DOKUMENTASI_KEAMANAN_SISTEM.md`
- Dokumentasi Izin/Sakit: `IMPLEMENTASI_IZIN_SAKIT_TERINTEGRASI.md`
- Changelog: `CHANGE_SUMMARY.md`

### Hosting Support:
- **Niagahoster:** support@niagahoster.co.id | WA: 0804-1-808-888
- **Hostinger:** support@hostinger.co.id | Live Chat 24/7
- **Rumahweb:** cs@rumahweb.com | Telp: 0274-5305505

### Developer Contact:
- [Your Name/Company]
- [Your Email]
- [Your Phone]

---

## 🎉 CHECKLIST FINAL DEPLOYMENT

Sebelum go-live, pastikan semua sudah ✅:

### Pre-Launch:
- [ ] ✅ Database production sudah di-import
- [ ] ✅ File aplikasi sudah di-upload
- [ ] ✅ `connect.php` sudah dikonfigurasi dengan benar
- [ ] ✅ Folder `uploads/` dan `logs/` sudah dibuat dan chmod 755
- [ ] ✅ SSL certificate sudah terinstall (HTTPS)
- [ ] ✅ `.htaccess` sudah dibuat dan dikonfigurasi
- [ ] ✅ PHP settings sudah sesuai (upload_max_filesize, dll)

### Testing:
- [ ] ✅ Login admin berhasil
- [ ] ✅ Login user berhasil
- [ ] ✅ Session persistence bekerja
- [ ] ✅ File upload bekerja
- [ ] ✅ Absensi bekerja (GPS, camera, foto)
- [ ] ✅ Izin/Sakit form bekerja
- [ ] ✅ Security features aktif
- [ ] ✅ Mobile responsive OK

### Post-Launch:
- [ ] ✅ Domain sudah pointing ke hosting
- [ ] ✅ Email notifikasi sudah ditest
- [ ] ✅ Backup otomatis sudah disetup
- [ ] ✅ Monitoring error logs aktif
- [ ] ✅ User training selesai
- [ ] ✅ Admin contact ready for support

---

## 🚀 GO LIVE!

Setelah semua checklist terpenuhi:

1. **Announce ke users:**
   ```
   🎉 Sistem KAORI HR sudah LIVE!
   
   URL: https://namadomain.com/aplikasi/
   
   Login dengan username dan password yang sudah didaftarkan.
   
   Untuk bantuan, hubungi: [Admin Contact]
   ```

2. **Monitor first 24 hours:**
   - Check error logs setiap 2 jam
   - Siap standby untuk support
   - Collect feedback dari users

3. **First week review:**
   - Collect issues dan feedback
   - Fix bugs jika ada
   - Update dokumentasi

---

**Selamat! Aplikasi KAORI HR sudah siap production! 🎉**

---

**END OF DEPLOYMENT GUIDE**
