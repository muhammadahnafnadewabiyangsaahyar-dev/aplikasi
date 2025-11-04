# ⚠️ QUICK WARNING - aplikasi.sql

## 🚨 PERINGATAN SINGKAT

**JANGAN import `aplikasi.sql` jika:**
- ❌ Sedang testing registrasi user baru
- ❌ Ada data penting yang belum di-backup
- ❌ Tidak yakin apa yang dilakukan

**File ini akan menghapus SEMUA data baru!**

---

## ✅ Cara Aman Import Database

### **Via Web Interface (RECOMMENDED):**

1. Login sebagai Admin
2. Klik menu **"⚠️ Import DB"** (warna merah)
3. Baca peringatan dengan seksama
4. Ketik: `HAPUS SEMUA DATA`
5. Konfirmasi dan import

Fitur ini dilengkapi:
- ⚠️ Peringatan visual
- 📊 Statistik data yang akan terhapus
- 💬 Double confirmation
- 📝 Logging aktivitas

---

## 📦 Backup Database Dulu!

```bash
chmod +x backup_database.sh
./backup_database.sh
```

Backup akan tersimpan di folder `backups/`

---

## 🔍 Monitor Database Real-time

```bash
# Auto-refresh setiap 2 detik
./watch_check_register.sh

# Quick check sekali
./check_register.sh
```

---

## 📖 Dokumentasi Lengkap

Baca file: **[PANDUAN_IMPORT_DATABASE.md](PANDUAN_IMPORT_DATABASE.md)**

---

## 🆘 Jika Data Hilang

1. **STOP** aktivitas
2. **RESTORE** dari backup:
   ```bash
   mysql -u root aplikasi < backups/aplikasi_backup_YYYYMMDD_HHMMSS.sql
   ```

---

**💡 INGAT:** Import database = Hapus data baru!

Gunakan web interface untuk safety maksimal.
