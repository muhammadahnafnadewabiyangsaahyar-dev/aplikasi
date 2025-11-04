# 🎯 CSRF Import Debug - Complete Toolkit

## Current Status
✅ All CSRF tokens confirmed present in session (via debug_csrf.php)
❌ "Invalid request" error still appearing in whitelist.php during CSV import
🔍 Need to diagnose WHERE and WHY the CSRF validation is failing

---

## 🛠️ Debug Tools Created (Use in This Order)

### 1. **test_logging.php** - Verify Logging Works
📍 **URL:** `http://localhost/aplikasi/test_logging.php`

**Purpose:** Confirm that error_log() is working and we can see debug messages

**What to check:**
- ✅ Page loads without errors
- ✅ Shows PHP logging configuration
- ✅ Creates `test_app.log` file
- ✅ Writes to Apache error_log

**Action:** Visit this page first to ensure our debug logs will work.

---

### 2. **debug_import_forms.php** - Side-by-Side Form Testing
📍 **URL:** `http://localhost/aplikasi/debug_import_forms.php`

**Purpose:** Test form submission with detailed console logging

**What to do:**
1. Open page in browser
2. Press F12 to open Developer Console
3. Test Method 1 (standard form)
4. Test Method 2 (with debug logging)
5. Check console output and test results

**What to look for:**
- ✅ CSRF token present in console logs
- ✅ FormData includes csrf_token
- ✅ Test passes with green success message

**If test FAILS here:** Form submission mechanism is broken
**If test PASSES here:** Issue is specific to whitelist.php

---

### 3. **diagnostic_import.php** - Full Import Diagnostic
📍 **URL:** `http://localhost/aplikasi/diagnostic_import.php`

**Purpose:** Complete diagnostic of CSRF import flow

**What to do:**
1. Review "Current Session State" section
2. Select a CSV file
3. Check "Show CSRF token being sent"
4. Submit form
5. Review all diagnostic results

**What to look for:**
- ✅ All checks green (POST token present, SESSION token present, tokens match)
- ❌ Any red errors indicate the problem area

---

### 4. **whitelist.php** (Updated with Debug Logging)
📍 **URL:** `http://localhost/aplikasi/whitelist.php`

**Purpose:** The actual import page with enhanced logging

**What changed:**
- ✅ Added detailed console.log() for form submission
- ✅ Enhanced JavaScript to log CSRF token presence
- ✅ Better error messages
- ✅ Extensive server-side logging

**What to do:**
1. Open page
2. Open Browser Console (F12)
3. Try to import a CSV file
4. Watch console for debug messages
5. Check Apache error_log for server-side logs

**Expected console output:**
```
Form submitting: POST
CSRF token present: 9f3a7b2c1d4e5f6a7b8c...
Form data being sent:
  csrf_token: 9f3a7b2c1d4e5f6a7b8c...
  import_file: [File object]
```

---

### 5. **debug_csrf.php** - Session/Token Inspector
📍 **URL:** `http://localhost/aplikasi/debug_csrf.php`

**Purpose:** View and manage CSRF tokens in session

**When to use:** To verify tokens exist and regenerate if needed

---

## 📋 Step-by-Step Debug Process

### Step 1: Verify Logging (1 minute)
```
1. Visit: http://localhost/aplikasi/test_logging.php
2. Confirm page loads and shows config
3. Check if test_app.log was created in aplikasi folder
```

### Step 2: Test Forms in Isolation (5 minutes)
```
1. Visit: http://localhost/aplikasi/debug_import_forms.php
2. Open Browser Console (F12 → Console tab)
3. Test Method 2 form with a CSV file
4. Observe console output (should show CSRF token)
5. Check if test passes or fails
```

### Step 3: Full Diagnostic Test (5 minutes)
```
1. Visit: http://localhost/aplikasi/diagnostic_import.php
2. Review session state (confirm token exists)
3. Select a CSV file
4. Submit form
5. Review diagnostic results panel
```

### Step 4: Test Actual Import in whitelist.php (10 minutes)
```
1. Visit: http://localhost/aplikasi/whitelist.php
2. Open Browser Console (F12 → Console tab)
3. Open Network tab (F12 → Network)
4. Click "Preserve log" in Network tab
5. Select a CSV file for import
6. Click "Import" button
7. Watch Console for JavaScript debug output
8. Check Network tab for POST request details
9. Look at Response tab to see error message
```

### Step 5: Check Server Logs
```bash
# In Terminal, run this to monitor logs in real-time:
tail -f /Applications/XAMPP/xamppfiles/logs/error_log | grep -i "whitelist\|csrf"

# Then try importing in whitelist.php
# Watch for log messages showing token validation
```

---

## 🔍 What to Look For (Checklist)

