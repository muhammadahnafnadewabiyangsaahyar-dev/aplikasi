# 🔧 Shift Confirmation - JSON Error Fix

## 📋 Issue Description

**Error Message:**
```
Error: Unexpected non-whitespace character after JSON at position 60 (line 1 column 61)
```

**Location:** Shift Confirmation Page (`shift_confirmation.php`)

**Cause:** Invalid JSON response from API due to PHP warnings/notices or extra output before JSON response.

---

## 🐛 Root Causes

### Common Causes of JSON Parse Errors:

1. **PHP Warnings/Notices**
   - Undefined variables
   - Array key warnings
   - Include file errors

2. **Extra Output**
   - Whitespace before `<?php`
   - Echo statements before JSON
   - HTML in API file

3. **Encoding Issues**
   - BOM (Byte Order Mark) in file
   - UTF-8 encoding problems

4. **Session Output**
   - Session warnings
   - Cookie headers after output

---

## ✅ Solutions Implemented

### 1. Output Buffering in API

**File:** `api_shift_confirmation.php`

```php
<?php
// ADDED: Output buffering to catch any accidental output
ob_start();

session_start();
include 'connect.php';

// Clear any previous output
ob_end_clean();

// Set JSON header
header('Content-Type: application/json');

// ... rest of the code
```

**Benefits:**
- Captures any accidental output
- Clears it before JSON response
- Prevents "headers already sent" errors

### 2. Improved Error Handling in JavaScript

**File:** `shift_confirmation.php`

```javascript
try {
    const response = await fetch('api_shift_confirmation.php', {
        method: 'POST',
        body: formData
    });
    
    // Check HTTP status
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    // Get raw response text first
    const responseText = await response.text();
    console.log('API Response:', responseText);
    
    // Try to parse JSON with error handling
    let result;
    try {
        result = JSON.parse(responseText);
    } catch (parseError) {
        console.error('JSON Parse Error:', parseError);
        console.error('Response Text:', responseText);
        throw new Error('Invalid JSON response from server');
    }
    
    // Process result
    if (result.status === 'success') {
        showAlert(result.message, 'success');
        setTimeout(() => location.reload(), 1500);
    } else {
        showAlert(result.message || 'Terjadi kesalahan', 'error');
    }
} catch (error) {
    console.error('API Error:', error);
    showAlert('Error: ' + error.message, 'error');
}
```

**Benefits:**
- Shows exact error in console
- Displays raw response for debugging
- Clear error messages to user
- Prevents silent failures

---

## 🔍 Debugging Steps

### Step 1: Check API Response

Open Browser Console and check:

```javascript
// Look for this log
API Response: {"status":"success","message":"Shift berhasil dikonfirmasi"}
```

**If you see:**
```
API Response: Warning: Undefined variable... {"status":"success",...}
```
→ There's PHP warning before JSON!

### Step 2: Test API Directly

```bash
curl -X POST http://localhost/aplikasi/api_shift_confirmation.php \
  -F "shift_id=1" \
  -F "status=confirmed" \
  -b "PHPSESSID=your_session_id"
```

**Expected Output:**
```json
{"status":"success","message":"Shift berhasil dikonfirmasi"}
```

**Bad Output (has error):**
```
<br />
<b>Notice</b>:  Undefined variable...
{"status":"success","message":"..."}
```

### Step 3: Check PHP Error Log

```bash
tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log
```

Look for warnings/errors when submitting confirmation.

---

## 📝 Status Field Values

### Database Enum:
```sql
status_konfirmasi ENUM('pending','confirmed','declined')
```

### Valid Values:
- `'pending'` - Default, awaiting confirmation
- `'confirmed'` - Employee confirmed the shift
- `'declined'` - Employee declined the shift

### ⚠️ Note:
Other parts of system use `'approved'` instead of `'confirmed'`. This is OK as long as:
- API uses correct enum values
- Frontend sends correct status
- Database has correct enum definition

---

## 🧪 Testing Checklist

### Test Confirm Flow:
1. [ ] Navigate to shift_confirmation.php
2. [ ] Click "✓ Konfirmasi" on a pending shift
3. [ ] Check console for "API Response" log
4. [ ] Verify JSON is valid
5. [ ] Confirm success message shows
6. [ ] Page reloads with updated status

