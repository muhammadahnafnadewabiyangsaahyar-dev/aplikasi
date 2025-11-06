# PANDUAN: Menambahkan Perhitungan Durasi Kerja di PHP

**Tanggal**: 6 November 2024  
**Status**: OPTIONAL - Untuk Kompatibilitas Maximum Free Hosting

## 🎯 TUJUAN
Menambahkan perhitungan `durasi_kerja_menit` dan `durasi_overwork_menit` di PHP layer, sehingga tidak bergantung pada DATABASE TRIGGER yang mungkin tidak didukung di free hosting.

---

## 📊 ANALISIS SAAT INI

### Database Trigger yang Ada
```sql
CREATE TRIGGER `tr_absensi_calculate_duration`
BEFORE UPDATE ON `absensi`
FOR EACH ROW
BEGIN
    IF NEW.waktu_masuk IS NOT NULL AND NEW.waktu_keluar IS NOT NULL THEN
        -- Calculate actual work duration in minutes
        SET NEW.durasi_kerja_menit = TIMESTAMPDIFF(MINUTE, NEW.waktu_masuk, NEW.waktu_keluar);
        
        -- Calculate overwork if shift times are set
        -- ... (logic overwork)
    END IF;
END
```

### Status Saat Ini
- ✅ Trigger berfungsi di local/production dengan MySQL support
- ⚠️ Trigger akan dihapus untuk ByetHost compatibility
- ❌ Tidak ada PHP code yang menghitung durasi_kerja_menit
- 🎯 **REKOMENDASI**: Tambahkan perhitungan di PHP

---

## 🛠️ IMPLEMENTASI

### 1. Buat Helper Function
Buat file: `duration_calculator.php`

