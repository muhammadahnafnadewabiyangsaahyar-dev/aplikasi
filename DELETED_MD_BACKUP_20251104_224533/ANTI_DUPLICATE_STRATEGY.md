# ANTI-DUPLICATE STRATEGY: Import CSV

## 🎯 PERTANYAAN KUNCI

### 1. Bagaimana mencegah duplikasi saat import CSV?
### 2. Bagaimana jika ada 2 orang dengan nama sama tapi posisi berbeda?
### 3. Strategi apa yang paling tepat?

---

## 📊 SKENARIO & SOLUSI

### Skenario 1: Nama Sama, Posisi Sama
**Contoh:**
```csv
Ahmad Rifai;Barista
Ahmad Rifai;Barista  (duplikat!)
```

**Solusi:** ❌ **SKIP/REJECT** - Duplikat murni
**Action:** Skip baris ke-2, tampilkan warning

---

### Skenario 2: Nama Sama, Posisi Berbeda
**Contoh:**
```csv
Ahmad Rifai;Barista
Ahmad Rifai;Kitchen  (orang berbeda atau double job?)
```

**Problem:** Apakah ini:
- Orang yang sama dengan 2 posisi? (Part-time di 2 divisi)
- 2 orang berbeda dengan nama sama?

#### **Opsi A: Strict Unique (Recommended) ✅**
**Policy:** Satu nama = satu pegawai
**Action:** Skip baris ke-2, warning "Nama sudah ada"
**Benefit:**
- ✅ Data clean & consistent
- ✅ Tidak ada ambiguitas
- ✅ Mudah maintain
- ✅ UNIQUE constraint di database work

**Solusi untuk double job:**
- Input posisi sebagai: `Barista/Kitchen` (kombinasi)
- Atau gunakan posisi primary saja

#### **Opsi B: Allow Multiple Positions ⚠️**
**Policy:** Nama + Posisi = unique key
**Action:** Import keduanya sebagai entry terpisah
**Problem:**
- ⚠️ Data duplikat di sistem
- ⚠️ Absensi jadi rancu (absen sebagai apa?)
- ⚠️ Gaji double atau split?
- ⚠️ Login credentials bentrok

**NOT RECOMMENDED** untuk sistem HR/absensi

---

### Skenario 3: Nama Mirip/Typo
**Contoh:**
```csv
Ahmad Rifai
Ahmad Rifa'i  (typo atau memang beda?)
Muh Rizki
Muhammad Rizki  (nama lengkap vs panggilan)
```

**Solusi:**
- ✅ Tambah kolom **NIK/ID Pegawai** (unique identifier)
- ✅ Pre-validate CSV sebelum import
- ✅ Show preview + confirmation

---

### Skenario 4: Update Data Existing
**Contoh:**
CSV berisi pegawai yang sudah ada tapi dengan data baru:
```csv
Ahmad Rifai;Kitchen  (update posisi dari Barista → Kitchen)
```

**Solusi:** Opsi **UPDATE** vs **SKIP**

#### **Mode 1: Skip Existing (Current) ✅**
- Pegawai sudah ada → Skip
- Safe, tidak overwrite data

#### **Mode 2: Update Existing**
- Pegawai sudah ada → Update posisi/role
- Useful untuk bulk update
- Risk: overwrite data penting

---

## 🛠️ IMPLEMENTASI RECOMMENDED

### Strategy: **UNIQUE BY NAME (Strict)**

#### 1. Database Constraint (Already Applied ✅)
```sql
ALTER TABLE pegawai_whitelist 
ADD UNIQUE KEY unique_nama_lengkap (nama_lengkap);
```

#### 2. Import Logic (Already Implemented ✅)
```php
// Cek duplikat sebelum insert
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pegawai_whitelist WHERE nama_lengkap = ?");
$stmt->execute([$nama]);

if ($stmt->fetchColumn() > 0) {
    // SKIP - Nama sudah ada
    $skipped++;
    $skippedRows[] = $rowNum;
    continue;
}

// Insert hanya jika belum ada
$stmt = $pdo->prepare("INSERT INTO pegawai_whitelist ...");
```

#### 3. Enhanced Logic dengan Detail Message
```php
// Cek duplikat dengan detail
$stmt = $pdo->prepare("
    SELECT nama_lengkap, posisi, role, status_registrasi 
    FROM pegawai_whitelist 
    WHERE nama_lengkap = ?
");
$stmt->execute([$nama]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $skipped++;
    $skippedDetails[] = [
        'row' => $rowNum,
        'nama' => $nama,
        'reason' => 'Already exists',
        'existing_posisi' => $existing['posisi'],
        'new_posisi' => $posisi,
        'action' => 'SKIP'
    ];
    continue;
}
```

---

## 🎨 SOLUSI LENGKAP: 3 MODE IMPORT

### Mode 1: **SKIP** (Default, Safest) ✅
```php
if ($exists) {
    $skipped++;
    $message = "Row $rowNum: '$nama' already exists - SKIPPED";
    continue;
}
```

**Use case:** Import pertama kali, atau add pegawai baru only

---

