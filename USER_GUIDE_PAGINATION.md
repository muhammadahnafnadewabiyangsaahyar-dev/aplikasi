# 📖 USER GUIDE - Pagination & Fixed Table Features

## 🎯 PANDUAN PENGGUNAAN FITUR PAGINATION

### **Fitur Utama:**
1. ✅ Tabel dengan ukuran tetap dan scrollable
2. ✅ Header yang tetap terlihat saat scroll
3. ✅ Pagination otomatis untuk data besar
4. ✅ Tombol navigasi yang muncul sesuai kebutuhan

---

## 📋 TABEL 1: RIWAYAT ABSENSI BULANAN

### **Kapan Pagination Muncul?**
- Tombol navigasi **HANYA** muncul jika data **> 10 record**
- Jika data ≤ 10: Semua data ditampilkan tanpa pagination

### **Cara Navigasi:**
1. **Tombol "Sebelumnya"** (◀ Sebelumnya)
   - Muncul jika Anda berada di halaman > 1
   - Klik untuk kembali ke halaman sebelumnya

2. **Info Halaman** (tengah)
   - Menampilkan: "Halaman X dari Y (Z data)"
   - Contoh: "Halaman 2 dari 5 (48 data)"

3. **Tombol "Selanjutnya"** (Selanjutnya ▶)
   - Muncul jika masih ada halaman berikutnya
   - Klik untuk ke halaman selanjutnya

### **Filter + Pagination:**
✅ **Filter Bulan/Tahun:** Pagination akan reset ke halaman 1  
✅ **Filter Nama:** Pagination tetap mempertahankan posisi halaman  
✅ **Filter Tanggal:** Bekerja di client-side (JavaScript) - tidak reset pagination

### **Contoh Penggunaan:**
```
Scenario: Anda memiliki 48 data absensi di Desember 2024

1. Pilih Bulan: Desember, Tahun: 2024, klik "Filter"
   → Sistem menampilkan halaman 1 (10 data pertama)
   
2. Klik "Selanjutnya"
   → Sistem menampilkan halaman 2 (10 data berikutnya)
   
3. Filter Nama: "John Doe"
   → Sistem tetap di halaman 2, tapi hanya tampilkan data John Doe
   
4. Klik "Sebelumnya"
   → Sistem kembali ke halaman 1, filter John Doe tetap aktif
```

---

## 📊 TABEL 2: REKAP ABSENSI HARIAN

### **Kapan Pagination Muncul?**
- Tombol navigasi **HANYA** muncul jika pegawai **> 15 orang**
- Jika pegawai ≤ 15: Semua data ditampilkan tanpa pagination

### **Dashboard Statistics:**
Sebelum tabel, Anda akan melihat 4 kartu statistik:

| Kartu | Arti | Warna |
|-------|------|-------|
| **Total Pegawai** | Jumlah semua pegawai | Biru |
| **Sudah Absen Masuk** | Yang sudah clock-in | Hijau |
| **Sudah Absen Keluar** | Yang sudah clock-out | Orange |
| **Belum Absen** | Yang belum absen sama sekali | Merah |

### **Filter Status:**
Anda dapat memfilter berdasarkan:

| Filter | Tampilkan |
|--------|-----------|
| **Semua** | Semua pegawai |
| **Sudah Absen** | Hanya yang sudah clock-in |
| **Belum Absen** | Hanya yang belum absen |
| **Sudah Keluar** | Hanya yang sudah clock-out |
| **Belum Keluar** | Sudah masuk tapi belum keluar |

### **Contoh Penggunaan:**
```
Scenario: 50 pegawai di perusahaan Anda

1. Buka halaman → Tampil halaman 1 (15 pegawai pertama)
2. Dashboard menunjukkan:
   - Total Pegawai: 50
   - Sudah Absen Masuk: 35
   - Sudah Absen Keluar: 20
   - Belum Absen: 15

3. Filter Status: "Belum Absen"
   → Hanya tampilkan 15 orang yang belum absen (dalam 1 halaman)

4. Ubah filter ke "Sudah Keluar"
   → Tampilkan 20 orang yang sudah keluar (2 halaman: 15+5)
```

---

## 🎨 VISUAL GUIDE - STATUS COLOR CODING

### **Tabel 1: Riwayat Absensi Bulanan**

#### Status Keterlambatan:
- ✅ **Hijau (✓):** Tepat Waktu
- 🟠 **Orange (⚠):** Terlambat 1-19 menit
- 🔶 **Orange Tua (⚠):** Terlambat 20-39 menit
- 🔴 **Merah (✗):** Terlambat ≥40 menit
- 🟣 **Ungu (⚠):** Di Luar Shift (absen terlalu awal/terlambat)

#### Potongan Tunjangan:
- ✅ **Hijau (-):** Tidak ada potongan
- 🟠 **Orange (🍽️):** Potongan Tunjangan Makan
- 🔴 **Merah (🍽️🚗):** Potongan Makan + Transport

#### Status Kehadiran:
- ✅ **Hijau (✓):** Hadir
- 🔴 **Merah (❌):** Tidak Hadir
- 🟠 **Orange (⚠):** Belum Absen Keluar
- 🔴 **Merah Muda (⏰):** Lupa Absen Pulang

