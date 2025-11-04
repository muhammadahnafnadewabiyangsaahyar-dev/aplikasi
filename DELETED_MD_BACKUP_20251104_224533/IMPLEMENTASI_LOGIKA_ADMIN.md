# Dokumentasi: Implementasi Logika Khusus ADMIN

## 📋 Ringkasan
Implementasi logika khusus untuk role ADMIN dalam sistem absensi, dengan aturan yang berbeda dari user biasa.

## 🎯 Fitur yang Diimplementasikan

### 1. **Absensi Remote untuk Admin**
- ✅ Admin dapat melakukan absensi dari **mana saja** (tidak terikat lokasi cabang)
- ✅ Tidak ada validasi GPS/lokasi untuk admin
- ✅ Status lokasi: `"Admin - Remote"`

### 2. **Tidak Ada Shift untuk Admin**
- ✅ Admin tidak terikat jam shift masuk/keluar
- ✅ Tidak ada perhitungan keterlambatan untuk admin
- ✅ Status keterlambatan: `"tidak ada shift"`
- ✅ Potongan tunjangan: `"tidak ada"`

### 3. **Validasi Jam Absen (07:00 - 23:59)**
- ✅ Berlaku untuk **SEMUA user** (admin dan non-admin)
- ✅ Admin hanya bisa absen antara jam 07:00 - 23:59
- ✅ Di luar jam tersebut akan ditolak dengan pesan error

### 4. **Status Kehadiran Berdasarkan Durasi Kerja**
- ✅ Admin: Status "Hadir" jika kerja **minimal 8 jam**
- ✅ Admin: Status "Tidak Hadir" jika kerja **< 8 jam** atau belum absen keluar
- ✅ User: Status berdasarkan jam keluar vs jam shift (existing logic)

## 📝 Perubahan Kode

### File: `proses_absensi.php`

#### 1. Logika Branching Admin vs User
```php
if ($is_admin) {
    // Admin: Skip validasi lokasi & shift
    $status_lokasi = 'Admin - Remote';
    // Gunakan data default cabang untuk konsistensi
} else {
    // User: Validasi lokasi & shift (existing logic)
}
```

#### 2. Keterlambatan untuk Admin
```php
if ($is_admin) {
    $menit_terlambat = 0;
    $status_keterlambatan = 'tidak ada shift';
    $potongan_tunjangan = 'tidak ada';
} else {
    // User: Hitung keterlambatan dengan 3 level (existing logic)
}
```

#### 3. Validasi Jam Absen (Berlaku untuk SEMUA)
```php
if ($jam_sekarang < '07:00:00' || $jam_sekarang > '23:59:59') {
    send_json(['status' => 'error', 'message' => 'Absensi hanya dapat dilakukan antara jam 07:00 - 23:59']);
}
```

### File Baru: `calculate_status_kehadiran.php`

Helper script untuk menghitung status kehadiran:
- **Admin**: Minimal 8 jam kerja → "Hadir"
- **User**: Berdasarkan jam keluar vs jam shift

Dapat dipanggil:
1. Via cron job untuk batch update
2. Real-time di view_absensi.php atau rekapabsen.php

### File Baru: `migration_add_status_kehadiran.sql`

Migration untuk menambahkan kolom `status_kehadiran` ke tabel `absensi`.

## 🔧 Cara Penggunaan

### 1. Jalankan Migration
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
mysql -u root aplikasi < migration_add_status_kehadiran.sql
```

### 2. Update Status Kehadiran (Batch)
```bash
# Update untuk hari ini
php calculate_status_kehadiran.php

