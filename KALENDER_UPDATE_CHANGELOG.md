# 🔄 UPDATE CHANGELOG - Kalender Modular

## Tanggal: 6 November 2025

---
**UPDATE: Shift Type Display Clarification**

Sistem calendar mendukung semua tipe shift yang ada di database:
- **pagi** (07:00-15:00 atau 08:00-15:00)
- **middle** (12:00-20:00 atau 13:00-21:00)
- **sore** (15:00-23:00)

⚠️ **Penting:** Shift hanya ditampilkan jika ada assignment untuk tanggal tersebut. Jika shift 'sore' atau 'middle' tidak muncul, berarti belum ada pegawai yang di-assign untuk shift tersebut pada tanggal yang ditampilkan.

Lihat `SHIFT_DISPLAY_GUIDE.md` untuk penjelasan lengkap cara kerja display shift dan troubleshooting.

---

## ✅ Perubahan yang Telah Dilakukan

### 1. ✅ Week View - Satukan Shift dengan Jam Sama
**File:** `script_kalender_core.js`
- Shift di week view sekarang dikelompokkan berdasarkan `jam_masuk`, `jam_keluar`, dan `nama_shift`
- Menampilkan nama shift, jam kerja, dan daftar pegawai dalam satu card
- Format: **[Nama Shift]** → **⏰ Jam** → **👥 X pegawai: Nama1, Nama2, ...**

### 2. ✅ Assign Shift Modal - Sembunyikan Pegawai yang Sudah Ada
**File:** `script_kalender_assign.js`
- Pegawai yang sudah memiliki shift **TIDAK** ditampilkan di modal Assign Shift
- Hanya pegawai yang belum punya shift yang muncul untuk di-assign
- Jika semua pegawai sudah punya shift, muncul pesan: "✅ Semua pegawai sudah memiliki shift"
- Logic delete dipindahkan sepenuhnya ke Delete Shift Modal

### 3. ✅ Delete Shift Modal - Tampilkan Pegawai dengan Shift
**File:** `script_kalender_delete.js`
- Hanya pegawai yang **sudah memiliki shift** yang muncul di modal Delete
- Pegawai dengan status locked (Approved/Izin/Sakit/Reschedule) ditandai dan tidak bisa dihapus
- Checkbox "Select All" hanya memilih pegawai yang bisa dihapus (tidak locked)
- Konfirmasi sebelum menghapus

### 4. ✅ Ringkasan Shift - Tabel Under-Minimum Notification
**File:** `script_kalender_summary.js`
- Menambahkan tabel notifikasi untuk pegawai dengan **kurang dari 26 hari shift**
- Menampilkan:
  - Nama pegawai
  - Jumlah hari kerja
  - Kekurangan hari
  - Persentase (dengan progress bar berwarna)
- Progress bar color coding:
  - 🔴 Merah: < 50%
  - 🟠 Orange: 50-80%
  - 🟢 Hijau: > 80%
- Tabel otomatis tersembunyi jika semua pegawai sudah memenuhi minimum

### 5. ✅ Day View - Tombol Notify Employee
**File:** `script_kalender_core.js`, `script_kalender_api.js`, `api_notify_shift.php`
- Menambahkan tombol **"📧 Kirim Notifikasi"** di bagian atas day view
- Tombol mengirim email ke semua pegawai yang memiliki shift pada tanggal tersebut
- Email berisi:
  - Detail shift (cabang, tanggal, nama shift, jam kerja)
  - Format HTML yang profesional
  - Auto-generated, tidak perlu balas
- Konfirmasi sebelum mengirim
- Notifikasi hasil pengiriman (berhasil/gagal)

**API File Baru:** `api_notify_shift.php`
- Menggunakan PHPMailer untuk mengirim email
- Konfigurasi SMTP di file ini (perlu disesuaikan dengan setting email Anda)
- Return: jumlah email terkirim dan gagal

### 6. ✅ CSS Navigation - Perbaikan Layout
**File:** `style.css`
- Navigation buttons sekarang **di tengah** dengan flexbox
- Tampilan lebih modern dengan gap dan padding yang konsisten
- Font size dan spacing yang lebih baik
- Hover effect pada tombol navigation
- `#current-nav` dan `#summary-current-nav` dengan min-width untuk konsistensi

### 7. ✅ Debug Logging - Week & Day View
**File:** `script_kalender_core.js`
- Menambahkan console logging untuk debugging:
  - Jumlah shift per hari di week view
  - Nama-nama shift yang ditampilkan
  - Grouped shifts di day view
  - Detail shift assignments

## 🧪 Testing Instructions

### Test 1: Week View - Grouped Shifts
1. Pilih cabang dari dropdown
2. Switch ke Week View
3. Pastikan shift dengan jam sama digabung dalam satu card
4. Lihat console: `📅 Week view - [date]: X shifts`
5. Verifikasi semua shift (pagi, sore, middle) ditampilkan

### Test 2: Assign Shift Modal
1. Buka Day View
2. Klik pada slot waktu untuk assign shift
3. Pegawai yang sudah punya shift **TIDAK MUNCUL**
4. Pilih shift dari dropdown
5. Pilih pegawai (checkbox)
6. Klik "Simpan Shift"
7. Pegawai yang baru di-assign sekarang hilang dari list

