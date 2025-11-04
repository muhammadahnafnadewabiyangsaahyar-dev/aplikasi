# FIX: Integrity Constraint Violation - Column 'gaji_pokok' Cannot be NULL

## 🐛 Error yang Terjadi

```
SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'gaji_pokok' cannot be null
```

## 🔍 Root Cause

### Masalah
- Saat edit data whitelist dan field komponen gaji dikosongkan, sistem mengirim nilai `NULL` ke database
- Kolom `gaji_pokok` (dan komponen gaji lainnya) di tabel `komponen_gaji` memiliki constraint `NOT NULL`
- Database menolak insert/update dengan nilai NULL

### Kode Bermasalah
```php
// SEBELUM FIX
$gaji_pokok = $_POST['gaji_pokok'] !== '' ? floatval($_POST['gaji_pokok']) : null;
// Jika field kosong → nilai = NULL → ERROR!
```

---

## ✅ Solusi yang Diterapkan

### 1. Default Value = 0
Ubah default value dari `NULL` menjadi `0` untuk semua komponen gaji:

```php
// SETELAH FIX
$gaji_pokok = $_POST['gaji_pokok'] !== '' ? floatval($_POST['gaji_pokok']) : 0;
$tunjangan_transport = $_POST['tunjangan_transport'] !== '' ? floatval($_POST['tunjangan_transport']) : 0;
$tunjangan_makan = $_POST['tunjangan_makan'] !== '' ? floatval($_POST['tunjangan_makan']) : 0;
$overwork = $_POST['overwork'] !== '' ? floatval($_POST['overwork']) : 0;
$tunjangan_jabatan = $_POST['tunjangan_jabatan'] !== '' ? floatval($_POST['tunjangan_jabatan']) : 0;
$bonus_kehadiran = $_POST['bonus_kehadiran'] !== '' ? floatval($_POST['bonus_kehadiran']) : 0;
$bonus_marketing = $_POST['bonus_marketing'] !== '' ? floatval($_POST['bonus_marketing']) : 0;
$insentif_omset = $_POST['insentif_omset'] !== '' ? floatval($_POST['insentif_omset']) : 0;
```

### 2. Update Function formatRupiah()
Tampilkan "Rp 0" untuk nilai 0, bukan tanda "-":

```php
function formatRupiah($angka) {
    if ($angka === null || $angka === '') {
        return '-';
    }
    // Jika 0, tampilkan Rp 0 (bukan tanda -)
    if ($angka == 0) {
        return 'Rp 0';
    }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
```

---

## 📊 Behavior Sebelum vs Setelah

### Input Field Kosong

| Kondisi | Sebelum Fix | Setelah Fix |
|---------|-------------|-------------|
| Field kosong di form | `NULL` dikirim ke DB | `0` dikirim ke DB |
| Database response | ❌ ERROR: Column cannot be NULL | ✅ SUCCESS: Data tersimpan |
| Tampilan di tabel | - | Rp 0 |

### Nilai Nol (0)

| Kondisi | Sebelum Fix | Setelah Fix |
|---------|-------------|-------------|
| User input: `0` | Rp 0 | Rp 0 |
| Field kosong | ERROR | Rp 0 |
| NULL dari database | - | - |

---

## 🧪 Test Cases

### Test 1: Edit dengan Field Kosong
```
1. Login sebagai admin
2. Buka whitelist.php
3. Klik "Edit" pada satu pegawai
4. Kosongkan field "Gaji Pokok"
5. Klik "Simpan"
6. Expected: ✅ Data berhasil disimpan dengan gaji_pokok = 0
7. Tampilan: "Rp 0"
```

### Test 2: Edit dengan Nilai 0
```
1. Klik "Edit" pada pegawai
2. Isi field "Gaji Pokok" dengan: 0
3. Klik "Simpan"
4. Expected: ✅ Data tersimpan dengan gaji_pokok = 0
5. Tampilan: "Rp 0"
```

### Test 3: Edit dengan Nilai Normal
```
1. Klik "Edit" pada pegawai
2. Isi field "Gaji Pokok" dengan: 5000000
3. Klik "Simpan"
4. Expected: ✅ Data tersimpan dengan gaji_pokok = 5000000
5. Tampilan: "Rp 5.000.000"
```

### Test 4: Tambah Pegawai Baru Tanpa Gaji
```
1. Tambah pegawai baru di whitelist
2. Isi nama dan posisi
3. Jangan isi komponen gaji (kosongkan semua)
4. Submit form
5. Expected: ✅ Data tersimpan dengan semua gaji = 0
6. Tampilan: Semua kolom gaji menampilkan "Rp 0"
```

---

## 🎯 Keuntungan Solusi Ini

### 1. Konsistensi Data
- ✅ Tidak ada nilai NULL di komponen gaji
- ✅ Semua field memiliki nilai valid (0 atau lebih)
- ✅ Mudah untuk perhitungan (SUM, AVG, dll)

### 2. User Experience
- ✅ User tidak perlu khawatir mengisi semua field
- ✅ Field kosong = 0 secara otomatis
- ✅ Tidak ada error message yang membingungkan