```php
<?php
/**
 * Duration Calculator Helper
 * Menghitung durasi kerja dan overwork untuk kompatibilitas free hosting
 */

/**
 * Calculate work duration in minutes
 * @param string $waktu_masuk - Format: 'Y-m-d H:i:s'
 * @param string $waktu_keluar - Format: 'Y-m-d H:i:s'
 * @return int Duration in minutes
 */
function calculate_durasi_kerja($waktu_masuk, $waktu_keluar) {
    if (empty($waktu_masuk) || empty($waktu_keluar)) {
        return 0;
    }
    
    $masuk = strtotime($waktu_masuk);
    $keluar = strtotime($waktu_keluar);
    
    if ($masuk === false || $keluar === false) {
        return 0;
    }
    
    $durasi_detik = $keluar - $masuk;
    $durasi_menit = round($durasi_detik / 60);
    
    // Validasi: durasi tidak boleh negatif atau lebih dari 24 jam
    if ($durasi_menit < 0 || $durasi_menit > 1440) {
        return 0;
    }
    
    return (int)$durasi_menit;
}

/**
 * Calculate overwork duration in minutes
 * @param string $waktu_keluar_actual - Actual checkout time
 * @param string $tanggal_absensi - Date of attendance
 * @param string $jam_keluar_shift - Expected shift end time (HH:mm:ss)
 * @return int Overwork duration in minutes
 */
function calculate_durasi_overwork($waktu_keluar_actual, $tanggal_absensi, $jam_keluar_shift) {
    if (empty($waktu_keluar_actual) || empty($jam_keluar_shift)) {
        return 0;
    }
    
    // Construct expected end time
    $expected_end = $tanggal_absensi . ' ' . $jam_keluar_shift;
    $expected_end_timestamp = strtotime($expected_end);
    $actual_end_timestamp = strtotime($waktu_keluar_actual);
    
    if ($expected_end_timestamp === false || $actual_end_timestamp === false) {
        return 0;
    }
    
    // Calculate overwork (only if worked longer than expected)
    $overwork_detik = $actual_end_timestamp - $expected_end_timestamp;
    
    if ($overwork_detik <= 0) {
        return 0; // No overwork
    }
    
    $overwork_menit = round($overwork_detik / 60);
    
    // Validasi: overwork maksimal 8 jam (480 menit)
    if ($overwork_menit > 480) {
        $overwork_menit = 480;
    }
    
    return (int)$overwork_menit;
}

/**
 * Calculate lateness in minutes
 * @param string $waktu_masuk_actual - Actual check-in time
 * @param string $tanggal_absensi - Date of attendance
 * @param string $jam_masuk_shift - Expected shift start time (HH:mm:ss)
 * @return int Lateness in minutes (0 if on time)
 */
function calculate_menit_terlambat($waktu_masuk_actual, $tanggal_absensi, $jam_masuk_shift) {
    if (empty($waktu_masuk_actual) || empty($jam_masuk_shift)) {
        return 0;
    }
    
    // Construct expected start time
    $expected_start = $tanggal_absensi . ' ' . $jam_masuk_shift;
    $expected_start_timestamp = strtotime($expected_start);
    $actual_start_timestamp = strtotime($waktu_masuk_actual);
    
    if ($expected_start_timestamp === false || $actual_start_timestamp === false) {
        return 0;
    }
    
    // Calculate lateness (only if came late)
    $late_detik = $actual_start_timestamp - $expected_start_timestamp;
    
    if ($late_detik <= 0) {
        return 0; // On time or early
    }
    
    $late_menit = round($late_detik / 60);
    
    return (int)$late_menit;
}

/**
 * Get lateness status based on minutes late
 * @param int $menit_terlambat
 * @return string Status keterlambatan
 */
function get_status_keterlambatan($menit_terlambat) {
    if ($menit_terlambat == 0) {
        return 'tepat waktu';
    } elseif ($menit_terlambat < 20) {
        return 'terlambat kurang dari 20 menit';
    } else {
        return 'terlambat lebih dari 20 menit';
    }
}

/**
 * Calculate all duration metrics at once
 * @param array $data - Array with keys: waktu_masuk, waktu_keluar, tanggal_absensi, jam_masuk_shift, jam_keluar_shift
 * @return array Array with keys: durasi_kerja_menit, durasi_overwork_menit, menit_terlambat, status_keterlambatan
 */
function calculate_all_durations($data) {
    $result = [
        'durasi_kerja_menit' => 0,
        'durasi_overwork_menit' => 0,
        'menit_terlambat' => 0,
        'status_keterlambatan' => 'tepat waktu'
    ];
    
    // Calculate work duration
    if (!empty($data['waktu_masuk']) && !empty($data['waktu_keluar'])) {
        $result['durasi_kerja_menit'] = calculate_durasi_kerja(
            $data['waktu_masuk'],
            $data['waktu_keluar']
        );
    }
    
    // Calculate overwork
    if (!empty($data['waktu_keluar']) && !empty($data['jam_keluar_shift'])) {
        $result['durasi_overwork_menit'] = calculate_durasi_overwork(
            $data['waktu_keluar'],
            $data['tanggal_absensi'],
            $data['jam_keluar_shift']
        );
    }
    
    // Calculate lateness
    if (!empty($data['waktu_masuk']) && !empty($data['jam_masuk_shift'])) {
        $result['menit_terlambat'] = calculate_menit_terlambat(
            $data['waktu_masuk'],
            $data['tanggal_absensi'],
            $data['jam_masuk_shift']
        );
        
        $result['status_keterlambatan'] = get_status_keterlambatan(
            $result['menit_terlambat']
        );
    }
    
    return $result;
}
?>
```

---

### 2. Update File yang Melakukan INSERT/UPDATE Absensi

#### a. Update `proses_approve.php` (Untuk izin/sakit yang auto-create absensi)

