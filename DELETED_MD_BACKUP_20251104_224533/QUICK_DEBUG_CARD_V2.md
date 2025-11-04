# 🚨 CSRF IMPORT DEBUG - QUICK REFERENCE CARD

## 🎯 THE PROBLEM
"Invalid request" error when importing CSV via whitelist.php, but debug_csrf.php shows all tokens present.

---

## 🔧 DEBUG TOOLS (In Order of Use)

### 1️⃣ VERIFY LOGGING WORKS
```
URL: http://localhost/aplikasi/test_logging.php
Goal: Confirm error_log() is working
Time: 30 seconds
```

### 2️⃣ TEST FORMS IN ISOLATION
```
URL: http://localhost/aplikasi/debug_import_forms.php
Goal: Test if basic form+CSRF+file upload works
Time: 2 minutes
Action: Try Method 2, watch Browser Console (F12)
```

### 3️⃣ FULL DIAGNOSTIC
```
URL: http://localhost/aplikasi/diagnostic_import.php
Goal: Complete CSRF flow analysis
Time: 2 minutes
Action: Submit test form, check all validation results
```

### 4️⃣ TEST ACTUAL IMPORT
```
URL: http://localhost/aplikasi/whitelist.php
Goal: Try actual import with debug logging
Time: 5 minutes
Actions:
  - Open Browser Console (F12)
  - Open Network Tab
  - Try to import CSV
  - Watch console for "CSRF token present" message
```

---

## 📊 WHAT TO LOOK FOR

### In Browser Console (F12 → Console):
```
✅ "Form submitting: POST"
✅ "CSRF token present: 9f3a7b2c..."
✅ "Form data being sent:"
✅ "  csrf_token: 9f3a7b2c..."
✅ "  import_file: [File object]"

❌ "WARNING: CSRF token NOT found"
❌ JavaScript errors
```

### In Network Tab (F12 → Network):
```
✅ POST request to whitelist.php
✅ Form Data includes csrf_token
✅ csrf_token is 64 characters long
✅ import_file is present

❌ csrf_token missing from Form Data
❌ Request fails or redirects immediately
```

### In Apache Error Log:
```bash
# Run in Terminal to monitor logs:
./monitor_csrf_logs.sh

# Or manually:
tail -f /Applications/XAMPP/xamppfiles/logs/error_log | grep -i whitelist
```

```
✅ "=== WHITELIST POST RECEIVED ==="
✅ "POST csrf_token: 9f3a7b2c... (length: 64)"
✅ "SESSION csrf_token: 9f3a7b2c... (length: 64)"
✅ "Token match: YES"
✅ "✅ CSRF validation passed"

❌ "❌ CSRF validation failed"
❌ "Reason: Token not posted"
❌ "Reason: Token mismatch"
```

---

## 🎯 DECISION TREE

### If debug_import_forms.php TEST PASSES:
→ **Issue is specific to whitelist.php**
→ **Action:** Compare form HTML between test page and whitelist.php
→ **Likely cause:** Multiple forms, JavaScript conflict, or form structure issue

### If debug_import_forms.php TEST FAILS:
→ **Issue is core form submission**
→ **Action:** Check session settings, cookies, browser compatibility
→ **Likely cause:** Session not persisting, CSRF token regenerating

### If Browser Console shows "CSRF token present":
→ **Token IS being sent from browser**
→ **Action:** Check server-side logs to see if it's received
→ **Likely cause:** Server-side processing issue, token mismatch on server

### If Browser Console shows "WARNING: CSRF token NOT found":
→ **Token is NOT in form HTML**
→ **Action:** Check if hidden input field exists in page source (View Page Source)
→ **Likely cause:** Session expired when rendering page, PHP error

### If Network Tab shows csrf_token in Form Data:
→ **Token is definitely being sent**
→ **Action:** Check server logs to see why validation fails
→ **Likely cause:** Token in session is different from token in POST

### If Network Tab shows csrf_token MISSING:
→ **Token not included in submission**
→ **Action:** Check if JavaScript is removing it, or form structure issue
→ **Likely cause:** JavaScript bug, form serialization problem

---

## 🚀 QUICK FIXES TO TRY

### Fix A: Clear Session and Regenerate Token
```
1. Visit: http://localhost/aplikasi/fix_csrf_tokens.php
2. Click "Force Regenerate All CSRF Tokens"
3. Try import again
```

### Fix B: Disable JavaScript Temporarily
```
In whitelist.php, add before </body>:

<script>
// TEST: Remove all event listeners
document.querySelectorAll('form').forEach(function(form) {
    var clone = form.cloneNode(true);
    form.parentNode.replaceChild(clone, form);
});
</script>
```

### Fix C: Test Without File (Isolate Issue)
```
In whitelist.php POST handler, add after CSRF validation passes:

if (isset($_POST['import'])) {
    error_log("✅ CSRF PASSED - Testing without file");
    header('Location: whitelist.php?success=CSRF+validation+passed');
    exit;
    // Comment out rest of file processing
}
```

---

## 📋 DATA COLLECTION CHECKLIST

When reporting back, include:

- [ ] Screenshot: debug_import_forms.php test result
- [ ] Screenshot: Browser Console from whitelist.php import attempt
- [ ] Screenshot: Network Tab showing POST request
- [ ] Text: Last 20 lines of error_log after import attempt
- [ ] Text: Exact error message shown in whitelist.php

```bash
# Get last 20 relevant log lines:
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log | grep -i "whitelist\|csrf" | tail -20
```

---

## 🔗 ALL TOOLS

| Tool | URL | Purpose |
|------|-----|---------|
| Logging Test | `/aplikasi/test_logging.php` | Verify logs work |
| Form Debugger | `/aplikasi/debug_import_forms.php` | Test forms ⭐ |
| Full Diagnostic | `/aplikasi/diagnostic_import.php` | Complete test |
| CSRF Debug | `/aplikasi/debug_csrf.php` | View tokens |
| Token Fix | `/aplikasi/fix_csrf_tokens.php` | Regenerate |
| Actual Import | `/aplikasi/whitelist.php` | Real import |

---

## ⚡ ONE-COMMAND START

```bash
# Terminal 1: Start log monitoring
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
./monitor_csrf_logs.sh

# Terminal 2: Open tools (run each line separately)
open http://localhost/aplikasi/debug_import_forms.php
open http://localhost/aplikasi/whitelist.php
```

---

## 🎓 EXPECTED OUTCOME

After running debug_import_forms.php and checking the console, you should know:

1. ✅ **Does CSRF token exist in form HTML?** (View Page Source)
2. ✅ **Is CSRF token being sent in FormData?** (Browser Console log)
3. ✅ **Is server receiving the token?** (Apache error_log)
4. ✅ **Do tokens match?** (Server log comparison)

**This will tell us EXACTLY where the breakdown is happening!**

---

Print this card or keep it open while debugging! 🚀
