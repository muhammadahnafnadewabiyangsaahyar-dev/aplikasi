# 📋 Panduan Implementasi Shift Management Enhancement

## 🎯 Tujuan
Menambahkan fitur manajemen shift, penjadwalan dinamis, konfirmasi shift, deteksi overwork otomatis, dan sistem payroll yang lebih komprehensif **TANPA** mengubah struktur tabel yang sudah ada secara drastis.

## 📊 Ringkasan Perubahan

### ✅ Tabel Baru yang Ditambahkan
1. **`shift_assignments`** - Penjadwalan shift harian per pegawai
2. **`libur_nasional`** - Daftar hari libur nasional
3. **`komponen_gaji_detail`** - Komponen gaji detail per pegawai per bulan
4. **`slip_gaji_history`** - Histori pembuatan batch slip gaji

### 🔧 Tabel yang Dimodifikasi
1. **`absensi`** - Menambahkan:
   - `cabang_id` (referensi ke shift yang dijalankan)
   - `jam_masuk_shift`, `jam_keluar_shift` (jam kerja shift)
   - `durasi_kerja_menit`, `durasi_overwork_menit` (tracking durasi)
   - `is_overwork_approved` (status approval lembur)

2. **`register`** - Menambahkan:
   - `gaji_pokok`, `tunjangan_transport`, `tunjangan_makan`, `tunjangan_jabatan`
   - `tarif_overwork_per_jam` (tarif lembur per jam)

3. **`pengajuan_izin`** - Menambahkan:
   - `mempengaruhi_shift` (apakah izin mempengaruhi jadwal shift)
   - `shift_diganti` (apakah shift sudah diganti pegawai lain)

### 👁️ Views yang Dibuat
1. **`v_jadwal_shift_harian`** - View jadwal shift harian dengan detail pegawai
2. **`v_absensi_dengan_shift`** - View absensi dengan informasi shift
3. **`v_ringkasan_gaji`** - View ringkasan gaji dengan detail komponen

### 🔄 Stored Procedures yang Dibuat
1. **`sp_assign_shift`** - Assign shift ke pegawai
2. **`sp_konfirmasi_shift`** - Konfirmasi shift oleh pegawai
3. **`sp_hitung_kehadiran_periode`** - Hitung kehadiran untuk periode tertentu

### ⚙️ Triggers yang Dibuat
1. **`tr_absensi_calculate_duration`** - Auto-calculate durasi kerja dan overwork

---

## 📝 Langkah-Langkah Implementasi

### Phase 1: Backup & Migration (Hari 1)

#### Step 1: Backup Database
```bash
# Backup database saat ini
mysqldump -u root -p aplikasi > backup_aplikasi_$(date +%Y%m%d).sql

# Verifikasi backup
ls -lh backup_aplikasi_*.sql
```

#### Step 2: Run Migration Script
```bash
# Login ke MySQL
mysql -u root -p aplikasi

# Run migration script
source /Applications/XAMPP/xamppfiles/htdocs/aplikasi/migration_shift_enhancement.sql

# Verify tables created
SHOW TABLES;
DESCRIBE shift_assignments;
DESCRIBE komponen_gaji_detail;
```

#### Step 3: Verify Migration
```sql
-- Check new tables
SELECT COUNT(*) FROM shift_assignments;
SELECT COUNT(*) FROM libur_nasional; -- Should have ~16 holidays for 2025

-- Check altered columns
DESCRIBE absensi;
DESCRIBE register;
DESCRIBE pengajuan_izin;

-- Test views
SELECT * FROM v_jadwal_shift_harian LIMIT 5;
SELECT * FROM v_absensi_dengan_shift LIMIT 5;
```

### Phase 2: Populate Initial Data (Hari 1-2)

