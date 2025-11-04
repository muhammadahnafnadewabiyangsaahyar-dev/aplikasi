# 🏢 Teman KAORI - Aplikasi Manajemen Kepegawaian & Shift

Sistem manajemen kepegawaian berbasis web dengan fitur absensi, shift management, pengajuan izin, dan penggajian otomatis.

## 📋 Fitur Utama

### 🔐 Authentication & Authorization
- Login dengan role-based access (Admin, User, Super Admin)
- Whitelist pegawai untuk kontrol registrasi
- Profile management dengan foto profil dan tanda tangan digital

### 📍 Absensi & Lokasi
- Absensi masuk/keluar dengan GPS tracking
- Validasi lokasi berdasarkan radius cabang
- Upload foto selfie saat absensi
- Deteksi keterlambatan otomatis
- Tracking durasi kerja dan overwork

### 📅 Shift Management (NEW! v2.0)
- Penjadwalan shift dinamis per cabang
- 3 tipe shift: Pagi, Middle, Sore
- Assign shift ke pegawai untuk tanggal tertentu
- Workflow konfirmasi shift oleh pegawai
- Notifikasi untuk assignment baru
- View kalendar shift harian/mingguan

### 🏖️ Pengajuan Izin & Cuti
- Pengajuan izin dengan generate surat otomatis (DOCX)
- Tanda tangan digital
- Status tracking (Pending, Diterima, Ditolak)
- Perhitungan hari izin untuk payroll

### ⏰ Lembur (Overwork)
- Deteksi overwork otomatis berdasarkan shift
- Workflow approval lembur
- Perhitungan upah lembur per jam
- Integrasi dengan payroll

### 💰 Penggajian
- Komponen gaji lengkap (Gaji Pokok, Tunjangan, Bonus)
- Potongan otomatis (Keterlambatan, Alfa, Kasbon)
- Generate slip gaji batch setiap tanggal 28
- Export slip gaji ke DOCX
- Histori slip gaji

## 🗄️ Database Schema

### Tabel Utama
- **register** - Data pegawai dan akun
- **cabang** - Data cabang/outlet dan shift
- **absensi** - Record absensi harian
- **shift_assignments** - Penjadwalan shift pegawai (NEW!)
- **pengajuan_izin** - Data pengajuan izin/cuti
- **komponen_gaji_detail** - Detail komponen gaji per pegawai per bulan (NEW!)
- **libur_nasional** - Daftar hari libur nasional (NEW!)
- **slip_gaji_history** - Histori pembuatan batch slip gaji (NEW!)

## 🚀 Installation

### Prerequisites
- PHP 7.4+
- MySQL/MariaDB 10.4+
- Apache (XAMPP recommended)
- Composer

### Setup Steps

1. **Clone/Copy Project**
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/
   ```

2. **Install Dependencies**
   ```bash
   cd aplikasi
   composer install
   ```

3. **Setup Database**
   ```bash
   mysql -u root -p
   CREATE DATABASE aplikasi;
   exit;
   
   # Import schema
   mysql -u root -p aplikasi < aplikasi.sql
   ```

4. **Run Migration (for Shift Management)**
   ```bash
   # IMPORTANT: Backup first!
   mysqldump -u root -p aplikasi > backup_$(date +%Y%m%d).sql
   
   # Run migration
   mysql -u root -p aplikasi < migration_shift_enhancement.sql
   ```

5. **Configure Connection**
   Edit `connect.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $password = "your_password";
   $database = "aplikasi";
   ```

6. **Access Application**
   Open: `http://localhost/aplikasi/`

## 📖 Documentation

- **[IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)** - Complete implementation guide
- **[shift_management_quick_reference.html](shift_management_quick_reference.html)** - Visual reference
- **[migration_shift_enhancement.sql](migration_shift_enhancement.sql)** - Migration script
- **[MIGRATION_ANALYSIS.md](MIGRATION_ANALYSIS.md)** - Schema comparison

## 🔐 Default Credentials

**Super Admin:**
- Username: `superadmin`
- Password: `superadmin` *(change after first login!)*

## 🔧 Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Document Generation:** TBS + OpenTBS
- **Libraries:** Composer, PHPWord

## 📊 Version History

### Version 2.0.0 (2025-01-XX) - Shift Management Enhancement
- ✨ Dynamic shift scheduling system
- ✨ Shift confirmation workflow
- ✨ Auto overwork detection
- ✨ Enhanced payroll with detailed components
- ✨ National holiday tracking

### Version 1.0.0 (2025-10-XX) - Initial Release
- ✨ Basic attendance system
- ✨ Leave request management
- ✨ Basic payroll generation

## 🐛 Troubleshooting

### Database Connection Error
- Check `connect.php` credentials
- Ensure MySQL service is running

### Migration Errors
- Always backup before migration!
- Check foreign key constraints
- Review error logs

## 📄 License

Proprietary - Internal Company Use Only

---

**Teman KAORI** — Absensi Modern, Mudah, dan Aman.  
**Last Updated:** 2025-01-XX | **Maintained by:** Development Team
