# 📦 PANDUAN SETUP BACKUP OTOMATIS (AMAN)

## ⚠️ PENTING: Export vs Import

### ❌ JANGAN Gunakan Cron untuk Import!
```bash
# BERBAHAYA - Akan menghapus data baru!
* * * * * /path/to/import_script.sh
```

### ✅ BOLEH Gunakan Cron untuk Export (Backup)
```bash
# AMAN - Hanya backup, tidak menghapus data
*/30 * * * * /path/to/backup_auto.sh
```

---

## 🎯 Rekomendasi Interval Backup

### **1. Setiap 1 Jam (Recommended untuk Development)**
```bash
0 * * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh
```
**Keuntungan:**
- ✅ Tidak terlalu sering (hemat disk)
- ✅ Cukup untuk recovery jika ada masalah
- ✅ File backup dengan timestamp, tidak tiban

**Cocok untuk:** Development, testing, low-traffic apps

---

### **2. Setiap 4 Jam (Recommended untuk Production)**
```bash
0 */4 * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh
```
**Keuntungan:**
- ✅ Balance antara safety dan storage
- ✅ 6 backup per hari
- ✅ Auto cleanup backup lama (>7 hari)

**Cocok untuk:** Production, medium-traffic apps

---

### **3. Setiap 1 Hari (Recommended untuk Stable Apps)**
```bash
0 2 * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh
```
**Keuntungan:**
- ✅ Minimal storage usage
- ✅ Backup di jam sepi (2 AM)
- ✅ Cukup untuk stable apps

**Cocok untuk:** Stable production, low-change apps

---

### **4. Setiap 30 Menit (Maximum untuk High-Change Apps)**
```bash
*/30 * * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh
```
**Keuntungan:**
- ✅ Backup frequent untuk high-change data
- ✅ Masih reasonable untuk storage

**Cocok untuk:** High-traffic, high-change apps

---

## ❌ TIDAK DISARANKAN

### **Setiap 1 Menit**
```bash
# TIDAK DISARANKAN!
* * * * * /path/to/backup_script.sh
```

**Kerugian:**
- ❌ Terlalu sering (60 backup/jam = 1440/hari!)
- ❌ Membebani server (disk I/O tinggi)
- ❌ Storage cepat penuh
- ❌ Tidak ada manfaat signifikan vs 30 menit

---

## 📋 Cara Setup Cron Job (AMAN)

### **Langkah 1: Test Script Manual**
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
./backup_auto.sh
```

Cek apakah backup berhasil di folder `backups/`

### **Langkah 2: Edit Crontab**
```bash
crontab -e
```

### **Langkah 3: Tambahkan Cron Job**

**Pilih salah satu (sesuai kebutuhan):**

```bash
# Option 1: Setiap 1 jam (RECOMMENDED untuk development)
0 * * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh >> /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backups/cron.log 2>&1

# Option 2: Setiap 4 jam (RECOMMENDED untuk production)
0 */4 * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh >> /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backups/cron.log 2>&1

# Option 3: Setiap hari jam 2 pagi
0 2 * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh >> /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backups/cron.log 2>&1

# Option 4: Setiap 30 menit (maximum frequency)
*/30 * * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh >> /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backups/cron.log 2>&1
```

### **Langkah 4: Save dan Exit**
- Nano: `Ctrl+X`, `Y`, `Enter`
- Vim: `:wq`

### **Langkah 5: Verify Cron Job**
```bash
crontab -l
```

---

## 🔍 Monitoring Backup

### **Cek Log Backup:**
```bash
tail -f backups/backup.log
```

### **Cek Log Cron:**
```bash
tail -f backups/cron.log
```

### **Lihat Backup Files:**
```bash
ls -lh backups/aplikasi_auto_*.sql | tail -10
```

### **Hitung Total Backup:**
```bash
ls -1 backups/aplikasi_auto_*.sql | wc -l
```

---

## 🧹 Auto Cleanup

Script `backup_auto.sh` sudah dilengkapi auto cleanup:

```bash
# Hapus backup > 7 hari otomatis
find "$BACKUP_DIR" -name "aplikasi_auto_*.sql" -mtime +7 -delete
```

**Artinya:**
- ✅ Backup lama (>7 hari) dihapus otomatis
- ✅ Storage tidak penuh
- ✅ Tetap punya history 7 hari ke belakang

**Untuk ubah retention period:**
```bash
# 3 hari
-mtime +3

