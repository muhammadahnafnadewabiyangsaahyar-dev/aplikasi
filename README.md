# Teman KAORI - Aplikasi Absensi Karyawan

Aplikasi absensi karyawan berbasis web (PHP, CSS, JS, PDO) untuk perusahaan/UMKM. Mendukung absensi dengan kamera, lokasi, slip gaji, surat izin, lembur, dan manajemen pengguna.

## Fitur Utama
- **Absensi Online**: Absen masuk/keluar dengan kamera & deteksi lokasi.
- **Slip Gaji**: Download slip gaji otomatis.
- **Surat Izin**: Pengajuan dan approval surat izin digital.
- **Lembur/Overwork**: Pengajuan dan approval lembur (khusus admin HR/Finance/Owner).
- **Manajemen Pengguna**: Admin dapat melihat, menambah, dan menghapus user.
- **Tanda Tangan Digital**: Untuk surat izin dan dokumen.
- **Dashboard Admin & User**: Navigasi responsif, tampilan modern.

## Teknologi
- PHP 7/8 (PDO, tanpa framework)
- MySQL/MariaDB
- HTML5, CSS3 (responsif)
- JavaScript (AJAX, SignaturePad)

## Struktur Folder
- `absen.php`, `proses_absensi.php` — Fitur absensi
- `slipgaji.php`, `generate_slip.php` — Slip gaji
- `suratizin.php`, `approve.php` — Surat izin
- `approve_lembur.php` — Approval lembur
- `profile.php` — Profil user/admin
- `view_user.php`, `view_absensi.php` — Manajemen data
- `uploads/` — Foto absensi, tanda tangan, dll
- `tbs/` — Library TBS untuk dokumen

## Instalasi
1. Clone repo ke folder XAMPP/Laragon/hosting Anda.
2. Import `aplikasi.sql` ke database MySQL/MariaDB.
3. Edit `connect.php` jika perlu (user/password DB).
4. Jalankan di browser: `http://localhost/aplikasi/`

## Akun Default
- Admin: `admin` / `admin123` (ubah setelah deploy)
- User: Daftar sendiri atau ditambah admin

## Lisensi
MIT. Silakan gunakan, modifikasi, dan kembangkan.

---

**Teman KAORI** — Absensi Modern, Mudah, dan Aman.
