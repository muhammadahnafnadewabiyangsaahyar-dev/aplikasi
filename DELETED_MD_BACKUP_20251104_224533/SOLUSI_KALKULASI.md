# SOLUSI KALKULASI TUNJANGAN DAN OVERWORK

## 🎯 Masalah yang Diidentifikasi

Berdasarkan pemeriksaan tabel `aplikasi.sql`:

### ❌ Masalah 1: Tunjangan Belum Ada di Database
- Tabel `register` **TIDAK memiliki** kolom untuk tunjangan makan dan transport
- Tabel `pegawai_whitelist` juga tidak memiliki kolom salary
- Data salary kemungkinan disimpan di sistem lain atau belum ada

### ❌ Masalah 2: Overwork Belum Ada di Database
- Tabel `absensi` hanya memiliki `status_lembur` (Pending/Approved/Rejected)
- **TIDAK ada** kolom untuk menyimpan durasi overwork atau tarif per jam
- Sistem saat ini hanya melacak status approval, bukan kalkulasi pembayaran

## ✅ Solusi yang Sudah Disiapkan

### 1. Migration Script Menambahkan Kolom Salary ke `register`
File: `migration_shift_enhancement.sql` (baris 150-160)

```sql
ALTER TABLE register
  ADD COLUMN gaji_pokok DECIMAL(15,2) DEFAULT 0 
    COMMENT 'Base salary per month',
  ADD COLUMN tunjangan_transport DECIMAL(15,2) DEFAULT 0 
    COMMENT 'Total transport allowance for 26 days',
  ADD COLUMN tunjangan_makan DECIMAL(15,2) DEFAULT 0 
    COMMENT 'Total meal allowance for 26 days',
  ADD COLUMN tunjangan_jabatan DECIMAL(15,2) DEFAULT 0 
    COMMENT 'Position allowance per month',
  ADD COLUMN upah_overwork_per_8jam DECIMAL(10,2) DEFAULT 50000 
    COMMENT 'Overwork pay for 8 hours';
```

**Penjelasan:**
- `tunjangan_transport` dan `tunjangan_makan` = **Total untuk 26 hari kerja**
- `upah_overwork_per_8jam` = **Upah untuk 8 jam kerja lembur**
- Nilai akan di-proporsi saat kalkulasi payroll

### 2. Migration Script Menambahkan Kolom Overwork ke `absensi`
File: `migration_shift_enhancement.sql` (baris 153-159)

```sql
ALTER TABLE absensi
  ADD COLUMN cabang_id INT(11) DEFAULT NULL,
  ADD COLUMN jam_masuk_shift TIME DEFAULT NULL,
  ADD COLUMN jam_keluar_shift TIME DEFAULT NULL,
  ADD COLUMN durasi_kerja_menit INT(11) DEFAULT 0,
  ADD COLUMN durasi_overwork_menit INT(11) DEFAULT 0,
  ADD COLUMN is_overwork_approved BOOLEAN DEFAULT FALSE;
```

**Penjelasan:**
- `durasi_kerja_menit` = Total menit bekerja
- `durasi_overwork_menit` = Total menit lembur (otomatis dihitung)
- `is_overwork_approved` = Status approval untuk pembayaran

### 3. Tabel Baru untuk Detail Payroll
File: `migration_shift_enhancement.sql` (tabel `komponen_gaji_detail`)

```sql
CREATE TABLE komponen_gaji_detail (
  ...
  overwork_amount DECIMAL(15,2) DEFAULT 0,
  overwork_hours DECIMAL(5,2) DEFAULT 0,
  jumlah_hadir INT(11) DEFAULT 0,
  ...
);
```

## 📊 Cara Kalkulasi yang Benar

### Kalkulasi Tunjangan (Proporsional)

**Data Tersimpan di Database:**
```
tunjangan_makan = 260000      (total untuk 26 hari)
tunjangan_transport = 390000  (total untuk 26 hari)
```

**Cara Kalkulasi per Bulan:**
```php
$jumlah_hadir = 22; // dari absensi
$tunjangan_makan_per_hari = 260000 / 26;    // = 10000
$tunjangan_transport_per_hari = 390000 / 26; // = 15000

$total_tunjangan_makan = $tunjangan_makan_per_hari * $jumlah_hadir;     // = 220000
$total_tunjangan_transport = $tunjangan_transport_per_hari * $jumlah_hadir; // = 330000
```

