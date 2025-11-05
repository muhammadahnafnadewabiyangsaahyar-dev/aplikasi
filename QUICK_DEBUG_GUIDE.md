# 🔍 Quick Debugging Guide - November 2 Issue

## Cara Test Cepat

### 1. Buka Test Page
```
http://localhost/aplikasi/test_kalender_fixes.html
```
Ikuti instruksi di halaman tersebut.

### 2. Atau Test Manual di Kalender

#### A. Cek File Terupdate
```bash
# Di terminal:
ls -la /Applications/XAMPP/xamppfiles/htdocs/aplikasi/script_kalender_database.js
```
Pastikan timestamp terbaru (hari ini).

#### B. Buka Kalender + Console
1. Buka: `http://localhost/aplikasi/kalender.php`
2. Tekan **F12** untuk buka DevTools
3. Klik tab **Console**

#### C. Test Sequence
1. **Pilih Cabang** dari dropdown
2. **Pilih Shift** yang punya data di Nov 2
3. **Lihat Console** - harus tampil:
   ```
   Loading shift assignments for months: ["2025-10", "2025-11"]
   ```

4. **Switch ke Week View**
5. **Navigate ke minggu 27 Okt - 2 Nov**
6. **Lihat Console** - harus tampil:
   ```
   Week view - Day 2025-11-02: Found 3 shifts
   ```
7. **IMPORTANT:** Lihat halaman - shift harus di **row waktu yang sesuai**

8. **Click tanggal 2** untuk Day View
9. **Lihat Console** - harus tampil:
   ```
   Day view - Found 3 shifts for 2025-11-02
   ```

## ⚠️ Jika Masih Error

### Error 1: Shift Nov 2 tidak tampil di week/day
**Penyebab:** Browser cache atau file belum reload

**Solusi:**
```bash
# Hard refresh browser
Ctrl+F5 (Windows/Linux)
Cmd+Shift+R (Mac)

# Atau clear cache:
Ctrl+Shift+Delete
```

### Error 2: Console tidak ada log "Loading shift assignments for months"
**Penyebab:** File JavaScript belum terupdate di browser

**Solusi:**
1. Tutup semua tab kalender.php
2. Clear browser cache
3. Buka kalender.php di tab baru
4. Check di Console apakah ada error load script

### Error 3: Week view masih summary box, bukan time slots
**Penyebab:** JavaScript belum reload atau ada error

**Solusi:**
1. Check Console untuk JavaScript errors (warna merah)
2. Pastikan tidak ada syntax error
3. Coba:
   ```bash
   # Restart XAMPP
   # Atau buka in private/incognito window
   ```

## 📋 Checklist Verifikasi

- [ ] File `script_kalender_database.js` timestamp hari ini
- [ ] Browser sudah di-refresh (hard refresh)
- [ ] Console tidak ada error merah
- [ ] Console menampilkan log debugging
- [ ] Month view: shift tampil di Nov 2
- [ ] Week view: console log "Loading... ['2025-10', '2025-11']"
- [ ] Week view: shift di row waktu, bukan summary box
- [ ] Day view: console log "Day view - Found X shifts"

## 🆘 Jika Semua Sudah Dicoba

Kirim screenshot:
1. Console output (semua log)
2. Week view (tampilan halaman)
3. Network tab (cek API call)

## 📞 Debug API Langsung

Test API manually:
```
http://localhost/aplikasi/api_shift_calendar.php?action=get_assignments&cabang_id=XXX&month=2025-11
```
Ganti XXX dengan cabang_id yang sesuai.

Harus return data untuk tanggal 2025-11-02.
