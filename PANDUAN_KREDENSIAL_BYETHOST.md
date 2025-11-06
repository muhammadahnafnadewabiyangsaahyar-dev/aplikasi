# 📋 PANDUAN MENGISI KREDENSIAL DATABASE BYETHOST

**Tanggal**: 6 November 2024  
**Untuk**: Deployment KAORI HR ke ByetHost Free Hosting

---

## 🎯 LANGKAH-LANGKAH MENDAPATKAN KREDENSIAL DATABASE

### Step 1: Login ke ByetHost Control Panel (VCP)

1. Buka browser dan akses: https://byethost.com/vcp/
2. Login dengan:
   - **Username**: username akun ByetHost Anda
   - **Password**: password akun ByetHost Anda

---

### Step 2: Buat Database MySQL (Jika Belum Ada)

1. Setelah login, cari menu **"MySQL Databases"**
2. Klik ikon atau menu **"MySQL Databases"**
3. Di bagian **"Create New Database"**:
   - **Database Name**: Masukkan nama database (contoh: `kaori`)
   - **Database Password**: Buat password yang kuat (CATAT INI!)
   - Klik tombol **"Create Database"**

4. Database akan dibuat dengan format:
   ```
   Format: bX_XXXXXXXX_namaanda
   Contoh: b6_40348133_kaori
   ```

---

### Step 3: Catat Kredensial Database

Setelah database dibuat, Anda akan melihat informasi berikut di bagian **"Current MySQL Databases"**:

```
Database Information:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Database Name:    b6_40348133_kaori
Database User:    b6_40348133_kaori  (sama dengan database name)
Database Host:    sql100.byetcluster.com
Password:         (password yang Anda buat tadi)
phpMyAdmin:       [Link ke phpMyAdmin]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**PENTING**: 
- Username **selalu sama** dengan Database Name
- Host biasanya: `sqlXXX.byetcluster.com` atau `sqlXXX.byethost.com`
- Password adalah yang Anda buat saat create database

---

### Step 4: Update File `connect_byethost.php`

Buka file `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/connect_byethost.php` dan ganti nilai berikut:

```php
<?php
// Set timezone untuk konsistensi PHP & MySQL
date_default_timezone_set('Asia/Makassar'); // WITA (UTC+8)

// ============================================================
// KONFIGURASI DATABASE BYETHOST
// ============================================================
// Ganti dengan kredensial dari ByetHost Anda

// 1. Database Host
$host = "sql100.byetcluster.com";  // 👈 GANTI: Copy dari "Database Host"

// 2. Database Name
$dbname = "b6_40348133_kaori";  // 👈 GANTI: Copy dari "Database Name"

// 3. Database Username (sama dengan database name)
$username = "b6_40348133_kaori";  // 👈 GANTI: Copy dari "Database User"

// 4. Database Password
$password = "password_anda_disini";  // 👈 GANTI: Masukkan password yang Anda buat

// Character set (JANGAN DIUBAH)
$charset = "utf8mb4";

// ...sisanya sama...
```

---

## 📝 CONTOH PENGISIAN

### Contoh 1: ByetHost Account b6_40348133

Informasi dari ByetHost:
```
Database Name:    b6_40348133_kaori
Database User:    b6_40348133_kaori
Database Host:    sql100.byetcluster.com
Password:         MyStr0ngP@ssw0rd!
```

Update `connect_byethost.php`:
```php
$host = "sql100.byetcluster.com";
$dbname = "b6_40348133_kaori";
$username = "b6_40348133_kaori";
$password = "MyStr0ngP@ssw0rd!";
```

---

### Contoh 2: ByetHost Account b10_12345678

Informasi dari ByetHost:
```
Database Name:    b10_12345678_aplikasi
Database User:    b10_12345678_aplikasi
Database Host:    sql110.byethost.com
Password:         Secure123!@#
```

Update `connect_byethost.php`:
```php
$host = "sql110.byethost.com";
$dbname = "b10_12345678_aplikasi";
$username = "b10_12345678_aplikasi";
$password = "Secure123!@#";
```

---

## ✅ VALIDASI KONEKSI

Setelah update `connect_byethost.php`, test koneksi dengan:

### Method 1: Uncomment Test Line

Buka `connect_byethost.php`, uncomment baris berikut:

```php
// ============================================================
// CONNECTION SUCCESS (optional - hapus di production)
// ============================================================
// Uncomment baris di bawah untuk testing koneksi
echo "✅ Koneksi database berhasil!<br>";
echo "Connected to: " . $dbname . " on " . $host . "<br>";
```

Kemudian buka di browser: `http://your-byethost-url.com/connect_byethost.php`

Jika berhasil akan tampil:
```
✅ Koneksi database berhasil!
Connected to: b6_40348133_kaori on sql100.byetcluster.com
```

