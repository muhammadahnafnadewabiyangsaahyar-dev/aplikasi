# 📸 Foto Absen Masuk & Keluar - Update Summary

## ✅ Perubahan yang Dilakukan

### 1. Database Schema (Sudah di migration_satukan_absensi.sql)
```sql
-- Kolom foto terpisah
foto_absen_masuk      VARCHAR(255)  -- Foto saat absen masuk
foto_absen_keluar     VARCHAR(255)  -- Foto saat absen keluar

-- Lokasi terpisah
latitude_absen_masuk   DECIMAL(10,8)
longitude_absen_masuk  DECIMAL(11,8)
latitude_absen_keluar  DECIMAL(10,8)
longitude_absen_keluar DECIMAL(11,8)
```

### 2. File PHP yang Diupdate

#### A. **proses_absensi.php** ✅
- **Absen Masuk**: Menyimpan foto ke `foto_absen_masuk`
- **Absen Keluar**: Menyimpan foto ke `foto_absen_keluar`
- Latitude dan longitude terpisah untuk masuk dan keluar
- Naming convention foto: 
  - Masuk: `masuk_{user_id}_{tanggal}_{timestamp}.jpg`
  - Keluar: `keluar_{user_id}_{tanggal}_{timestamp}.jpg`

#### B. **rekapabsen.php** ✅
Struktur tabel baru:
```
| Tanggal | Waktu Masuk | Waktu Keluar | Status Lokasi | 
| Foto Masuk | Foto Keluar | Status Keterlambatan | Potongan |
| Status Kehadiran | Status Overwork |
```

**Fitur:**
- ✅ Foto masuk dan keluar ditampilkan terpisah
- ✅ Foto dapat diklik untuk preview (open in new tab)
- ✅ Fallback jika foto tidak ada: "-" atau "(File tidak ditemukan)"
- ✅ Thumbnail size: 60x60px (clickable)

#### C. **view_absensi.php** ✅
Struktur tabel sama dengan rekapabsen.php

**Fitur:**
- ✅ Foto masuk dan keluar terpisah
- ✅ Export CSV include foto masuk dan keluar
- ✅ Filter dan search tetap berfungsi
- ✅ Tabel rekap harian tetap ada

## 📊 Struktur Tabel Lengkap

### Tabel Utama (Riwayat Absensi Bulanan)
```
┌─────────────────┬──────────────┬──────────────┬──────────────┬──────────────┬──────────────┐
│ Tanggal Absensi │ Waktu Masuk  │ Waktu Keluar │ Status Lokasi│ Foto Masuk   │ Foto Keluar  │
├─────────────────┼──────────────┼──────────────┼──────────────┼──────────────┼──────────────┤
│ Status Keterlam │ Potongan     │ Status       │ Status       │              │              │
│ batan           │ Tunjangan    │ Kehadiran    │ Overwork     │              │              │
└─────────────────┴──────────────┴──────────────┴──────────────┴──────────────┴──────────────┘
```

### Tabel Rekap Harian (Hari Ini)
```
┌─────────────────┬──────────────────┬──────────────┬──────────────┬──────────────┐
│ Nama Lengkap    │ Status Absen     │ Waktu Masuk  │ Waktu Keluar │ Overwork     │
│                 │ Hari Ini         │              │              │              │
└─────────────────┴──────────────────┴──────────────┴──────────────┴──────────────┘
```

## 🔍 Validasi & Testing

### Testing Checklist:
- [x] Absen masuk dengan foto → Foto tersimpan di `foto_absen_masuk`
- [x] Absen keluar dengan foto → Foto tersimpan di `foto_absen_keluar`
- [x] Absen keluar tanpa foto → Kolom `foto_absen_keluar` = NULL
- [x] Foto ditampilkan di rekapabsen.php (user view)
- [x] Foto ditampilkan di view_absensi.php (admin view)
- [x] Foto dapat diklik untuk preview full size
- [x] CSV export include kedua foto
- [x] Backward compatibility (data lama dengan `foto_absen` tetap work)

## 📁 File Struktur

```
/aplikasi/
├── proses_absensi.php               # ✅ Updated (save foto masuk & keluar)
├── rekapabsen.php                   # ✅ Updated (display foto terpisah)
├── view_absensi.php                 # ✅ Updated (display foto terpisah)
├── migration_satukan_absensi.sql    # ✅ Database migration
└── uploads/
    └── absensi/
        ├── masuk_1_2025-11-05_xxx.jpg
        ├── keluar_1_2025-11-05_yyy.jpg
        └── ...
```

## 🎨 UI/UX Improvements

### Foto Display:
- **Size**: 60x60px thumbnail (auto height)
- **Clickable**: Open full image in new tab
- **Hover**: Pointer cursor
- **Missing**: Graceful fallback text
- **Style**: Clean, consistent with table design

### Status Colors:
- **Green**: Hadir, Tepat Waktu, No Potongan
- **Orange**: Terlambat < 40 menit, Pending Overwork
- **Red**: Terlambat 40+ menit, Tidak Hadir, Potongan Full
- **Purple**: Di luar shift (perlu review)
- **Gray**: Belum absen keluar, No data

## 🔄 Migration Path

### Untuk Data Existing:
1. Jalankan `migration_satukan_absensi.sql`
2. Data lama di `foto_absen` akan di-rename ke `foto_absen_masuk`
3. `foto_absen_keluar` akan NULL untuk data lama
4. Latitude/longitude lama akan di-rename ke `latitude_absen_masuk` dan `longitude_absen_masuk`

### Untuk Data Baru:
1. User absen masuk → Foto ke `foto_absen_masuk`
2. User absen keluar → Foto ke `foto_absen_keluar` (opsional)
3. Kedua foto akan ditampilkan terpisah di tabel

## 📝 Notes

### Foto Absen Keluar (Optional):
- Foto absen keluar **tidak wajib**
- Jika user tidak upload foto saat absen keluar, kolom akan NULL
- System tetap update waktu keluar meskipun tanpa foto
- Display akan show "-" atau "Tidak ada foto keluar"

### Backward Compatibility:
- Data lama dengan kolom `foto_absen` akan di-migrate ke `foto_absen_masuk`
- Query existing tetap work dengan kolom baru
- Tidak ada breaking changes untuk fungsi lain

## 🚀 Next Steps (Optional)

1. **Compress foto** sebelum save (reduce storage)
2. **Thumbnail generation** untuk performa
3. **Lazy loading** untuk banyak foto
4. **Photo viewer modal** (instead of new tab)
5. **Compare foto** masuk vs keluar side-by-side
6. **Face detection** untuk validasi foto wajah
7. **Photo metadata** (EXIF: time, location, device)

---

**Status**: ✅ Complete  
**Tested**: ✅ Yes  
**Production Ready**: ✅ Yes  
**Documentation**: ✅ Complete
