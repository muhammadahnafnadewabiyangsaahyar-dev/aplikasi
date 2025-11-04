# 🔧 PERBAIKAN SHIFT CALENDAR - ACTION PLAN

## Masalah yang Ditemukan:

1. ❌ **Data Shift Salah** - Saya mengasumsikan 3 shift per hari (pagi/siang/malam), padahal di database Anda:
   - 1 Cabang = 1 Shift dengan jam tertentu
   - Contoh: Citraland Gowa = Shift Pagi (07:00-15:00)
   - Assignment = Pegawai di-assign ke 1 cabang pada tanggal tertentu

2. ❌ **Calendar Tidak Muncul** - Terlalu kompleks dengan DayPilot dan asumsi data yang salah

3. ✅ **Solusi: Gabungkan Fungsi** - Buat satu halaman dengan 2 mode (Table & Simple Calendar)

---

## Yang Akan Saya Buat:

### File Baru: `shift_calendar.php`

**Fitur:**
1. **Toggle Button** - Switch between Table View & Calendar View
2. **Table View** (Mode 1):
   - Form assign shift (pilih pegawai, cabang, tanggal)
   - Tabel list assignments dengan status
   - Tombol hapus
   
3. **Calendar View** (Mode 2):
   - Simple calendar bulanan (HTML table)
   - Click pada tanggal untuk quick assign
   - Color-coded berdasarkan cabang
   - Hover tooltip untuk detail shift

**Struktur Data yang Benar:**
```
cabang table:
- id, nama_cabang, nama_shift, jam_masuk, jam_keluar

shift_assignments table:
- id, user_id, cabang_id, tanggal_shift, status_konfirmasi

Logic:
- 1 assignment = 1 pegawai di 1 cabang pada 1 tanggal
- Shift info diambil dari cabang (bukan 3 shift hardcoded)
```

---

## File yang Akan Saya Update:

1. ✅ `shift_calendar.php` - Halaman utama (gabungan table & calendar)
2. ✅ `api_shift_calendar.php` - Backend API (sudah benar, tinggal perbaiki sedikit)
3. ✅ `navbar.php` - Update link (hapus "Kelola Shift", ganti jadi "Shift Calendar")

---

## File yang TIDAK Perlu Lagi:

- ❌ `shift_management.php` - DEPRECATED (fungsi digabung ke shift_calendar.php)
- ❌ `api_shift_management.php` - DEPRECATED (pakai api_shift_calendar.php)

---

## Next Steps:

1. Buat `shift_calendar.php` yang baru (simple & benar)
2. Fix `api_shift_calendar.php` agar sesuai dengan data structure
3. Update navbar.php - hapus link "Kelola Shift"
4. Test dan verifikasi

---

**Status: READY TO IMPLEMENT** 🚀

Saya akan membuat file yang benar sekarang...
