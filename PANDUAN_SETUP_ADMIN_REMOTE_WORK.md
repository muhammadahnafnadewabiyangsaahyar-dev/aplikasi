# 🎯 PANDUAN: Setup Absensi untuk Admin/Remote Workers

**Tanggal**: 6 November 2024  
**Untuk**: Sistem KAORI HR - ByetHost Deployment

---

## 📋 RINGKASAN KEBUTUHAN

### Logika Admin/Remote Workers:
1. ✅ **Tidak terikat outlet khusus** - bisa bekerja dari mana saja
2. ✅ **Tidak terikat shift** - flexible working hours
3. ✅ **Tetap harus absen** - untuk tracking waktu kerja
4. ✅ **Target 8 jam kerja per hari**
5. ✅ **Batas absen**: 07:00 - 23:59
6. ✅ **Validasi lokasi di-skip** - karena remote

---

## 🔧 LANGKAH IMPLEMENTASI

### Step 1: Buat Cabang "Kaori HQ" di Database

Login ke **phpMyAdmin** di ByetHost, pilih database `b6_40348133_kaori`, lalu jalankan SQL ini:

```sql
-- Buat cabang khusus untuk admin/remote workers
INSERT INTO cabang (
    nama_cabang, 
    lokasi, 
    latitude, 
    longitude, 
    radius_meter, 
    jam_masuk, 
    jam_keluar, 
    nama_shift, 
    created_at,
    updated_at
) 
VALUES (
    'Kaori HQ',                    -- Nama cabang
    'Remote/Anywhere',             -- Lokasi (simbolis)
    '0',                           -- Latitude (0 = simbolis, tidak digunakan)
    '0',                           -- Longitude (0 = simbolis, tidak digunakan)
    999999999,                     -- Radius SANGAT BESAR (auto-accept semua lokasi)
    '00:00:00',                    -- Jam masuk (fleksibel, mulai dari tengah malam)
    '23:59:59',                    -- Jam keluar (sampai akhir hari)
    'Flexible',                    -- Nama shift
    NOW(),                         -- Created at
    NOW()                          -- Updated at
);
```

**Catat ID cabang yang dibuat!** Cek dengan:
```sql
SELECT * FROM cabang WHERE nama_cabang = 'Kaori HQ';
```

Output contoh:
```
id: 10
nama_cabang: Kaori HQ
lokasi: Remote/Anywhere
radius_meter: 999999999
jam_masuk: 00:00:00
jam_keluar: 23:59:59
nama_shift: Flexible
```

---

### Step 2: Assign Cabang "Kaori HQ" ke Semua Admin

Jalankan SQL ini untuk update semua user dengan role 'admin':

```sql
-- Update outlet_id admin ke cabang Kaori HQ
UPDATE register 
SET outlet_id = 10,              -- Ganti 10 dengan ID cabang Kaori HQ dari Step 1
    updated_at = NOW()
WHERE role = 'admin';
```

**Verifikasi:**
```sql
SELECT id, username, nama_lengkap, role, outlet_id 
FROM register 
WHERE role = 'admin';
```

Output harus menunjukkan `outlet_id = 10` (atau ID cabang Kaori HQ Anda).

---

### Step 3: Update File `proses_absensi.php` ✅

**SUDAH SELESAI!** File sudah diupdate dengan logika:
- Admin menggunakan cabang "Kaori HQ"
- Validasi lokasi di-bypass
- Status lokasi: "Remote - Kaori HQ"
- Fallback ke cabang manapun jika Kaori HQ belum dibuat

---

### Step 4: Upload File yang Sudah Diupdate

Upload file ini ke ByetHost (replace yang lama):
```
✅ proses_absensi.php  (sudah diupdate di lokal)
```

Via FTP atau File Manager ByetHost.

---

## 📊 CARA KERJA SISTEM

### Untuk Admin (Role = 'admin'):

#### Saat Absen Masuk:
1. ✅ Klik "Absen Masuk" (bisa dari mana saja)
2. ✅ Sistem ambil cabang "Kaori HQ"
3. ✅ **Skip validasi lokasi** (karena remote)
4. ✅ Record absensi dengan `status_lokasi = "Remote - Kaori HQ"`
5. ✅ Waktu masuk dicatat
6. ✅ Target keluar: 8 jam dari masuk

#### Saat Absen Keluar:
1. ✅ Klik "Absen Keluar" (dari mana saja)
2. ✅ Sistem update `waktu_keluar`
3. ✅ Hitung durasi kerja
4. ✅ Jika > 8 jam, ditanya overwork
5. ✅ Record tersimpan

### Untuk User Biasa (Role != 'admin'):

#### Saat Absen Masuk/Keluar:
1. ✅ **Harus di lokasi outlet** (validasi GPS)
2. ✅ Check shift assignment hari ini
3. ✅ Validasi jarak ke outlet (radius)
4. ✅ Validasi jam masuk sesuai shift
5. ✅ Record absensi

---

## 🔍 TESTING

### Test Admin Absen:

1. **Login sebagai admin** (superadmin)
2. **Buka halaman absen**: `http://kaoriapp.byethost6.com/absen.php`
3. **Klik "Absen Masuk"**
   - Bisa dari lokasi manapun
   - Tidak ada error "lokasi terlalu jauh"
4. **Cek database**:
   ```sql
   SELECT * FROM absensi WHERE user_id = 1 ORDER BY tanggal_absensi DESC LIMIT 1;
   ```
