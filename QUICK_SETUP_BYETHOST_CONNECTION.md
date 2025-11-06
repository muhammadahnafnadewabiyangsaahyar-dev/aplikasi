# 🚀 QUICK SETUP - ByetHost Database Connection

## Step 1: Dapatkan Kredensial (2 menit)

Login ke ByetHost → MySQL Databases → Lihat info database:

```
┌─────────────────────────────────────────┐
│ Database Name:  b6_40348133_kaori       │
│ Database User:  b6_40348133_kaori       │
│ Database Host:  sql100.byetcluster.com  │
│ Password:       (yang Anda buat)        │
└─────────────────────────────────────────┘
```

---

## Step 2: Edit connect_byethost.php (1 menit)

Buka file dan ganti 4 baris ini:

```php
$host = "sql100.byetcluster.com";      // 👈 Copy dari Database Host
$dbname = "b6_40348133_kaori";          // 👈 Copy dari Database Name
$username = "b6_40348133_kaori";        // 👈 Copy dari Database User
$password = "YOUR_PASSWORD_HERE";       // 👈 Tulis password Anda
```

---

## Step 3: Test Koneksi (30 detik)

Uncomment baris ini di `connect_byethost.php`:

```php
echo "✅ Koneksi database berhasil!<br>";
echo "Connected to: " . $dbname . " on " . $host . "<br>";
```

Buka di browser: `http://your-site.byethost.com/connect_byethost.php`

**Berhasil?** → Lanjut ke Step 4  
**Gagal?** → Cek kredensial lagi

---

## Step 4: Rename File (10 detik)

Via FTP atau File Manager:
```
connect_byethost.php  →  connect.php
```

---

## Step 5: Done! ✅

Sekarang semua file PHP di aplikasi Anda akan menggunakan koneksi ByetHost!

Test:
- Login: `http://your-site.byethost.com/index.php`
- Dashboard: `http://your-site.byethost.com/mainpage.php`

---

## 🆘 Troubleshooting Cepat

| Error | Solusi |
|-------|--------|
| "Koneksi gagal" | Cek password & host |
| "Database not found" | Import SQL dulu via phpMyAdmin |
| "Access denied" | Verifikasi username = database name |

---

**Total Time**: ~4 menit  
**Dokumentasi Lengkap**: Lihat `PANDUAN_KREDENSIAL_BYETHOST.md`