Jika gagal akan tampil:
```
Koneksi ke database gagal. Silakan coba lagi nanti.
```

---

### Method 2: Buat Test Script

Buat file `test_connection.php`:

```php
<?php
require_once 'connect_byethost.php';

echo "<h2>Test Koneksi Database ByetHost</h2>";

try {
    // Test query sederhana
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as current_db");
    $result = $stmt->fetch();
    
    echo "✅ <b>Koneksi Berhasil!</b><br><br>";
    echo "Database: <b>" . $result['current_db'] . "</b><br>";
    echo "MySQL Version: <b>" . $result['version'] . "</b><br>";
    echo "Host: <b>" . $host . "</b><br>";
    
    // Test count tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<br>📊 <b>Total Tables: " . count($tables) . "</b><br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "❌ <b>Koneksi Gagal!</b><br>";
    echo "Error: " . $e->getMessage();
}
?>
```

Buka: `http://your-byethost-url.com/test_connection.php`

---

## 🔧 TROUBLESHOOTING

### Error: "Koneksi ke database gagal"

**Penyebab dan Solusi:**

#### 1. Password Salah
```
✅ Solusi: 
- Pastikan password yang dimasukkan BENAR
- Password ini bukan password login ByetHost!
- Password ini adalah yang Anda buat saat create database
- Jika lupa, reset password di ByetHost MySQL Databases menu
```

#### 2. Host Salah
```
✅ Solusi:
- Periksa kembali Database Host di ByetHost panel
- Format biasanya: sqlXXX.byetcluster.com
- Jangan gunakan "localhost" di ByetHost!
```

#### 3. Database Name/Username Salah
```
✅ Solusi:
- Database Name dan Username HARUS SAMA di ByetHost
- Format: bX_XXXXXXXX_namadb
- Copy-paste dari ByetHost panel untuk menghindari typo
```

#### 4. Database Belum Dibuat
```
✅ Solusi:
- Pastikan database sudah dibuat di ByetHost MySQL Databases
- Import SQL file dulu ke phpMyAdmin
- Verifikasi tables sudah ada
```

---

## 📊 CHECKLIST DEPLOYMENT

Sebelum production, pastikan:

### Database Setup
- [ ] Database sudah dibuat di ByetHost
- [ ] SQL file sudah diimport via phpMyAdmin
- [ ] Semua tables sudah ada (cek via phpMyAdmin)
- [ ] Data sudah terimport (cek beberapa tables)

### File Configuration
- [ ] File `connect_byethost.php` sudah diupdate
- [ ] Kredensial database sudah benar
- [ ] Timezone sudah diset ke `Asia/Makassar` (WITA)
- [ ] Test koneksi berhasil

### Security
- [ ] Comment/hapus test connection code di production
- [ ] Hapus file `test_connection.php` setelah testing
- [ ] Pastikan error tidak menampilkan detail kredensial
- [ ] Password database cukup kuat

### Testing
- [ ] Test login system
- [ ] Test absensi
- [ ] Test pengajuan izin/sakit
- [ ] Test dashboard
- [ ] Test laporan

---

## 🎯 NEXT STEPS

Setelah `connect_byethost.php` berhasil:

1. **Upload semua file PHP** ke ByetHost via FTP/File Manager
2. **Rename `connect_byethost.php` menjadi `connect.php`**
   ```bash
   # Di ByetHost File Manager atau via FTP
   rename: connect_byethost.php → connect.php
   ```
3. **Test aplikasi**:
   - Login: `http://your-site.byethost.com/index.php`
   - Register: `http://your-site.byethost.com/register.php`
   - Dashboard: `http://your-site.byethost.com/mainpage.php`

---

## 📞 SUPPORT

Jika masih ada masalah:

1. **Cek error log** di ByetHost Control Panel → Error Logs
2. **Test koneksi manual** via phpMyAdmin
3. **Verifikasi kredensial** di ByetHost MySQL Databases panel
4. **Contact ByetHost Support** jika masalah teknis hosting

---

## 📋 QUICK REFERENCE

### Format Kredensial ByetHost
```
Host:     sqlXXX.byetcluster.com (atau sqlXXX.byethost.com)
DB Name:  bX_XXXXXXXX_namadb
Username: bX_XXXXXXXX_namadb (sama dengan DB Name)
Password: (yang Anda buat saat create database)
```

### Timezone Settings
```php
PHP:   date_default_timezone_set('Asia/Makassar'); // WITA (UTC+8)
MySQL: $pdo->exec("SET time_zone = '+08:00'");      // WITA (UTC+8)
```

### Test Connection Query
```php
$stmt = $pdo->query("SELECT VERSION(), DATABASE()");
$result = $stmt->fetch();
print_r($result);
```

---

**Status**: ✅ READY TO CONFIGURE  
**Last Updated**: November 6, 2024  
**Next Action**: Update kredensial di `connect_byethost.php`
