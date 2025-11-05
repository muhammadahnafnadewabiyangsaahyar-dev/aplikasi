# 📅 Jadwal Shift User - Dokumentasi

## Overview
Fitur **Jadwal Shift User** adalah halaman khusus untuk karyawan (user) melihat dan mengonfirmasi jadwal shift mereka yang telah ditetapkan oleh admin.

## File yang Terlibat

### 1. **jadwal_shift.php**
- Halaman utama untuk user melihat jadwal shift
- Menampilkan kalender bulan berjalan dan bulan berikutnya
- Statistik shift (total, pending, confirmed, declined)
- Interface untuk konfirmasi/tolak shift

### 2. **style_jadwal_shift.css**
- Styling khusus untuk halaman jadwal shift user
- Terpisah dari style kalender admin
- Responsive design untuk mobile

### 3. **script_jadwal_shift.js**
- JavaScript untuk kalender user
- Fungsi konfirmasi/tolak shift
- Terpisah dari script kalender admin

### 4. **api_shift_confirmation.php**
- API endpoint untuk konfirmasi shift oleh user
- Memproses status: `confirmed` atau `declined`
- Menyimpan catatan dari user

## Fitur Utama

### 1. Dashboard Statistik
- **Total Shift**: Jumlah shift yang ditetapkan
- **Menunggu Konfirmasi**: Shift dengan status pending
- **Dikonfirmasi**: Shift yang sudah dikonfirmasi
- **Ditolak**: Shift yang ditolak

### 2. Kalender Interaktif
- View per bulan dengan navigasi prev/next
- Color coding:
  - 🟡 **Kuning (#fff3cd)**: Hari ini
  - 🔵 **Biru muda (#e3f2fd)**: Ada shift (pending)
  - 🟢 **Hijau muda (#e8f5e9)**: Shift dikonfirmasi
  - 🔴 **Merah muda (#ffebee)**: Shift ditolak

### 3. Aksi pada Shift
- **✓ Konfirmasi**: Setujui shift yang ditetapkan
- **✗ Tolak**: Tolak shift dengan memberikan alasan
- **📋 Detail**: Lihat detail lengkap shift

### 4. Modal Detail Shift
Menampilkan informasi lengkap:
- Tanggal (hari, tanggal, bulan, tahun)
- Nama cabang
- Nama shift
- Jam kerja (mulai - selesai)
- Status konfirmasi
- Waktu konfirmasi (jika sudah dikonfirmasi)
- Catatan karyawan (jika ada)

### 5. Modal Konfirmasi
- Input untuk menambahkan catatan
- **Konfirmasi shift**: Catatan opsional
- **Tolak shift**: Catatan wajib (alasan penolakan)

## Alur Kerja

```
1. Admin assign shift ke user (via shift_management.php)
   ↓
2. User login dan buka jadwal_shift.php
   ↓
3. User melihat shift dengan status "pending"
   ↓
4. User klik "✓ Konfirmasi" atau "✗ Tolak"
   ↓
5. User dapat menambahkan catatan (wajib untuk tolak)
   ↓
6. System update status via api_shift_confirmation.php
   ↓
7. Admin dapat melihat status konfirmasi di shift_management.php
```

## Database Schema

### Table: `shift_assignments`
```sql
CREATE TABLE `shift_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cabang_id` int(11) NOT NULL,
  `tanggal_shift` date NOT NULL,
  `status_konfirmasi` enum('pending','confirmed','declined') DEFAULT 'pending',
  `waktu_konfirmasi` datetime DEFAULT NULL,
  `catatan_pegawai` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`, `tanggal_shift`),
  KEY `user_id` (`user_id`),
  KEY `cabang_id` (`cabang_id`),
  CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES `register` (`id`),
  CONSTRAINT FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`)
);
```

### Field Explanation:
- **status_konfirmasi**: Status konfirmasi shift
  - `pending`: Belum dikonfirmasi
  - `confirmed`: Dikonfirmasi oleh user
  - `declined`: Ditolak oleh user
- **waktu_konfirmasi**: Timestamp saat user konfirmasi/tolak
- **catatan_pegawai**: Catatan dari user (opsional untuk confirm, wajib untuk decline)

## API Endpoints

### POST api_shift_confirmation.php
**Parameters:**
- `shift_id` (required): ID shift yang akan dikonfirmasi
- `status` (required): 'confirmed' atau 'declined'
- `catatan` (optional): Catatan dari user

**Response:**
```json
{
  "status": "success",
  "message": "Shift berhasil dikonfirmasi"
}
```

**Security:**
- Validasi user login
- Validasi shift milik user yang login
- Prevent unauthorized access

## Perbedaan dengan Kalender Admin

| Feature | jadwal_shift.php (User) | kalender.php (Admin) |
|---------|------------------------|----------------------|
| **Role** | User/Karyawan | Admin only |
| **View** | Shift sendiri saja | Semua shift semua karyawan |
| **Action** | Konfirmasi/Tolak shift | Assign/Edit/Delete shift |
| **Data Scope** | Current & next month | All time |
| **Features** | Simple & focused | Advanced management |
| **Script** | script_jadwal_shift.js | script_kalender_database.js |
| **CSS** | style_jadwal_shift.css | style.css |

## Keamanan

1. **Session Check**: Validasi user login di PHP
2. **User Validation**: API cek shift milik user yang login
3. **XSS Prevention**: Escape HTML di JavaScript
4. **CSRF Protection**: Gunakan session-based auth

## User Experience

### Workflow User:
1. Login ke system
2. Klik menu "Jadwal Shift Saya"
3. Lihat kalender dengan shift yang ditetapkan
4. Klik shift untuk melihat detail
5. Konfirmasi atau tolak shift dengan catatan
6. System update real-time

### Visual Feedback:
- ✓ Success: Alert hijau
- ✗ Error: Alert merah
- 🔄 Loading: Disabled button saat proses
- 🎨 Color coding: Status shift jelas

## Responsive Design

- **Desktop**: Full calendar dengan semua fitur
- **Tablet**: Grid statistik 2 kolom
- **Mobile**: Stack layout, full-width buttons

## Testing Checklist

- [ ] User dapat login dan akses halaman
- [ ] Kalender tampil dengan benar
- [ ] Statistik shift akurat
- [ ] Navigasi bulan berfungsi
- [ ] Shift pending dapat dikonfirmasi
- [ ] Shift pending dapat ditolak dengan catatan
- [ ] Modal detail tampil dengan benar
- [ ] Status update tersimpan di database
- [ ] Admin dapat melihat status konfirmasi
- [ ] Responsive di mobile

## Future Enhancements

1. **Notifikasi Push**: Alert saat shift baru ditetapkan
2. **Export PDF**: Download jadwal shift per bulan
3. **Riwayat**: Lihat shift bulan-bulan sebelumnya
4. **Request Shift**: User bisa request perubahan shift
5. **Swap Shift**: User bisa tukar shift dengan user lain
6. **Reminder**: Email/SMS reminder H-1 shift
7. **Konfirmasi Batch**: Konfirmasi multiple shift sekaligus
8. **Filter View**: Filter by status, cabang, dll

## Maintenance Notes

- Update script terpisah dari kalender admin
- CSS khusus untuk flexibility customization
- Keep API simple dan focused
- Monitor user feedback untuk improvement

---

**Created:** 2025-11-05  
**Last Updated:** 2025-11-05  
**Version:** 1.0  
**Author:** Development Team
