# 🚀 QUICK DEBUG REFERENCE CARD

## ❌ Error: "Invalid request. Please try again."

---

## 🎯 3-Step Quick Fix

### Step 1: Open Debug Tool (30 seconds)
```
http://localhost/aplikasi/debug_csrf.php
```
**Look for:**
- ✅ Session ACTIVE?
- ✅ csrf_token_import exists?
- ✅ User logged in?

**Quick Action:** Click "Generate All Tokens"

---

### Step 2: Test Import (1 minute)
```
1. Go to import page
2. Press F12 (open console)
3. Select CSV file
4. Click Import
5. Check console logs
```

**Expected in console:**
```
=== FORM SUBMIT DEBUG ===
CSRF Token Value: [64 chars]
File Selected: your-file.csv
Form validation passed. Submitting...
```

---

### Step 3: Check Debug Box (if error)
If error still occurs, page will show:
```
🔍 DEBUG INFORMATION:
[csrf_error] => Array
    (
        [posted_token] => abc...
        [session_token] => xyz...
        [tokens_match] => NO  ← Problem here!
    )
```

---

## 💊 Quick Remedies

### Remedy A: Hard Refresh
```
Mac: Cmd + Shift + R
Windows: Ctrl + Shift + R
```

### Remedy B: Generate Tokens
```
http://localhost/aplikasi/debug_csrf.php?action=generate_tokens
```

### Remedy C: Restart XAMPP
```bash
sudo /Applications/XAMPP/xamppfiles/xampp restart
```

### Remedy D: Clear Browser
```
1. Clear cache
2. Clear cookies for localhost
3. Try incognito mode
```

---

## 🔍 Debug Checklist

Quick verification:
- [ ] Open: debug_csrf.php
- [ ] Session: ACTIVE ✅
- [ ] Token: csrf_token_import EXISTS ✅
- [ ] User: user_id EXISTS ✅
- [ ] Role: admin ✅
- [ ] Form: has csrf_token input ✅
- [ ] Token: 64 chars length ✅

**All checked?** → Should work!

---

## 🎨 Visual Clues

### Good (No Error):
```
✅ Import complete! Imported: 5, Updated: 0, Skipped: 0, Errors: 0
📊 Import Report
[Green rows with IMPORTED status]
```

### Bad (Error):
```
❌ Invalid CSRF token. Please refresh the page and try again.
🔍 DEBUG INFORMATION:
[Red box with debug array]
```

---

## 🧪 Quick Tests

### Test 1: Token in Form?
```javascript
// Browser console
document.querySelector('input[name="csrf_token"]').value.length
// Should return: 64
```

### Test 2: Token in Session?
```
// Check debug_csrf.php
csrf_token_import: ✅ YES (64 chars)
```

### Test 3: Tokens Match?
```
// On error, debug box shows:
[tokens_match] => NO  ← If NO, token mismatch!
```

---

## 📞 Still Stuck?

### Option 1: Check Error Logs
```bash
tail -50 /Applications/XAMPP/xamppfiles/logs/error_log
```

### Option 2: Read Full Guide
```
TROUBLESHOOTING_CSRF.md
```

### Option 3: Check All Docs
```
- DEBUGGING_SUMMARY.md
- TROUBLESHOOTING_CSRF.md
- IMPORT_CSV_GUIDE.md
```

---

## 🎯 Expected Flow (No Error)

```
1. Load import page
   ↓ Token status box shows: ✅ Active
   
2. Select CSV file
   ↓ File input populated
   
3. Click Import
   ↓ Console: "Form validation passed. Submitting..."
   
4. Processing...
   ↓ Server validates token
   
5. Success!
   ↓ Report shows: "Import complete!"
```

---

## 🔐 Token Quick Reference

| Token Name | Used By | Location |
|------------|---------|----------|
| `csrf_token` | whitelist.php | `$_SESSION['csrf_token']` |
| `csrf_token_import` | import_csv_enhanced.php | `$_SESSION['csrf_token_import']` |
| `csrf_token_import_smart` | import_csv_smart.php | `$_SESSION['csrf_token_import_smart']` |

**Important:** Each page uses its own token for security!

---

## ⚡ Ultra Quick Fix (1 minute)

```
1. http://localhost/aplikasi/debug_csrf.php
2. Click: "Generate All Tokens"
3. Go to import page
4. Hard refresh: Cmd+Shift+R
5. Try import
```

**Works?** ✅ Done!
**Still error?** Check console & debug box

---

## 📊 Debug Priority

1. **First:** Check debug_csrf.php
2. **Second:** Check browser console
3. **Third:** Check debug box on error
4. **Last:** Check error logs

---

**Quick Link:** http://localhost/aplikasi/debug_csrf.php

**Remember:** Token must be 64 chars, in session AND in form!

---

**Created:** 2024-01-20
**Purpose:** Ultra-quick debugging reference
