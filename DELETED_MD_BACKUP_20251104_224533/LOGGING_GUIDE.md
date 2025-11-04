# 📊 LOGGING GUIDE - Import CSV Debug

## 🎯 Purpose
Comprehensive logging telah ditambahkan untuk trace setiap langkah import CSV dan identify exact error point.

---

## 📝 What's Being Logged

### **1. Session Initialization (import_csv_enhanced.php)**
```
=== IMPORT CSV ENHANCED DEBUG ===
Session ID: [session_id]
Session status: 2 (PHP_SESSION_ACTIVE)
User ID: [user_id]
csrf_token_import before: NOT SET/EXISTS
csrf_token_import after: EXISTS (64 chars)
```

### **2. POST Request Received**
```
=== POST REQUEST RECEIVED ===
REQUEST_METHOD: POST
POST keys: csrf_token, import_mode, import, ...
FILES keys: import_file
```

### **3. Token Validation**
```
POST csrf_token: abc123... (length: 64)
SESSION csrf_token_import: xyz789... (length: 64)
✅ CSRF VALIDATION PASSED!
```
or
```
❌ CSRF VALIDATION FAILED!
Reason: Token mismatch
```

### **4. Whitelist.php Import**
```
=== WHITELIST POST RECEIVED ===
REQUEST_METHOD: POST
POST keys: csrf_token, import, ...
POST action buttons: IMPORT
POST csrf_token: abc123... (length: 64)
SESSION csrf_token: xyz789... (length: 64)
Token match: YES/NO
✅ CSRF validation passed in whitelist.php
Processing IMPORT action...
```

---

## 🔍 How to Monitor Logs

### **Option 1: Real-Time Monitor (Recommended)**
```bash
# Run the monitoring script
./monitor_logs.sh
```

**Features:**
- ✅ Real-time log streaming
- ✅ Color-coded output:
  - 🟢 Green: Success messages (✅)
  - 🔴 Red: Error messages (❌)
  - 🟡 Yellow: CSRF related
  - 🔵 Cyan: POST/SESSION data
  - ⚪ White: Normal logs
- ✅ Auto-scrolling
- ✅ Press Ctrl+C to stop

### **Option 2: Tail Command**
```bash
# Follow error log
tail -f /Applications/XAMPP/xamppfiles/logs/error_log
```

### **Option 3: View Last N Lines**
```bash
# View last 50 lines
tail -50 /Applications/XAMPP/xamppfiles/logs/error_log

# View last 100 lines
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log
```

### **Option 4: Grep Specific Logs**
```bash
# Only CSRF related
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log | grep CSRF

# Only import related
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log | grep "IMPORT CSV"

# Only errors
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log | grep "❌"

# Only success
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log | grep "✅"
```

---

## 🧪 Testing Workflow with Logs

### **Step 1: Start Log Monitor**
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
./monitor_logs.sh
```

### **Step 2: Open Import Page**
```
http://localhost/aplikasi/import_csv_enhanced.php
```

**Expected in logs:**
```
=== IMPORT CSV ENHANCED DEBUG ===
Session ID: [id]
Session status: 2
User ID: 1
csrf_token_import before: NOT SET
csrf_token_import after: EXISTS (64 chars)
```

### **Step 3: Submit Form**
Select CSV and click Import

**Expected in logs:**
```
=== POST REQUEST RECEIVED ===
REQUEST_METHOD: POST
POST keys: csrf_token, import_mode, import
FILES keys: import_file
POST csrf_token: abc123... (length: 64)
SESSION csrf_token_import: abc123... (length: 64)
✅ CSRF VALIDATION PASSED!
```

### **Step 4: Check for Errors**
If error occurs:
```
❌ CSRF VALIDATION FAILED!
Reason: Token mismatch
```

---

## 📊 Log Analysis

### **Scenario 1: Token Not Generated**
```
csrf_token_import before: NOT SET
csrf_token_import after: STILL NOT SET  ← PROBLEM!
```

**Diagnosis:** Token generation failed
**Solution:** Check session permissions, restart XAMPP

### **Scenario 2: Token Mismatch**
```
POST csrf_token: abc123... (length: 64)
SESSION csrf_token_import: xyz789... (length: 64)  ← DIFFERENT!
❌ CSRF VALIDATION FAILED!
Reason: Token mismatch
```

**Diagnosis:** Token changed between page load and submit
**Solution:** Don't refresh page between load and submit

### **Scenario 3: Token Not Posted**
```
POST csrf_token: NOT SET  ← PROBLEM!
SESSION csrf_token_import: xyz789... (length: 64)
❌ CSRF VALIDATION FAILED!
Reason: Token not posted
```

**Diagnosis:** Form missing CSRF token input
**Solution:** Check form HTML, ensure token field exists

### **Scenario 4: Whitelist Import Error**
```
=== WHITELIST POST RECEIVED ===
POST csrf_token: abc123... (length: 64)
SESSION csrf_token: xyz789... (length: 64)  ← USING DIFFERENT TOKEN NAME!
Token match: NO
❌ CSRF validation failed in whitelist.php!
```

**Diagnosis:** Using wrong token name (`csrf_token` vs `csrf_token_import`)
**Solution:** Whitelist uses `csrf_token`, import_csv_enhanced uses `csrf_token_import`

---

## 🎯 Key Log Indicators

### ✅ **Success Pattern:**
```
=== IMPORT CSV ENHANCED DEBUG ===
csrf_token_import after: EXISTS (64 chars)
↓
=== POST REQUEST RECEIVED ===
POST csrf_token: ... (length: 64)
SESSION csrf_token_import: ... (length: 64)
↓
✅ CSRF VALIDATION PASSED!
```

### ❌ **Error Pattern:**
```
=== POST REQUEST RECEIVED ===
POST csrf_token: ... (length: 64)
SESSION csrf_token_import: ... (length: 64)
↓
❌ CSRF VALIDATION FAILED!
Reason: Token mismatch
```

---

## 🔧 Troubleshooting with Logs

### **Issue: "Invalid request" Error**

**Step 1:** Check if token generated
```bash
tail -20 /Applications/XAMPP/xamppfiles/logs/error_log | grep "csrf_token_import after"
```
Should see: `EXISTS (64 chars)`

**Step 2:** Check if token posted
```bash
tail -20 /Applications/XAMPP/xamppfiles/logs/error_log | grep "POST csrf_token"
```
Should see: token value with length 64

**Step 3:** Check validation result
```bash
tail -20 /Applications/XAMPP/xamppfiles/logs/error_log | grep "CSRF VALIDATION"
```
Should see: `✅ CSRF VALIDATION PASSED!`

**Step 4:** If failed, check reason
```bash
tail -20 /Applications/XAMPP/xamppfiles/logs/error_log | grep "Reason:"
```

---

## 📁 Log File Locations

| Log Type | Path | Purpose |
|----------|------|---------|
| PHP Error Log | `/Applications/XAMPP/xamppfiles/logs/error_log` | Main error log |
| Apache Error | `/Applications/XAMPP/xamppfiles/logs/apache_error_log` | Apache errors |
| PHP Access Log | `/Applications/XAMPP/xamppfiles/logs/access_log` | HTTP requests |

---

## 💡 Pro Tips

### **Tip 1: Clear Old Logs**
```bash
# Backup and clear error log
cp /Applications/XAMPP/xamppfiles/logs/error_log /tmp/error_log_backup.txt
> /Applications/XAMPP/xamppfiles/logs/error_log

