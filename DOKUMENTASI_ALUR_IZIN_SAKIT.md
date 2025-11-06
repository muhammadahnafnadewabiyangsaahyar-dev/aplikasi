# DOKUMENTASI: ALUR IZIN DAN SAKIT DI SISTEM KAORI

**Tanggal:** 6 November 2025  
**Status:** ✅ SUDAH IMPLEMENTASI

---

## 🎯 KONSEP DASAR

### **Kategori Status Kehadiran:**

```
1. HADIR       → User datang dan absen (ada waktu_masuk)
2. IZIN        → User tidak datang karena izin yang disetujui
3. SAKIT       → User tidak datang karena sakit yang disetujui
4. ALPHA       → User tidak datang tanpa keterangan (tidak ada izin/sakit)
```

### **PENTING:**
- ✅ **HADIR** = Masuk kerja dan absen
- ❌ **IZIN ≠ HADIR** (Izin adalah kategori tersendiri)
- ❌ **SAKIT ≠ HADIR** (Sakit adalah kategori tersendiri)
- ❌ **IZIN ≠ ALPHA** (Izin sudah ada keterangan resmi)
- ❌ **SAKIT ≠ ALPHA** (Sakit sudah ada keterangan resmi)

---

## 📋 ALUR PENGAJUAN IZIN/SAKIT

### **STEP 1: User Mengajukan Izin/Sakit**

**File:** `form_izin.php` (atau sejenisnya)

User mengisi form dengan data:
- **Perihal:** Dropdown memilih "Izin" atau "Sakit"
- **Tanggal Mulai:** Tanggal awal izin/sakit
- **Tanggal Selesai:** Tanggal akhir izin/sakit
- **Lama Izin:** Dihitung otomatis (hari)
- **Alasan:** Text area untuk alasan
- **File Surat:** Upload dokumen (untuk Sakit bisa upload surat dokter)
- **Tanda Tangan:** Upload tanda tangan digital

**Insert ke Database:**
```sql
INSERT INTO pengajuan_izin 
(user_id, perihal, tanggal_mulai, tanggal_selesai, lama_izin, alasan, file_surat, tanda_tangan_file, status)
VALUES 
(?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
```

**Status Awal:** `'Pending'` (menunggu approval)

---

### **STEP 2: Admin Melihat Daftar Pengajuan**

**File:** `approve.php`

Admin melihat semua pengajuan yang status = `'Pending'`, termasuk:
- ✅ Izin (perihal = 'Izin')
- ✅ Sakit (perihal = 'Sakit')

**Query:**
```sql
SELECT p.id, r.nama_lengkap, p.perihal, p.tanggal_mulai, p.tanggal_selesai, 
       p.lama_izin, p.alasan, p.file_surat, p.tanda_tangan_file 
FROM pengajuan_izin p
JOIN register r ON p.user_id = r.id
WHERE p.status = 'Pending'
ORDER BY p.tanggal_pengajuan ASC
```

**Tampilan:**
| Nama | Perihal | Tanggal | Lama | Alasan | File Surat | TTD | Aksi |
|------|---------|---------|------|--------|------------|-----|------|
| Kata Hnaf | Izin | 15 Nov | 1 | Keperluan keluarga | Download | [IMG] | [Setujui] [Tolak] |
| Kata Hnaf | Sakit | 18 Nov | 1 | Demam dan flu | Download | [IMG] | [Setujui] [Tolak] |

**Catatan:**
- ✅ File `approve.php` **SUDAH** bisa handle baik Izin maupun Sakit
- ✅ Tidak perlu file terpisah `approve_sakit.php`
- ✅ Kolom `perihal` membedakan antara 'Izin' dan 'Sakit'

---

### **STEP 3: Admin Menyetujui/Menolak**

**File:** `proses_approve.php`

Admin klik tombol:
- **[Setujui]** → Update status jadi `'Diterima'`
- **[Tolak]** → Update status jadi `'Ditolak'`

**Query Update:**
```sql
UPDATE pengajuan_izin 
SET status = 'Diterima'  -- atau 'Ditolak'
WHERE id = ?
```

---

### **STEP 4: Sistem Membuat Record Absensi (OTOMATIS)**

**File:** `fix_izin_sakit_status.php` (saat ini manual, tapi bisa di-trigger otomatis)

**Trigger:** Setelah admin approve (status = 'Diterima')