### **Tabel 2: Rekap Harian**

#### Status Absen:
- ✅ **Hijau:** Sudah Absen Masuk & Keluar
- 🟠 **Orange:** Sudah Masuk, Belum Keluar
- 🔴 **Merah:** Belum Absen Masuk

#### Status Overwork:
- 🟠 **Orange (⏳):** Pending approval
- ✅ **Hijau (✓):** Approved
- 🔴 **Merah (✗):** Rejected

---

## 🔧 FITUR FIXED TABLE

### **Sticky Header:**
- Saat Anda scroll ke bawah, header tabel **tetap terlihat**
- Memudahkan Anda mengingat nama kolom tanpa scroll ke atas

### **Fixed Height:**
- Tabel dibatasi tinggi maksimal **600px**
- Jika data lebih banyak, akan muncul scrollbar vertikal
- Tabel tidak akan "membanjiri" halaman

### **Hover Effect:**
- Saat cursor di atas baris tabel, baris akan **highlight**
- Memudahkan membaca data per baris

---

## 💡 TIPS & TRICKS

### **1. Mengekspor Data**
```
Tabel 1:
- "Download CSV" → Export semua data bulan/tahun yang dipilih
- "Download CSV Nama Ini" → Export data spesifik 1 pegawai
  (tombol muncul setelah filter nama)
```

### **2. Kombinasi Filter**
```
✅ Filter Bulan/Tahun + Filter Nama → Data spesifik pegawai di periode tertentu
✅ Filter Nama + Filter Tanggal → Absensi pegawai di tanggal spesifik
✅ Filter Status + Filter Nama → Pegawai dengan status tertentu
```

### **3. Navigasi Cepat**
```
- Gunakan filter dropdown untuk jump ke data tertentu
- Gunakan pagination untuk browse data secara terstruktur
- Gunakan export CSV jika perlu analisis di Excel
```

### **4. Monitoring Real-time (Tabel 2)**
```
- Refresh halaman untuk update status terkini
- Dashboard statistics memberikan overview sekilas
- Filter "Belum Absen" untuk follow-up pegawai
```

---

## 🚨 TROUBLESHOOTING

### **Problem 1: Tombol navigasi tidak muncul**
**Kemungkinan:**
- Data ≤ 10 (Tabel 1) atau ≤ 15 (Tabel 2)
- Ini normal! Tombol hanya muncul jika data melebihi batas

**Solusi:**
- Tidak perlu action, semua data sudah tampil di 1 halaman

---

### **Problem 2: Filter tidak bekerja setelah navigasi**
**Kemungkinan:**
- Filter tanggal menggunakan JavaScript (tidak preserve antar halaman)

**Solusi:**
- Setelah ganti halaman, pilih ulang filter tanggal
- Atau gunakan filter Bulan/Tahun di atas tabel

---

### **Problem 3: Data tidak muncul setelah filter**
**Kemungkinan:**
- Tidak ada data yang match dengan filter criteria

**Solusi:**
- Reset filter dengan memilih "-- Semua --"
- Cek kembali periode bulan/tahun yang dipilih

---

### **Problem 4: Halaman terlalu lambat load**
**Kemungkinan:**
- Data terlalu banyak di database
- Koneksi internet lambat

**Solusi:**
- Gunakan filter bulan/tahun untuk membatasi data
- Gunakan filter nama untuk fokus ke pegawai tertentu
- Contact admin jika masalah persisten

---

## 📱 MOBILE USAGE

### **Responsiveness:**
✅ Tabel akan adjust otomatis di layar kecil  
✅ Navigation buttons tetap accessible  
✅ Scroll horizontal untuk kolom banyak  
✅ Touch-friendly button sizing

### **Best Practices di Mobile:**
1. Gunakan landscape mode untuk tabel lebar
2. Pinch-to-zoom jika teks terlalu kecil
3. Gunakan filter untuk kurangi data di layar

---

## 🎓 TRAINING CHECKLIST

### **Untuk Admin Baru:**
- [ ] Buka halaman view_absensi.php
- [ ] Coba filter bulan/tahun berbeda
- [ ] Navigasi antar halaman menggunakan tombol
- [ ] Coba semua filter dropdown
- [ ] Export CSV untuk 1 pegawai
- [ ] Export CSV untuk semua data
- [ ] Cek dashboard statistics di tabel 2
- [ ] Coba filter status di tabel 2

### **Untuk Power User:**
- [ ] Kombinasi multiple filters
- [ ] Cepat identifikasi keterlambatan dengan color coding
- [ ] Monitor pegawai belum absen real-time
- [ ] Follow-up overwork pending approval
- [ ] Analisis pattern keterlambatan per pegawai

---

## 📞 SUPPORT

**Jika mengalami masalah:**
1. Screenshot error/issue
2. Catat step-by-step yang dilakukan
3. Contact IT support dengan info:
   - Browser yang digunakan
   - Waktu kejadian
   - Filter yang aktif
   - Halaman pagination saat error

---

**Happy Managing! 🚀**

---
*Document Version: 1.0*  
*Last Updated: 2024*  
*Author: AI Assistant*