5. **Verifikasi**:
   - `status_lokasi` = "Remote - Kaori HQ"
   - `outlet_id` = ID cabang Kaori HQ
   - `waktu_masuk` tercatat

### Test User Biasa:

1. **Login sebagai user** (bukan admin)
2. **Buka halaman absen**
3. **Cek validasi lokasi tetap berfungsi**
   - Jika di luar radius → error
   - Jika di dalam radius → sukses

---

## 📝 VALIDASI YANG TETAP BERLAKU

### Untuk SEMUA (Admin & User):

| Validasi | Status | Keterangan |
|----------|--------|------------|
| **Jam Absen** | ✅ Aktif | 07:00 - 23:59 (untuk semua) |
| **CSRF Token** | ✅ Aktif | Security |
| **Rate Limiting** | ✅ Aktif | Max 10x per jam |
| **Mock Location Detection** | ✅ Aktif | Cegah GPS fake |
| **Time Manipulation** | ✅ Aktif | Cegah ubah waktu |

### Khusus Admin:

| Validasi | Status | Keterangan |
|----------|--------|------------|
| **Validasi Lokasi GPS** | ❌ Skip | Remote work allowed |
| **Validasi Shift** | ❌ Skip | Flexible schedule |
| **Validasi Radius Outlet** | ❌ Skip | Bisa dari mana saja |

### Khusus User:

| Validasi | Status | Keterangan |
|----------|--------|------------|
| **Validasi Lokasi GPS** | ✅ Aktif | Harus di outlet |
| **Validasi Shift** | ✅ Aktif | Sesuai jadwal |
| **Validasi Radius Outlet** | ✅ Aktif | Dalam radius |

---

## 🎯 BUSINESS RULES

### Admin Target Kerja:
- **Minimal 8 jam per hari**
- **Batas absen**: sampai 23:59
- **Overwork**: Jika > 8 jam, ada konfirmasi lembur
- **Lokasi**: Anywhere (remote-friendly)
- **Laporan**: Tetap masuk ke laporan gaji

### Perhitungan Gaji Admin:
```php
// Durasi kerja admin
$durasi_menit = ($waktu_keluar_ts - $waktu_masuk_ts) / 60;
$durasi_jam = $durasi_menit / 60;

// Jika < 8 jam: mungkin potong atau alpha
// Jika >= 8 jam: normal
// Jika > 8 jam: + upah lembur (jika approved)
```

---

## 🐛 TROUBLESHOOTING

### Error: "Data cabang tidak ditemukan"
**Penyebab**: Cabang "Kaori HQ" belum dibuat  
**Solusi**: Jalankan SQL Step 1

### Error: "Lokasi terlalu jauh" (untuk admin)
**Penyebab**: 
1. File `proses_absensi.php` belum diupdate
2. User `outlet_id` belum di-set ke Kaori HQ

**Solusi**:
1. Upload `proses_absensi.php` terbaru
2. Jalankan SQL Step 2

### Admin tidak bisa absen
**Cek**:
1. Role di database = 'admin'?
   ```sql
   SELECT role FROM register WHERE id = 1;
   ```
2. Outlet_id sudah di-set?
   ```sql
   SELECT outlet_id FROM register WHERE id = 1;
   ```
3. Cabang Kaori HQ ada?
   ```sql
   SELECT * FROM cabang WHERE nama_cabang = 'Kaori HQ';
   ```

---

## ✅ CHECKLIST IMPLEMENTASI

Sebelum production, pastikan:

### Database Setup
- [ ] Cabang "Kaori HQ" sudah dibuat
- [ ] ID cabang dicatat
- [ ] Semua admin `outlet_id` sudah diupdate
- [ ] Verifikasi dengan SELECT query

### File Update
- [ ] `proses_absensi.php` sudah diupdate
- [ ] File sudah diupload ke ByetHost
- [ ] Backup file lama (jaga-jaga)

### Testing
- [ ] Test login sebagai admin
- [ ] Test absen masuk admin (dari lokasi manapun)
- [ ] Test absen keluar admin
- [ ] Verifikasi data di database
- [ ] Test user biasa (validasi lokasi masih jalan)

### Production
- [ ] Semua admin bisa absen remote
- [ ] User biasa masih tervalidasi lokasi
- [ ] Laporan absensi lengkap
- [ ] Gaji terhitung dengan benar

---

## 📞 SUPPORT

Jika ada masalah:
1. Cek error log: ByetHost Control Panel → Error Logs
2. Cek database: phpMyAdmin → tabel `absensi`
3. Gunakan `debug_absen.php` untuk diagnostic
4. Review log file untuk detail error

---

## 🎉 BENEFIT

### Untuk Admin:
✅ Fleksibilitas kerja remote  
✅ Tidak perlu ke kantor untuk absen  
✅ Tetap tracked waktu kerjanya  
✅ Upah lembur tetap dihitung

### Untuk Sistem:
✅ Data absensi lengkap (admin + user)  
✅ Laporan akurat  
✅ Gaji calculated otomatis  
✅ Security tetap terjaga

### Untuk Perusahaan:
✅ Support remote work culture  
✅ Monitoring produktivitas  
✅ Cost efficiency  
✅ Modern HR system

---

**Status**: ✅ READY FOR IMPLEMENTATION  
**Last Updated**: 6 November 2024  
**Next Action**: Jalankan SQL Step 1 & 2 di ByetHost