**Logika:**
```php
// Untuk setiap hari dalam range izin/sakit
foreach ($dateRange as $date) {
    // Skip Sunday
    if ($date->format('N') == 7) continue;
    
    // Insert record absensi dengan status sesuai perihal
    INSERT INTO absensi 
    (user_id, tanggal_absensi, status_kehadiran, waktu_masuk, waktu_keluar, menit_terlambat, status_keterlambatan)
    VALUES 
    (?, ?, 'Izin', NULL, NULL, 0, 'Izin')
    -- atau status_kehadiran = 'Sakit' jika perihal = 'Sakit'
}
```

**Hasil:**
- Tanggal 15 Nov → Record absensi dengan `status_kehadiran = 'Izin'`
- Tanggal 18 Nov → Record absensi dengan `status_kehadiran = 'Sakit'`

---

### **STEP 5: Overview Menampilkan Statistik**

**File:** `mainpage.php`

**Query Statistik:**
```sql
-- Hitung HADIR (hanya yang status = 'Hadir')
SELECT COUNT(DISTINCT tanggal_absensi) 
FROM absensi 
WHERE user_id = ? 
AND status_kehadiran = 'Hadir'
AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?

-- Hitung IZIN (hanya yang status = 'Izin')
SELECT COUNT(DISTINCT tanggal_absensi) 
FROM absensi 
WHERE user_id = ? 
AND status_kehadiran = 'Izin'
AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?

-- Hitung SAKIT (hanya yang status = 'Sakit')
SELECT COUNT(DISTINCT tanggal_absensi) 
FROM absensi 
WHERE user_id = ? 
AND status_kehadiran = 'Sakit'
AND DATE_FORMAT(tanggal_absensi, '%Y-%m') = ?

-- Hitung ALPHA
Alpha = Total Hari Kerja - (Hadir + Izin + Sakit)
```

**Tampilan Overview:**
```
┌─────────────────────┐
│ Total Kehadiran: 16 │ → Hanya yang HADIR (ada waktu masuk)
│ Dari 26 hari kerja  │
└─────────────────────┘

┌─────────────────────┐
│ Tepat Waktu: 16     │ → Dari 16 hadir, semua tepat waktu
└─────────────────────┘

┌─────────────────────┐
│ Terlambat: 0        │ → Tidak ada yang terlambat
└─────────────────────┘

┌─────────────────────┐
│ Alpha: 8            │ → Tidak hadir tanpa keterangan
└─────────────────────┘

┌─────────────────────┐
│ Izin: 1             │ → Izin yang disetujui (15 Nov)
└─────────────────────┘

┌─────────────────────┐
│ Sakit: 1            │ → Sakit yang disetujui (18 Nov)
└─────────────────────┘
```

**Formula:**
```
Total Hari Kerja = 26
Hadir = 16
Izin = 1
Sakit = 1
Alpha = 26 - (16 + 1 + 1) = 8 ✅

Persentase Kehadiran = (16 + 1 + 1) / 26 × 100 = 69.2%
```

---

## 🔧 IMPLEMENTASI SAAT INI

### **✅ SUDAH ADA:**

1. **Form Pengajuan Izin/Sakit**
   - User bisa pilih perihal: "Izin" atau "Sakit"
   - Upload file surat (bisa surat izin atau surat dokter)

2. **Halaman Approval (`approve.php`)**
   - Admin bisa approve/reject SEMUA pengajuan (Izin dan Sakit)
   - Tidak perlu halaman terpisah

3. **Script Otomatis (`fix_izin_sakit_status.php`)**
   - Membuat record absensi untuk izin/sakit yang disetujui
   - Saat ini manual, tapi bisa di-trigger otomatis

4. **Overview Dashboard (`mainpage.php`)**
   - Menampilkan 6 card: Hadir, Tepat Waktu, Terlambat, Alpha, Izin, Sakit
   - Formula perhitungan sudah benar

---

## 🚀 REKOMENDASI PERBAIKAN

### **1. Automasi Pembuatan Record Absensi**

**Problem:** Script `fix_izin_sakit_status.php` harus dijalankan manual

**Solusi:** Trigger otomatis di `proses_approve.php`