### Test Decline Flow:
1. [ ] Click "✗ Tolak" on a pending shift
2. [ ] Modal opens for reason
3. [ ] Submit with/without reason
4. [ ] Check console for valid JSON
5. [ ] Verify success message
6. [ ] Page reloads with declined status

### Test Error Cases:
1. [ ] Test with invalid shift_id
2. [ ] Test without session (logged out)
3. [ ] Test with invalid status value
4. [ ] Verify error messages display correctly

---

## 🛠️ Additional Fixes (If Error Persists)

### Fix 1: Check File Encoding

```bash
# Check for BOM
file /Applications/XAMPP/xamppfiles/htdocs/aplikasi/api_shift_confirmation.php

# Should show: "PHP script, UTF-8 Unicode text"
# NOT: "PHP script, UTF-8 Unicode (with BOM) text"
```

**Remove BOM if present:**
```bash
# Using sed
sed -i '1s/^\xEF\xBB\xBF//' api_shift_confirmation.php

# Or open in VS Code and save as "UTF-8" (not "UTF-8 with BOM")
```

### Fix 2: Check for Whitespace

```bash
# Check first character
od -c api_shift_confirmation.php | head -1

# Should start with: <   ?   p   h   p
# NOT with spaces or newlines
```

### Fix 3: Strict Error Reporting

Add to top of `api_shift_confirmation.php`:

```php
<?php
// Disable all output except JSON
error_reporting(0);
ini_set('display_errors', 0);

ob_start();
session_start();
// ... rest of code
```

**⚠️ Note:** Only for production! Keep errors ON during development.

---

## 📊 API Response Format

### Success Response:
```json
{
    "status": "success",
    "message": "Shift berhasil dikonfirmasi"
}
```

### Error Response:
```json
{
    "status": "error",
    "message": "Shift tidak ditemukan atau bukan milik Anda"
}
```

### All Possible Messages:

| Status | Message | Meaning |
|--------|---------|---------|
| error | "Unauthorized" | User not logged in |
| error | "Data tidak lengkap" | Missing shift_id or status |
| error | "Status tidak valid" | Status not 'confirmed' or 'declined' |
| error | "Shift tidak ditemukan..." | Invalid shift_id or not user's shift |
| error | "Gagal update status" | Database update failed |
| success | "Shift berhasil dikonfirmasi" | Confirmation successful |
| success | "Shift berhasil ditolak" | Decline successful |

---

## 🎯 Prevention Tips

### For API Files:

1. **Always use output buffering:**
   ```php
   <?php
   ob_start();
   // ... code ...
   ob_end_clean();
   header('Content-Type: application/json');
   ```

2. **No whitespace before <?php:**
   ```php
   <?php // First line, no space before
   ```

3. **No echo/print except JSON:**
   ```php
   // ❌ Don't do this
   echo "Debug: " . $var;
   echo json_encode($data);
   
   // ✅ Do this
   error_log("Debug: " . $var); // Logs to file
   echo json_encode($data);
   ```

4. **Proper error handling:**
   ```php
   try {
       // ... code ...
       echo json_encode(['status' => 'success']);
   } catch (Exception $e) {
       error_log($e->getMessage()); // Log, don't echo
       echo json_encode(['status' => 'error', 'message' => 'Internal error']);
   }
   ```

### For Frontend:

1. **Always log raw response:**
   ```javascript
   const responseText = await response.text();
   console.log('Raw API Response:', responseText);
   ```

2. **Separate parse from fetch:**
   ```javascript
   try {
       const result = JSON.parse(responseText);
   } catch (e) {
       console.error('Parse error:', e);
       console.error('Invalid JSON:', responseText);
   }
   ```

3. **Show helpful errors:**
   ```javascript
   showAlert('Server returned invalid data. Please contact admin.', 'error');
   ```

---

## 📚 Related Files

1. `api_shift_confirmation.php` - Backend API
2. `shift_confirmation.php` - Frontend page
3. `shift_assignments` table - Database

---

## ✅ Verification

After implementing fixes, test:

```javascript
// Console should show:
API Response: {"status":"success","message":"Shift berhasil dikonfirmasi"}

// NOT:
API Response: <b>Warning</b>: Undefined variable...{"status":"success"...}
```

---

**Status:** ✅ Fixed with output buffering and improved error handling  
**Date:** November 6, 2025  
**Version:** 1.0