```php
// Di bagian yang create absensi untuk izin/sakit
require_once 'duration_calculator.php';

// ... existing code ...

// Prepare data untuk insert absensi
$tanggal_absensi = $current_date;
$waktu_masuk = null; // Izin/sakit tidak ada waktu masuk
$waktu_keluar = null;

// Calculate durations (akan return 0 semua karena waktu_masuk dan waktu_keluar NULL)
$durations = calculate_all_durations([
    'waktu_masuk' => $waktu_masuk,
    'waktu_keluar' => $waktu_keluar,
    'tanggal_absensi' => $tanggal_absensi,
    'jam_masuk_shift' => $jam_masuk_shift,
    'jam_keluar_shift' => $jam_keluar_shift
]);

// Insert dengan durasi
$insert_query = "INSERT INTO absensi 
    (user_id, tanggal_absensi, waktu_masuk, waktu_keluar, 
     jam_masuk_shift, jam_keluar_shift, status_lokasi, outlet_id,
     durasi_kerja_menit, durasi_overwork_menit, menit_terlambat, status_keterlambatan)
VALUES 
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
    waktu_masuk = VALUES(waktu_masuk),
    status_lokasi = VALUES(status_lokasi),
    durasi_kerja_menit = VALUES(durasi_kerja_menit)";

$stmt = $conn->prepare($insert_query);
$stmt->bind_param("issssssiiiis", 
    $user_id, 
    $tanggal_absensi, 
    $waktu_masuk, 
    $waktu_keluar,
    $jam_masuk_shift,
    $jam_keluar_shift,
    $tipe_pengajuan, // 'izin' atau 'sakit'
    $outlet_id,
    $durations['durasi_kerja_menit'],
    $durations['durasi_overwork_menit'],
    $durations['menit_terlambat'],
    $durations['status_keterlambatan']
);
```

#### b. Update Script yang Handle Absen Keluar

Cari file yang handle absen keluar (biasanya ada API atau proses absen):

```php
require_once 'duration_calculator.php';

// ... existing code untuk get data absensi ...

// Saat update waktu_keluar
$waktu_keluar = date('Y-m-d H:i:s');

// Calculate durations
$durations = calculate_all_durations([
    'waktu_masuk' => $existing_waktu_masuk, // dari database
    'waktu_keluar' => $waktu_keluar,
    'tanggal_absensi' => $tanggal_absensi,
    'jam_masuk_shift' => $jam_masuk_shift,
    'jam_keluar_shift' => $jam_keluar_shift
]);

// Update dengan durasi
$update_query = "UPDATE absensi 
    SET waktu_keluar = ?,
        durasi_kerja_menit = ?,
        durasi_overwork_menit = ?,
        status_lembur = ?
    WHERE id = ?";

$status_lembur = ($durations['durasi_overwork_menit'] > 0) ? 'pending' : NULL;

$stmt = $conn->prepare($update_query);
$stmt->bind_param("siisi",
    $waktu_keluar,
    $durations['durasi_kerja_menit'],
    $durations['durasi_overwork_menit'],
    $status_lembur,
    $absensi_id
);
```

---

### 3. Update Script Existing Data (One-time migration)

Buat script: `recalculate_all_durations.php`

```php
<?php
require_once 'connect.php';
require_once 'duration_calculator.php';

// Get all absensi with waktu_masuk and waktu_keluar
$query = "SELECT id, waktu_masuk, waktu_keluar, tanggal_absensi, 
                 jam_masuk_shift, jam_keluar_shift
          FROM absensi
          WHERE waktu_masuk IS NOT NULL 
            AND waktu_keluar IS NOT NULL
            AND (durasi_kerja_menit IS NULL OR durasi_kerja_menit = 0)";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$updated = 0;
$errors = 0;

while ($row = mysqli_fetch_assoc($result)) {
    // Calculate durations
    $durations = calculate_all_durations($row);
    
    // Update database
    $update_query = "UPDATE absensi 
                    SET durasi_kerja_menit = ?,
                        durasi_overwork_menit = ?,
                        menit_terlambat = ?,
                        status_keterlambatan = ?
                    WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "iiisi",
        $durations['durasi_kerja_menit'],
        $durations['durasi_overwork_menit'],
        $durations['menit_terlambat'],
        $durations['status_keterlambatan'],
        $row['id']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $updated++;
    } else {
        $errors++;
        echo "Error updating ID {$row['id']}: " . mysqli_error($conn) . "\n";
    }
    
    mysqli_stmt_close($stmt);
}

echo "Migration completed:\n";
echo "- Updated: $updated records\n";
echo "- Errors: $errors records\n";

mysqli_close($conn);
?>
```

---

## 📋 CHECKLIST IMPLEMENTASI

### Fase 1: Persiapan
- [ ] Buat file `duration_calculator.php`
- [ ] Test function dengan sample data
- [ ] Backup database sebelum migration

