# 🔍 TROUBLESHOOTING GUIDE - "Invalid Request" Error

## 🚨 Problem: "Invalid request. Please try again."

Error ini terjadi ketika CSRF token validation gagal. Berikut adalah panduan lengkap untuk mendiagnosa dan memperbaiki masalah.

---

## 📋 Quick Diagnosis Steps

### Step 1: Use Debug Tool
Akses file debug khusus yang sudah dibuat:
```
http://localhost/aplikasi/debug_csrf.php
```

Debug tool ini akan menampilkan:
- ✅ Status session
- ✅ Semua CSRF tokens yang ada
- ✅ User login info
- ✅ Test forms untuk verify tokens

### Step 2: Check Browser Console
1. Buka import page
2. Press `F12` untuk buka DevTools
3. Klik tab "Console"
4. Submit form dan lihat log debug

---

## 🔧 Common Causes & Solutions

### Cause 1: Token Not Generated
**Symptoms:**
- Debug shows: `csrf_token_import: ❌ NO`

**Solution:**
```php
// File sudah auto-generate token, tapi bisa manual generate:
$_SESSION['csrf_token_import'] = bin2hex(random_bytes(32));
```

**Quick Fix:**
1. Go to: `http://localhost/aplikasi/debug_csrf.php`
2. Click: "Generate All Tokens"
3. Try import again

---

### Cause 2: Session Not Active
**Symptoms:**
- Session Status: `❌ NOT ACTIVE`
- User not logged in

**Solution:**
1. Make sure `session_start()` is called
2. Check session.save_path is writable:
   ```bash
   # Check current save path
   php -i | grep session.save_path
   
   # Check if writable
   ls -ld /path/to/session/save/path
   ```

3. Restart XAMPP:
   ```bash
   sudo /Applications/XAMPP/xamppfiles/xampp stop
   sudo /Applications/XAMPP/xamppfiles/xampp start
   ```

---

### Cause 3: Token Mismatch Between Pages
**Symptoms:**
- Form token ≠ Session token
- Token length different

**Root Cause:**
`whitelist.php` uses `$_SESSION['csrf_token']`
`import_csv_enhanced.php` uses `$_SESSION['csrf_token_import']`

**Solution:**
Kedua token harus berbeda untuk security, tapi pastikan form menggunakan token yang benar.

**Verify:**
1. View page source di import page
2. Find: `<input type="hidden" name="csrf_token" value="..."`
3. Copy token value (first 20 chars)
4. Go to debug_csrf.php
5. Compare dengan session token

---

### Cause 4: Browser Cache
**Symptoms:**
- Old token cached in form
- Token not updated after refresh

**Solution:**
1. Hard refresh: `Cmd + Shift + R` (Mac) or `Ctrl + Shift + R` (Windows)
2. Clear browser cache
3. Try incognito mode
4. Use different browser

---

### Cause 5: Session Cookie Issues
**Symptoms:**
- Session ID changes on each request
- Token regenerated constantly

**Solution:**
Check session cookie settings in `php.ini`:
```ini
session.cookie_httponly = 1
session.cookie_secure = 0  # Set to 0 for localhost
session.cookie_samesite = "Lax"
```

Restart XAMPP after changing `php.ini`.

---

### Cause 6: Form Submission Issue
**Symptoms:**
- Token present but not sent in POST
- Form submits but POST data empty

**Solution:**
Check form attributes:
```html
<!-- Must have these attributes -->
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="...">
    <!-- other fields -->
</form>
```

**Verify via JavaScript:**
```javascript
// In browser console
document.querySelector('input[name="csrf_token"]').value
```

---

## 🧪 Testing Protocol

### Test 1: Verify Token Generation
```bash
# Access debug page
open http://localhost/aplikasi/debug_csrf.php

# Expected result:
# csrf_token_import: ✅ YES (64 chars)
```

### Test 2: Verify Form Token
```javascript
// In browser console on import page
const token = document.querySelector('input[name="csrf_token"]');
console.log('Token exists:', !!token);
console.log('Token value:', token ? token.value : 'NOT FOUND');
console.log('Token length:', token ? token.value.length : 0);

// Expected:
// Token exists: true
// Token value: [64 char string]
// Token length: 64
```

### Test 3: Verify POST Data
```php
// Add this temporarily at the top of import_csv_enhanced.php
error_log('POST data: ' . print_r($_POST, true));
error_log('SESSION csrf_token_import: ' . ($_SESSION['csrf_token_import'] ?? 'NOT SET'));

// Then check error log:
tail -f /Applications/XAMPP/xamppfiles/logs/error_log
```

