# 🔍 ABSENSI LOGGING & DEBUG SYSTEM

## ✅ **IMPLEMENTED - Nov 3, 2025**

### **Problem:**
- Admin mendapat error "Silahkan hubungi admin" saat mencoba absen
- Tidak ada logging untuk debug
- Sulit track error root cause

---

## 🛠️ **Solution Implemented:**

### **1. Enhanced Logging in `proses_absensi.php`**

**Added comprehensive logging function:**
```php
function log_absen($message, $data = []) {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message";
    if (!empty($data)) {
        $log_message .= " | DATA: " . json_encode($data);
    }
    error_log($log_message);
}
```

**Log Points Added:**
1. ✅ Process start (user_id, role, method)
2. ✅ CSRF validation
3. ✅ POST data received
4. ✅ Rate limiting checks
5. ✅ Time validation
6. ✅ **Admin mode detection** ← KEY POINT
7. ✅ User mode (location validation)
8. ✅ All error conditions
9. ✅ PDO exceptions
10. ✅ General exceptions

---

### **2. Created Log Viewer (`view_absen_log.php`)**

**Features:**
- ✅ Real-time log viewing
- ✅ Auto-refresh (5s / 10s)
- ✅ Filter/search functionality
- ✅ Shows PHP error_log
- ✅ Shows custom absensi errors
- ✅ Color-coded messages (error, warning, success)
- ✅ Admin-only access
- ✅ Clear log button

**Access:**
```
http://localhost/aplikasi/view_absen_log.php
```

---

## 🔎 **How to Debug Admin Absen Issue:**

### **Step 1: Open Log Viewer**
1. Login as admin
2. Go to: `http://localhost/aplikasi/view_absen_log.php`
3. Enable auto-refresh (5s)

### **Step 2: Try Absen**
1. Open another tab: `absen.php`
2. Try absen masuk/keluar
3. Watch the logs in real-time

### **Step 3: Analyze Logs**

**Look for these key markers:**

✅ **Normal Admin Flow:**
```
🚀 ABSEN PROCESS START | user_id: 1, user_role: admin
👑 ADMIN MODE ACTIVATED - Skip location validation
✅ Admin default branch assigned
```

❌ **Error Indicators:**
```
❌ CSRF validation failed
❌ Time validation FAILED
❌ Rate limit exceeded
💥 PDO EXCEPTION
```

---

## 📊 **Log Format:**

```
[2025-11-03 15:30:45] 🚀 ABSEN PROCESS START | DATA: {"user_id":1,"user_role":"admin"}
[2025-11-03 15:30:45] ✅ CSRF VALIDATION PASSED
[2025-11-03 15:30:45] 📥 POST DATA received | DATA: {"latitude":-5.198,"longitude":119.448,"tipe_absen":"masuk"}
[2025-11-03 15:30:45] 👑 ADMIN MODE ACTIVATED - Skip location validation
[2025-11-03 15:30:45] ✅ Admin default branch assigned | DATA: {"branch_id":1,"jam_masuk":"07:00:00"}
```

---

## 🎯 **Expected Behavior for Admin:**

**Admin should:**
1. ✅ Skip location validation
2. ✅ Skip shift validation
3. ✅ Can absen from anywhere
4. ✅ Auto-assigned default branch for reference
5. ✅ Status lokasi = "Admin - Remote"

**Logged as:**
```php
log_absen("👑 ADMIN MODE ACTIVATED - Skip location validation");
```

---

## 🚨 **Common Error Sources:**

### **1. Rate Limiting**
```
⏰ Rate limit: Too fast | time_diff: 5, remaining: 5
```
**Fix:** Wait 10 seconds between attempts

### **2. Time Validation**
```
❌ Time validation FAILED | current_time: 06:30:00
```
**Fix:** Absen only between 07:00 - 23:59

### **3. CSRF Token**
```
❌ CSRF validation failed | post_token: missing
```
**Fix:** Refresh page (F5)

### **4. Database Error**
```
💥 PDO EXCEPTION | error_message: "Table not found"
```
**Fix:** Check database schema

---

## 📝 **Testing Checklist:**

- [ ] Login as admin
- [ ] Open log viewer
- [ ] Try absen masuk
- [ ] Check logs for "ADMIN MODE ACTIVATED"
- [ ] Verify no location validation
- [ ] Try absen keluar
- [ ] Check for any errors
- [ ] Test with different browsers
- [ ] Test at different times (before 07:00, after 23:59)

---

## 🔧 **Files Modified:**

1. **proses_absensi.php**
   - Added `log_absen()` function
   - Added 10+ log points
   - Enhanced error handling

2. **view_absen_log.php** (NEW)
   - Log viewer interface
   - Real-time monitoring
   - Filter & search

---

## 📞 **Next Steps:**

1. ✅ Open log viewer
2. ✅ Simulate absen as admin
3. ✅ Check logs for exact error
4. ✅ Share log output if issue persists

**If you see the error again:**
1. Copy the log entries
2. Look for the ❌ emoji
3. Find the error_message
4. That's the root cause!

---

## 🎉 **Benefits:**

- ✅ Real-time debugging
- ✅ No need to check server logs
- ✅ Visual color-coded interface
- ✅ Filter/search capability
- ✅ Auto-refresh for monitoring
- ✅ Easy error tracking

---

**Last Updated:** November 3, 2025
**Status:** ✅ Ready for Testing
**Access:** Admin Only