#### Step 1: Set Salary Components for Existing Users
```sql
-- Update salary components for existing users
-- Example: Set basic salary for all users
UPDATE register 
SET 
    gaji_pokok = 3500000,
    tunjangan_transport = 500000,
    tunjangan_makan = 300000,
    tunjangan_jabatan = 0,
    tarif_overwork_per_jam = 20000
WHERE role = 'user' AND posisi IN ('Barista', 'Server', 'Kitchen');

-- Set higher salary for admin roles
UPDATE register 
SET 
    gaji_pokok = 5000000,
    tunjangan_transport = 700000,
    tunjangan_makan = 500000,
    tunjangan_jabatan = 1000000,
    tarif_overwork_per_jam = 30000
WHERE role = 'admin';

-- Verify
SELECT id, nama_lengkap, posisi, gaji_pokok, tunjangan_transport 
FROM register 
WHERE gaji_pokok > 0;
```

#### Step 2: Link Existing Absensi to Shifts
```sql
-- Update existing absensi records with cabang_id and shift times
-- This assumes you can determine cabang_id from user's outlet or other logic

-- Example: Update absensi with shift information
UPDATE absensi a
JOIN register r ON a.user_id = r.id
JOIN cabang c ON r.outlet = c.nama_cabang 
SET 
    a.cabang_id = c.id,
    a.jam_masuk_shift = c.jam_masuk,
    a.jam_keluar_shift = c.jam_keluar
WHERE a.cabang_id IS NULL
  AND c.nama_shift = 'pagi'; -- Adjust based on your logic

-- Verify
SELECT * FROM v_absensi_dengan_shift LIMIT 10;
```

### Phase 3: Develop UI for Shift Management (Hari 3-5)

#### File: `jadwal_shift.php` (Admin - Assign Shifts)
```php
<?php
require 'connect.php';
session_start();

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch outlets and shifts
$cabang_query = "SELECT * FROM cabang ORDER BY nama_cabang, jam_masuk";
$cabang_result = mysqli_query($connect, $cabang_query);

// Fetch active users
$users_query = "SELECT id, nama_lengkap, posisi, outlet FROM register WHERE role = 'user' ORDER BY nama_lengkap";
$users_result = mysqli_query($connect, $users_query);

// Get current week dates
$today = new DateTime();
$week_start = clone $today;
$week_start->modify('monday this week');
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $date = clone $week_start;
    $date->modify("+$i days");
    $dates[] = $date;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Shift</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .shift-calendar {
            overflow-x: auto;
        }
        .shift-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        .shift-table th, .shift-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .shift-table th {
            background-color: #4CAF50;
            color: white;
        }
        .shift-cell {
            cursor: pointer;
            min-height: 50px;
            position: relative;
        }
        .shift-cell:hover {
            background-color: #f0f0f0;
        }
        .shift-assigned {
            background-color: #4CAF50;
            color: white;
            padding: 5px;
            border-radius: 3px;
            font-size: 11px;
            margin: 2px;
        }
        .shift-pending {
            background-color: #FFC107;
            color: black;
        }
        .shift-confirmed {
            background-color: #4CAF50;
        }
        .shift-declined {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>📅 Jadwal Shift Pegawai</h1>
        
        <!-- Week Navigation -->
        <div class="week-nav">
            <button onclick="previousWeek()">← Minggu Sebelumnya</button>
            <span id="current-week">Minggu: <?php echo $week_start->format('d M Y'); ?></span>
            <button onclick="nextWeek()">Minggu Berikutnya →</button>
        </div>
        
        <!-- Shift Calendar -->
        <div class="shift-calendar">
            <table class="shift-table">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <?php foreach ($dates as $date): ?>
                            <th><?php echo $date->format('D<br>d M'); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($users_result, 0); ?>
                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['nama_lengkap']); ?><br>
                                <small><?php echo htmlspecialchars($user['posisi']); ?></small>
                            </td>
                            <?php foreach ($dates as $date): ?>
                                <td class="shift-cell" 
                                    data-user-id="<?php echo $user['id']; ?>"
                                    data-date="<?php echo $date->format('Y-m-d'); ?>"
                                    onclick="openShiftModal(this)">
                                    <?php
                                    // Check if shift assigned
                                    $date_str = $date->format('Y-m-d');
                                    $shift_query = "SELECT sa.*, c.nama_shift, c.jam_masuk, c.jam_keluar 
                                                   FROM shift_assignments sa
                                                   JOIN cabang c ON sa.cabang_id = c.id
                                                   WHERE sa.user_id = {$user['id']} 
                                                   AND sa.tanggal_shift = '$date_str'";
                                    $shift_result = mysqli_query($connect, $shift_query);
                                    
                                    if ($shift = mysqli_fetch_assoc($shift_result)) {
                                        $status_class = 'shift-' . $shift['status_konfirmasi'];
                                        echo "<div class='shift-assigned $status_class'>";
                                        echo htmlspecialchars($shift['nama_shift']) . "<br>";
                                        echo $shift['jam_masuk'] . " - " . $shift['jam_keluar'];
                                        echo "</div>";
                                    } else {
                                        echo "<span style='color: #999;'>+ Assign</span>";
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal for shift assignment -->
    <div id="shiftModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeShiftModal()">&times;</span>
            <h2>Assign Shift</h2>
            <form id="shiftForm" method="POST" action="proses_assign_shift.php">
                <input type="hidden" name="user_id" id="modal_user_id">
                <input type="hidden" name="tanggal_shift" id="modal_tanggal">
                
                <label>Pilih Shift:</label>
                <select name="cabang_id" required>
                    <option value="">-- Pilih Shift --</option>
                    <?php mysqli_data_seek($cabang_result, 0); ?>
                    <?php while ($cabang = mysqli_fetch_assoc($cabang_result)): ?>
                        <option value="<?php echo $cabang['id']; ?>">
                            <?php echo $cabang['nama_cabang'] . " - " . $cabang['nama_shift'] . 
                                     " (" . $cabang['jam_masuk'] . " - " . $cabang['jam_keluar'] . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <button type="submit">Assign Shift</button>
                <button type="button" onclick="deleteShift()">Hapus Shift</button>
            </form>
        </div>
    </div>
    
    <script>
        function openShiftModal(cell) {
            const userId = cell.dataset.userId;
            const date = cell.dataset.date;
            
            document.getElementById('modal_user_id').value = userId;
            document.getElementById('modal_tanggal').value = date;
            document.getElementById('shiftModal').style.display = 'block';
        }
        
        function closeShiftModal() {
            document.getElementById('shiftModal').style.display = 'none';
        }
        
        function deleteShift() {
            if (confirm('Hapus shift assignment?')) {
                const userId = document.getElementById('modal_user_id').value;
                const date = document.getElementById('modal_tanggal').value;
                
                fetch('proses_delete_shift.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `user_id=${userId}&tanggal_shift=${date}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                });
            }
        }
        
        function previousWeek() {
            // Implement week navigation
            window.location.href = '?week_offset=-1';
        }
        
        function nextWeek() {
            // Implement week navigation
            window.location.href = '?week_offset=1';
        }
    </script>