### Mode 2: **UPDATE** (Advanced)
```php
if ($exists) {
    // Update existing data
    $stmt = $pdo->prepare("
        UPDATE pegawai_whitelist 
        SET posisi = ?, role = ? 
        WHERE nama_lengkap = ?
    ");
    $stmt->execute([$posisi, $role, $nama]);
    $updated++;
    $message = "Row $rowNum: '$nama' UPDATED (Posisi: $old_posisi → $posisi)";
    continue;
}
```

**Use case:** Bulk update posisi/role pegawai existing

---

### Mode 3: **SMART** (Intelligent Decision)
```php
if ($exists) {
    // Compare data
    if ($existing['posisi'] === $posisi && $existing['role'] === $role) {
        // Exact duplicate - SKIP
        $skipped++;
        $message = "Row $rowNum: Duplicate - SKIPPED";
    } else {
        // Data berbeda - ASK or UPDATE
        if ($import_mode === 'update') {
            // UPDATE
            $updated++;
            $message = "Row $rowNum: UPDATED";
        } else {
            // SKIP with warning
            $skipped++;
            $message = "Row $rowNum: Data mismatch - SKIPPED (use update mode to override)";
        }
    }
    continue;
}
```

**Use case:** Flexible, user pilih mode saat import

---

## 🚀 RECOMMENDATION UNTUK SISTEM ANDA

### Best Practice: **UNIQUE by Name + NIK**

#### Database Schema Enhancement:
```sql
ALTER TABLE pegawai_whitelist ADD COLUMN nik VARCHAR(20) UNIQUE;
ALTER TABLE register ADD COLUMN nik VARCHAR(20) UNIQUE;
```

#### CSV Format Enhancement:
```csv
No;NIK;Nama Lengkap;Posisi
1;12345;Ahmad Rifai;Barista
2;67890;Ahmad Rifai;Kitchen  ← OK! Beda NIK = beda orang
```

#### Import Logic:
```php
// Primary check: NIK (jika ada)
if (!empty($nik)) {
    $stmt = $pdo->prepare("SELECT * FROM pegawai_whitelist WHERE nik = ?");
    $stmt->execute([$nik]);
} else {
    // Fallback: Nama
    $stmt = $pdo->prepare("SELECT * FROM pegawai_whitelist WHERE nama_lengkap = ?");
    $stmt->execute([$nama]);
}
```

**Benefits:**
- ✅ Handle nama sama dengan benar
- ✅ Unique identifier yang solid
- ✅ Standard HR practice
- ✅ Scalable untuk ratusan pegawai

---

## 📋 QUICK COMPARISON

| Strategy | Pros | Cons | Recommended |
|----------|------|------|-------------|
| **Unique by Name** | Simple, Clean | Nama sama = problem | ✅ Small teams |
| **Unique by NIK** | Robust, Scalable | Perlu extra kolom | ✅ Large teams |
| **Unique by Name+Posisi** | Allow double job | Complex, confusing | ❌ Not for HR |

---

## 🎯 IMMEDIATE ACTION

### Saat Ini (SKIP Mode): ✅ ALREADY IMPLEMENTED
```php
// whitelist.php line ~172
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pegawai_whitelist WHERE nama_lengkap = ?");
$stmt->execute([$nama]);

if ($stmt->fetchColumn() > 0) {
    $skipped++;
    $skippedRows[] = $rowNum;
    continue;
}
```

**Status:** ✅ **AMAN** - Tidak ada duplikasi akan terjadi!

---

## 💡 OPTIONAL ENHANCEMENTS

### 1. Add Import Mode Selector
```html
<select name="import_mode">
    <option value="skip">Skip Existing (Safe)</option>
    <option value="update">Update Existing (Advanced)</option>
</select>
```

### 2. Add Preview Before Import
- Show CSV data sebelum import
- User confirm: Skip or Update duplicates
- Tampilkan conflict yang detected

### 3. Add NIK Column
- Database migration
- Update CSV template
- Update import logic

---

## ✅ KESIMPULAN

### Untuk Pertanyaan Anda:

#### 1. **Mencegah duplikasi saat import?**
**Jawaban:** ✅ **SUDAH AMAN!** 
- Code cek duplikat sudah ada
- UNIQUE constraint di database
- Duplikat akan di-SKIP automatic

#### 2. **Nama sama, posisi berbeda?**
**Jawaban:** Saat ini akan di-SKIP (nama pertama yang masuk)

**Solusi terbaik:**
- **Short term:** Gunakan nama lengkap yang berbeda (tambah middle name)
- **Long term:** Tambah kolom NIK sebagai unique identifier

#### 3. **Strategi terpilih?**
**Recommendation:**
- ✅ **Now:** Unique by Name (SKIP mode) - SAFE
- ✅ **Future:** Add NIK column - ROBUST
- ⚠️ **Avoid:** Allow multiple same name - CONFUSING

---

## 🔧 APAKAH ANDA INGIN:

1. ✅ Tetap dengan SKIP mode (current) - Paling aman
2. 🔄 Implementasi UPDATE mode - Untuk bulk update
3. 🆔 Tambah kolom NIK - Long term solution
4. 🎨 Tambah preview + confirmation - Better UX

**Silakan pilih, saya siap implementasikan!** 🚀