# Now test import, logs will be clean
```

### **Tip 2: Filter by Session**
```bash
# Get your session ID from debug_csrf.php
# Then filter logs by that session ID
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log | grep "i2M4idmc"
```

### **Tip 3: Time-based Analysis**
```bash
# Show logs with timestamp
tail -f /Applications/XAMPP/xamppfiles/logs/error_log | while read line; do
    echo "[$(date +%H:%M:%S)] $line"
done
```

### **Tip 4: Save Debug Session**
```bash
# Save all logs to file for analysis
tail -200 /Applications/XAMPP/xamppfiles/logs/error_log > debug_session_$(date +%Y%m%d_%H%M%S).log
```

---

## 🎨 Log Format Examples

### **Full Success Log:**
```
[03-Nov-2025 10:30:15 UTC] === IMPORT CSV ENHANCED DEBUG ===
[03-Nov-2025 10:30:15 UTC] Session ID: i2M4idmc35io0lm0d6k6ddu
[03-Nov-2025 10:30:15 UTC] Session status: 2
[03-Nov-2025 10:30:15 UTC] User ID: 1
[03-Nov-2025 10:30:15 UTC] csrf_token_import before: NOT SET
[03-Nov-2025 10:30:15 UTC] csrf_token_import after: EXISTS (64 chars)
[03-Nov-2025 10:30:18 UTC] === POST REQUEST RECEIVED ===
[03-Nov-2025 10:30:18 UTC] REQUEST_METHOD: POST
[03-Nov-2025 10:30:18 UTC] POST keys: csrf_token, import_mode, import
[03-Nov-2025 10:30:18 UTC] FILES keys: import_file
[03-Nov-2025 10:30:18 UTC] POST csrf_token: 617bc1e64ee235d8bA8c... (length: 64)
[03-Nov-2025 10:30:18 UTC] SESSION csrf_token_import: 617bc1e64ee235d8bA8c... (length: 64)
[03-Nov-2025 10:30:18 UTC] ✅ CSRF VALIDATION PASSED!
```

### **Error Log:**
```
[03-Nov-2025 10:30:18 UTC] === POST REQUEST RECEIVED ===
[03-Nov-2025 10:30:18 UTC] POST csrf_token: 617bc1e64ee235d8bA8c... (length: 64)
[03-Nov-2025 10:30:18 UTC] SESSION csrf_token_import: 58c8Ke0934d2b3868... (length: 64)
[03-Nov-2025 10:30:18 UTC] ❌ CSRF VALIDATION FAILED!
[03-Nov-2025 10:30:18 UTC] Reason: Token mismatch
```

---

## 🚀 Quick Commands Reference

```bash
# Start monitoring (best option)
./monitor_logs.sh

# View last 50 lines
tail -50 /Applications/XAMPP/xamppfiles/logs/error_log

# Filter CSRF logs
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log | grep CSRF

# Filter errors only
tail -100 /Applications/XAMPP/xamppfiles/logs/error_log | grep "❌"

# Clear log (careful!)
> /Applications/XAMPP/xamppfiles/logs/error_log

# Save debug session
tail -200 /Applications/XAMPP/xamppfiles/logs/error_log > debug.log
```

---

## ✅ Success Checklist

After import, logs should show:
- [ ] Session started successfully
- [ ] Token generated (64 chars)
- [ ] POST received with all data
- [ ] Token posted matches session token
- [ ] ✅ CSRF VALIDATION PASSED!
- [ ] No ❌ error messages

All checked? ✅ **Import should work!**

---

**Created:** 2024-01-20
**Purpose:** Debug import CSV with comprehensive logging
**Tools:** monitor_logs.sh, tail, grep
**Status:** ✅ Ready to use