### Test 3: Delete Shift Modal
1. Di Day View, klik pada **shift card** (bukan slot kosong)
2. Modal delete terbuka dengan list pegawai yang punya shift
3. Pegawai dengan status locked tidak bisa dipilih
4. Pilih pegawai untuk dihapus
5. Klik "Hapus Shift yang Dipilih"
6. Pegawai yang dihapus kembali muncul di Assign Modal

### Test 4: Under-Minimum Notification
1. Klik "Tampilkan Ringkasan"
2. Scroll ke bawah
3. Lihat tabel "⚠️ Perhatian: Pegawai Belum Memenuhi Minimum Shift"
4. Pegawai dengan < 26 hari shift ditampilkan dengan progress bar
5. Tabel tersembunyi jika semua pegawai sudah memenuhi

### Test 5: Notify Employee
1. Pilih cabang
2. Buka Day View untuk tanggal yang ada shift-nya
3. Klik tombol **"📧 Kirim Notifikasi"** di bagian atas
4. Konfirmasi pengiriman
5. Tunggu proses (tombol jadi "⏳ Mengirim...")
6. Alert muncul dengan hasil (berhasil/gagal)
7. Cek email pegawai (jika SMTP sudah dikonfigurasi)

### Test 6: CSS Navigation
1. Lihat navigation buttons di semua view
2. Pastikan tombol di **tengah**
3. Hover pada tombol (warna berubah)
4. Label tanggal/bulan di tengah dengan jelas

## ⚙️ Konfigurasi yang Perlu Disesuaikan

### Email Configuration (api_notify_shift.php)
Baris yang perlu disesuaikan:
```php
$mail->Host = 'smtp.gmail.com'; // Sesuaikan dengan SMTP server Anda
$mail->Username = 'your-email@gmail.com'; // Email pengirim
$mail->Password = 'your-app-password'; // App password
$mail->setFrom('your-email@gmail.com', 'Shift Management System');
```

**Untuk Gmail:**
1. Enable 2-factor authentication
2. Generate App Password di Google Account settings
3. Gunakan App Password (bukan password biasa)

**Alternatif SMTP:**
- SendGrid
- Mailgun
- AWS SES
- SMTP lokal

## 📊 Browser Console Output

Setelah refresh dan pilih cabang, Anda akan lihat:
```
✅ KalenderUtils module loaded
✅ KalenderAPI module loaded
✅ KalenderSummary module loaded
✅ KalenderAssign module loaded
✅ KalenderDelete module loaded
✅ KalenderCore module loaded
Initializing Kalender Core...
DOM Loaded - Starting Kalender App
🏢 Cabang selected: { cabangId: "123", cabangName: "Adhyaksa" }
📥 Loading shift list and assignments...
✅ Shift list loaded: Array(3)
📋 Shift list count: 3
📝 Shift names: ["pagi", "sore", "middle"]
✅ Shift assignments loaded: Object
📊 Total assignments: 10
🔍 Unique shift types in assignments: ["pagi", "sore", "middle"]
```

## 🐛 Troubleshooting

### Shift tidak muncul di Week/Day View
1. Buka browser console
2. Cek log: `🔍 Unique shift types in assignments`
3. Pastikan ada shift selain "pagi"
4. Verifikasi `shift_date` di assignments match dengan tanggal yang dilihat
5. Cek mapping field `tanggal_shift` → `shift_date` di API module

### Email tidak terkirim
1. Cek error di alert setelah klik "Kirim Notifikasi"
2. Verifikasi SMTP configuration di `api_notify_shift.php`
3. Test SMTP connection secara manual
4. Cek apakah pegawai punya email di database
5. Lihat PHP error log

### Navigation buttons tidak di tengah
1. Hard refresh browser (Ctrl+Shift+R / Cmd+Shift+R)
2. Clear browser cache
3. Verifikasi CSS di `style.css` line ~1040
4. Inspect element dengan browser DevTools

### Under-minimum table tidak muncul
1. Pastikan sudah di Summary View
2. Cek console: `updateSummaryDisplay`
3. Verifikasi ada pegawai dengan < 26 hari kerja
4. Scroll ke bawah di summary view

## 📝 Files Modified

1. `script_kalender_core.js` - Main calendar logic
2. `script_kalender_api.js` - API calls + notify function
3. `script_kalender_assign.js` - Hide employees with shifts
4. `script_kalender_delete.js` - Already correct
5. `script_kalender_summary.js` - Under-minimum table
6. `style.css` - Navigation styling
7. `api_notify_shift.php` - NEW FILE for email sending

## 🎯 Next Steps

- [ ] Test semua fitur setelah refresh
- [ ] Configure SMTP untuk email notifications
- [ ] Verifikasi semua shift (pagi/sore/middle) muncul
- [ ] Test assign → delete → assign cycle
- [ ] Screenshot hasil untuk dokumentasi

---

**Status:** ✅ SEMUA PERUBAHAN SELESAI
**Ready for Testing:** YA
**Requires Configuration:** Email SMTP settings
