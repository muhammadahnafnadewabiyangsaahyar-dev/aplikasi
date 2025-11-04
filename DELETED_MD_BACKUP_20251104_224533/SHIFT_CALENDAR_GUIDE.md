# 📅 Shift Calendar - Panduan Lengkap

## ✅ Implementasi Selesai

Sistem manajemen shift telah berhasil di-refactor untuk menggunakan **data struktur yang benar**:
- ✅ Setiap cabang memiliki **1 shift dengan jam kerja spesifik**
- ✅ Assignment adalah **per-pegawai, per-tanggal, per-cabang**
- ✅ Tidak ada lagi asumsi 3 shift universal per hari
- ✅ Calendar menampilkan shift sesuai jam kerja cabang yang sebenarnya

---

## 🏗️ Struktur Data

### Tabel: `cabang`
```sql
- id (primary key)
- nama_cabang (nama cabang)
- nama_shift (misal: "Shift Pagi", "Shift Sore")
- jam_masuk (misal: "08:00:00")
- jam_keluar (misal: "16:00:00")
```

### Tabel: `shift_assignments`
```sql
- id (primary key)
- user_id (FK ke register.id)
- cabang_id (FK ke cabang.id)
- tanggal_shift (DATE, tanggal assignment)
- status_konfirmasi ('pending', 'confirmed', 'declined')
- waktu_konfirmasi (timestamp konfirmasi)
- created_by (admin yang assign)
- created_at, updated_at
```

**Catatan Penting:**
- Jam shift **tidak disimpan** di `shift_assignments`
- Jam shift diambil dari tabel `cabang` saat query JOIN
- Satu pegawai hanya bisa punya **1 assignment per tanggal**

---

## 🎨 Fitur Calendar View

### 1. Filter Cabang (Wajib)
- Admin **harus pilih cabang** terlebih dahulu
- Hanya pegawai dari cabang terpilih yang muncul di rows
- Hanya assignments untuk cabang terpilih yang ditampilkan

### 2. Assign Shift
**Cara:**
1. Pilih cabang di dropdown
2. Klik dan drag pada baris pegawai di tanggal yang diinginkan
3. Konfirmasi assignment
4. Shift akan dibuat dengan jam kerja sesuai cabang terpilih

**Logika Backend:**
```php
// API akan:
1. Cek apakah pegawai sudah punya shift di tanggal tersebut
2. Insert ke shift_assignments dengan: user_id, cabang_id, tanggal_shift
3. Jam kerja diambil dari tabel cabang saat display
```

### 3. Pindah Shift (Drag & Drop)
**Cara:**
- Drag shift ke pegawai lain (vertical)
- Drag shift ke tanggal lain (horizontal)

**Logika:**
- `user_id` dan `tanggal_shift` diupdate
- `cabang_id` dan jam kerja **tetap sama**

### 4. Hapus Shift
**Cara:**
- Klik tombol X merah di pojok shift
- Konfirmasi penghapusan