# Update untuk tanggal tertentu
php calculate_status_kehadiran.php 2025-01-15
```

### 3. Setup Cron Job (Opsional)
```bash
# Tambahkan ke crontab untuk auto-update setiap hari jam 23:30
30 23 * * * cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi && php calculate_status_kehadiran.php
```

## 📊 Perbandingan: Admin vs User

| Aspek | Admin | User |
|-------|-------|------|
| **Validasi Lokasi** | ❌ Tidak ada | ✅ Wajib (GPS) |
| **Validasi Shift** | ❌ Tidak ada | ✅ Wajib |
| **Jam Absen** | ⏰ 07:00 - 23:59 | ⏰ 07:00 - 23:59 |
| **Keterlambatan** | ❌ Tidak ada | ✅ 3 Level (1-19, 20-39, 40+ menit) |
| **Potongan Tunjangan** | ❌ Tidak ada | ✅ Ya (sesuai level) |
| **Status Kehadiran** | ✅ Minimal 8 jam kerja | ✅ Berdasarkan jam keluar vs shift |
| **Status Lokasi** | "Admin - Remote" | "Valid" / "Tidak Valid" |

## 🧪 Skenario Testing

### Admin Testing
1. ✅ **Absen dari lokasi jauh** → Harus berhasil (tidak ada error lokasi)
2. ✅ **Absen jam 08:00 (pagi)** → Berhasil, status "tidak ada shift"
3. ✅ **Absen keluar jam 17:00 (9 jam kerja)** → Status kehadiran: "Hadir"
4. ✅ **Absen keluar jam 15:00 (7 jam kerja)** → Status kehadiran: "Tidak Hadir"
5. ❌ **Absen jam 06:00** → Error: "Absensi hanya dapat dilakukan antara jam 07:00 - 23:59"
6. ❌ **Absen jam 00:00** → Error: "Absensi hanya dapat dilakukan antara jam 07:00 - 23:59"

### User Testing (Existing)
1. ✅ **Absen dari cabang** → Berhasil dengan validasi lokasi & shift
2. ✅ **Absen terlambat 15 menit** → Keterlambatan Level 1, tidak ada potongan
3. ✅ **Absen terlambat 25 menit** → Keterlambatan Level 2, potong tunjangan makan
4. ✅ **Absen terlambat 50 menit** → Keterlambatan Level 3, potong makan+transport
5. ❌ **Absen dari rumah** → Error: "Lokasi tidak sah"
6. ❌ **Absen jam 06:00** → Error: "Absensi hanya dapat dilakukan antara jam 07:00 - 23:59"

## 🐛 Bug Fixes & Improvements

### Fixed
- ✅ Admin sekarang tidak pernah dicek lokasi/shift
- ✅ Admin tidak ada keterlambatan atau potongan tunjangan
- ✅ Jam absen dibatasi 07:00-23:59 untuk SEMUA user (termasuk admin)
- ✅ Status kehadiran admin berdasarkan durasi kerja (≥8 jam)

### Pending
- ⏳ Manual browser testing untuk semua skenario
- ⏳ Update tampilan view_absensi.php untuk menampilkan status kehadiran real-time
- ⏳ Update tampilan rekapabsen.php untuk menampilkan status kehadiran
- ⏳ Setup cron job untuk auto-update status kehadiran
- ⏳ Dokumentasi user manual

## 📁 File yang Terlibat

```
/Applications/XAMPP/xamppfiles/htdocs/aplikasi/
├── proses_absensi.php             # ✅ Updated (logika admin vs user)
├── calculate_status_kehadiran.php # 🆕 Helper untuk hitung status kehadiran
├── migration_add_status_kehadiran.sql # 🆕 Migration SQL
├── IMPLEMENTASI_LOGIKA_ADMIN.md   # 🆕 Dokumentasi ini
├── view_absensi.php               # ⏳ Perlu update untuk tampilan status kehadiran
├── rekapabsen.php                 # ⏳ Perlu update untuk tampilan status kehadiran
└── absen.php                      # ✅ No changes needed (form tetap sama)
```

## 💡 Catatan Penting

1. **Minimal Jam Kerja Admin**: Disesuaikan dengan kebijakan perusahaan (default: 8 jam)
2. **Status Kehadiran**: Dihitung di akhir hari atau saat view data (bukan real-time saat absensi)
3. **Jam Absen**: Batasan 07:00-23:59 berlaku untuk SEMUA user tanpa exception
4. **Admin Remote**: Admin tetap harus input GPS coordinate, tapi tidak divalidasi

## 🔐 Keamanan

- ✅ CSRF token tetap wajib
- ✅ Rate limiting tetap aktif (10 percobaan per jam)
- ✅ Validasi role dari session
- ✅ Logging error ke file & database
- ✅ Validasi ukuran foto (max 5MB)

## 📞 Support

Jika ada bug atau pertanyaan:
1. Cek file log: `logs/absensi_errors.log`
2. Cek database table: `absensi_error_log`
3. Contact: admin sistem

---
**Last Updated**: 2025-01-XX
**Version**: 2.0.0
