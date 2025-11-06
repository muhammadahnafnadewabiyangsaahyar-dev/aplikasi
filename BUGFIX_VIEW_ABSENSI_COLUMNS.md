# Fix: view_absensi.php - Column Name Mismatch

## Problem
```
PHP Fatal error: SQLSTATE[42S22]: Column not found: 1054 
Unknown column 'a.latitude_absen_masuk' in 'field list'
```

## Root Cause
The code in `view_absensi.php` was trying to query columns that don't exist in the `absensi` table:
- `latitude_absen_masuk` ❌
- `longitude_absen_masuk` ❌
- `latitude_absen_keluar` ❌
- `longitude_absen_keluar` ❌

## Actual Database Schema
The `absensi` table only has **one set of coordinates**, not separate ones for check-in and check-out:
- `latitude_absen` ✅
- `longitude_absen` ✅

## Changes Made

### 1. Main Query (Line 24-33)
**Before:**
```sql
SELECT a.id, a.tanggal_absensi, ...,
       a.latitude_absen_masuk, a.longitude_absen_masuk,
       a.latitude_absen_keluar, a.longitude_absen_keluar,
       ...
```

**After:**
```sql
SELECT a.id, a.tanggal_absensi, ...,
       a.latitude_absen, a.longitude_absen,
       ...
```

### 2. CSV Export - All Users (Line 77-99)
**Before:**
```php
fputcsv($output, ['ID', ..., 'Lat Masuk', 'Lng Masuk', 'Lat Keluar', 'Lng Keluar', ...]);
// ...
$absensi['latitude_absen_masuk'] ?? '-',
$absensi['longitude_absen_masuk'] ?? '-',
$absensi['latitude_absen_keluar'] ?? '-',
$absensi['longitude_absen_keluar'] ?? '-',
```

**After:**
```php
fputcsv($output, ['ID', ..., 'Latitude', 'Longitude', ...]);
// ...
$absensi['latitude_absen'] ?? '-',
$absensi['longitude_absen'] ?? '-',
```

### 3. Query Per User Export (Line 105-115)
**Before:**
```sql
SELECT a.id, ...,
       a.latitude_absen_masuk, a.longitude_absen_masuk,
       a.latitude_absen_keluar, a.longitude_absen_keluar,
       ...
```

**After:**
```sql
SELECT a.id, ...,
       a.latitude_absen, a.longitude_absen,
       ...
```

### 4. CSV Export Per User (Line 126-149)
**Before:**
```php
fputcsv($output, ['ID', ..., 'Lat Masuk', 'Lng Masuk', 'Lat Keluar', 'Lng Keluar', ...]);
// ...
$absensi['latitude_absen_masuk'] ?? '-',
$absensi['longitude_absen_masuk'] ?? '-',
$absensi['latitude_absen_keluar'] ?? '-',
$absensi['longitude_absen_keluar'] ?? '-',
```

**After:**
```php
fputcsv($output, ['ID', ..., 'Latitude', 'Longitude', ...]);
// ...
$absensi['latitude_absen'] ?? '-',
$absensi['longitude_absen'] ?? '-',
```

## Database Schema Reference
```sql
DESCRIBE absensi;

Relevant columns:
| Field             | Type          |
|-------------------|---------------|
| latitude_absen    | decimal(10,8) |
| longitude_absen   | decimal(11,8) |
| foto_absen_masuk  | varchar(255)  |
| foto_absen_keluar | varchar(255)  |
```

**Note:** 
- Photos are separated (masuk/keluar) ✅
- Coordinates are NOT separated (single location) ✅

## Impact
- ✅ Fixed fatal error preventing page load
- ✅ Main absensi query now works
- ✅ CSV exports (both full and per-user) now work
- ✅ Coordinates are correctly referenced
- ✅ No data loss or migration needed

## Testing
1. ✅ Load `view_absensi.php` - No fatal error
2. ✅ Query executes successfully
3. ✅ Data displays correctly
4. ✅ CSV export works (all users)
5. ✅ CSV export works (per user)

## Files Modified
- `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/view_absensi.php`
  - 4 locations updated
  - Lines: 24, 90, 109, 142

## Related Files
No other files were affected. The coordinate column names are correctly used in:
- `absen.php` (check-in/check-out logic)
- `rekapabsen.php` (attendance recap)

## Lesson Learned
When refactoring or adding new features, always verify the actual database schema before assuming column names. Use:
```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "USE aplikasi; DESCRIBE table_name;"
```

## Status
✅ **FIXED** - Page now loads successfully and all features work correctly.

---

**Fixed:** 2024-01-XX  
**Developer:** Development Team  
**Severity:** Critical (Fatal Error)  
**Time to Fix:** ~10 minutes
