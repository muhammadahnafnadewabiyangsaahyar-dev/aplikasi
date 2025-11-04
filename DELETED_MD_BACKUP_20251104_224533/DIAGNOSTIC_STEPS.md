# 🔍 CSRF Import Issue - Step-by-Step Diagnostic Guide

## Problem Summary
User reports "Invalid request" error when trying to import CSV via `whitelist.php`, despite all CSRF tokens being present in the session (confirmed via `debug_csrf.php`).

## Files Created for Diagnosis
1. **test_logging.php** - Verify error_log() is working
2. **diagnostic_import.php** - Comprehensive CSRF import testing tool
3. This document - Step-by-step troubleshooting guide

---

## 🎯 Diagnostic Steps (Follow in Order)

### Step 1: Verify Error Logging is Working
**What to do:**
1. Open browser: `http://localhost/aplikasi/test_logging.php`
2. Check if page loads and shows PHP configuration
3. Check if `test_app.log` file was created in the aplikasi folder

**Expected result:**
- Page shows logging configuration
- `test_app.log` file exists with timestamp
- Apache error_log shows "TEST ERROR LOG" message

**If this fails:**
- Error logging is not configured properly
- Check PHP configuration: `display_errors`, `log_errors`

---

### Step 2: Test CSRF Token Flow with Diagnostic Tool
**What to do:**
1. Open browser: `http://localhost/aplikasi/diagnostic_import.php`
2. Review the "Current Session State" section
   - Verify CSRF token is present
   - Verify you're logged in as admin
3. Select a CSV file (or create a test CSV)
4. Check "Show CSRF token being sent" checkbox
5. Click "Test Import Submission"
6. Review the diagnostic results

**Expected result:**
- ✅ CSRF Token in POST: present
- ✅ CSRF Token in SESSION: present
- ✅ CSRF Token Validation: Tokens match!

**If tokens don't match:**
- Token is being regenerated between page load and submission
- Session is being reset
- Multiple tabs causing token conflicts

---

### Step 3: Check Actual Import in whitelist.php
**What to do:**
1. Open browser: `http://localhost/aplikasi/whitelist.php`
2. Try the import form with a CSV file
3. Watch the browser console (F12 → Console tab) for any JavaScript errors
4. After clicking Import, immediately check logs:
   ```bash
   tail -f /Applications/XAMPP/xamppfiles/logs/error_log | grep -i whitelist
   ```

**Expected result:**
- Logs show "=== WHITELIST POST RECEIVED ==="
- Logs show token validation details
- Import succeeds OR detailed error message

**If "Invalid request" error appears:**
- Check logs for the EXACT reason (token not posted or token mismatch)
- Token might be missing from POST due to form issue

---

### Step 4: Inspect Form HTML in Browser
**What to do:**
1. Open `whitelist.php` in browser
2. Press F12 (Developer Tools)
3. Go to Inspector/Elements tab
4. Find the import form: `<form method="post" enctype="multipart/form-data">`
5. Check if hidden CSRF input exists:
   ```html
   <input type="hidden" name="csrf_token" value="[64-char-token]">
   ```
6. Verify the token value matches the session token from `debug_csrf.php`

**Expected result:**
- Hidden input with name="csrf_token" exists
- Value is 64 characters long (hex string)
- Value matches session token

**If hidden input is missing or empty:**
- PHP session not accessible when rendering form
- Template rendering issue

---

### Step 5: Monitor Form Submission in Network Tab
**What to do:**
1. Open `whitelist.php`
2. Press F12 → Network tab
3. Click "Preserve log"
4. Submit the import form
5. Find the POST request to `whitelist.php`
6. Click on it → Go to "Request" or "Payload" tab
7. Check if `csrf_token` is in the Form Data

**Expected result:**
- csrf_token is present in Form Data
- Value is 64 characters
- import_file is present

**If csrf_token is missing from Form Data:**
- JavaScript might be interfering with form submission
- Form might have multiple submit buttons causing conflict
- File upload might be causing form data to be lost

---

## 🔧 Quick Fixes to Try

### Fix 1: Disable JavaScript Form Handler (Temporary Test)
Add this to `whitelist.php` just before `</body>`:
```html
<script>
// Temporarily disable the form submit handler
document.querySelectorAll('form').forEach(function(form) {
    form.replaceWith(form.cloneNode(true));
});
</script>
```

### Fix 2: Add Console Logging to Form Submit
Add this to `whitelist.php` before `</body>`:
```html
<script>
document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        console.log('Form submitting:', this);
        console.log('Form data:', new FormData(this));
        console.log('CSRF token field:', this.querySelector('input[name="csrf_token"]'));
    });
});
</script>
```

### Fix 3: Force Token Persistence
In `whitelist.php`, after session_start(), add:
```php
// Force token to persist
if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// NEVER regenerate in the same session!
```

---

## 📊 Data Collection Checklist

When reporting results, please provide:
- [ ] Screenshot of `test_logging.php` output
- [ ] Screenshot of `diagnostic_import.php` before submission (showing session state)
- [ ] Screenshot of `diagnostic_import.php` after submission (showing results)
- [ ] Screenshot of browser console (F12) when submitting in `whitelist.php`
- [ ] Screenshot of Network tab showing POST data
- [ ] Last 20 lines of Apache error_log after import attempt:
  ```bash
  tail -20 /Applications/XAMPP/xamppfiles/logs/error_log
  ```
- [ ] Contents of `test_app.log` if it exists

---

## 🎯 Most Likely Root Causes (Ranked)

1. **JavaScript form handler interfering** (60% likely)
   - The submit button disabling code might be preventing token from being sent
   - Solution: Temporarily disable or modify the JavaScript

2. **File upload causing form data loss** (20% likely)
   - multipart/form-data might not be properly posting hidden fields
   - Solution: Check enctype and verify token in FormData

3. **Session timing issue** (10% likely)
   - Token regenerating between page load and submit
   - Solution: Never regenerate token during the same session

4. **Multiple form conflict** (10% likely)
   - Page has multiple forms, wrong one being submitted
   - Solution: Add unique identifiers to each form

---

## 🚀 Next Actions

1. **First**: Run through all 5 diagnostic steps above
2. **Second**: Try Quick Fixes 1-3 one at a time
3. **Third**: Collect all data from checklist
4. **Fourth**: Report findings with screenshots

Once we have the diagnostic results, we can pinpoint the exact issue and implement a permanent fix.

---

## 📝 Tools Reference

- **test_logging.php** - Test if error_log() works
- **diagnostic_import.php** - Full CSRF import testing
- **debug_csrf.php** - View session tokens and test forms
- **fix_csrf_tokens.php** - Force regenerate all CSRF tokens

All tools are in: `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/`