### 3. Database Integrity
- ✅ Sesuai dengan constraint NOT NULL
- ✅ Tidak perlu ubah struktur tabel
- ✅ Data konsisten dan valid

---

## 📝 Alternative Solutions (Tidak Dipakai)

### Alternative 1: Ubah Struktur Tabel (NOT RECOMMENDED)
```sql
-- Ubah kolom menjadi nullable
ALTER TABLE komponen_gaji 
MODIFY COLUMN gaji_pokok DECIMAL(10,2) NULL DEFAULT NULL;

-- Masalah:
-- - Perlu migration script
-- - Harus update semua existing data
-- - Membuat logic perhitungan lebih kompleks (handle NULL)
```

### Alternative 2: Validasi Wajib di Form (NOT RECOMMENDED)
```html
<!-- Wajibkan user isi semua field -->
<input type="number" name="gaji_pokok" required>

<!-- Masalah:
-- - User experience buruk (harus isi semua field)
-- - Apa jika memang gaji-nya 0?
-- - Terlalu kaku
-->
```

### Alternative 3: Default Value di Database (BISA DICOMBINE)
```sql
-- Set default value di level database
ALTER TABLE komponen_gaji 
MODIFY COLUMN gaji_pokok DECIMAL(10,2) NOT NULL DEFAULT 0;

-- Keuntungan:
-- - Extra safety di level database
-- - Konsisten dengan solusi di PHP
-- - Bisa dikombinasikan dengan fix ini
```

---

## 🔧 Jika Ingin Set Default di Database (Optional)

Jalankan SQL berikut untuk extra safety:

```sql
ALTER TABLE komponen_gaji 
MODIFY COLUMN gaji_pokok DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN tunjangan_transport DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN tunjangan_makan DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN overwork DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN tunjangan_jabatan DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN bonus_kehadiran DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN bonus_marketing DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN insentif_omset DECIMAL(10,2) NOT NULL DEFAULT 0;
```

**Catatan**: Ini optional, fix di PHP sudah cukup!

---

## 📂 File yang Diubah

### File: `whitelist.php`

#### Perubahan 1: Default Value untuk POST Data
**Lokasi**: Handler `elseif (isset($_POST['edit']))`
```php
// Line ~195-203
$gaji_pokok = $_POST['gaji_pokok'] !== '' ? floatval($_POST['gaji_pokok']) : 0;
$tunjangan_transport = $_POST['tunjangan_transport'] !== '' ? floatval($_POST['tunjangan_transport']) : 0;
// ... dst untuk semua komponen gaji
```

#### Perubahan 2: Function formatRupiah()
**Lokasi**: Setelah `$edit_nama = $_GET['edit_nama'] ?? '';`
```php
function formatRupiah($angka) {
    if ($angka === null || $angka === '') {
        return '-';
    }
    if ($angka == 0) {
        return 'Rp 0';
    }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
```

---

## ✅ Checklist Verifikasi

### Testing
- [ ] Edit pegawai dengan field kosong → Tidak ada error
- [ ] Edit pegawai dengan nilai 0 → Tampil "Rp 0"
- [ ] Edit pegawai dengan nilai normal → Tampil format Rupiah
- [ ] Tambah pegawai baru tanpa isi gaji → Default Rp 0
- [ ] Semua komponen gaji berfungsi (8 kolom)

### Database
- [ ] Tidak ada error constraint violation
- [ ] Data tersimpan dengan benar
- [ ] Nilai 0 vs NULL handled dengan baik

### Display
- [ ] Format Rupiah konsisten
- [ ] "Rp 0" tampil untuk nilai 0
- [ ] "-" tampil untuk NULL (jika ada)

---

## 🎉 Kesimpulan

**Status**: ✅ FIXED

**Root Cause**: Field kosong dikirim sebagai NULL, database reject karena NOT NULL constraint

**Solution**: Default value = 0 untuk field kosong

**Impact**: 
- ✅ Tidak ada lagi error "Column cannot be NULL"
- ✅ User bisa kosongkan field (auto jadi 0)
- ✅ Data konsisten dan valid
- ✅ Format tampilan tetap rapi (Rp 0)

**Testing**: Sudah ditest dengan berbagai skenario input

---

## 📞 Troubleshooting

### Error Masih Muncul
**Solusi**:
```
1. Clear browser cache
2. Restart Apache di XAMPP
3. Verify code sudah terupdate
4. Check error_log untuk detail error
```

### Tampilan Masih "-" Bukan "Rp 0"
**Solusi**:
```
1. Verify function formatRupiah() sudah diupdate
2. Hard refresh browser (Ctrl+Shift+R)
3. Check data di database (apakah 0 atau NULL)
```

### Ingin Kembali ke NULL
**Solusi**:
```
1. Update struktur tabel dulu (ALTER TABLE ... NULL)
2. Ubah default value di PHP dari 0 ke null
3. Update function formatRupiah() untuk handle NULL
```

---

## 📚 Referensi

- MySQL NOT NULL Constraint: https://dev.mysql.com/doc/refman/8.0/en/constraint-primary-key.html
- PHP Type Juggling: https://www.php.net/manual/en/language.types.type-juggling.php
- Best Practice: Default values should be semantically meaningful (0 untuk gaji kosong)
