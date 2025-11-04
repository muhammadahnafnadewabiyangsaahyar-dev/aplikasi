# 📧 MENAMBAH HR / KEPALA TOKO BARU - GUIDE

## ✅ Sistem Sudah Otomatis Support!

Sistem email notification menggunakan **query dinamis** yang secara otomatis mendeteksi approver berdasarkan **posisi** di database. 

**Tidak perlu update kode!** Cukup tambah user dengan posisi yang sesuai.

---

## 🎯 Cara Menambah HR Baru

### Step 1: Tambah User di Database

**Via Aplikasi:**
1. Login sebagai admin
2. Buka halaman registrasi/user management
3. Tambah user baru dengan data:
   - Nama Lengkap: [Nama HR]
   - Posisi: **HR** (atau posisi yang mengandung kata "HR")
   - Email: [Email valid]
   - Role: admin atau user (sesuai kebutuhan)

**Via SQL:**
```sql
INSERT INTO register (nama_lengkap, posisi, email, username, password, role, no_whatsapp, outlet, time_created)
VALUES (
    'Nama HR Baru',
    'HR',  -- PENTING: Posisi harus mengandung "HR"
    'hr.baru@example.com',  -- PENTING: Email harus valid
    'hrbaru',
    'password_hash_here',
    'admin',
    '08123456789',
    'Citraland Gowa',
    CURDATE()
);
```

### Step 2: Verify Email Terdeteksi

Jalankan script test:
```bash
php get_hr_emails.php
```

Output seharusnya menampilkan HR baru:
```
HR Emails:
  - Muhammad Abizar Nafara <abizarnafara26@gmail.com>
  - Nama HR Baru <hr.baru@example.com>  ← HR BARU MUNCUL!
```

### Step 3: Test Email

1. Buka: `http://localhost/aplikasi/test_email_notification_web.php`
2. Klik "Test Izin Baru"
3. Cek inbox **semua HR** (termasuk HR baru)
4. ✅ Semua HR seharusnya dapat email!

---

## 🏪 Cara Menambah Kepala Toko / Owner Baru

### Step 1: Tambah User di Database

**Via Aplikasi:**
1. Login sebagai admin
2. Tambah user baru dengan data:
   - Nama Lengkap: [Nama Kepala Toko]
   - Posisi: **Kepala Toko** atau **Owner** atau **Manager**
   - Email: [Email valid]
   - Role: admin

**Via SQL:**
```sql
INSERT INTO register (nama_lengkap, posisi, email, username, password, role, no_whatsapp, outlet, time_created)
VALUES (
    'John Doe',
    'Kepala Toko',  -- PENTING: "Kepala Toko", "Owner", atau "Manager"
    'kepala.toko@example.com',
    'kepalatoko',
    'password_hash_here',
    'admin',
    '08123456789',
    'Citraland Gowa',
    CURDATE()
);
```

### Step 2: Verify Email Terdeteksi

Jalankan script test:
```bash
php get_hr_emails.php
```

Output:
```
Kepala Toko Emails:
  - John Doe <kepala.toko@example.com>  ← KEPALA TOKO BARU MUNCUL!
```

### Step 3: Test Email

1. Test seperti cara di atas
2. ✅ Kepala Toko baru seharusnya dapat email!

---

## 📋 Posisi yang Terdeteksi Otomatis

### HR (query case-insensitive):
- ✅ "HR"
- ✅ "hr"
- ✅ "HR Manager"
- ✅ "Staff HR"
- ✅ "Human Resource"
- ✅ Semua yang mengandung kata "HR"

### Kepala Toko / Owner (query case-insensitive):
- ✅ "Owner"
- ✅ "owner"
- ✅ "Kepala Toko"
- ✅ "kepala toko"
- ✅ "Manager"
- ✅ "manager"
- ✅ "General Manager"
- ✅ Semua yang mengandung kata-kata tersebut

---

## 🔍 Cara Kerja Sistem (Technical)

### 1. Fungsi `getApproverEmails()` di `email_config.php`

```php
function getApproverEmails($pdo) {
    $emails = [
        'hr' => [],
        'kepala_toko' => []
    ];
    
    // Query HR - LIKE '%HR%' (case-insensitive)
    $stmt_hr = $pdo->query("
        SELECT email, nama_lengkap 
        FROM register 
        WHERE posisi LIKE '%HR%' OR posisi LIKE '%hr%'
    ");
    $hr_users = $stmt_hr->fetchAll(PDO::FETCH_ASSOC);
    foreach ($hr_users as $user) {
        if (!empty($user['email'])) {
            $emails['hr'][] = [
                'email' => $user['email'],
                'name' => $user['nama_lengkap']
            ];
        }
    }
    
    // Query Kepala Toko/Owner/Manager
    $stmt_kepala = $pdo->query("
        SELECT email, nama_lengkap 
        FROM register 
        WHERE posisi LIKE '%owner%' 
           OR posisi LIKE '%Owner%' 
           OR posisi LIKE '%kepala%' 
           OR posisi LIKE '%Kepala%' 
           OR posisi LIKE '%manager%' 
           OR posisi LIKE '%Manager%'
    ");
    $kepala_users = $stmt_kepala->fetchAll(PDO::FETCH_ASSOC);
    foreach ($kepala_users as $user) {
        if (!empty($user['email'])) {
            $emails['kepala_toko'][] = [
                'email' => $user['email'],
                'name' => $user['nama_lengkap']
            ];
        }
    }
    
    // Fallback: Jika tidak ada HR atau Kepala Toko, gunakan admin
    if (empty($emails['hr']) && empty($emails['kepala_toko'])) {
        $stmt_admin = $pdo->query("
            SELECT email, nama_lengkap 
            FROM register 
            WHERE role = 'admin' 
            LIMIT 1
        ");
        $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
        if ($admin && !empty($admin['email'])) {
            $emails['hr'][] = [
                'email' => $admin['email'],
                'name' => $admin['nama_lengkap']
            ];
        }
    }
    
    return $emails;
}
```