# 14 hari
-mtime +14

# 30 hari
-mtime +30
```

---

## 📊 Perbandingan Interval

| Interval | Backup/Hari | Storage/Hari* | Retention | Use Case |
|----------|------------|---------------|-----------|----------|
| **1 menit** | 1,440 | ~720 MB | ❌ Penuh cepat | ❌ Tidak praktis |
| **30 menit** | 48 | ~24 MB | 7 hari | High-change apps |
| **1 jam** | 24 | ~12 MB | 7 hari | ✅ Development |
| **4 jam** | 6 | ~3 MB | 7 hari | ✅ Production |
| **1 hari** | 1 | ~500 KB | 7 hari | Stable apps |

*Asumsi: Ukuran database ~500KB

---

## 💡 Best Practices

### **1. Kombinasi Strategi**
```bash
# Backup frequent (4 jam) dengan retention 7 hari
0 */4 * * * /path/to/backup_auto.sh

# Backup weekly (Minggu 2 AM) simpan permanen
0 2 * * 0 /path/to/backup_weekly.sh
```

### **2. Notifikasi jika Backup Gagal**
Edit `backup_auto.sh`:
```bash
if [ $? -ne 0 ]; then
    echo "Backup failed!" | mail -s "Backup Alert" admin@example.com
fi
```

### **3. Backup ke Remote/Cloud**
```bash
# Setelah backup lokal, sync ke remote
rsync -av backups/ user@remote:/backup/aplikasi/
```

---

## 🎯 Rekomendasi untuk Anda

**Berdasarkan kebutuhan development/testing:**

```bash
# Edit crontab
crontab -e

# Tambahkan (backup setiap 1 jam):
0 * * * * /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backup_auto.sh >> /Applications/XAMPP/xamppfiles/htdocs/aplikasi/backups/cron.log 2>&1
```

**Hasil:**
- ✅ 24 backup per hari
- ✅ History 7 hari (168 backup)
- ✅ Auto cleanup backup lama
- ✅ File dengan timestamp, tidak timpa
- ✅ Storage reasonable (~12 MB/hari)

---

## 🚫 Yang HARUS DIHINDARI

### **1. Import via Cron**
```bash
# JANGAN PERNAH!
* * * * * mysql -u root aplikasi < aplikasi.sql
```
**Akibat:** Data baru akan terhapus terus!

### **2. Export Tanpa Timestamp**
```bash
# JANGAN!
* * * * * mysqldump aplikasi > aplikasi.sql
```
**Akibat:** File lama ditimpa, tidak ada history!

### **3. Backup Terlalu Sering**
```bash
# TIDAK EFISIEN!
* * * * * /path/to/backup.sh  # Setiap menit
```
**Akibat:** Storage penuh, server lambat!

---

## ✅ Checklist Setup

- [ ] Script `backup_auto.sh` sudah executable
- [ ] Test manual backup: `./backup_auto.sh`
- [ ] Folder `backups/` ada dan writable
- [ ] Pilih interval yang sesuai (rekomendasi: 1 jam)
- [ ] Setup cron job dengan `crontab -e`
- [ ] Verify cron job: `crontab -l`
- [ ] Monitor log: `tail -f backups/backup.log`
- [ ] Tunggu 1 interval dan cek hasilnya

---

## 📞 Troubleshooting

### **Cron tidak jalan:**
```bash
# Cek cron service (macOS)
sudo launchctl list | grep cron

# Cek permission
ls -la backup_auto.sh  # Harus executable
```

### **Permission denied:**
```bash
chmod +x backup_auto.sh
chmod 755 backups/
```

### **Backup tidak muncul:**
```bash
# Cek log cron
tail -f backups/cron.log

# Cek log backup
tail -f backups/backup.log
```

---

## 📝 Summary

**✅ LAKUKAN:**
- Export (backup) dengan cron
- Gunakan timestamp di nama file
- Auto cleanup backup lama
- Interval reasonable (1-4 jam)
- Monitor log backup

**❌ JANGAN:**
- Import dengan cron
- Backup setiap menit
- Timpa file backup lama
- Lupa cleanup backup

---

📅 **Dibuat:** 2025-11-03
🎯 **Rekomendasi:** Backup setiap 1 jam untuk development
📦 **Script:** backup_auto.sh
🔗 **Related:** MASALAH_AUTO_IMPORT_RESOLVED.md