```php
// File: proses_approve.php
if ($action === 'approve') {
    // Update status jadi 'Diterima'
    $update = "UPDATE pengajuan_izin SET status = 'Diterima' WHERE id = ?";
    $stmt = $pdo->prepare($update);
    $stmt->execute([$pengajuan_id]);
    
    // TAMBAHAN: Buat record absensi otomatis
    $select = "SELECT user_id, perihal, tanggal_mulai, tanggal_selesai 
               FROM pengajuan_izin WHERE id = ?";
    $stmt = $pdo->prepare($select);
    $stmt->execute([$pengajuan_id]);
    $izin = $stmt->fetch();
    
    // Loop tanggal mulai sampai selesai
    $start = new DateTime($izin['tanggal_mulai']);
    $end = new DateTime($izin['tanggal_selesai']);
    $end->modify('+1 day'); // Include end date
    
    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
    
    foreach ($period as $date) {
        // Skip Sunday
        if ($date->format('N') == 7) continue;
        
        $tanggal = $date->format('Y-m-d');
        $status = $izin['perihal']; // 'Izin' atau 'Sakit'
        
        // Insert or update absensi
        $insert = "INSERT INTO absensi 
                   (user_id, tanggal_absensi, status_kehadiran, waktu_masuk, waktu_keluar, menit_terlambat, status_keterlambatan)
                   VALUES (?, ?, ?, NULL, NULL, 0, ?)
                   ON DUPLICATE KEY UPDATE 
                   status_kehadiran = VALUES(status_kehadiran),
                   status_keterlambatan = VALUES(status_keterlambatan)";
        $stmt = $pdo->prepare($insert);
        $stmt->execute([$izin['user_id'], $tanggal, $status, $status]);
    }
}
```

---

### **2. Validasi Upload File Surat**

**Rekomendasi:**
- Untuk **Izin**: Wajib upload surat izin
- Untuk **Sakit**: Wajib upload surat dokter (jika > 3 hari)

```php
if ($perihal === 'Sakit' && $lama_izin > 3 && empty($file_surat)) {
    die("Error: Untuk sakit lebih dari 3 hari, wajib upload surat dokter!");
}
```

---

### **3. Notifikasi untuk User**

**Fitur Baru:**
- Email/WA saat pengajuan diterima
- Email/WA saat pengajuan ditolak

---

## 📊 CONTOH KASUS: KAT AHNAF NOVEMBER 2025

### **Data:**
```
Total Shift: 26 hari
Hadir: 16 hari (6, 7, 10, 11, 12, 13, 14, 19, 22, 23, 24, 25, 26, 27, 28, 29 Nov)
Izin: 1 hari (15 Nov - Keperluan keluarga) ← DISETUJUI
Sakit: 1 hari (18 Nov - Demam dan flu) ← DISETUJUI
Alpha: 8 hari (1, 3, 4, 5, 8, 17, 20, 21 Nov)
```

### **Alur:**

1. **Kat Ahnaf mengajukan Izin (15 Nov):**
   - Status awal: `Pending`
   - Admin approve via `approve.php`
   - Sistem buat record absensi: `status_kehadiran = 'Izin'`

2. **Kat Ahnaf mengajukan Sakit (18 Nov):**
   - Status awal: `Pending`
   - Admin approve via `approve.php` (SAMA dengan izin!)
   - Sistem buat record absensi: `status_kehadiran = 'Sakit'`

3. **Overview menampilkan:**
   - Hadir: 16 (tidak termasuk izin dan sakit)
   - Izin: 1
   - Sakit: 1
   - Alpha: 8 (26 - 16 - 1 - 1)

---

## ✅ KESIMPULAN

### **Jawaban Pertanyaan:**

**Q: "Di manakah sakit akan dikonfirmasi? Kalau konfirmasi surat izin sudah ada."**

**A:** ✅ **Di file `approve.php` yang SAMA!**

- ✅ Tidak perlu file terpisah `approve_sakit.php`
- ✅ File `approve.php` sudah menampilkan SEMUA pengajuan (Izin dan Sakit)
- ✅ Kolom `perihal` membedakan antara 'Izin' dan 'Sakit'
- ✅ Admin hanya perlu buka 1 halaman untuk approve semua

### **Status Implementasi:**

| Fitur | Status | Keterangan |
|-------|--------|------------|
| Form Pengajuan Izin/Sakit | ✅ | Sudah ada |
| Approval Izin/Sakit | ✅ | `approve.php` handle keduanya |
| Pembuatan Record Absensi | ⚠️ | Manual (perlu automasi) |
| Overview Dashboard | ✅ | Sudah benar |
| Perhitungan Alpha | ✅ | Sudah benar |

### **Next Step:**
1. ✅ Automasi pembuatan record absensi di `proses_approve.php`
2. ✅ Validasi upload file surat dokter untuk sakit > 3 hari
3. ✅ Notifikasi email/WA saat approve/reject

---

**Status:** ✅ **SISTEM SUDAH BENAR, PERLU AUTOMASI**
