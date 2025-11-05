# 📸 Pemisahan Foto Absen Masuk & Keluar - Dokumentasi

## Overview
Update sistem absensi untuk memisahkan foto absen masuk dan foto absen keluar menjadi kolom terpisah di database dan interface.

## Tanggal Update
**2025-11-05**

## Perubahan Database

### Tabel `absensi` - Kolom Baru:
```sql
-- Kolom foto
foto_absen_masuk      VARCHAR(255)    -- Foto saat absen masuk
foto_absen_keluar     VARCHAR(255)    -- Foto saat absen keluar

-- Kolom lokasi masuk
latitude_absen_masuk  DECIMAL(10,8)   -- Latitude saat masuk
longitude_absen_masuk DECIMAL(11,8)   -- Longitude saat masuk

-- Kolom lokasi keluar  
latitude_absen_keluar DECIMAL(10,8)   -- Latitude saat keluar
longitude_absen_keluar DECIMAL(11,8)  -- Longitude saat keluar
```

## File yang Diupdate

### 1. **proses_absensi.php** ✅
- ABSEN MASUK: Simpan ke `foto_absen_masuk`, `latitude_absen_masuk`, `longitude_absen_masuk`
- ABSEN KELUAR: Simpan ke `foto_absen_keluar`, `latitude_absen_keluar`, `longitude_absen_keluar`

### 2. **view_absensi.php** ✅
- Query SELECT mencakup kolom foto dan lokasi masuk & keluar
- Tabel HTML menampilkan 2 kolom: "Foto Masuk" dan "Foto Keluar"
- CSV export mencakup kedua foto dan koordinat

### 3. **migration_satukan_absensi.sql** ✅
- SQL migration script untuk alter table
- Backup dan rollback instructions

## Keuntungan Pemisahan Foto

### 1. **Audit Trail Lengkap** ✅
- Verifikasi kehadiran saat masuk DAN keluar
- Deteksi fraud (orang lain yang absen keluar)

### 2. **Compliance** ✅
- Memenuhi standar audit kehadiran
- Data lengkap untuk verifikasi lembur

### 3. **User Experience** ✅
- User dapat melihat foto mereka saat masuk dan keluar
- Admin dapat compare foto masuk vs keluar

## Testing Checklist

- [ ] Absen masuk dengan foto → tersimpan di foto_absen_masuk
- [ ] Absen keluar dengan foto → tersimpan di foto_absen_keluar
- [ ] View absensi tampilkan kedua foto
- [ ] CSV export include foto_masuk dan foto_keluar

---

**Status**: ✅ **IMPLEMENTED**  
**Date**: 2025-11-05