</body>
</html>
```

#### File: `proses_assign_shift.php`
```php
<?php
require 'connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_POST['user_id']);
    $cabang_id = intval($_POST['cabang_id']);
    $tanggal_shift = $_POST['tanggal_shift'];
    $created_by = $_SESSION['user_id'];
    
    // Call stored procedure
    $query = "CALL sp_assign_shift(?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "iisi", $user_id, $cabang_id, $tanggal_shift, $created_by);
    
    if (mysqli_stmt_execute($stmt)) {
        // Send notification to user
        $user_query = "SELECT nama_lengkap, no_whatsapp FROM register WHERE id = ?";
        $user_stmt = mysqli_prepare($connect, $user_query);
        mysqli_stmt_bind_param($user_stmt, "i", $user_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user = mysqli_fetch_assoc($user_result);
        
        // TODO: Send WhatsApp notification using Fonnte API
        // sendWhatsAppNotification($user['no_whatsapp'], "Anda dijadwalkan shift pada $tanggal_shift. Silakan konfirmasi.");
        
        $_SESSION['success_message'] = "Shift berhasil di-assign!";
    } else {
        $_SESSION['error_message'] = "Gagal assign shift: " . mysqli_error($connect);
    }
    
    header('Location: jadwal_shift.php');
    exit;
}
?>
```

#### File: `konfirmasi_shift.php` (User - Confirm Shifts)
```php
<?php
require 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get pending shift assignments
$query = "SELECT * FROM v_jadwal_shift_harian 
          WHERE user_id = ? 
          AND status_konfirmasi = 'pending'
          AND tanggal_shift >= CURDATE()
          ORDER BY tanggal_shift ASC";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Shift</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>✅ Konfirmasi Jadwal Shift</h1>
        
        <?php if (mysqli_num_rows($result) == 0): ?>
            <p>Tidak ada shift yang perlu dikonfirmasi.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Outlet</th>
                        <th>Shift</th>
                        <th>Jam Kerja</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($shift = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo date('d M Y (l)', strtotime($shift['tanggal_shift'])); ?></td>
                            <td><?php echo htmlspecialchars($shift['outlet']); ?></td>
                            <td><?php echo htmlspecialchars($shift['nama_shift']); ?></td>
                            <td><?php echo $shift['jam_masuk'] . " - " . $shift['jam_keluar']; ?></td>
                            <td>
                                <form method="POST" action="proses_konfirmasi_shift.php" style="display:inline;">
                                    <input type="hidden" name="assignment_id" value="<?php echo $shift['id']; ?>">
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-success">✓ Terima</button>
                                </form>
                                <button class="btn btn-danger" onclick="declineShift(<?php echo $shift['id']; ?>)">✗ Tolak</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <script>
        function declineShift(assignmentId) {
            const reason = prompt('Alasan menolak shift:');
            if (reason) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'proses_konfirmasi_shift.php';
                
                const idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'assignment_id';
                idField.value = assignmentId;
                
                const statusField = document.createElement('input');
                statusField.type = 'hidden';
                statusField.name = 'status';
                statusField.value = 'declined';
                
                const reasonField = document.createElement('input');
                reasonField.type = 'hidden';
                reasonField.name = 'catatan';
                reasonField.value = reason;
                
                form.appendChild(idField);
                form.appendChild(statusField);
                form.appendChild(reasonField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
```

### Phase 4: Update Attendance Logic (Hari 6-7)

#### Update `proses_absensi.php` to include shift information
```php
// After validating location and before inserting absensi
// Fetch user's shift for today
$today = date('Y-m-d');
$shift_query = "SELECT sa.cabang_id, c.jam_masuk, c.jam_keluar 
                FROM shift_assignments sa
                JOIN cabang c ON sa.cabang_id = c.id
                WHERE sa.user_id = ? 
                AND sa.tanggal_shift = ?
                AND sa.status_konfirmasi = 'confirmed'";
$shift_stmt = mysqli_prepare($connect, $shift_query);
mysqli_stmt_bind_param($shift_stmt, "is", $user_id, $today);
mysqli_stmt_execute($shift_stmt);
$shift_result = mysqli_stmt_get_result($shift_stmt);

if ($shift = mysqli_fetch_assoc($shift_result)) {
    $cabang_id = $shift['cabang_id'];
    $jam_masuk_shift = $shift['jam_masuk'];
    $jam_keluar_shift = $shift['jam_keluar'];
} else {
    // Fallback: use default shift from cabang_outlet or show error
    $cabang_id = null;
    $jam_masuk_shift = null;
    $jam_keluar_shift = null;
}

// Update INSERT query to include shift info
$insert_query = "INSERT INTO absensi (user_id, cabang_id, waktu_masuk, jam_masuk_shift, jam_keluar_shift, ...) 
                 VALUES (?, ?, NOW(), ?, ?, ...)";
```

### Phase 5: Payroll Generation (Hari 8-10)

#### File: `generate_payroll_batch.php`
```php
<?php
require 'connect.php';
require 'vendor/autoload.php';

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die('Unauthorized');
}

// Get periode
$periode_bulan = isset($_POST['bulan']) ? intval($_POST['bulan']) : date('n');
$periode_tahun = isset($_POST['tahun']) ? intval($_POST['tahun']) : date('Y');

// Generate batch ID
$batch_id = 'BATCH_' . $periode_tahun . str_pad($periode_bulan, 2, '0', STR_PAD_LEFT) . '_' . time();

// Start batch
$batch_query = "INSERT INTO slip_gaji_history (batch_id, periode_bulan, periode_tahun, generated_by, status_batch)
                VALUES (?, ?, ?, ?, 'processing')";
$stmt = mysqli_prepare($connect, $batch_query);
mysqli_stmt_bind_param($stmt, "siii", $batch_id, $periode_bulan, $periode_tahun, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);

// Get all active users
$users_query = "SELECT * FROM register WHERE role = 'user'";
$users_result = mysqli_query($connect, $users_query);

$total_processed = 0;
$total_gaji = 0;

while ($user = mysqli_fetch_assoc($users_result)) {
    // Calculate attendance using stored procedure
    $stmt = mysqli_prepare($connect, "CALL sp_hitung_kehadiran_periode(?, ?, ?, @hadir, @telat_ringan, @telat_berat, @alfa, @izin, @overwork_jam)");
    mysqli_stmt_bind_param($stmt, "iii", $user['id'], $periode_bulan, $periode_tahun);
    mysqli_stmt_execute($stmt);
    
    // Get output parameters
    $result = mysqli_query($connect, "SELECT @hadir, @telat_ringan, @telat_berat, @alfa, @izin, @overwork_jam");
    $attendance = mysqli_fetch_assoc($result);
    
    // Calculate salary components
    $gaji_pokok = $user['gaji_pokok'];
    $tunjangan_transport = $user['tunjangan_transport'];
    $tunjangan_makan = $user['tunjangan_makan'];
    $tunjangan_jabatan = $user['tunjangan_jabatan'];
    
    $overwork_amount = $attendance['@overwork_jam'] * $user['tarif_overwork_per_jam'];
    
    // Calculate deductions
    $potongan_telat_berat = $attendance['@telat_berat'] * 50000; // Rp 50k per terlambat berat
    $potongan_telat_ringan = $attendance['@telat_ringan'] * 25000; // Rp 25k per terlambat ringan
    $potongan_alfa = $attendance['@alfa'] * 100000; // Rp 100k per alfa
    
    $total_pendapatan = $gaji_pokok + $tunjangan_transport + $tunjangan_makan + $tunjangan_jabatan + $overwork_amount;
    $total_potongan = $potongan_telat_berat + $potongan_telat_ringan + $potongan_alfa;
    $gaji_bersih = $total_pendapatan - $total_potongan;
    
    // Insert into komponen_gaji_detail
    $insert_query = "INSERT INTO komponen_gaji_detail 
                     (user_id, periode_bulan, periode_tahun, gaji_pokok, tunjangan_transport, 
                      tunjangan_makan, tunjangan_jabatan, overwork_amount, overwork_hours,
                      potongan_telat_berat, potongan_telat_ringan, potongan_alfa,
                      total_pendapatan, total_potongan, gaji_bersih,
                      jumlah_hadir, jumlah_telat_ringan, jumlah_telat_berat, jumlah_alfa, jumlah_izin,
                      status_slip, generated_at, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'generated', NOW(), ?)
                     ON DUPLICATE KEY UPDATE
                     gaji_pokok = VALUES(gaji_pokok),
                     total_pendapatan = VALUES(total_pendapatan),
                     gaji_bersih = VALUES(gaji_bersih),
                     updated_at = NOW()";
    
    $stmt = mysqli_prepare($connect, $insert_query);
    mysqli_stmt_bind_param($stmt, "iiiddddddddddddiiiiiii", 
        $user['id'], $periode_bulan, $periode_tahun,
        $gaji_pokok, $tunjangan_transport, $tunjangan_makan, $tunjangan_jabatan,
        $overwork_amount, $attendance['@overwork_jam'],
        $potongan_telat_berat, $potongan_telat_ringan, $potongan_alfa,
        $total_pendapatan, $total_potongan, $gaji_bersih,
        $attendance['@hadir'], $attendance['@telat_ringan'], $attendance['@telat_berat'],
        $attendance['@alfa'], $attendance['@izin'],
        $_SESSION['user_id']
    );
    mysqli_stmt_execute($stmt);
    
    $total_processed++;
    $total_gaji += $gaji_bersih;
}

// Update batch status
$update_batch = "UPDATE slip_gaji_history 
                 SET status_batch = 'completed',
                     jumlah_pegawai = ?,
                     total_gaji_dibayarkan = ?,
                     completed_at = NOW()
                 WHERE batch_id = ?";
$stmt = mysqli_prepare($connect, $update_batch);
mysqli_stmt_bind_param($stmt, "ids", $total_processed, $total_gaji, $batch_id);
mysqli_stmt_execute($stmt);

echo json_encode([
    'success' => true,
    'batch_id' => $batch_id,
    'total_processed' => $total_processed,
    'total_gaji' => $total_gaji
]);
?>
```

---

## 🧪 Testing Checklist

### ✅ Database Migration
- [ ] Backup database berhasil
- [ ] Migration script running tanpa error
- [ ] Semua tabel baru ter-create
- [ ] Semua kolom baru ter-add
- [ ] Views berfungsi dengan baik
- [ ] Stored procedures bisa dipanggil
- [ ] Triggers berfungsi otomatis

### ✅ Shift Management
- [ ] Admin bisa assign shift ke pegawai
- [ ] Pegawai menerima notifikasi shift baru
- [ ] Pegawai bisa konfirmasi/tolak shift
- [ ] View jadwal shift menampilkan data dengan benar
- [ ] Tidak bisa assign shift double untuk pegawai yang sama di tanggal yang sama

### ✅ Attendance with Shift
- [ ] Absensi otomatis link ke shift yang dikonfirmasi
- [ ] Durasi kerja ter-calculate otomatis
- [ ] Overwork ter-detect otomatis jika > 30 menit
- [ ] Status overwork pending untuk approval
- [ ] Potongan keterlambatan ter-calculate dengan benar

### ✅ Payroll Generation
- [ ] Batch payroll generation berfungsi
- [ ] Komponen gaji ter-calculate dengan benar
- [ ] Potongan keterlambatan sesuai dengan data absensi
- [ ] Overwork amount sesuai dengan jam lembur approved
- [ ] Slip gaji ter-generate dalam format DOCX
- [ ] Email notification terkirim ke pegawai

---

## 📱 API Integration (Optional)

### WhatsApp Notification via Fonnte
```php
function sendWhatsAppNotification($phone, $message) {
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62'
        ),
        CURLOPT_HTTPHEADER => array(
            'Authorization: YOUR_FONNTE_TOKEN'
        ),
    ));
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    return json_decode($response, true);
}
```

---

## 🔒 Security Considerations

1. **Input Validation**: Semua input dari form harus divalidasi
2. **SQL Injection Prevention**: Gunakan prepared statements
3. **Role-Based Access Control**: Pastikan hanya admin yang bisa assign shift dan generate payroll
4. **File Upload Security**: Validasi tipe file slip gaji
5. **Database Backup**: Backup rutin setiap hari sebelum jam 12 malam

---

## 📈 Performance Optimization

1. **Indexing**: Semua foreign key dan date columns sudah diindex
2. **Query Optimization**: Gunakan views untuk query kompleks
3. **Caching**: Consider caching jadwal shift untuk mengurangi database queries
4. **Batch Processing**: Payroll generation menggunakan batch untuk handle banyak pegawai

---

## 🆘 Troubleshooting

### Error: "Duplicate entry for unique_user_date"
**Solusi**: Pegawai sudah di-assign shift untuk tanggal tersebut. Hapus assignment lama atau pilih tanggal lain.

### Error: "Cannot add foreign key constraint"
**Solusi**: Pastikan data referensi (user_id, cabang_id) exist di tabel parent.

### Trigger tidak berfungsi
**Solusi**: 
```sql
SHOW TRIGGERS;
DROP TRIGGER IF EXISTS tr_absensi_calculate_duration;
-- Re-create trigger from migration script
```

### Stored procedure error
**Solusi**:
```sql
SHOW PROCEDURE STATUS WHERE Db = 'aplikasi';
DROP PROCEDURE IF EXISTS sp_assign_shift;
-- Re-create procedure from migration script
```

---

## 📞 Support

Untuk bantuan lebih lanjut:
1. Check log error di `/var/log/mysql/error.log`
2. Review migration script untuk rollback instructions
3. Konsultasi dengan database administrator

---

**Last Updated**: 2025-01-XX
**Version**: 1.0.0
**Author**: Development Team
