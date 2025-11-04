# Perbaikan Bug - Form Registrasi index.php

## Masalah yang Dilaporkan

1. **Database `posisi_jabatan` berubah saat tekan tombol "Daftar"**
2. **Muncul tulisan error padahal belum mengetikkan nomor HP**

## Root Cause

### Masalah 1: Field No. WhatsApp Auto-filled

**Lokasi**: `index.php` baris 245
```php
// SEBELUM (SALAH):
value="<?php echo isset($form_data['no_wa']) ? htmlspecialchars($form_data['no_wa']) : '+62 '; ?>"
```

**Problem**:
- Field `no_wa` sudah diisi dengan `+62 ` secara default
- Ketika user tekan "Daftar" tanpa mengetik apapun, field dianggap "sudah diisi"
- Validasi server-side cek field ini dan anggap invalid karena hanya berisi `+62 ` tanpa nomor
- Error muncul: "Format salah (Contoh: +62 81234567890)"

### Masalah 2: JavaScript Clear All Inputs

**Lokasi**: `script.js` baris 17-18
```javascript
// SEBELUM (SALAH):
allInputs.forEach(input => {
    input.value = ''; // Clear SEMUA input termasuk readonly fields
```

**Problem**:
- JavaScript men-clear SEMUA input saat page load
- Ini juga men-clear field readonly seperti `nama_panjang` dan `posisi` yang sudah diisi dari whitelist
- User bingung karena data yang sudah dicek di whitelist hilang

### Masalah 3: Validasi No. WhatsApp Terlalu Strict

**Lokasi**: `index.php` baris 33
```php
// SEBELUM (SALAH):
if (empty($form_data['no_wa'])) {
    $errors['no_wa'] = 'No. WhatsApp harus diisi.';
}
```

**Problem**:
- `empty()` tidak mendeteksi string '+62 ' sebagai kosong
- Jadi meskipun user belum ketik nomor, validasi lanjut ke regex check
- Regex gagal karena tidak ada digit setelah '+62 '

## Solusi yang Diterapkan

### 1. Perbaiki Default Value di Input Field

**File**: `index.php` baris ~245
```php
// SETELAH (BENAR):
value="<?php echo isset($form_data['no_wa']) ? htmlspecialchars($form_data['no_wa']) : ''; ?>"
```

**Perubahan**:
- Field `no_wa` sekarang **kosong** by default
- Prefix `+62 ` hanya muncul saat user **fokus** ke field (handled by JavaScript)

### 2. Update JavaScript: Jangan Clear Field Readonly & no_wa

**File**: `script.js` baris ~17
```javascript
// SETELAH (BENAR):
allInputs.forEach(input => {
    // Jangan clear field no_wa atau field readonly
    if (input.id !== 'no_wa' && !input.hasAttribute('readonly')) {
        input.value = '';
    }
```

**Perubahan**:
- Field dengan `id="no_wa"` tidak di-clear
- Field dengan atribut `readonly` tidak di-clear
- Field lain tetap di-clear untuk mencegah autofill browser

### 3. Perbaiki Validasi No. WhatsApp

**File**: `index.php` baris ~33
```php
// SETELAH (BENAR):
$no_wa_cleaned = trim($form_data['no_wa']);
if (empty($no_wa_cleaned) || $no_wa_cleaned === '+62' || $no_wa_cleaned === '+62 ') {
    $errors['no_wa'] = 'No. WhatsApp harus diisi.';
}
```

**Perubahan**:
- Cek apakah field benar-benar kosong ATAU hanya berisi prefix
- String `+62` atau `+62 ` dianggap sebagai "kosong"
- Regex hanya dijalankan jika field benar-benar ada isinya

### 4. Improve JavaScript Auto-fill Logic

**File**: `index.php` baris ~346
```javascript
// SETELAH (BENAR):
noWaInput.addEventListener('focus', function() {
    if (this.value === '' || this.value === '+62') {
        this.value = '+62 ';
        setTimeout(() => {
            this.setSelectionRange(4, 4); // Cursor setelah prefix
        }, 0);
    }
});
```

**Perubahan**:
- Prefix `+62 ` hanya diisi saat user **fokus** ke field
- Cursor otomatis di-set setelah prefix untuk UX lebih baik
- Improved paste handling: accept dan format nomor yang di-paste

### 5. Tambah Logging untuk Debugging

**File**: `index.php` baris ~159
```php
error_log("=== FETCHING DROPDOWN DATA FOR REGISTRATION ===");
error_log("Posisi fetched: " . count($daftar_posisi) . " items");
error_log("Posisi list: " . print_r($daftar_posisi, true));
```

**Perubahan**:
- Log saat fetch data dropdown posisi dan cabang
- Membantu debug jika memang ada masalah dengan database

## Testing Checklist

### Test Case 1: Field No. WhatsApp Kosong
✅ **Expected**: 
- Field kosong saat page load
- Prefix `+62 ` muncul saat user klik field
- Tidak ada error sampai user submit tanpa isi nomor
- Error "No. WhatsApp harus diisi" muncul HANYA saat submit dengan field kosong

