# 📋 SISTEM KALKULASI GAJI - PENJELASAN

## 🎯 Sistem Kalkulasi yang Digunakan

### 1. Tunjangan (Transport & Makan)
**Nilai yang disimpan adalah TOTAL untuk 26 hari kerja, BUKAN per hari**

#### Contoh:
```
Tunjangan Transport: Rp 500.000 = Total untuk 26 hari
Tunjangan Makan:     Rp 300.000 = Total untuk 26 hari

Jika pegawai hadir 20 hari dari 26 hari:
Tunjangan Transport = (20/26) × Rp 500.000 = Rp 384.615
Tunjangan Makan     = (20/26) × Rp 300.000 = Rp 230.769
```

### 2. Overwork (Lembur)
**Nilai yang disimpan adalah upah untuk 8 JAM kerja lembur, BUKAN per jam**

#### Contoh:
```
Upah Overwork 8 jam: Rp 50.000

Jika pegawai lembur 3 jam:
Upah per jam = Rp 50.000 / 8 jam = Rp 6.250/jam
Total bayar   = 3 jam × Rp 6.250 = Rp 18.750

Jika pegawai lembur 10 jam:
Total bayar = 10 jam × Rp 6.250 = Rp 62.500
```

### 3. Gaji Pokok
**Nilai bulanan penuh, tidak tergantung kehadiran**

#### Contoh:
```
Gaji Pokok: Rp 3.500.000 per bulan

Hadir 26 hari = Rp 3.500.000
Hadir 20 hari = Rp 3.500.000 (tetap sama)

Note: Potongan keterlambatan/alfa dihitung terpisah
```

---

## 🔢 Formula Perhitungan Payroll

### A. Pendapatan

#### 1. Gaji Pokok
```sql
gaji_pokok = nilai dari register.gaji_pokok (fixed)
```

#### 2. Tunjangan Transport (Proporsional)
```sql
-- Jika hadir kurang dari 26 hari, potong proporsional
IF jumlah_hadir < 26 THEN
    tunjangan_transport_diterima = (jumlah_hadir / 26) × register.tunjangan_transport
ELSE
    tunjangan_transport_diterima = register.tunjangan_transport
END IF
```

#### 3. Tunjangan Makan (Proporsional)
```sql
-- Sama seperti transport
IF jumlah_hadir < 26 THEN
    tunjangan_makan_diterima = (jumlah_hadir / 26) × register.tunjangan_makan
ELSE
    tunjangan_makan_diterima = register.tunjangan_makan
END IF
```

#### 4. Tunjangan Jabatan
```sql
tunjangan_jabatan = register.tunjangan_jabatan (fixed)
```

#### 5. Overwork
```sql
-- Calculate hourly rate from 8-hour base
upah_per_jam = register.upah_overwork_per_8jam / 8

-- Total overwork approved hours in period
total_jam_overwork = SUM(durasi_overwork_menit / 60) 
                     WHERE is_overwork_approved = TRUE

-- Total overwork payment
overwork_amount = total_jam_overwork × upah_per_jam
```

### B. Potongan

#### 1. Potongan Keterlambatan
```sql
-- Terlambat > 20 menit
potongan_telat_berat = COUNT(terlambat > 20 menit) × Rp 50.000

-- Terlambat < 20 menit
potongan_telat_ringan = COUNT(terlambat < 20 menit) × Rp 25.000
```

#### 2. Potongan Shift Tidak Hadir
```sql
-- Jika ada shift assignment tapi tidak hadir (dan bukan izin/sakit)
potongan_shift_missed = COUNT(shift tidak hadir) × Rp 50.000
```

#### 3. Potongan Alfa (Tanpa Keterangan)
```sql
-- Tidak hadir dan tidak ada izin
jumlah_alfa = 26 - jumlah_hadir - jumlah_izin_approved - libur_nasional

potongan_alfa = jumlah_alfa × Rp 100.000
```

#### 4. Kasbon & Piutang
```sql
-- Input manual oleh admin
kasbon = input manual
piutang_toko = input manual
```

### C. Total
```sql
total_pendapatan = gaji_pokok 
                 + tunjangan_transport_diterima 
                 + tunjangan_makan_diterima 
                 + tunjangan_jabatan 
                 + overwork_amount 
                 + bonus_marketing 
                 + insentif_omset

total_potongan = potongan_telat_berat 
               + potongan_telat_ringan 
               + potongan_shift_missed 
               + potongan_alfa 
               + kasbon 
               + piutang_toko

gaji_bersih = total_pendapatan - total_potongan
```

---

## 💾 Data di Database

### Tabel `register`
```sql
-- Salary components stored here
gaji_pokok                  DECIMAL(15,2)  -- Rp 3.500.000 (bulanan)
tunjangan_transport         DECIMAL(15,2)  -- Rp 500.000 (total 26 hari)
tunjangan_makan             DECIMAL(15,2)  -- Rp 300.000 (total 26 hari)
tunjangan_jabatan           DECIMAL(15,2)  -- Rp 500.000 (bulanan)
upah_overwork_per_8jam      DECIMAL(10,2)  -- Rp 50.000 (untuk 8 jam)
```

