# 🎯 Quick Fix Summary: Foto Absen Masuk & Keluar

## Problem
Tabel absensi menampilkan foto tidak jelas (masuk atau keluar), dan strukturnya tidak rapi.

## Solution
Pisahkan kolom foto menjadi **Foto Absen Masuk** dan **Foto Absen Keluar** yang terpisah.

## Changes Made

### 1. Database (Run SQL Migration)
```bash
# Jalankan file migration
mysql -u root -p aplikasi < migration_satukan_absensi.sql
```

### 2. PHP Files Updated
- ✅ `proses_absensi.php` - Save foto ke kolom terpisah
- ✅ `rekapabsen.php` - Display foto masuk & keluar (10 kolom)
- ✅ `view_absensi.php` - Display foto masuk & keluar (admin view)

### 3. Struktur Tabel Baru

**rekapabsen.php & view_absensi.php:**
```
┌──────────┬────────┬────────┬────────┬─────────┬─────────┬──────────┬─────────┬─────────┬──────────┐
│ Tanggal  │ Masuk  │ Keluar │ Lokasi │ Foto    │ Foto    │ Terlam-  │ Potong- │ Kehad-  │ Over-    │
│ Absensi  │        │        │        │ Masuk   │ Keluar  │ bat      │ an      │ iran    │ work     │
└──────────┴────────┴────────┴────────┴─────────┴─────────┴──────────┴─────────┴─────────┴──────────┘
```

## Before vs After

### BEFORE ❌
```
| Foto Absen | → Tidak jelas, masuk atau keluar?
```

### AFTER ✅
```
| Foto Masuk | Foto Keluar | → Jelas terpisah!
```

## Testing

```bash
# 1. Test absen masuk dengan foto
- Foto tersimpan di foto_absen_masuk ✓
- Ditampilkan di kolom "Foto Masuk" ✓

# 2. Test absen keluar dengan foto
- Foto tersimpan di foto_absen_keluar ✓
- Ditampilkan di kolom "Foto Keluar" ✓

# 3. Test absen keluar tanpa foto
- foto_absen_keluar = NULL ✓
- Display show "-" ✓

# 4. Test view di rekapabsen.php
- User bisa lihat foto masuk & keluar ✓
- Foto dapat diklik untuk preview ✓

# 5. Test view di view_absensi.php (admin)
- Admin bisa lihat semua foto ✓
- Export CSV include kedua foto ✓
```

## Files Modified

```
📝 proses_absensi.php          - Line ~440: INSERT foto_absen_masuk
                                - Line ~515: UPDATE foto_absen_keluar
                                
📝 rekapabsen.php              - Line 60: Header tabel (10 kolom)
                                - Line 86-120: Display foto terpisah
                                
📝 view_absensi.php            - Line 73: CSV export (kedua foto)
                                - Line 246: Header tabel (12 kolom)
                                - Line 268-290: Display foto terpisah
```

## Rollback (If Needed)

```sql
-- Kembalikan nama kolom (jangan lakukan ini jika sudah production!)
ALTER TABLE absensi 
CHANGE COLUMN foto_absen_masuk foto_absen VARCHAR(255);

ALTER TABLE absensi 
DROP COLUMN foto_absen_keluar;
```

---

**Date**: 2025-11-05  
**Status**: ✅ DONE  
**Tested**: ✅ YES  
**Ready**: ✅ PRODUCTION READY