**Query SQL:**
```sql
SELECT 
    (tunjangan_makan / 26) * jumlah_hadir AS total_tunjangan_makan,
    (tunjangan_transport / 26) * jumlah_hadir AS total_tunjangan_transport
FROM register r
JOIN komponen_gaji_detail k ON r.id = k.user_id
WHERE k.periode_bulan = 1 AND k.periode_tahun = 2025;
```

### Kalkulasi Overwork (Per Jam)

**Data Tersimpan di Database:**
```
upah_overwork_per_8jam = 50000  (untuk 8 jam)
durasi_overwork_menit = 90      (1.5 jam)
```

**Cara Kalkulasi:**
```php
$upah_per_jam = 50000 / 8;  // = 6250 per jam
$durasi_jam = 90 / 60;      // = 1.5 jam

$total_upah_overwork = $upah_per_jam * $durasi_jam; // = 9375
```

**Query SQL:**
```sql
SELECT 
    user_id,
    SUM(durasi_overwork_menit) / 60 AS total_jam_lembur,
    (SELECT upah_overwork_per_8jam FROM register WHERE id = absensi.user_id) / 8 AS upah_per_jam,
    ((SELECT upah_overwork_per_8jam FROM register WHERE id = absensi.user_id) / 8) * 
    (SUM(durasi_overwork_menit) / 60) AS total_upah_lembur
FROM absensi
WHERE is_overwork_approved = TRUE
  AND MONTH(tanggal_absensi) = 1
  AND YEAR(tanggal_absensi) = 2025
GROUP BY user_id;
```

## 🚀 Langkah Implementasi

### Step 1: Backup & Migrate (Yang akan kita lakukan sekarang)
```bash
./backup_and_migrate.sh
```

### Step 2: Populate Salary Data
Setelah migration, isi data salary untuk setiap pegawai:

```sql
-- Contoh: Update salary untuk pegawai
UPDATE register SET 
    gaji_pokok = 4500000,
    tunjangan_transport = 390000,    -- Total untuk 26 hari
    tunjangan_makan = 260000,        -- Total untuk 26 hari
    tunjangan_jabatan = 500000,
    upah_overwork_per_8jam = 50000   -- Untuk 8 jam
WHERE id = 1;

-- Atau batch update dari CSV/Excel
LOAD DATA LOCAL INFILE 'salary_data.csv'
INTO TABLE register
FIELDS TERMINATED BY ',' 
LINES TERMINATED BY '\n'
(id, gaji_pokok, tunjangan_transport, tunjangan_makan, tunjangan_jabatan, upah_overwork_per_8jam);
```

### Step 3: Update PHP Logic
File: `proses_absensi.php` - Tambahkan kalkulasi durasi overwork:

```php
// Hitung durasi kerja
$waktu_masuk = new DateTime($row['waktu_masuk']);
$waktu_keluar = new DateTime($row['waktu_keluar']);
$durasi_menit = ($waktu_keluar->getTimestamp() - $waktu_masuk->getTimestamp()) / 60;

// Hitung overwork (jika lebih dari 8 jam + 30 menit toleransi)
$durasi_kerja_jam = 8; // dari shift
$batas_normal_menit = ($durasi_kerja_jam * 60) + 30; // 8 jam + 30 menit
$overwork_menit = max(0, $durasi_menit - $batas_normal_menit);

// Update ke database
$sql = "UPDATE absensi SET 
    durasi_kerja_menit = ?,
    durasi_overwork_menit = ?,
    status_lembur = ?
WHERE id = ?";

$status = ($overwork_menit > 0) ? 'Pending' : 'Not Applicable';
$stmt->execute([$durasi_menit, $overwork_menit, $status, $absen_id]);
```

### Step 4: Create Payroll Generation Script
File: `generate_monthly_payroll.php` (BARU)

