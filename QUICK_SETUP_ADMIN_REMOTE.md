# 🚀 QUICK SETUP - Admin Remote Work

## Step 1: Buat Cabang "Kaori HQ" (2 menit)

Login phpMyAdmin → Pilih database `b6_40348133_kaori` → SQL tab → Run:

```sql
INSERT INTO cabang (nama_cabang, lokasi, latitude, longitude, radius_meter, jam_masuk, jam_keluar, nama_shift, created_at, updated_at) 
VALUES ('Kaori HQ', 'Remote/Anywhere', '0', '0', 999999999, '00:00:00', '23:59:59', 'Flexible', NOW(), NOW());
```

**Catat ID-nya:**
```sql
SELECT id FROM cabang WHERE nama_cabang = 'Kaori HQ';
```

---

## Step 2: Assign ke Admin (1 menit)

Ganti `10` dengan ID dari Step 1:

```sql
UPDATE register SET outlet_id = 10 WHERE role = 'admin';
```

---

## Step 3: Upload File (1 menit)

Upload file yang sudah diupdate:
- ✅ `proses_absensi.php` (sudah fix di lokal)

Via FTP/File Manager ByetHost.

---

## Step 4: Test (30 detik)

1. Login sebagai admin
2. Buka `/absen.php`
3. Klik "Absen Masuk"
4. ✅ Sukses! (dari lokasi manapun)

---

## ✅ Done!

Admin sekarang bisa:
- ✅ Absen dari mana saja (remote)
- ✅ Flexible hours (00:00-23:59)
- ✅ Tidak perlu validasi GPS
- ✅ Data tetap tercatat untuk gaji

User biasa tetap:
- ✅ Harus di lokasi outlet
- ✅ Terikat shift
- ✅ Validasi GPS aktif

---

**Total Time**: ~5 menit  
**Dokumentasi Lengkap**: `PANDUAN_SETUP_ADMIN_REMOTE_WORK.md`