### Test 4: Manual Token Check
1. Open import page
2. View source (Cmd+U)
3. Search for: `csrf_token`
4. Copy the value attribute
5. Go to debug_csrf.php
6. Compare tokens

---

## 📊 Debug Checklist

Run through this checklist:

- [ ] Session is active (check debug_csrf.php)
- [ ] User is logged in (user_id exists)
- [ ] Token `csrf_token_import` exists in session
- [ ] Token is in form HTML (view source)
- [ ] Token length is 64 characters
- [ ] Form has `method="post"`
- [ ] Form has `enctype="multipart/form-data"`
- [ ] JavaScript debugFormSubmit() logs show token
- [ ] Browser console shows no errors
- [ ] XAMPP is running properly
- [ ] No session.save_path permission issues

---

## 🔬 Advanced Debugging

### Enable PHP Error Logging
```php
// Add to top of import_csv_enhanced.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log everything
error_log('=== IMPORT CSV DEBUG START ===');
error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
error_log('POST keys: ' . implode(', ', array_keys($_POST)));
error_log('POST csrf_token: ' . ($_POST['csrf_token'] ?? 'NOT SET'));
error_log('SESSION csrf_token_import: ' . ($_SESSION['csrf_token_import'] ?? 'NOT SET'));
error_log('Tokens match: ' . (($_POST['csrf_token'] ?? '') === ($_SESSION['csrf_token_import'] ?? '') ? 'YES' : 'NO'));
```

### Check Session Files
```bash
# Find session save path
php -i | grep session.save_path

# List session files
ls -la /path/to/sessions/

# Read session file (replace PHPSESSID with your session ID)
cat /path/to/sessions/sess_PHPSESSID
```

### Network Tab Analysis
1. Open DevTools → Network tab
2. Submit form
3. Click on the POST request
4. Check "Payload" or "Form Data"
5. Verify `csrf_token` is sent

---

## 💡 Prevention Tips

### 1. Always Use Debug Mode During Development
```php
// Add this flag at top of import files
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_log('Debug info...');
}
```

### 2. Consistent Token Naming
```php
// Each page should have unique token name
$_SESSION['csrf_token_import']       // For import_csv_enhanced.php
$_SESSION['csrf_token_import_smart'] // For import_csv_smart.php
$_SESSION['csrf_token']              // For whitelist.php and others
```

### 3. Token Refresh Strategy
```php
// Regenerate token after successful action
if ($success) {
    $_SESSION['csrf_token_import'] = bin2hex(random_bytes(32));
}
```

---

## 📞 Still Not Working?

If masih error setelah semua step:

### 1. Check PHP Error Log
```bash
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log
```

### 2. Check Apache Error Log
```bash
tail -100 /Applications/XAMPP/xamppfiles/logs/apache_error_log
```

### 3. Restart Everything
```bash
# Stop XAMPP
sudo /Applications/XAMPP/xamppfiles/xampp stop

# Wait 5 seconds
sleep 5

# Start XAMPP
sudo /Applications/XAMPP/xamppfiles/xampp start

# Clear browser cache and cookies
# Try again in incognito mode
```

### 4. Test with Minimal Code
Create `test_csrf.php`:
```php
<?php
session_start();

if (!isset($_SESSION['test_token'])) {
    $_SESSION['test_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['token'] === $_SESSION['test_token']) {
        echo "✅ SUCCESS: Token valid!";
    } else {
        echo "❌ FAIL: Token invalid";
        echo "<br>Posted: " . ($_POST['token'] ?? 'NOT SET');
        echo "<br>Session: " . $_SESSION['test_token'];
    }
    exit;
}
?>
<form method="post">
    <input type="hidden" name="token" value="<?= $_SESSION['test_token'] ?>">
    <button type="submit">Test</button>
</form>
```

---

## 🎯 Expected Behavior After Fix

✅ No "Invalid request" error
✅ Import proceeds normally
✅ Debug console shows token present
✅ Report displayed after import
✅ Session remains active

---

## 📚 Related Files

- `import_csv_enhanced.php` - Main import file (Mode 1 & 2)
- `import_csv_smart.php` - Smart import file (Mode 3)
- `debug_csrf.php` - Debug tool (NEW)
- `whitelist.php` - Uses different token name
- `functions_role.php` - Role detection function

---

## 🔄 Update Log

**2024-01-20:**
- Added comprehensive debugging
- Created debug_csrf.php tool
- Added JavaScript form validation
- Added visual debug indicators
- Added detailed error messages

---

**Last Updated:** 2024-01-20
**Status:** Debug tools ready
**Next Step:** Run debug_csrf.php to diagnose