### Test Case 2: Field Readonly Tetap Terisi
✅ **Expected**:
- User cek nama di whitelist
- Nama dan posisi muncul di hasil
- User klik "Lanjut Daftar"
- Form registrasi muncul dengan field `nama_panjang` dan `posisi` sudah terisi (readonly)
- Field tersebut TIDAK boleh di-clear oleh JavaScript

### Test Case 3: Submit dengan No. WhatsApp Valid
✅ **Expected**:
- User isi no. WhatsApp dengan format `+62 81234567890`
- Submit form
- Tidak ada error validasi untuk field no_wa
- Registrasi berhasil

### Test Case 4: Submit dengan No. WhatsApp Invalid
✅ **Expected**:
- User isi no. WhatsApp dengan format salah (misal: `0812345` atau `+62812` tanpa spasi)
- Submit form
- Error "Format salah (Contoh: +62 81234567890)" muncul
- Form tetap terbuka dengan data yang sudah diisi

### Test Case 5: Paste Nomor Telepon
✅ **Expected**:
- User copy nomor dari tempat lain (misal: `081234567890`)
- User paste ke field no_wa
- Nomor otomatis diformat menjadi `+62 81234567890`
- Validasi lolos

## Penjelasan: Database Posisi Tidak Berubah

**PENTING**: Database `posisi_jabatan` **TIDAK** berubah saat tombol "Daftar" ditekan.

**Yang sebenarnya terjadi**:
1. Form registrasi hanya **membaca** data dari `posisi_jabatan` untuk dropdown
2. Tidak ada query INSERT/UPDATE/DELETE ke tabel `posisi_jabatan` di `index.php`
3. Yang di-insert hanya ke tabel `register` dan update status di `pegawai_whitelist`

**Jika Anda melihat perubahan di `posisi_jabatan`**:
- Kemungkinan ada proses lain yang mengubah (misal: di halaman `posisi_jabatan.php`)
- Atau ada trigger database yang tidak sengaja
- Cek log dengan script helper untuk memastikan:
  ```bash
  cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
  ./watch_posisi_log.sh
  # Pilih option 5 untuk lihat 50 baris terakhir
  ```

## Cara Test Perbaikan

### 1. Clear Browser Cache
```
Chrome/Edge: Ctrl+Shift+Del (Windows) atau Cmd+Shift+Del (Mac)
Pilih: Cached images and files
```

### 2. Buka Halaman Registrasi
```
http://localhost/aplikasi/index.php
```

### 3. Scenario Testing

#### A. Test Field Kosong
1. Klik tombol "Daftar"
2. Masukkan nama (yang ada di whitelist)
3. Klik "Cek Whitelist"
4. Klik "Lanjut Daftar"
5. **Cek**: Field no_wa harus KOSONG
6. Klik field no_wa
7. **Cek**: Prefix `+62 ` muncul otomatis
8. Klik submit tanpa isi nomor
9. **Cek**: Error "No. WhatsApp harus diisi" muncul

#### B. Test Submit Normal
1. Lakukan step 1-7 dari Scenario A
2. Ketik nomor: `81234567890` (tanpa +62, akan auto-format)
3. Isi field lain (outlet, email, username, password)
4. Klik "Daftar"
5. **Cek**: Registrasi berhasil, redirect ke login

#### C. Test Field Readonly
1. Lakukan step 1-4 dari Scenario A
2. **Cek**: Field "Nama Lengkap" dan "Jabatan" sudah terisi dan readonly
3. Refresh halaman (F5)
4. **Cek**: Field tersebut TIDAK boleh hilang isinya

## Log Files untuk Debugging

Jika masih ada masalah, cek log:

```bash
# Lihat log dropdown fetch
grep "FETCHING DROPDOWN DATA" /Applications/XAMPP/xamppfiles/logs/php_error_log | tail -10

# Lihat log posisi data
grep "Posisi list:" /Applications/XAMPP/xamppfiles/logs/php_error_log | tail -5

# Lihat error registrasi
grep "Registrasi" /Applications/XAMPP/xamppfiles/logs/php_error_log | tail -10
```

## Rollback (Jika Diperlukan)

Jika perbaikan ini menyebabkan masalah lain, restore dari backup atau revert perubahan:

### File yang Diubah:
1. `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/index.php`
   - Baris ~33: Validasi no_wa
   - Baris ~159: Logging dropdown
   - Baris ~245: Default value no_wa
   - Baris ~346: JavaScript auto-fill

2. `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/script.js`
   - Baris ~17: Clear input logic

## Summary

✅ **Fixed**: Field no_wa tidak lagi auto-filled dengan `+62 ` saat page load
✅ **Fixed**: Error tidak muncul sebelum user submit form
✅ **Fixed**: Field readonly tidak di-clear oleh JavaScript
✅ **Improved**: Validasi no_wa lebih robust
✅ **Improved**: UX untuk input no_wa lebih baik (auto-format, paste support)
✅ **Added**: Logging untuk debug database fetch

**Database `posisi_jabatan` tidak dimodifikasi** oleh form registrasi. Jika ada perubahan, itu dari halaman lain (posisi_jabatan.php).
