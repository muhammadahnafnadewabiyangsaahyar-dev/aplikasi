# TROUBLESHOOTING: Import CSV Error

## ❌ PROBLEM
Error saat import CSV: `Invalid request. Please try again.`

## 🔍 ROOT CAUSE
CSRF token validation gagal saat form disubmit. Kemungkinan penyebab:
1. Session expire atau berubah
2. CSRF token tidak match antara form dan session
3. Browser cache atau cookie issue

## ✅ SOLUTION

### Solusi 1: Refresh Halaman
**Paling Simple:**
1. Refresh halaman whitelist.php (F5 atau Cmd+R)
2. Coba import lagi

### Solusi 2: Clear Session & Login Ulang
1. Logout dari aplikasi
2. Clear browser cache/cookies
3. Login kembali
4. Coba import lagi

### Solusi 3: Test dengan Script Terpisah  
Gunakan `test_import_csv.php` untuk test import tanpa issue CSRF:
```
http://localhost/aplikasi/test_import_csv.php
```

Script ini:
- ✅ Tidak memerlukan login
- ✅ Test import CSV langsung
- ✅ Menampilkan detail setiap row
- ✅ Auto-detect role dari posisi

### Solusi 4: Debug Mode
1. Buka file `whitelist.php`
2. Cek error log di console browser (F12 → Console)
3. Atau jalankan: `php debug_session.php`

## 🧪 TEST STEPS

### 1. Test Auto-Detect Role
```bash
php test_auto_detect_role.php
```
Output yang benar:
```
✅ Passed: 20+
🎉 ALL TESTS PASSED!
```

### 2. Test Import (Web Interface)
```
http://localhost/aplikasi/test_import_csv.php
```
Upload file: `template_import_basic.csv`

### 3. Verify Import Success
```
http://localhost/aplikasi/whitelist.php
```
Cek:
- ✅ Data muncul di tabel
- ✅ Role correct (HR=admin, Barista=user)
- ✅ Status = pending

## 🔒 CSRF TOKEN EXPLAINED

### Apa itu CSRF Token?
Token keamanan untuk mencegah form resubmission dan CSRF attack.

### Flow Normal:
1. User buka whitelist.php
2. Session dibuat, CSRF token di-generate
3. Token disimpan di:
   - Session: `$_SESSION['csrf_token']`
   - Form (hidden): `<input type="hidden" name="csrf_token" value="...">`
4. Saat submit, token dibandingkan
5. Jika match → OK, Jika tidak → Error

### Kapan Token Berubah?
- Logout
- Session expire (biasanya 24 jam)
- Clear cookies
- Server restart

## 🛠️ FIX APPLIED

### Perubahan di whitelist.php:

**Before:**
```php
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: whitelist.php?error=Invalid request...');
    exit;
}
```

**After:**
```php
// Added detailed logging
error_log("POST csrf_token: " . ($_POST['csrf_token'] ?? 'NOT SET'));
error_log("SESSION csrf_token: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("CSRF validation failed!");
    header('Location: whitelist.php?error=Invalid CSRF token...');
    exit;
}
```

## 📊 AUTO-DETECT ROLE

### Mapping Posisi → Role:

| Posisi | Role | Contoh |
|--------|------|--------|
| HR | admin | Human Resources |
| Finance | admin | Finance Manager |  
| Marketing | admin | Marketing Specialist |
| SCM | admin | Supply Chain Mgmt |
| Akuntan | admin | Accountant |
| Owner | admin | Business Owner |
| Superadmin | admin | System Admin |
| **Lainnya** | **user** | Barista, Kitchen, Server, dll |

### Code Implementation:
```php
function getRoleByPosisi($posisi) {
    $posisi_lower = strtolower(trim($posisi));
    $admin_positions = ['hr', 'finance', 'marketing', 'scm', 'akuntan', 'owner', 'superadmin'];
    return in_array($posisi_lower, $admin_positions) ? 'admin' : 'user';
}
```

**Features:**
- ✅ Case-insensitive (HR = hr = Hr)
- ✅ Auto-trim whitespace
- ✅ Extensible (mudah tambah posisi admin baru)

## 📝 QUICK FIX CHECKLIST

Jika import CSV error:

- [ ] Refresh halaman whitelist.php
- [ ] Check: Sudah login sebagai admin?
- [ ] Check: Session tidak expire?
- [ ] Clear browser cache/cookies
- [ ] Logout & login ulang
- [ ] Test dengan `test_import_csv.php`
- [ ] Check file CSV format (delimiter = `;`)
- [ ] Check file encoding (UTF-8)

## 🎯 CURRENT STATUS

### ✅ FIXED:
- Auto-detect role dari posisi di import CSV
- Logging untuk troubleshoot CSRF issue
- Test script untuk import tanpa login
- Template CSV dengan auto-role

### ⚠️ KNOWN ISSUE:
- CSRF token validation too strict
- Perlu refresh setelah long idle

### 💡 RECOMMENDATION:
Gunakan `test_import_csv.php` untuk import batch pertama kali, kemudian gunakan `whitelist.php` untuk manage data.

---

## 🚀 NEXT TRY:

1. **Buka terminal/browser baru (fresh session)**
2. **Login ke aplikasi**
3. **Langsung ke whitelist.php**
4. **Import CSV**

Jika masih error, gunakan alternative:
```
http://localhost/aplikasi/test_import_csv.php
```

---

**Created:** 2025-11-03  
**Status:** Debugging  
**Priority:** High
