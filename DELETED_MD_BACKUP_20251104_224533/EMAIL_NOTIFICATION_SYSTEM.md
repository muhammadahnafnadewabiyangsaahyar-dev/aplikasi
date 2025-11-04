# 📧 SISTEM EMAIL NOTIFICATION SURAT IZIN

## 📋 Overview

Sistem email notification otomatis untuk proses permohonan izin dengan alur sebagai berikut:

### Alur Email:
1. **User mengajukan izin** → Email dikirim ke **HR & Kepala Toko** (dengan link approve)
2. **HR/Kepala Toko approve/reject** → Email dikirim ke **User yang mengajukan**

---

## 🏗️ Arsitektur Sistem

### File-file yang Digunakan:

1. **`email_config.php`** - Konfigurasi SMTP dan email settings
2. **`email_helper.php`** - Fungsi-fungsi helper untuk kirim email
3. **`docx.php`** - Proses pengajuan izin (kirim email ke HR/Kepala Toko)
4. **`proses_approve.php`** - Proses approve/reject (kirim email ke user)

### Database:
- **Tabel `register`**: Menyimpan data user termasuk `email` dan `posisi`
- **Tabel `pengajuan_izin`**: Menyimpan data permohonan izin

---

## ⚙️ Konfigurasi

### 1. Email Configuration (`email_config.php`)

```php
// SMTP Configuration (sudah dikonfigurasi)
SMTP_HOST: smtp.gmail.com
SMTP_PORT: 465
SMTP_USERNAME: kaori.aplikasi.notif@gmail.com
SMTP_PASSWORD: imjq nmeq vyig umgn (App Password)
SMTP_SECURE: ssl
```

### 2. Approver Emails (Dinamis dari Database)

Sistem secara otomatis mengambil email approver dari database berdasarkan posisi:

- **HR**: User dengan posisi yang mengandung "HR" atau "hr"
- **Kepala Toko/Owner/Manager**: User dengan posisi yang mengandung "owner", "kepala", "manager"

Jika tidak ada HR atau Kepala Toko, sistem akan fallback ke admin pertama.

---

## 📧 Fungsi-fungsi Email

### 1. `sendEmailIzinBaru($izin_data, $user_data, $pdo)`

Mengirim email notifikasi izin baru ke HR dan Kepala Toko.

**Parameter:**
- `$izin_data`: Array berisi data izin (id, tanggal_mulai, tanggal_selesai, durasi_hari, alasan, jenis_izin)
- `$user_data`: Array berisi data user (nama_lengkap, posisi, email)
- `$pdo`: Database connection

**Return:** `true` jika berhasil, `false` jika gagal

**Email Content:**
- Subject: 🔔 Permohonan Izin Baru - [Nama Pegawai]
- Berisi detail izin dan link ke `approve.php`

### 2. `sendEmailIzinStatus($izin_data, $user_data, $status, $catatan, $approver_data)`

Mengirim email notifikasi status izin (approve/reject) ke user.

**Parameter:**
- `$izin_data`: Array berisi data izin
- `$user_data`: Array berisi data user
- `$status`: 'Disetujui' atau 'Ditolak'
- `$catatan`: Catatan dari approver (optional)
- `$approver_data`: Data approver (optional)

**Return:** `true` jika berhasil, `false` jika gagal

**Email Content:**
- Subject: ✅/❌ Permohonan Izin [Status] - [Tanggal]
- Berisi detail izin dan status approval

### 3. `getApproverEmails($pdo)`

Mengambil daftar email HR dan Kepala Toko dari database.

**Return:** Array dengan struktur:
```php
[
    'hr' => [
        ['email' => 'hr@example.com', 'name' => 'HR Name']
    ],
    'kepala_toko' => [
        ['email' => 'kepala@example.com', 'name' => 'Kepala Name']
    ]
]
```

---

## 🚀 Implementasi

### 1. Di `docx.php` (Setelah user mengajukan izin)

```php
// 14. Kirim Email Notifikasi ke HR dan Kepala Toko
require_once __DIR__ . '/email_helper.php';

$pengajuan_id = $pdo->lastInsertId();

$izin_data = [
    'id' => $pengajuan_id,
    'tanggal_mulai' => $tanggal_mulai_form,
    'tanggal_selesai' => $tanggal_selesai_form,
    'durasi_hari' => $lama_izin_form,
    'alasan' => $alasan_form,
    'jenis_izin' => $perihal_form
];

// Kirim email notification
$email_sent = sendEmailIzinBaru($izin_data, $user_data, $pdo);
```

### 2. Di `proses_approve.php` (Setelah approve/reject)

```php
// === KIRIM NOTIFIKASI EMAIL MENGGUNAKAN HELPER FUNCTION ===
$email_sent = sendEmailIzinStatus($izin_data, $user_data, $new_status, '', $approver_data);
```

---

## 🧪 Testing

### Test Email System:

```bash
php test_email_notification.php
```

Script ini akan:
1. ✅ Test konfigurasi email SMTP
2. ✅ Get approver emails dari database
3. ✅ Test kirim basic email
4. ✅ Test email izin baru (simulasi)
5. ✅ Test email approve (simulasi)
6. ✅ Test email reject (simulasi)

### Expected Output:

