# 👥 Test Users - KAORI System

## User List

### 1. Kata Hnaf
- **Username:** `katahnaf`
- **Email:** `katahnaf@gmail.com`
- **Password:** `Test123!`
- **No. WhatsApp:** 081234567890
- **Posisi:** Staff
- **Outlet:** Cabang A
- **Gaji Pokok:** Rp 5.000.000
- **Tunjangan Makan:** Rp 500.000
- **Tunjangan Transport:** Rp 500.000

---

### 2. Pilar Aforisma
- **Username:** `pilaraforisma`
- **Email:** `pilaraforismacinta@gmail.com`
- **Password:** `Test123!`
- **No. WhatsApp:** 081234567891
- **Posisi:** Staff
- **Outlet:** Cabang A
- **Gaji Pokok:** Rp 5.000.000
- **Tunjangan Makan:** Rp 500.000
- **Tunjangan Transport:** Rp 500.000

---

### 3. Galih Ganji
- **Username:** `galihganji`
- **Email:** `galihganji@gmail.com`
- **Password:** `Test123!`
- **No. WhatsApp:** 081234567892
- **Posisi:** Staff
- **Outlet:** Cabang B
- **Gaji Pokok:** Rp 5.000.000
- **Tunjangan Makan:** Rp 500.000
- **Tunjangan Transport:** Rp 500.000

---

### 4. Dot Pikir
- **Username:** `dotpikir`
- **Email:** `dotpikir@gmail.com`
- **Password:** `Test123!`
- **No. WhatsApp:** 081234567893
- **Posisi:** Staff
- **Outlet:** Cabang B
- **Gaji Pokok:** Rp 5.000.000
- **Tunjangan Makan:** Rp 500.000
- **Tunjangan Transport:** Rp 500.000

---

## Quick Reference

### Login Information
**Username atau Email + Password**

| User | Username | Email | Password |
|------|----------|-------|----------|
| Kata Hnaf | `katahnaf` | katahnaf@gmail.com | `Test123!` |
| Pilar Aforisma | `pilaraforisma` | pilaraforismacinta@gmail.com | `Test123!` |
| Galih Ganji | `galihganji` | galihganji@gmail.com | `Test123!` |
| Dot Pikir | `dotpikir` | dotpikir@gmail.com | `Test123!` |

---

## How to Create Users

### Method 1: Via Browser (Recommended)
```
http://localhost/aplikasi/create_test_users.php
```

### Method 2: Via PHP CLI
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
php create_test_users.php > users_created.html
```

### Method 3: Manual SQL
```sql
INSERT INTO register (
    username, password, nama_lengkap, email, no_whatsapp,
    role, posisi, outlet, gaji_pokok, tunjangan_transport, 
    tunjangan_makan, tunjangan_jabatan
) VALUES 
(
    'katahnaf', 
    '$2y$10$...', -- password hash for 'Test123!'
    'Kata Hnaf', 
    'katahnaf@gmail.com', 
    '081234567890',
    'user', 
    'Staff', 
    'Cabang A', 
    5000000, 
    500000, 
    500000, 
    0
);
-- Repeat for other users...
```

---

## Features per User

### ✅ Sudah Dikonfigurasi:
- ✓ Account registered di table `register`
- ✓ Komponen gaji sudah dibuat di table `komponen_gaji`
- ✓ Password sudah di-hash dengan bcrypt
- ✓ Role = `user` (bukan admin)
- ✓ Overwork rate = Rp 50.000/hari

### ⏳ Perlu Dikonfigurasi:
- ⏳ Assign shift (via jadwal_shift.php)
- ⏳ Absensi masuk/keluar (setelah shift confirmed)
- ⏳ Pengajuan izin/sakit (via form pengajuan)

---

## Testing Flow

### 1. Login Test
```
URL: http://localhost/aplikasi/index.php
Username: katahnaf
Password: Test123!
```

### 2. User Dashboard
- Lihat profil user
- Lihat jadwal shift (jika sudah assigned)
- Lihat histori absensi
- Ajukan izin/sakit

### 3. Shift Assignment (Admin)
- Login sebagai admin
- Buka jadwal_shift.php
- Assign shift untuk user test
- User bisa confirm shift

### 4. Attendance Flow
- User login
- Confirm shift yang sudah assigned
- Absen masuk (clock in)
- Absen keluar (clock out)
- Sistem track keterlambatan otomatis

### 5. Leave Request Flow
- User ajukan izin/sakit via form
- Upload surat keterangan
- Admin approve/reject via approve.php
- Status terupdate otomatis

### 6. Salary Slip Generation
- Run auto_generate_slipgaji.php setiap bulan
- Sistem kalkulasi gaji otomatis berdasarkan:
  - Absensi (hadir/tidak hadir)
  - Keterlambatan (< 20 menit / > 20 menit)
  - Overwork (kerja di luar shift)
  - Izin/sakit (approved)
- Slip gaji ter-generate di table riwayat_gaji
- Email otomatis dikirim ke user

---

## Common Issues

### Issue: User tidak bisa login
**Solution:**
1. Cek username/email sudah benar
2. Pastikan password = `Test123!` (case sensitive)
3. Cek table register apakah user ada
4. Cek role = 'user' bukan yang lain

### Issue: User tidak punya komponen_gaji
**Solution:**
Run query manual:
```sql
INSERT INTO komponen_gaji (
    register_id, jabatan, gaji_pokok, tunjangan_makan, 
    tunjangan_transport, tunjangan_jabatan, overwork
) VALUES (
    [USER_ID], 'Staff', 5000000, 500000, 500000, 0, 50000
);
```

### Issue: User sudah ada (duplicate)
**Solution:**
1. Delete existing user first:
```sql
DELETE FROM register WHERE email = 'katahnaf@gmail.com';
```
2. Or update existing user credentials

### Issue: Slip gaji tidak ter-generate
**Solution:**
1. Pastikan user punya komponen_gaji
2. Pastikan user punya absensi/shift di periode tersebut
3. Run auto_generate_slipgaji.php secara manual
4. Check error log

---

## Cleanup (Delete Test Users)

### Via SQL
```sql
DELETE FROM register WHERE email IN (
    'katahnaf@gmail.com',
    'pilaraforismacinta@gmail.com',
    'galihganji@gmail.com',
    'dotpikir@gmail.com'
);
```

### Via PHP Script
Create `delete_test_users.php`:
```php
<?php
require_once 'connect.php';

$emails = [
    'katahnaf@gmail.com',
    'pilaraforismacinta@gmail.com',
    'galihganji@gmail.com',
    'dotpikir@gmail.com'
];

$stmt = $pdo->prepare("DELETE FROM register WHERE email = ?");
foreach ($emails as $email) {
    $stmt->execute([$email]);
    echo "Deleted: {$email}<br>";
}
?>
```

---

## Production Notes

### ⚠️ For Testing Only
These users are created for testing purposes with:
- Simple password (Test123!)
- Real email addresses (for email testing)
- Default salary components
- No photo/profile picture

### 🔒 For Production
Consider:
- Strong password policy
- Email verification
- Profile photo upload
- Custom salary per user
- Two-factor authentication
- Password reset functionality

---

## Contact Information

If you need to test email functionality:
- All 4 emails are real Gmail addresses
- Can receive salary slip emails
- Can receive notification emails
- Can test password reset

---

**Created:** November 6, 2025  
**Script:** create_test_users.php  
**Password:** Test123! (for all users)  
**Status:** ✅ Ready for Testing
