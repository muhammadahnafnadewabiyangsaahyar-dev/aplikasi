# BUGFIX: Missing hitungStatusKehadiran() Function

## Problem
Error terjadi pada `rekapabsen.php` dan `view_absensi.php`:
```
PHP Fatal error: Uncaught Error: Call to undefined function hitungStatusKehadiran()
```

## Root Cause
File `calculate_status_kehadiran.php` kosong/terhapus, padahal file tersebut berisi fungsi `hitungStatusKehadiran()` yang dibutuhkan oleh:
- `rekapabsen.php` (line 41)
- `view_absensi.php` (line 35)

## Solution
1. **Restore `calculate_status_kehadiran.php`** dari backup
   - File: `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/calculate_status_kehadiran.php`
   - Restored from: `DELETED_FILES_BACKUP_20251104_224651/calculate_status_kehadiran.php`

## File Structure
```
aplikasi/
├── calculate_status_kehadiran.php  (RESTORED - Helper functions)
├── rekapabsen.php                  (Already includes helper - line 10)
└── view_absensi.php                (Already includes helper - line 7)
```

## Functions in calculate_status_kehadiran.php

### 1. `hitungStatusKehadiran($absensi_record, $pdo)`
Menghitung status kehadiran untuk satu record absensi.

**Logic:**
- **Belum absen keluar:**
  - Jika tanggal < hari ini → "Lupa Absen Pulang"
  - Jika tanggal = hari ini → "Belum Absen Keluar"
  
- **Admin:**
  - Durasi kerja ≥ 8 jam → "Hadir"
  - Durasi kerja < 8 jam → "Tidak Hadir"
  
- **User:**
  - Waktu keluar ≥ jam_keluar_shift → "Hadir"
  - Waktu keluar < jam_keluar_shift → "Tidak Hadir"

### 2. `updateAllStatusKehadiran($pdo, $tanggal)`
Batch update status kehadiran untuk semua absensi pada tanggal tertentu.
Digunakan untuk cron job atau batch processing.

## Implementation in Files

### rekapabsen.php (line 9-10)
```php
define('INCLUDED_FROM_WEB', true);
include 'calculate_status_kehadiran.php';
```

### view_absensi.php (line 6-7)
```php
define('INCLUDED_FROM_WEB', true);
include 'calculate_status_kehadiran.php';
```

### Usage Example (line 41 in rekapabsen.php)
```php
foreach ($daftar_absensi as &$absen) {
    $absen['status_kehadiran_calculated'] = hitungStatusKehadiran($absen, $pdo);
}
```

## Status
✅ **FIXED** - File restored and function is now available

## Testing
1. Access `rekapabsen.php` - Should display attendance records with calculated status
2. Access `view_absensi.php` - Should display attendance view without errors
3. Check error logs - No more "Call to undefined function" errors

## Notes
- File dapat dipanggil via CLI untuk batch update: `php calculate_status_kehadiran.php 2025-11-05`
- File menggunakan flag `INCLUDED_FROM_WEB` untuk membedakan web include vs CLI execution
- PSR standard: closing PHP tag dihilangkan untuk mencegah whitespace output

---
**Date:** November 5, 2025  
**Fixed by:** AI Assistant  
**Status:** Resolved ✅