### Fase 2: Migration Existing Data
- [ ] Jalankan `recalculate_all_durations.php`
- [ ] Verifikasi hasil kalkulasi
- [ ] Cek report untuk anomali

### Fase 3: Update Kode Produksi
- [ ] Update `proses_approve.php` (izin/sakit auto-create)
- [ ] Update script absen masuk/keluar
- [ ] Update script manual entry (jika ada)
- [ ] Update script reschedule/swap

### Fase 4: Testing
- [ ] Test absen masuk/keluar normal
- [ ] Test dengan shift berbeda
- [ ] Test overwork calculation
- [ ] Test lateness calculation
- [ ] Test izin/sakit auto-create

### Fase 5: Deployment
- [ ] Deploy ke staging/testing
- [ ] User testing
- [ ] Deploy ke production
- [ ] Monitor logs 7 hari

---

## 🎯 MANFAAT

### Dengan Implementasi PHP Duration Calculator:
1. ✅ **Kompatibilitas Maksimum**: Jalan di semua hosting (termasuk free)
2. ✅ **Tidak Bergantung Trigger**: Lebih portable
3. ✅ **Debugging Mudah**: Logic ada di PHP, bisa di-debug
4. ✅ **Validasi Lebih Baik**: Bisa tambah business rules di PHP
5. ✅ **Testing Lebih Mudah**: Unit test untuk function

### Trade-offs:
- ⚠️ Perlu update manual di setiap tempat INSERT/UPDATE absensi
- ⚠️ Sedikit lebih banyak kode PHP
- ⚠️ Perlu migration script untuk data existing

---

## ⚡ QUICK START

### Option A: Implementasi Penuh (Recommended)
```bash
# 1. Copy helper file
cp duration_calculator.php /path/to/aplikasi/

# 2. Run migration
php recalculate_all_durations.php

# 3. Update all INSERT/UPDATE absensi code
# (Manual - lihat panduan di atas)

# 4. Test thoroughly
# 5. Deploy
```

### Option B: Minimal (Hanya untuk data baru)
```bash
# 1. Copy helper file
cp duration_calculator.php /path/to/aplikasi/

# 2. Update hanya di file baru (proses_approve.php)
# (Manual)

# 3. Data lama tetap pakai nilai dari trigger
# (OK untuk transition period)
```

---

## 🔍 TROUBLESHOOTING

### Durasi negatif atau tidak masuk akal
```php
// Add validation in calculate_durasi_kerja
if ($durasi_menit < 0 || $durasi_menit > 1440) {
    error_log("Invalid duration: $durasi_menit for absensi_id: $id");
    return 0;
}
```

### Overwork lebih dari 8 jam
```php
// Add cap in calculate_durasi_overwork
if ($overwork_menit > 480) {
    error_log("Overwork capped at 8 hours for absensi_id: $id");
    $overwork_menit = 480;
}
```

### Timezone issues
```php
// Set timezone di awal script
date_default_timezone_set('Asia/Jakarta');
```

---

## 📊 MONITORING

Setelah implementasi, monitor:
1. Durasi kerja rata-rata per hari
2. Anomali (durasi > 12 jam atau < 4 jam)
3. Overwork patterns
4. Lateness trends

Query untuk monitoring:
```sql
-- Check for anomalies
SELECT id, user_id, tanggal_absensi, 
       durasi_kerja_menit, durasi_overwork_menit
FROM absensi
WHERE durasi_kerja_menit > 720  -- > 12 jam
   OR durasi_kerja_menit < 240  -- < 4 jam
ORDER BY tanggal_absensi DESC;
```

---

## ✅ KESIMPULAN

**Prioritas**: MEDIUM (optional tapi recommended)

**Kapan harus implement?**
- ✅ Sebelum deploy ke free hosting (ByetHost, etc)
- ✅ Jika ingin portabilitas maksimum
- ✅ Jika perlu custom business rules untuk durasi

**Kapan bisa skip?**
- ⚠️ Jika deploy ke hosting dengan TRIGGER support
- ⚠️ Jika sistem sudah stable dan tidak mau risky changes
- ⚠️ Jika data existing sudah benar dan tidak perlu recalculate

**Rekomendasi Tim**:
Implement sekarang untuk future-proof, atau minimal implement untuk data baru saja.