### Tabel `komponen_gaji_detail`
```sql
-- Calculated values for each month
gaji_pokok                  DECIMAL(15,2)  -- Copy from register
tunjangan_transport         DECIMAL(15,2)  -- Calculated: (hadir/26) × register value
tunjangan_makan             DECIMAL(15,2)  -- Calculated: (hadir/26) × register value
tunjangan_jabatan           DECIMAL(15,2)  -- Copy from register
overwork_amount             DECIMAL(15,2)  -- Calculated: jam × (upah_8jam / 8)
overwork_hours              DECIMAL(5,2)   -- Total approved overtime hours

potongan_telat_berat        DECIMAL(15,2)  -- count × 50.000
potongan_telat_ringan       DECIMAL(15,2)  -- count × 25.000
potongan_alfa               DECIMAL(15,2)  -- count × 100.000
kasbon                      DECIMAL(15,2)  -- manual input
piutang_toko                DECIMAL(15,2)  -- manual input

total_pendapatan            DECIMAL(15,2)  -- SUM(all income)
total_potongan              DECIMAL(15,2)  -- SUM(all deductions)
gaji_bersih                 DECIMAL(15,2)  -- pendapatan - potongan
```

---

## 📊 Contoh Perhitungan Lengkap

### Input Data
```
Pegawai: Andi
Periode: Januari 2025 (28 Des - 27 Jan)

Master Data (dari register):
- Gaji Pokok:             Rp 3.500.000
- Tunjangan Transport:    Rp 500.000 (26 hari)
- Tunjangan Makan:        Rp 300.000 (26 hari)
- Tunjangan Jabatan:      Rp 200.000
- Upah Overwork 8 jam:    Rp 50.000

Kehadiran:
- Hadir:                  24 hari
- Terlambat > 20 menit:   2 hari
- Terlambat < 20 menit:   1 hari
- Alfa:                   0 hari
- Izin:                   0 hari
- Overwork approved:      12 jam

Manual Input:
- Kasbon:                 Rp 200.000
- Bonus Marketing:        Rp 150.000
```

### Perhitungan

#### Pendapatan
```
1. Gaji Pokok            = Rp 3.500.000

2. Tunjangan Transport   = (24/26) × Rp 500.000
                         = Rp 461.538

3. Tunjangan Makan       = (24/26) × Rp 300.000
                         = Rp 276.923

4. Tunjangan Jabatan     = Rp 200.000

5. Overwork              = 12 jam × (Rp 50.000 / 8)
                         = 12 × Rp 6.250
                         = Rp 75.000

6. Bonus Marketing       = Rp 150.000

TOTAL PENDAPATAN         = Rp 4.663.461
```

#### Potongan
```
1. Telat > 20 menit      = 2 × Rp 50.000
                         = Rp 100.000

2. Telat < 20 menit      = 1 × Rp 25.000
                         = Rp 25.000

3. Alfa                  = 0 × Rp 100.000
                         = Rp 0

4. Kasbon                = Rp 200.000

TOTAL POTONGAN           = Rp 325.000
```

#### Gaji Bersih
```
GAJI BERSIH = Rp 4.663.461 - Rp 325.000
            = Rp 4.338.461
```

---

## 🔄 Update Stored Procedure

Stored procedure `sp_hitung_kehadiran_periode` sudah meng-handle:
- Counting: hadir, telat_ringan, telat_berat, alfa, izin
- Calculating: total overwork hours (approved only)
- Excluding: libur nasional dari perhitungan

Payroll generation script perlu meng-handle:
- Proportional calculation untuk tunjangan
- Overwork hourly rate calculation
- All deductions calculation

---

## ✅ Checklist Implementasi

Saat generate payroll, pastikan:

- [ ] Ambil master data dari `register`:
  - gaji_pokok
  - tunjangan_transport (total 26 hari)
  - tunjangan_makan (total 26 hari)
  - tunjangan_jabatan
  - upah_overwork_per_8jam

- [ ] Hitung attendance menggunakan `sp_hitung_kehadiran_periode`

- [ ] Calculate proportional allowances:
  ```php
  $tunjangan_transport_diterima = ($jumlah_hadir / 26) * $register['tunjangan_transport'];
  $tunjangan_makan_diterima = ($jumlah_hadir / 26) * $register['tunjangan_makan'];
  ```

- [ ] Calculate overwork:
  ```php
  $upah_per_jam = $register['upah_overwork_per_8jam'] / 8;
  $overwork_amount = $total_overwork_hours * $upah_per_jam;
  ```

- [ ] Calculate deductions:
  ```php
  $potongan_telat_berat = $count_telat_berat * 50000;
  $potongan_telat_ringan = $count_telat_ringan * 25000;
  $potongan_alfa = $count_alfa * 100000;
  ```

- [ ] Sum totals and save to `komponen_gaji_detail`

---

**Catatan Penting:**
- Sistem ini memastikan pegawai yang jarang hadir tidak mendapat full tunjangan
- Overwork dihitung per jam dengan rate yang proporsional
- Potongan konsisten dan jelas
- Mudah untuk di-adjust (ubah multiplier di code)

---

**File ini harus dibaca sebelum implement payroll generation!**
