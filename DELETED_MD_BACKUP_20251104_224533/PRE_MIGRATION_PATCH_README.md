# Pre-Migration Patch - Penjelasan

## 🎯 Tujuan

Pre-migration patch ini menambahkan kolom `id_cabang` ke tabel `pegawai_whitelist` dan `register` sebelum menjalankan migration utama. Kolom ini diperlukan untuk:

1. **Mengaitkan pegawai dengan cabang tertentu**
2. **Memudahkan assignment shift per cabang**
3. **Mempermudah laporan payroll per cabang**
4. **Kompatibilitas dengan sistem shift management**

## 📋 Apa yang Dilakukan Patch Ini?

### 1. Menambahkan `id_cabang` ke `pegawai_whitelist`
```sql
ALTER TABLE pegawai_whitelist
  ADD COLUMN id_cabang INT(11) DEFAULT NULL;
```

### 2. Menambahkan `id_cabang` ke `register`
```sql
ALTER TABLE register
  ADD COLUMN id_cabang INT(11) DEFAULT NULL;
```

### 3. Mapping Otomatis Data Existing
Script akan otomatis memetakan data yang sudah ada:
- Kolom `outlet` di tabel `register` akan dicocokkan dengan `nama_cabang` di tabel `cabang`
- Jika cocok, `id_cabang` akan diisi otomatis

Contoh:
- Jika `outlet` = "Cabang Makassar" dan ada `cabang.nama_cabang` = "Cabang Makassar"
- Maka `id_cabang` akan diisi dengan ID cabang tersebut

## ⚠️ Penting!

### Data yang Sudah Ada
Patch ini akan:
- ✅ Mempertahankan semua data existing
- ✅ Tidak menghapus apapun
- ✅ Hanya menambahkan kolom baru
- ✅ Otomatis mapping data outlet → cabang

### Jika Outlet Tidak Cocok
Jika ada user dengan `outlet` yang tidak cocok dengan `nama_cabang` manapun:
- Kolom `id_cabang` akan tetap `NULL`
- Anda perlu update manual setelah migration:
  ```sql
  UPDATE register SET id_cabang = 1 WHERE id = <user_id>;
  ```

## 🔍 Verifikasi Setelah Patch

Jalankan query ini untuk cek hasil mapping:

```sql
-- Cek struktur tabel register
DESCRIBE register;

-- Cek mapping outlet → cabang
SELECT 
    r.id, 
    r.nama_lengkap, 
    r.outlet, 
    r.id_cabang, 
    c.nama_cabang 
FROM register r 
LEFT JOIN cabang c ON r.id_cabang = c.id;

-- Cek user yang belum ter-mapping
SELECT id, nama_lengkap, outlet, id_cabang 
FROM register 
WHERE id_cabang IS NULL;
```

## 🔄 Rollback Jika Diperlukan

Jika ada masalah, rollback dengan:

```sql
START TRANSACTION;
ALTER TABLE pegawai_whitelist DROP FOREIGN KEY fk_whitelist_cabang;
ALTER TABLE pegawai_whitelist DROP KEY idx_cabang;
ALTER TABLE pegawai_whitelist DROP COLUMN id_cabang;

ALTER TABLE register DROP FOREIGN KEY fk_register_cabang;
ALTER TABLE register DROP KEY idx_cabang;
ALTER TABLE register DROP COLUMN id_cabang;
COMMIT;
```

Atau gunakan backup:
```bash
mysql -u root -p aplikasi < backup_pre_migration_YYYYMMDD_HHMMSS.sql
```

## 📊 Keuntungan Setelah Patch

1. **Relasi Data Lebih Kuat**: Pegawai secara eksplisit terkait dengan cabang
2. **Query Lebih Cepat**: Index pada `id_cabang` mempercepat pencarian
3. **Shift Management**: Memudahkan assignment shift per cabang
4. **Laporan Payroll**: Mudah filter dan laporan per cabang
5. **Data Integrity**: Foreign key constraint menjaga konsistensi data

## 🚀 Cara Menjalankan

### Otomatis (Recommended)
```bash
chmod +x backup_and_migrate.sh
./backup_and_migrate.sh
```

Script akan otomatis:
1. Backup database
2. Run pre-migration patch
3. Run main migration

### Manual
```bash
# 1. Backup dulu
mysqldump -u root -p aplikasi > backup.sql

# 2. Run pre-migration patch
mysql -u root -p aplikasi < pre_migration_patch.sql

# 3. Verifikasi
mysql -u root -p aplikasi -e "DESCRIBE register;"

# 4. Run main migration
mysql -u root -p aplikasi < migration_shift_enhancement.sql
```

## ❓ FAQ

### Q: Apakah ini wajib?
**A:** Ya, untuk sistem shift management yang optimal. Tanpa `id_cabang`, Anda harus selalu mengandalkan field `outlet` (text) yang tidak efisien dan rawan error.

### Q: Apakah data saya aman?
**A:** Ya, patch ini hanya menambahkan kolom baru dan mapping data. Tidak ada data yang dihapus.

### Q: Bagaimana jika outlet user tidak cocok dengan nama cabang?
**A:** `id_cabang` akan `NULL`. Anda bisa update manual setelah migration, atau edit `nama_cabang` di tabel `cabang` agar cocok dengan `outlet`.

### Q: Apakah bisa rollback?
**A:** Ya, gunakan backup atau jalankan rollback script yang disediakan.

---

**Dibuat:** 2025-01-XX  
**Versi:** 1.0.0  
**Compatibility:** MariaDB 10.4+, MySQL 8.0+