### 5. Color Coding
Setiap cabang punya warna berbeda:
- Cabang 1 → Light green (#bfd9a9)
- Cabang 2 → Light blue (#b3d9ff)
- Cabang 3 → Light orange (#ffccb3)
- dst. (8 warna tersedia, dirotasi dengan modulo)

---

## 📋 Fitur Table View

### 1. Form Assignment
Form manual untuk assign shift:
- **Pegawai**: Pilih dari dropdown
- **Cabang/Shift**: Pilih cabang (akan menentukan jam kerja)
- **Tanggal**: Pilih tanggal assignment

### 2. Tabel Assignment
Menampilkan semua assignment bulan ini dengan:
- Tanggal shift
- Nama pegawai
- Nama cabang
- Nama shift + jam kerja
- Status konfirmasi (pending/confirmed/declined)
- Waktu konfirmasi
- Tombol hapus

---

## 🔧 File-file Penting

### 1. `shift_calendar.php` (Halaman Utama)
- UI dengan 2 views: Calendar & Table
- DayPilot Scheduler untuk calendar view
- Form dan tabel untuk table view
- JavaScript untuk CRUD operations

### 2. `api_shift_calendar.php` (Backend API)
Endpoints:
- `GET ?action=get_cabang` - List semua cabang
- `GET ?action=get_pegawai&cabang_id=X` - List pegawai per cabang
- `GET ?action=get_assignments&month=YYYY-MM&cabang_id=X` - List assignments
- `POST action=create` - Buat assignment baru
- `POST action=update` - Update assignment
- `POST action=delete` - Hapus assignment

### 3. `navbar.php`
Updated dengan link ke shift calendar:
```php
<a href="shift_calendar.php">📅 Shift Management</a>
```

### 4. `shift_management.php` (DEPRECATED)
File lama yang menggunakan 3 shift universal. **Jangan dipakai lagi.**

---

## 🚀 Cara Menggunakan

### Untuk Admin:

#### **A. Via Calendar View (Recommended)**
1. Login sebagai admin
2. Klik menu "📅 Shift Management"
3. **Pilih Cabang** dari dropdown (wajib)
4. Pilih bulan jika perlu
5. **Assign shift:**
   - Klik dan drag pada baris pegawai di tanggal yang diinginkan
   - Konfirmasi
6. **Pindah shift:**
   - Drag shift ke pegawai lain atau tanggal lain
7. **Hapus shift:**
   - Klik tombol X merah

#### **B. Via Table View**
1. Klik tombol "📋 Table View"
2. Isi form:
   - Pilih pegawai
   - Pilih cabang (menentukan shift dan jam kerja)
   - Pilih tanggal
3. Klik "✓ Assign Shift"
4. Lihat di tabel, hapus jika perlu

### Untuk User/Pegawai:

User akan melihat shift mereka di:
- `mainpageuser.php` - Dashboard dengan shift hari ini
- `view_absensi.php` - Riwayat absensi dan shift
- Notifikasi shift assignment (jika ada)

User bisa:
- **Konfirmasi** shift yang di-assign
- **Decline** shift jika ada alasan
- Lihat jam masuk/keluar sesuai cabang

---

## 🎯 Perbedaan dengan Sistem Lama

| Aspek | Sistem Lama (❌) | Sistem Baru (✅) |
|-------|-----------------|-----------------|
| **Shift per hari** | 3 shift universal (Pagi, Siang, Malam) | 1 shift per cabang dengan jam spesifik |
| **Jam kerja** | Hardcoded 00-08, 08-16, 16-24 | Dynamic dari tabel `cabang` |
| **Assignment** | Per shift_id (shift 1, 2, 3) | Per cabang_id + tanggal |
| **Fleksibilitas** | Tidak fleksibel | Sangat fleksibel per cabang |
| **Data akurat** | Tidak match DB | Match DB 100% |
| **Calendar** | Timeline 3 kolom per hari | Timeline per hari (simple) |
| **Color code** | By shift time | By cabang |

---

## 🐛 Troubleshooting

### Calendar tidak muncul?
**Cek:**
1. Apakah file `js/daypilot/daypilot-all.min.js` ada?
2. Apakah sudah pilih cabang? (wajib untuk load data)
3. Cek console browser untuk error JavaScript

### Data tidak muncul?
**Cek:**
1. Apakah sudah pilih cabang di dropdown?
2. Cek network tab di browser, pastikan API call berhasil
3. Cek response dari `api_shift_calendar.php`

### Error saat assign shift?
**Kemungkinan:**
- Pegawai sudah punya shift di tanggal tersebut
- Cabang tidak dipilih
- Data tidak valid

**Solusi:**
- Pilih tanggal lain atau pegawai lain
- Pastikan cabang sudah dipilih

### Warna shift tidak muncul?
**Cek:**
- Pastikan `cabang_id` ada di event data
- Cek function `getCabangColor()` di JavaScript

---

## 📊 Contoh Data

### Contoh Cabang:
```
ID | Nama Cabang | Nama Shift | Jam Masuk | Jam Keluar
1  | Jakarta Pusat | Shift Pagi | 08:00 | 16:00
2  | Bandung | Shift Sore | 14:00 | 22:00
3  | Surabaya | Shift Malam | 22:00 | 06:00
```

### Contoh Assignment:
```
ID | User ID | Cabang ID | Tanggal Shift | Status
1  | 5       | 1         | 2025-02-10   | pending
2  | 7       | 2         | 2025-02-10   | confirmed
3  | 5       | 1         | 2025-02-11   | pending
```

**Display di Calendar:**
- User 5, tanggal 10 Feb → Shift Pagi (08:00-16:00) warna hijau muda
- User 7, tanggal 10 Feb → Shift Sore (14:00-22:00) warna biru muda
- User 5, tanggal 11 Feb → Shift Pagi (08:00-16:00) warna hijau muda

---

## 🔐 Keamanan

- ✅ Session validation (hanya admin bisa akses)
- ✅ SQL injection protection (prepared statements)
- ✅ CSRF protection (recommended: tambahkan token)
- ✅ Input validation di backend
- ✅ Error handling yang proper

---

## 📝 TODO / Improvement Ideas

1. **Notifikasi Push** - Kirim notif ke user saat shift di-assign
2. **Export Excel** - Export shift calendar ke Excel
3. **Konfirmasi Batch** - Admin bisa approve/decline batch
4. **Shift Swap** - User bisa request tukar shift
5. **Recurring Assignments** - Assign shift berulang (mingguan/bulanan)
6. **Shift Template** - Template shift untuk copy assignment
7. **Conflict Detection** - Warning jika pegawai double shift
8. **Mobile Responsive** - Optimize untuk mobile
9. **Dark Mode** - Tema gelap
10. **Audit Log** - Log semua perubahan shift

---

## 📞 Support

Jika ada masalah atau pertanyaan, hubungi:
- Developer: [Your Name]
- Email: [your-email@example.com]
- GitHub: [repository-link]

---

## 📜 License

Proprietary - Internal Use Only

---

**Last Updated:** 2025-02-07  
**Version:** 2.0 (Complete Refactor)  
**Status:** ✅ Production Ready