```
========================================
TEST EMAIL NOTIFICATION SYSTEM
========================================

1. TESTING EMAIL CONFIGURATION...
   ✅ SMTP configured correctly

2. GET APPROVER EMAILS FROM DATABASE...
   ✅ HR Emails: Muhammad Abizar Nafara <abizarnafara26@gmail.com>
   
3. TEST BASIC EMAIL...
   ✅ Test email berhasil dikirim

4. TEST EMAIL IZIN BARU...
   ✅ Email izin baru berhasil dikirim!

5. TEST EMAIL APPROVE...
   ✅ Email approve berhasil dikirim

6. TEST EMAIL REJECT...
   ✅ Email reject berhasil dikirim

========================================
✅ TEST COMPLETE!
========================================
```

---

## 📝 Email Templates

### 1. Email Izin Baru (ke HR & Kepala Toko)

- **Design**: Gradient header (purple), info box dengan border kiri biru
- **Content**: 
  - Nama pegawai, posisi
  - Jenis izin, tanggal mulai/selesai, durasi
  - Alasan
  - Button "Proses Permohonan Izin" (link ke approve.php)

### 2. Email Izin Disetujui (ke User)

- **Design**: Gradient header (purple), green badge status
- **Content**:
  - Status: DISETUJUI ✅
  - Detail izin
  - Diproses oleh: [Nama Approver]
  - Catatan (jika ada)
  - Alert box hijau: "Selamat! Izin Anda telah disetujui"

### 3. Email Izin Ditolak (ke User)

- **Design**: Gradient header (pink-red), red badge status
- **Content**:
  - Status: DITOLAK ❌
  - Detail izin
  - Diproses oleh: [Nama Approver]
  - Catatan (jika ada)
  - Alert box merah: "Mohon maaf, permohonan izin Anda ditolak"

---

## 🔧 Troubleshooting

### Email tidak terkirim:

1. **Cek SMTP Configuration**
   - Pastikan SMTP_USERNAME dan SMTP_PASSWORD benar
   - Untuk Gmail, pastikan menggunakan App Password (bukan password biasa)

2. **Cek Email Recipient**
   - Pastikan email di database tidak kosong
   - Cek apakah ada user dengan posisi HR/Kepala Toko

3. **Cek Error Log**
   ```bash
   tail -f /Applications/XAMPP/xamppfiles/logs/error_log
   ```

4. **Test Email Configuration**
   ```bash
   php test_email_notification.php
   ```

### Email masuk ke SPAM:

- Pastikan email pengirim (kaori.aplikasi.notif@gmail.com) sudah di-whitelist
- Minta recipient untuk mark as "Not Spam"
- Untuk production, gunakan domain email sendiri

### Email lambat terkirim:

- Normal untuk SMTP, biasanya 5-30 detik
- Jika lebih dari 1 menit, cek koneksi internet
- Cek apakah SMTP server down

---

## 📊 Monitoring

### Log Messages:

Sistem akan log ke PHP error log dengan format:

```
✅ Email notifikasi izin #[ID] berhasil dikirim
✅ Email status izin berhasil dikirim ke [email]
⚠️ Email notifikasi gagal dikirim (izin tetap tersimpan)
❌ Email Error: [error detail]
```

### Cara Monitor:

```bash
# Real-time monitoring
tail -f /Applications/XAMPP/xamppfiles/logs/error_log | grep "Email"

# Lihat email yang berhasil dikirim hari ini
grep "$(date '+%d-%b-%Y')" /Applications/XAMPP/xamppfiles/logs/error_log | grep "Email.*berhasil"

# Lihat email yang gagal dikirim
grep "Email.*gagal\|Email Error" /Applications/XAMPP/xamppfiles/logs/error_log | tail -20
```

---

## 🔒 Security

### Best Practices:

1. **JANGAN commit email password ke git**
   - Tambahkan `email_config.php` ke `.gitignore`
   - Gunakan environment variable untuk production

2. **Gunakan App Password untuk Gmail**
   - JANGAN gunakan password email biasa
   - Generate App Password: https://myaccount.google.com/apppasswords

3. **Validate email sebelum kirim**
   - Sistem sudah handle empty email
   - Sistem sudah log error jika email tidak valid

4. **Rate limiting (untuk production)**
   - Batasi jumlah email per user per hari
   - Gunakan queue system untuk high volume

---

## 🎯 Production Checklist

- [ ] Update `APP_URL` di `email_config.php` ke URL production
- [ ] Set `EMAIL_DEBUG` ke `false` di `email_config.php`
- [ ] Gunakan environment variable untuk SMTP credentials
- [ ] Setup email monitoring/alerting
- [ ] Test email di berbagai email client (Gmail, Outlook, Yahoo)
- [ ] Pastikan email template responsive untuk mobile
- [ ] Setup SPF, DKIM, DMARC untuk domain email
- [ ] Backup konfigurasi email secara aman

---

## 📚 References

- PHPMailer Documentation: https://github.com/PHPMailer/PHPMailer
- Gmail SMTP Settings: https://support.google.com/mail/answer/7126229
- Gmail App Passwords: https://myaccount.google.com/apppasswords

---

## 🆘 Support

Jika ada masalah dengan email notification:

1. Run test script: `php test_email_notification.php`
2. Cek error log
3. Verifikasi SMTP credentials
4. Cek internet connection
5. Contact system admin jika masalah berlanjut

---

**Last Updated**: November 3, 2025  
**Version**: 1.0.0  
**Status**: ✅ Production Ready