### In Browser Console (JavaScript):
- [ ] "Form submitting: POST" message appears
- [ ] "CSRF token present: ..." message appears
- [ ] No "WARNING: CSRF token NOT found" message
- [ ] FormData shows csrf_token field
- [ ] No JavaScript errors

### In Network Tab:
- [ ] POST request to whitelist.php is made
- [ ] Request Payload/Form Data includes csrf_token
- [ ] csrf_token value is 64 characters
- [ ] import_file is present

### In Apache error_log:
- [ ] "=== WHITELIST POST RECEIVED ===" message
- [ ] "POST csrf_token: ..." shows token is present
- [ ] "SESSION csrf_token: ..." shows token in session
- [ ] "Token match: YES" appears
- [ ] "✅ CSRF validation passed" appears
- OR specific error message showing why it failed

---

## 🎯 Expected Outcomes

### ✅ If Tests PASS in debug_import_forms.php:
**Conclusion:** Form submission mechanism works fine
**Next:** Issue is specific to whitelist.php
**Solution:** Compare HTML/JavaScript between working test and actual whitelist.php

### ❌ If Tests FAIL in debug_import_forms.php:
**Conclusion:** Core form submission issue
**Possible causes:**
- Session not persisting between page load and submit
- Browser blocking cookies
- CSRF token being regenerated
**Solution:** Focus on session management

### ⚠️ If Tests PASS but whitelist.php FAILS:
**Conclusion:** Something specific to whitelist.php
**Possible causes:**
- Multiple forms on page causing conflict
- Different JavaScript handling
- Form action/method mismatch
**Solution:** Compare whitelist.php form structure with test forms

---

## 📊 Data to Collect

When you run through the tests, please collect:

1. **Screenshot of test_logging.php** output
2. **Screenshot of debug_import_forms.php** after Method 2 test
3. **Screenshot of Browser Console** from whitelist.php during import attempt
4. **Screenshot of Network tab** showing POST request data
5. **Last 20 lines of error_log:**
   ```bash
   tail -20 /Applications/XAMPP/xamppfiles/logs/error_log
   ```
6. **Any error messages** shown in whitelist.php

---

## 🚀 Quick Fixes to Try

### Fix A: Disable JavaScript Form Handler Temporarily
Add this to whitelist.php just before `</body>`:
```html
<script>
// Disable all form event listeners temporarily
document.querySelectorAll('form').forEach(function(form) {
    var newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);
});
</script>
```

### Fix B: Simplify CSRF Validation
In whitelist.php, change CSRF check to log more detail:
```php
if (!isset($_POST['csrf_token'])) {
    error_log("FAIL: csrf_token not in POST");
    error_log("POST keys: " . implode(', ', array_keys($_POST)));
    // ... rest of error handling
} elseif ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("FAIL: token mismatch");
    error_log("POST token: " . $_POST['csrf_token']);
    error_log("SESSION token: " . $_SESSION['csrf_token']);
    // ... rest of error handling
}
```

### Fix C: Test Without File Upload
Temporarily comment out file upload requirement to test if multipart/form-data is causing issues:
```php
// In whitelist.php POST handler
if (isset($_POST['import'])) {
    error_log("Import button clicked!");
    error_log("CSRF validated successfully");
    // Comment out file processing temporarily
    // Just test if we can get past CSRF validation
    header('Location: whitelist.php?success=' . urlencode('CSRF validation passed!'));
    exit;
}
```

---

## 📞 Next Steps

1. **Run through all tests** in order (Steps 1-5 above)
2. **Collect screenshots and logs** as listed
3. **Try Quick Fixes** if pattern emerges
4. **Report findings** with specific error messages and logs

Once we have the diagnostic data, we can pinpoint the exact issue and implement a permanent fix.

---

## 📁 Files Reference

All tools are in: `/Applications/XAMPP/xamppfiles/htdocs/aplikasi/`

- `test_logging.php` - Verify error_log() works
- `debug_import_forms.php` - Side-by-side form testing ⭐
- `diagnostic_import.php` - Full CSRF import diagnostic
- `whitelist.php` - Actual import page (now with debug logging)
- `debug_csrf.php` - Session token inspector
- `fix_csrf_tokens.php` - Force regenerate tokens
- `DIAGNOSTIC_STEPS.md` - Detailed troubleshooting guide
- This file: `DEBUG_TOOLKIT_SUMMARY.md`

---

## 💡 Key Insights

**Based on analysis so far:**

1. ✅ **Tokens ARE in session** (confirmed by user via debug_csrf.php)
2. ❓ **Unknown:** Are tokens being POSTED with the form?
3. ❓ **Unknown:** Is JavaScript interfering with form submission?
4. 🎯 **Most likely issue:** Form data not including csrf_token field during submission

**The enhanced JavaScript logging in whitelist.php will show us if the CSRF token is present in the FormData being sent!**

---

Good luck with the diagnostics! The console logging should tell us exactly what's happening. 🚀
