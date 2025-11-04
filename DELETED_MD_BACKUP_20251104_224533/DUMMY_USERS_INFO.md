# 🎭 Dummy Data untuk Testing Sistem

## 📋 Ringkasan Dummy Data

File ini berisi informasi tentang dummy data yang sudah dibuat untuk testing sistem.

### ✅ Data yang Tersedia:

1. **10 Dummy Users** (5 admin, 5 pegawai)
2. **3 Cabang** dengan shift times berbeda
3. **20 Shift Assignments** untuk bulan ini
4. **30 Data Absensi** (masuk dan keluar)
5. **Sample Whitelist Data**

---

## 👥 Dummy Users

### Admin Accounts:
| Username | Password | Nama | Email |
|----------|----------|------|-------|
| admin1 | admin123 | Admin Jakarta | galihganji@gmail.com |
| admin2 | admin123 | Admin Bandung | pilaraforismacinta@gmail.com |
| admin3 | admin123 | Admin Surabaya | dotpikir@gmail.com |
| hradmin | admin123 | HR Manager | katahnaf@gmail.com |
| superadmin | admin123 | Super Admin | galihganji@gmail.com |

### User/Pegawai Accounts:
| Username | Password | Nama | Cabang | Posisi |
|----------|----------|------|--------|--------|
| pegawai1 | user123 | Budi Santoso | Citraland Gowa | Kasir |
| pegawai2 | user123 | Siti Nurhaliza | Citraland Gowa | SPG |
| pegawai3 | user123 | Ahmad Rifai | Adhyaksa | Kasir |
| pegawai4 | user123 | Dewi Lestari | Adhyaksa | SPG |
| pegawai5 | user123 | Eko Prasetyo | BTP | Kasir |
| pegawai6 | user123 | Fitri Handayani | BTP | SPG |
| pegawai7 | user123 | Gunawan Wijaya | Citraland Gowa | Security |
| pegawai8 | user123 | Hendra Kurniawan | Adhyaksa | Cleaning Service |

---

## 🏢 Cabang Data

| ID | Nama Cabang | Shift | Jam Masuk | Jam Keluar |
|----|-------------|-------|-----------|------------|
| 1 | Citraland Gowa | Shift Pagi | 07:00 | 15:00 |
| 2 | Adhyaksa | Shift Pagi | 07:00 | 15:00 |
| 3 | BTP | Shift Pagi | 08:00 | 15:00 |

---

## 📅 Shift Assignments (Bulan Ini)

**Total: 20 assignments** untuk testing calendar dan table view

Contoh:
- Budi Santoso → Citraland Gowa, tanggal 5-10 November 2025
- Siti Nurhaliza → Citraland Gowa, tanggal 5-10 November 2025
- Ahmad Rifai → Adhyaksa, tanggal 5-10 November 2025
- Dewi Lestari → Adhyaksa, tanggal 5-10 November 2025
- Eko Prasetyo → BTP, tanggal 5-10 November 2025
- Fitri Handayani → BTP, tanggal 5-10 November 2025

---

## 🕒 Absensi Data (Sample)

**Total: 30 records** dengan berbagai status:
- ✅ Tepat waktu
- ⏰ Terlambat (5-30 menit)
- 🏠 Lupa absen pulang
- 🌙 Lembur

---

## 💰 Komponen Gaji (Sample)

Setiap pegawai punya komponen gaji:
- Gaji Pokok: Rp 3.000.000 - Rp 5.000.000
- Tunjangan Transportasi: Rp 500.000
- Tunjangan Makan: Rp 400.000
- BPJS: Rp 200.000

---

## 🔐 Login untuk Testing

### Quick Test Admin:
```
Username: superadmin
Password: admin123
```

### Quick Test User:
```
Username: pegawai1
Password: user123
```

---

## 📦 File SQL

Dummy data tersimpan di:
- `dummy_data_complete.sql` - Semua data dummy

---

## 🧪 Cara Install Dummy Data

### Via Terminal:
```bash
# Backup dulu database Anda!
mysql -u root aplikasi < dummy_data_complete.sql
```

### Via phpMyAdmin:
1. Buka http://localhost/phpmyadmin
2. Pilih database `aplikasi`
3. Tab "Import"
4. Pilih file `dummy_data_complete.sql`
5. Click "Go"

---

## ⚠️ PENTING - Backup Dulu!

**SEBELUM import dummy data:**
```bash
# Backup database Anda
mysqldump -u root aplikasi > backup_before_dummy_$(date +%Y%m%d).sql
```

---

## 🔄 Reset ke Kondisi Awal

Jika ingin hapus dummy data:
```sql
-- Hapus dummy users (ID > 100)
DELETE FROM register WHERE id > 100;

-- Hapus dummy shift assignments
DELETE FROM shift_assignments WHERE created_at >= '2025-11-04';

-- Hapus dummy absensi
DELETE FROM absen WHERE tanggal_absen >= '2025-11-01';
```

---

## ✅ Testing Checklist

Setelah install dummy data, test:

- [ ] Login sebagai `superadmin` - berhasil
- [ ] Login sebagai `pegawai1` - berhasil
- [ ] Buka Shift Calendar - muncul 3 cabang
- [ ] Filter cabang - pegawai muncul sesuai cabang
- [ ] Lihat shift assignments - ada data
- [ ] Buka Dashboard User - ada data absensi
- [ ] Cek Rekap Absensi - kalkulasi benar

---

## 📊 Data Statistics

**Setelah import, Anda akan punya:**
- 📋 Total Users: 13 (5 admin + 8 pegawai)
- 🏢 Total Cabang: 3
- 📅 Total Shift Assignments: 20+
- 🕒 Total Absensi: 30+
- 💰 Total Whitelist: 8

---

**Created:** November 4, 2025  
**Version:** 1.0  
**Status:** ✅ Ready to Use
