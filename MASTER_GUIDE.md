# 📚 MASTER GUIDE - APLIKASI ABSENSI

> **Panduan Lengkap Setup, Konfigurasi, dan Penggunaan Sistem Absensi**  
> **Last Updated:** November 4, 2025

---

## 📑 TABLE OF CONTENTS

1. [Quick Start](#quick-start)
2. [System Requirements](#system-requirements)
3. [Installation](#installation)
4. [Database Setup](#database-setup)
5. [Configuration](#configuration)
6. [Features Overview](#features-overview)
7. [Shift Management](#shift-management)
8. [Custom Calendar](#custom-calendar)
9. [Troubleshooting](#troubleshooting)
10. [Backup & Restore](#backup--restore)
11. [API Reference](#api-reference)

---

## 🚀 QUICK START

### Step 1: Clone & Setup
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/
git clone <repository-url> aplikasi
cd aplikasi
```

### Step 2: Database Import
```bash
# Import main database
mysql -u root -p aplikasi < aplikasi.sql

# Or import cleaned version
mysql -u root -p aplikasi < aplikasi_cleaned.sql
```

### Step 3: Configuration
```bash
# Copy config template
cp connect.php.example connect.php

# Edit database credentials
nano connect.php
```

### Step 4: Run Migration (Optional - untuk shift system)
```bash
mysql -u root -p aplikasi < migration_shift_enhancement.sql
```

### Step 5: Access Application
```
http://localhost/aplikasi/
```

**Default Login:**
- **Admin:** `admin@example.com` / `admin123`
- **Superadmin:** `superadmin@example.com` / `super123`

---

## 💻 SYSTEM REQUIREMENTS

### Server Requirements
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Apache:** 2.4 or higher
- **PHP Extensions:**
  - mysqli
  - pdo_mysql
  - gd (for image processing)
  - mbstring
  - json

### Recommended
- **XAMPP:** 8.0+ (includes all requirements)
- **Disk Space:** 500MB minimum
- **RAM:** 2GB minimum

---

## 📦 INSTALLATION

### Option 1: XAMPP (Recommended)
1. Download XAMPP from https://www.apachefriends.org
2. Install XAMPP to `/Applications/XAMPP` (macOS) or `C:\xampp` (Windows)
3. Start Apache and MySQL from XAMPP Control Panel
4. Clone repository to `htdocs/aplikasi`
5. Import database via phpMyAdmin or command line

### Option 2: Manual LAMP/LEMP Stack
1. Install Apache/Nginx, MySQL, PHP
2. Configure virtual host pointing to application directory
3. Set proper permissions: `chmod 755 -R aplikasi/`
4. Import database
5. Configure `connect.php`

---

## 🗄️ DATABASE SETUP

### Import Database
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE aplikasi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema & data
mysql -u root -p aplikasi < aplikasi.sql
```

### Database Structure
```
aplikasi/
├── users                  # User accounts
├── cabang                 # Branch/outlet management
├── absensi                # Attendance records
├── shift_assignments      # Shift scheduling
├── whitelist_pegawai      # Employee whitelist
├── komponen_gaji          # Salary components
├── lembur                 # Overtime records
├── cuti                   # Leave management
└── izin                   # Permission requests
```

### Run Migrations
```bash
# Main shift enhancement migration
mysql -u root -p aplikasi < migration_shift_enhancement.sql

# Keterlambatan (tardiness) system
mysql -u root -p aplikasi < migration_keterlambatan_complete.sql
```

---

## ⚙️ CONFIGURATION

### 1. Database Connection (`connect.php`)
```php
<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "aplikasi";

$pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
```

### 2. Timezone (`connect.php`)
```php
date_default_timezone_set('Asia/Makassar'); // WITA
// or 'Asia/Jakarta' for WIB
```

### 3. Auto Backup (Crontab)
```bash
# Edit crontab
crontab -e

# Add line for hourly backup
0 * * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh
```

### 4. Session Configuration
Already configured in application with:
- Session timeout: 24 hours
- Secure session handling
- CSRF protection

---

## 🎯 FEATURES OVERVIEW

### Core Features
- ✅ **Multi-Role System** - Admin, Superadmin, Karyawan
- ✅ **Multi-Branch** - Manage multiple outlets/branches
- ✅ **Attendance Tracking** - Clock in/out with photo proof
- ✅ **Shift Management** - Flexible shift scheduling
- ✅ **Leave Management** - Cuti, Izin, Sakit
- ✅ **Overtime Tracking** - Lembur calculation
- ✅ **Salary Components** - Configurable salary structure
- ✅ **Reports & Analytics** - Comprehensive reporting
- ✅ **Mobile Responsive** - Works on all devices

### Advanced Features
- ✅ **Custom Calendar** - Visual shift planning
- ✅ **Auto Tardiness Calculation** - Keterlambatan tracking
- ✅ **Photo Verification** - Selfie for clock in/out
- ✅ **Whitelist System** - Employee authorization
- ✅ **Backup Automation** - Scheduled backups
- ✅ **API Integration** - RESTful APIs

---

## 🕐 SHIFT MANAGEMENT

### Shift Configuration per Branch
Each branch can have custom shift times:

```sql
-- Example: Jakarta Pusat
shift_pagi: 08:00 - 16:00
shift_siang: 16:00 - 00:00
shift_malam: 00:00 - 08:00
```

### Assign Shifts
**Via Calendar UI:**
1. Access: `http://localhost/aplikasi/shift_calendar.php`
2. Select branch (cabang)
3. Select month/year
4. Click on employee row + date cell
5. Choose shift type
6. Save

**Via Database:**
```sql
INSERT INTO shift_assignments (user_id, tanggal, shift_masuk, shift_keluar)
VALUES (1, '2025-11-05', '08:00:00', '16:00:00');
```

### Shift Confirmation
Employees can view their shifts:
```
http://localhost/aplikasi/shift_confirmation.php
```

---

## 📅 CUSTOM CALENDAR

### Overview
Custom calendar system with **30+ features** for shift management.

### Access
```
http://localhost/aplikasi/KALENDER/kalender.html
```

### Features
- **Multi-View:** Month, Week, Day, Year
- **Dual Mode:** LocalStorage (demo) & Database (production)
- **Employee Management:** Add, search, filter employees
- **Shift Assignment:** Click to assign, drag & drop
- **Export:** CSV export for schedules
- **Backup/Restore:** JSON backup system
- **Notifications:** Upcoming shifts, alerts
- **Summary:** Statistics per employee/shift

### Usage Modes

**Mode 1: LocalStorage (Demo)**
- No database required
- Data saved in browser
- Perfect for testing/planning
- All 30+ features available

**Mode 2: Database (Production)**
- Select branch from dropdown
- Auto-load employees from database
- Save shifts to `shift_assignments` table
- Real-time sync with main application

### Documentation
See: `KALENDER/HYBRID_CALENDAR_COMPLETE.md`

---

## 🐛 TROUBLESHOOTING

### Issue: Database Connection Failed
**Solution:**
```bash
# Check MySQL is running
ps aux | grep mysql

# Start MySQL
/Applications/XAMPP/xamppfiles/bin/mysql.server start

# Test connection
mysql -u root -p -e "SHOW DATABASES;"
```

### Issue: Shift Calendar Not Loading
**Solution:**
1. Check session: Login as admin/superadmin
2. Check cabang data: Verify branch exists
3. Check API: `curl http://localhost/aplikasi/api_shift_calendar.php?action=get_cabang`
4. Check console: Open browser DevTools for errors

### Issue: Photos Not Uploading
**Solution:**
```bash
# Check upload directory permissions
chmod 777 /Applications/XAMPP/xamppfiles/htdocs/aplikasi/uploads/

# Check PHP upload limits in php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Issue: Timezone Incorrect
**Solution:**
```php
// In connect.php
date_default_timezone_set('Asia/Makassar'); // WITA
// or
date_default_timezone_set('Asia/Jakarta'); // WIB
```

### Issue: Session Timeout
**Solution:**
```php
// In session config
ini_set('session.gc_maxlifetime', 86400); // 24 hours
session_set_cookie_params(86400);
```

---

## 💾 BACKUP & RESTORE

### Manual Backup
```bash
# Full database backup
mysqldump -u root -p aplikasi > backup_$(date +%Y%m%d_%H%M%S).sql

# Compressed backup
mysqldump -u root -p aplikasi | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Automated Backup
```bash
# Script: backup_auto.sh
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi/backups"
MYSQL="/Applications/XAMPP/xamppfiles/bin/mysqldump"

$MYSQL -u root aplikasi > $BACKUP_DIR/aplikasi_auto_$TIMESTAMP.sql

# Keep only last 5 backups
ls -t $BACKUP_DIR/aplikasi_auto_*.sql | tail -n +6 | xargs rm -f
```

### Restore Backup
```bash
# Restore from backup
mysql -u root -p aplikasi < backup_20251104_220000.sql

# Restore compressed backup
gunzip < backup_20251104_220000.sql.gz | mysql -u root -p aplikasi
```

### Backup Schedule (Crontab)
```bash
# Hourly backup
0 * * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh

# Daily backup at midnight
0 0 * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh

# Weekly backup on Sunday 2 AM
0 2 * * 0 /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh
```

---

## 📡 API REFERENCE

### Shift Calendar API

**Base URL:** `http://localhost/aplikasi/api_shift_calendar.php`

#### Get Cabang
```http
GET /api_shift_calendar.php?action=get_cabang
```
**Response:**
```json
{
  "cabang": [
    {"id": 1, "nama": "Jakarta Pusat"},
    {"id": 2, "nama": "Jakarta Selatan"}
  ]
}
```

#### Get Users by Cabang
```http
GET /api_shift_calendar.php?action=get_users&cabang_id=1
```

#### Get Shifts
```http
GET /api_shift_calendar.php?action=get_shifts&cabang_id=1&month=11&year=2025
```

#### Save Shift
```http
POST /api_shift_calendar.php?action=save_shift
Content-Type: application/json

{
  "user_id": 1,
  "cabang_id": 1,
  "date": "2025-11-05",
  "shift_masuk": "08:00:00",
  "shift_keluar": "16:00:00"
}
```

#### Delete Shift
```http
POST /api_shift_calendar.php?action=delete_shift
Content-Type: application/json

{
  "user_id": 1,
  "date": "2025-11-05"
}
```

---

## 📚 ADDITIONAL DOCUMENTATION

### In-Depth Guides
- **Shift System:** `database_schema_shift_system.sql` (schema reference)
- **Dummy Data:** `DUMMY_USERS_INFO.md` (test data guide)
- **Kalender:** `KALENDER/HYBRID_CALENDAR_COMPLETE.md`
- **Migration:** `migration_shift_enhancement.sql` (with comments)

### Quick References
- **Backup Guide:** `PANDUAN_BACKUP_OTOMATIS.md`
- **Client Guide:** `PANDUAN_KLIEN.md`
- **Registration Check:** `CARA_CEK_DATA_REGISTRASI.md`

### Test Files
- **Calendar Test:** `KALENDER/test_integration.html`
- **Feature Verify:** `KALENDER/verify_features.html`

---

## 🎓 TRAINING & SUPPORT

### For Administrators
1. Read this guide thoroughly
2. Test with dummy data first
3. Configure shift times per branch
4. Set up auto backups
5. Train employees on system usage

### For Employees
1. Access application via provided URL
2. Login with provided credentials
3. Check shift schedule regularly
4. Clock in/out with photo verification
5. Submit leave/overtime requests via system

---

## 🔐 SECURITY NOTES

- ✅ Change default passwords immediately
- ✅ Use HTTPS in production
- ✅ Regular database backups
- ✅ Keep PHP/MySQL updated
- ✅ Monitor access logs
- ✅ Implement firewall rules
- ✅ Use strong passwords
- ✅ Enable CSRF protection (already implemented)

---

## 📞 SUPPORT

For issues or questions:
1. Check [Troubleshooting](#troubleshooting) section
2. Review error logs: `error_log`, `backups/cron.log`
3. Test with dummy data: `install_dummy_data.sh`
4. Contact system administrator

---

## 📝 CHANGELOG

### Version 2.0 (November 2025)
- ✅ Added hybrid calendar system (30+ features)
- ✅ Enhanced shift management
- ✅ Improved API endpoints
- ✅ Auto backup system
- ✅ Complete documentation

### Version 1.5 (October 2025)
- ✅ Shift system enhancement
- ✅ Keterlambatan calculation
- ✅ Multi-cabang support
- ✅ Database optimization

---

**📌 TIP:** Bookmark this guide for quick reference!

**🎉 READY TO USE!** Follow the Quick Start guide to get started immediately.