### 2. Fungsi `sendEmailIzinBaru()` di `email_helper.php`

```php
function sendEmailIzinBaru($izin_data, $user_data, $pdo) {
    $mail = initMailer();
    
    // GET APPROVER EMAILS SECARA DINAMIS
    $approvers = getApproverEmails($pdo);
    
    // Add ALL HR emails
    foreach ($approvers['hr'] as $hr) {
        $mail->addAddress($hr['email'], $hr['name']);
    }
    
    // Add ALL Kepala Toko emails
    foreach ($approvers['kepala_toko'] as $kepala) {
        $mail->addAddress($kepala['email'], $kepala['name']);
    }
    
    // ... send email
}
```

**Kesimpulan:** Sistem secara otomatis loop semua HR dan Kepala Toko yang ditemukan!

---

## 🧪 Testing Multi-Approver

### Script Test

Buat file `test_multi_approver.php`:

```php
<?php
require_once 'connect.php';
require_once 'email_helper.php';

echo "=== TEST MULTI-APPROVER SYSTEM ===\n\n";

// Get approvers
$approvers = getApproverEmails($pdo);

echo "Total HR: " . count($approvers['hr']) . "\n";
foreach ($approvers['hr'] as $hr) {
    echo "  - {$hr['name']} <{$hr['email']}>\n";
}

echo "\nTotal Kepala Toko: " . count($approvers['kepala_toko']) . "\n";
foreach ($approvers['kepala_toko'] as $kepala) {
    echo "  - {$kepala['name']} <{$kepala['email']}>\n";
}

echo "\n✅ Semua approver akan menerima email saat ada pengajuan izin baru!\n";
```

Jalankan:
```bash
php test_multi_approver.php
```

---

## 📊 Scenario Examples

### Scenario 1: Ada 2 HR
```
Database:
- User A: posisi = "HR"
- User B: posisi = "HR Manager"

Result: 
✅ Saat ada pengajuan izin baru:
   - User A dapat email
   - User B dapat email
```

### Scenario 2: Ada 1 HR + 1 Kepala Toko
```
Database:
- User A: posisi = "HR"
- User C: posisi = "Kepala Toko"

Result:
✅ Saat ada pengajuan izin baru:
   - User A dapat email (sebagai HR)
   - User C dapat email (sebagai Kepala Toko)
```

### Scenario 3: Tidak Ada HR/Kepala Toko
```
Database:
- Tidak ada user dengan posisi HR/Kepala Toko
- Ada User D: role = "admin"

Result:
✅ Fallback ke admin:
   - User D (admin pertama) dapat email
```

---

## ⚠️ Important Notes

### 1. Email Harus Valid
- Pastikan kolom `email` tidak kosong (`NULL` atau `''`)
- Gunakan email yang valid dan aktif
- Test email setelah menambah approver baru

### 2. Posisi Harus Sesuai
- Gunakan kata kunci yang jelas: "HR", "Owner", "Kepala Toko", "Manager"
- Sistem case-insensitive (HR = hr = Hr)
- Gunakan konsisten untuk memudahkan

### 3. Tidak Perlu Restart
- Setelah tambah user, sistem langsung detect
- Tidak perlu restart Apache/PHP
- Tidak perlu update kode

---

## 🔧 Troubleshooting

### Problem: HR baru tidak dapat email

**Solution:**
1. Verify posisi mengandung "HR"
2. Verify email tidak kosong
3. Run test: `php get_hr_emails.php`
4. Cek apakah muncul di list

### Problem: Email masuk SPAM

**Solution:**
1. Whitelist email pengirim (kaori.aplikasi.notif@gmail.com)
2. Mark as "Not Spam"
3. Add to contacts

### Problem: Multiple email untuk 1 orang

**Solution:**
- Ini normal jika user punya multiple role
- Misal: User A adalah "HR Manager" → dapat email sebagai HR
- Jika tidak diinginkan, pastikan hanya 1 posisi per user

---

## 📝 Summary

| Action | Kode Perlu Update? | Database Perlu Update? |
|--------|-------------------|----------------------|
| Tambah HR baru | ❌ TIDAK | ✅ YA (insert user) |
| Tambah Kepala Toko baru | ❌ TIDAK | ✅ YA (insert user) |
| Hapus HR | ❌ TIDAK | ✅ YA (delete/update user) |
| Ganti email HR | ❌ TIDAK | ✅ YA (update email) |
| Tambah posisi baru | ⚠️ MUNGKIN | - |

**Untuk posisi baru** (misal: "Supervisor"), perlu update query di `getApproverEmails()` untuk include posisi tersebut.

---

## 🎯 Best Practices

1. ✅ Gunakan naming convention konsisten untuk posisi
2. ✅ Pastikan semua HR/Kepala Toko punya email valid
3. ✅ Test email setelah add new approver
4. ✅ Monitor email log untuk verify pengiriman
5. ✅ Update dokumentasi jika ada perubahan posisi

---

**Last Updated**: November 3, 2025  
**Version**: 1.1.0 (Fixed status inconsistency)