```php
<?php
include 'connect.php';

// Periode payroll
$bulan = date('n'); // 1-12
$tahun = date('Y');

// Get all active employees
$sql = "SELECT id, nama_lengkap, gaji_pokok, tunjangan_transport, 
               tunjangan_makan, tunjangan_jabatan, upah_overwork_per_8jam
        FROM register WHERE role = 'user'";
$result = mysqli_query($conn, $sql);

while ($pegawai = mysqli_fetch_assoc($result)) {
    $user_id = $pegawai['id'];
    
    // 1. Hitung kehadiran
    $sql_hadir = "SELECT COUNT(*) as jumlah 
                  FROM absensi 
                  WHERE user_id = ? 
                    AND MONTH(tanggal_absensi) = ? 
                    AND YEAR(tanggal_absensi) = ?";
    $stmt = mysqli_prepare($conn, $sql_hadir);
    $stmt->bind_param('iii', $user_id, $bulan, $tahun);
    $stmt->execute();
    $jumlah_hadir = $stmt->get_result()->fetch_assoc()['jumlah'];
    
    // 2. Hitung tunjangan proporsional
    $tunj_makan_per_hari = $pegawai['tunjangan_makan'] / 26;
    $tunj_transport_per_hari = $pegawai['tunjangan_transport'] / 26;
    $total_tunj_makan = $tunj_makan_per_hari * $jumlah_hadir;
    $total_tunj_transport = $tunj_transport_per_hari * $jumlah_hadir;
    
    // 3. Hitung overwork
    $sql_overwork = "SELECT SUM(durasi_overwork_menit) as total_menit
                     FROM absensi 
                     WHERE user_id = ? 
                       AND is_overwork_approved = TRUE
                       AND MONTH(tanggal_absensi) = ? 
                       AND YEAR(tanggal_absensi) = ?";
    $stmt = mysqli_prepare($conn, $sql_overwork);
    $stmt->bind_param('iii', $user_id, $bulan, $tahun);
    $stmt->execute();
    $overwork_menit = $stmt->get_result()->fetch_assoc()['total_menit'] ?? 0;
    $overwork_jam = $overwork_menit / 60;
    $upah_per_jam = $pegawai['upah_overwork_per_8jam'] / 8;
    $total_overwork = $upah_per_jam * $overwork_jam;
    
    // 4. Hitung potongan keterlambatan
    // ... (sesuai aturan bisnis)
    
    // 5. Insert ke komponen_gaji_detail
    $sql_insert = "INSERT INTO komponen_gaji_detail 
                   (user_id, periode_bulan, periode_tahun, 
                    gaji_pokok, tunjangan_transport, tunjangan_makan,
                    overwork_amount, overwork_hours, jumlah_hadir)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                   ON DUPLICATE KEY UPDATE
                    gaji_pokok = VALUES(gaji_pokok),
                    tunjangan_transport = VALUES(tunjangan_transport),
                    tunjangan_makan = VALUES(tunjangan_makan),
                    overwork_amount = VALUES(overwork_amount),
                    overwork_hours = VALUES(overwork_hours),
                    jumlah_hadir = VALUES(jumlah_hadir)";
    
    // Execute...
}

echo "Payroll generated successfully!";
?>
```

## ✅ Checklist Solusi

- [x] **Migration script sudah benar** - Menambahkan kolom yang diperlukan
- [x] **Dokumentasi lengkap** - SALARY_CALCULATION_SYSTEM.md
- [x] **Pre-migration patch** - Menambahkan id_cabang
- [x] **Backup script** - Aman untuk rollback
- [ ] **Populate salary data** - Setelah migration (manual/import CSV)
- [ ] **Update PHP logic** - proses_absensi.php, generate_payroll.php
- [ ] **Testing** - Cek kalkulasi dengan data dummy

## 🎯 Kesimpulan

**TIDAK ADA MASALAH** dengan migration script yang sudah dibuat. Script sudah:

1. ✅ Menambahkan kolom salary dengan comment yang jelas
2. ✅ Menjelaskan bahwa tunjangan adalah total untuk 26 hari
3. ✅ Menjelaskan bahwa overwork adalah untuk 8 jam
4. ✅ Menyediakan stored procedure untuk kalkulasi otomatis
5. ✅ Dokumentasi lengkap cara kalkulasi

**Yang perlu dilakukan:**
1. **Run migration** (sekarang)
2. **Populate data salary** (setelah migration)
3. **Update PHP logic** (sesuai dokumentasi)
4. **Testing dan fine-tuning**

---

**READY TO MIGRATE!** 🚀

Mari kita mulai dengan backup dan migration sekarang.
