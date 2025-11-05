# 📋 Quick Guide: Cara Assign Shift ke User

## Masalah: Kalender User Kosong

Jika user membuka `jadwal_shift.php` dan kalender kosong dengan pesan **"Belum Ada Jadwal Shift"**, berarti user tersebut belum di-assign shift oleh admin.

## ✅ Solusi: Admin Assign Shift

### Cara 1: Via Interface (Recommended)

1. **Login sebagai Admin**
   - Gunakan akun admin
   - Password: (admin password)

2. **Buka Shift Management**
   - Akses: `http://localhost/aplikasi/shift_management.php`
   - Atau klik menu "Shift Management" di navbar

3. **Assign Shift ke Pegawai**
   ```
   Form: Assign Shift
   ├─ Pilih Pegawai: [Pilih nama user]
   ├─ Pilih Cabang & Shift: [Pilih cabang]
   └─ Tanggal Shift: [Pilih tanggal]
   
   Klik: [Assign Shift]
   ```

4. **Bulk Assign (Opsional)**
   - Untuk assign beberapa hari sekaligus
   - Gunakan date range picker
   - Pilih tanggal mulai dan tanggal akhir

5. **Verifikasi**
   - Scroll ke bawah
   - Lihat tabel "Shift Assignments Bulan Ini"
   - Pastikan data shift muncul

### Cara 2: Via Database (Untuk Testing)

Jika ingin cepat untuk testing, gunakan SQL ini:

```sql
-- Jalankan di phpMyAdmin
-- Ganti @user_id dengan ID user yang login
-- Ganti @cabang_id dengan ID cabang yang valid

SET @user_id = 2;      -- ID user (cek di tabel register)
SET @cabang_id = 1;    -- ID cabang (cek di tabel cabang)
SET @admin_id = 1;     -- ID admin yang assign

-- Insert shift untuk 7 hari ke depan
INSERT INTO shift_assignments 
(user_id, cabang_id, tanggal_shift, status_konfirmasi, created_by, created_at)
VALUES
(@user_id, @cabang_id, CURDATE(), 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, DATE_ADD(CURDATE(), INTERVAL 4 DAY), 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'pending', @admin_id, NOW()),
(@user_id, @cabang_id, DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'pending', @admin_id, NOW());

-- Cek hasil
SELECT * FROM shift_assignments WHERE user_id = @user_id;
```

Atau gunakan file SQL yang sudah disediakan:
```bash
# Import via terminal
mysql -u root aplikasi < sample_shift_data.sql

# Atau via phpMyAdmin
# Import > Choose file > sample_shift_data.sql
```

### Cara 3: Cek User ID yang Login

Jika tidak tahu user_id, jalankan query ini:

```sql
-- Lihat semua user (non-admin)
SELECT id, nama_lengkap, email, outlet, posisi 
FROM register 
WHERE role = 'user'
ORDER BY nama_lengkap;
```

## 🎯 Verifikasi Berhasil

Setelah assign shift:

1. **Refresh halaman jadwal_shift.php**
2. **Seharusnya muncul:**
   - ✅ Card statistik berisi angka (bukan 0 semua)
   - ✅ Kalender dengan tanggal yang memiliki shift info
   - ✅ Badge status (Pending/Konfirmasi/Ditolak)
   - ✅ Button aksi (✓ Konfirmasi, ✗ Tolak, 📋 Detail)

3. **Test Konfirmasi:**
   - Klik shift dengan status "Pending"
   - Klik "✓ Konfirmasi"
   - Tambah catatan (opsional)
   - Klik "Konfirmasi"
   - Status berubah jadi "✓ Dikonfirmasi"

## 📊 Expected Result

```
┌─────────────────────────────────────────┐
│  📅 Jadwal Shift Saya                   │
├─────────────────────────────────────────┤
│  Total: 7 | Pending: 5 | Confirmed: 2   │
├─────────────────────────────────────────┤
│  Kalender dengan shift:                 │
│  ┌───┬───┬───┬───┬───┬───┬───┐         │
│  │ S │ S │ S │ R │ K │ J │ S │         │
│  ├───┼───┼───┼───┼───┼───┼───┤         │
│  │   │   │ 5 │ 6 │ 7 │ 8 │ 9 │         │
│  │   │   │🔵│🔵│🔵│🔵│🔵│         │
│  │   │   │ P │ P │ P │ P │ P │         │
│  └───┴───┴───┴───┴───┴───┴───┘         │
└─────────────────────────────────────────┘

Legend:
🔵 = Ada shift (Pending)
🟢 = Shift dikonfirmasi
🔴 = Shift ditolak
P = Pending, C = Confirmed, D = Declined
```

## 🚨 Troubleshooting

### Problem: Shift sudah di-assign tapi tidak muncul

**Check 1: User ID benar?**
```sql
SELECT id, nama_lengkap FROM register WHERE nama_lengkap LIKE '%nama_user%';
```

**Check 2: Tanggal valid?**
```sql
-- Shift hanya muncul untuk bulan ini dan bulan depan
SELECT tanggal_shift FROM shift_assignments WHERE user_id = X;
```

**Check 3: Cabang ID valid?**
```sql
SELECT * FROM cabang WHERE id = X;
```

### Problem: User tidak bisa konfirmasi shift

**Check:** Status harus 'pending'
```sql
UPDATE shift_assignments 
SET status_konfirmasi = 'pending' 
WHERE id = X;
```

---

**File Reference:**
- Admin Interface: `shift_management.php`
- User Interface: `jadwal_shift.php`
- API: `api_shift_management.php`, `api_shift_confirmation.php`
- Sample Data: `sample_shift_data.sql`

**Last Updated:** 2025-11-05
