# IMPORT CSV - WHITELIST PEGAWAI & KOMPONEN GAJI

## ❓ PERTANYAAN

### 1. Apakah import CSV bisa berlaku untuk komponen gaji di whitelist pegawai?
**Jawaban: YA, BISA!** Tapi perlu modifikasi pada script import.

### 2. Apakah perlu kolom "role" di CSV, atau website bisa baca otomatis?
**Jawaban: TIDAK PERLU!** Website bisa menentukan role secara otomatis berdasarkan **posisi**.

---

## 📋 FORMAT CSV YANG DIREKOMENDASIKAN

### Format Minimal (Sudah Ada Sekarang):
```csv
No;Nama Lengkap;Posisi
1;John Doe;Barista
2;Jane Smith;Kitchen
3;Bob Manager;HR
```

### Format Lengkap (Dengan Komponen Gaji):
```csv
No;Nama Lengkap;Posisi;Gaji Pokok;Tunjangan Transport;Tunjangan Makan;Overwork;Tunjangan Jabatan;Bonus Kehadiran;Bonus Marketing;Insentif Omset
1;John Doe;Barista;5000000;500000;400000;0;0;300000;0;0
2;Jane Smith;Kitchen;4500000;500000;400000;0;0;250000;0;0
3;Bob Manager;HR;7500000;1000000;800000;0;1500000;500000;0;0
```

**Catatan:**
- Kolom gaji bersifat **OPSIONAL** - jika tidak diisi akan default ke **0**
- Delimiter: **titik koma (;)**
- Encoding: **UTF-8**

---

## 🤖 LOGIKA ROLE OTOMATIS

### Mapping Posisi → Role:

#### **ADMIN** (posisi manajemen/strategic):
- HR
- Finance  
- Marketing
- SCM (Supply Chain Management)
- Akuntan
- Owner
- Superadmin

#### **USER** (posisi operasional):
- Barista
- Kitchen
- Server
- Kasir
- Security
- Dan posisi lainnya

### Implementasi di Code:
```php
// Fungsi untuk menentukan role berdasarkan posisi
function getRoleByPosisi($posisi) {
    $posisi = strtolower(trim($posisi));
    $admin_positions = ['hr', 'finance', 'marketing', 'scm', 'akuntan', 'owner', 'superadmin'];
    
    return in_array($posisi, $admin_positions) ? 'admin' : 'user';
}
```

---

## 🛠️ MODIFIKASI YANG DIPERLUKAN

### Opsi 1: Import Tanpa Komponen Gaji (MINIMAL)
**Status: ✅ SUDAH ADA (saat ini)**

Format CSV:
```csv
No;Nama Lengkap;Posisi
```

Fitur:
- ✅ Auto-detect role berdasarkan posisi
- ✅ Status registrasi = 'pending'
- ❌ Komponen gaji tidak diisi (harus manual via Edit)

### Opsi 2: Import Dengan Komponen Gaji (LENGKAP)
**Status: ⚠️ PERLU MODIFIKASI**

Format CSV:
```csv
No;Nama Lengkap;Posisi;Gaji Pokok;Tunjangan Transport;Tunjangan Makan;Overwork;Tunjangan Jabatan;Bonus Kehadiran;Bonus Marketing;Insentif Omset
```

Fitur:
- ✅ Auto-detect role berdasarkan posisi
- ✅ Status registrasi = 'pending'
- ✅ Komponen gaji langsung diisi dari CSV
- ✅ Jika kolom gaji kosong = default 0

---

## 💡 REKOMENDASI

### Untuk Kemudahan Penggunaan:
**TIDAK PERLU kolom "role" di CSV**

**Alasan:**
1. ✅ **Otomatis & Konsisten**: Website menentukan role berdasarkan posisi secara otomatis
2. ✅ **Lebih Simple**: CSV lebih sederhana, user tidak perlu mikir role
3. ✅ **Mencegah Error**: User tidak bisa salah input role (misal: HR tapi role=user)
4. ✅ **Maintenance Mudah**: Jika ada perubahan mapping posisi→role, hanya perlu update 1 tempat di code

### Kolom CSV yang Optimal:

#### **Format Standar (Tanpa Gaji):**
```csv
No;Nama Lengkap;Posisi
```
- Untuk: Quick import pegawai baru
- Gaji: Diisi manual nanti via Edit

#### **Format Lengkap (Dengan Gaji):**
```csv
No;Nama Lengkap;Posisi;Gaji Pokok;Tunjangan Transport;Tunjangan Makan;Overwork;Tunjangan Jabatan;Bonus Kehadiran;Bonus Marketing;Insentif Omset
```
- Untuk: Import data lengkap sekaligus
- Semua kolom gaji opsional (default: 0)

---

## 📦 IMPLEMENTASI YANG SAYA SARANKAN

### Script Import Baru (whitelist.php):

```php
// Di bagian import CSV, tambahkan:

// 1. Deteksi jumlah kolom
$columnCount = count($row);

// 2. Ambil data dasar
$nama = trim($row[1] ?? '');
$posisi = trim($row[2] ?? '');

// 3. Auto-detect role dari posisi
$posisi_admin = ['hr', 'finance', 'marketing', 'scm', 'akuntan', 'owner', 'superadmin'];
$role = in_array(strtolower($posisi), $posisi_admin) ? 'admin' : 'user';

// 4. Ambil komponen gaji (jika ada)
$gaji_pokok = isset($row[3]) && $row[3] !== '' ? floatval($row[3]) : 0;
$tunjangan_transport = isset($row[4]) && $row[4] !== '' ? floatval($row[4]) : 0;
$tunjangan_makan = isset($row[5]) && $row[5] !== '' ? floatval($row[5]) : 0;
$overwork = isset($row[6]) && $row[6] !== '' ? floatval($row[6]) : 0;
$tunjangan_jabatan = isset($row[7]) && $row[7] !== '' ? floatval($row[7]) : 0;
$bonus_kehadiran = isset($row[8]) && $row[8] !== '' ? floatval($row[8]) : 0;
$bonus_marketing = isset($row[9]) && $row[9] !== '' ? floatval($row[9]) : 0;
$insentif_omset = isset($row[10]) && $row[10] !== '' ? floatval($row[10]) : 0;

// 5. Insert ke pegawai_whitelist
$stmt = $pdo->prepare("INSERT INTO pegawai_whitelist (nama_lengkap, posisi, status_registrasi, role) VALUES (?, ?, 'pending', ?)");
$stmt->execute([$nama, $posisi, $role]);

// 6. Jika ada data gaji, langsung insert ke komponen_gaji
if ($columnCount >= 4) { // Ada kolom gaji
    // Dapatkan register_id (jika sudah terdaftar)
    $stmt = $pdo->prepare("SELECT id FROM register WHERE nama_lengkap = ?");
    $stmt->execute([$nama]);
    $register_id = $stmt->fetchColumn();
    
    if ($register_id) {
        $stmt = $pdo->prepare("INSERT INTO komponen_gaji (register_id, jabatan, gaji_pokok, tunjangan_transport, tunjangan_makan, overwork, tunjangan_jabatan, bonus_kehadiran, bonus_marketing, insentif_omset) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$register_id, $posisi, $gaji_pokok, $tunjangan_transport, $tunjangan_makan, $overwork, $tunjangan_jabatan, $bonus_kehadiran, $bonus_marketing, $insentif_omset]);
    }
}
```

---

## ✅ KESIMPULAN & REKOMENDASI

### Untuk CSV Import:

1. **JANGAN gunakan kolom "role"** di CSV
   - Website auto-detect berdasarkan posisi
   - Lebih simple & error-free

2. **Format CSV Minimal** (Sudah OK saat ini):
   ```csv
   No;Nama Lengkap;Posisi
   ```

3. **Format CSV Lengkap** (Perlu modifikasi script):
   ```csv
   No;Nama Lengkap;Posisi;Gaji Pokok;Tunjangan Transport;Tunjangan Makan;Overwork;Tunjangan Jabatan;Bonus Kehadiran;Bonus Marketing;Insentif Omset
   ```

### Benefit Auto-Detect Role:
- ✅ Konsistensi data
- ✅ Mengurangi human error
- ✅ CSV lebih simple
- ✅ Maintenance lebih mudah

---

## 🎯 NEXT STEPS

Jika Anda ingin saya implementasikan:
1. ✅ Auto-detect role dari posisi (import CSV)
2. ✅ Support import komponen gaji dari CSV
3. ✅ Backward compatible (CSV lama tetap bisa dipakai)

**Apakah Anda ingin saya buatkan modifikasi script-nya sekarang?**

---

## 📝 CONTOH FILE CSV

### Template 1: Basic (Tanpa Gaji)
```csv
No;Nama Lengkap;Posisi
1;Ahmad Rifai;Barista
2;Siti Nurhaliza;Kitchen
3;Budi Santoso;HR
```

### Template 2: Lengkap (Dengan Gaji)
```csv
No;Nama Lengkap;Posisi;Gaji Pokok;Tunjangan Transport;Tunjangan Makan;Overwork;Tunjangan Jabatan;Bonus Kehadiran;Bonus Marketing;Insentif Omset
1;Ahmad Rifai;Barista;5000000;500000;400000;0;0;300000;0;0
2;Siti Nurhaliza;Kitchen;4500000;500000;400000;0;0;250000;0;0
3;Budi Santoso;HR;7500000;1000000;800000;0;1500000;500000;0;0
```

**Save as: `import_pegawai.csv` (UTF-8 encoding)**
