# 🚀 QUICK START GUIDE - Integration Test Suite

## Running the Test Suite

### Prerequisites
- XAMPP with MySQL and PHP running
- Database `aplikasi` properly configured
- PHPMailer installed via Composer
- Internet connection for email sending

### Quick Run Command
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
php comprehensive_integration_test.php
```

### Expected Duration
**~35-40 seconds** (includes email sending with delays)

---

## What the Test Does

### 1. Cleanup Phase (2 seconds)
- Removes previous test data
- Deletes old test users and related records

### 2. User Creation (5 seconds)
- Creates 4 test users with real email addresses
- Creates komponen_gaji for each user
- Sets up base salary and allowances

### 3. Integration Tests (15 seconds)
- Tests leave approval workflow
- Tests sick leave integration
- Tests shift confirmation scenarios
- Tests lateness calculations
- Tests overwork detection
- Generates monthly shifts and attendance

### 4. Salary Generation (8 seconds)
- Runs auto_generate_slipgaji.php for all users
- Calculates all components (base, allowances, deductions)
- Verifies calculation accuracy

### 5. Email Delivery (10 seconds)
- Sends salary slip emails to 4 real addresses
- Updates email_sent flags in database
- Includes 2-second delays between emails

---

## Test Output Format

### Success Output Example:
```
✓ PASS - Create test user
✓ PASS - Generate monthly shifts: 25 shifts created
✓ PASS - Send email to katahnaf@gmail.com
```

### Failure Output Example:
```
✗ FAIL - Create test user: Database connection failed
```

### Color Coding:
- 🟢 **Green (✓ PASS)** - Test passed successfully
- 🔴 **Red (✗ FAIL)** - Test failed with error message

---

## Understanding Test Results

### 100% Pass Rate = Ready for Production
All integration points are working correctly:
- Database operations
- Feature integrations
- Calculation accuracy
- Email delivery

### Partial Pass Rate = Investigation Needed
Check failed tests in the summary section:
- Review error messages
- Check database schema
- Verify email configuration
- Check file permissions

---

## Test Data Created

### Test Users (Automatically Created):
1. **Kata Hnaf** - katahnaf@gmail.com
2. **Pilar Aforisma** - pilaraforismacinta@gmail.com
3. **Galih Ganji** - galihganji@gmail.com
4. **Dot Pikir** - dotpikir@gmail.com

**Password for all:** `Test123!`

### Test Data Scope:
- **Period:** November 2025
- **Shifts:** 25+ per user (excluding Sundays)
- **Attendance:** Varied scenarios (on-time, late, overwork)
- **Leave Requests:** Both izin and sakit with different statuses
- **Salary Slips:** 4 complete slips with emails

---

## Cleanup Between Runs

### Automatic Cleanup
The test suite automatically cleans up previous test data at the start:
- Deletes test users (by email or username pattern)
- Cascades to delete related records (shifts, attendance, leaves, salary slips)

### Manual Cleanup (if needed)
```sql
DELETE FROM register WHERE email IN (
    'katahnaf@gmail.com',
    'pilaraforismacinta@gmail.com',
    'galihganji@gmail.com',
    'dotpikir@gmail.com'
);
```

---

## Troubleshooting

### Test Fails at User Creation
**Issue:** Database connection or schema mismatch  
**Fix:** Check `connect.php` and database structure

### Test Fails at Email Sending
**Issue:** SMTP configuration or internet connectivity  
**Fix:** Verify PHPMailer settings in test suite

### Test Fails at Salary Generation
**Issue:** Missing komponen_gaji records  
**Fix:** Ensure all test users have komponen_gaji entries

### "Komponen gaji not found" for Other Users
**Note:** This is expected! The test suite only creates komponen_gaji for the 4 test users. Other dummy users in the database will show failures, which is normal.

---

## Manual Verification After Tests

### Required UI Checks:
1. **Dashboard (mainpageadmin.php)**
   - Verify statistics update correctly
   - Check overview cards display accurate data

2. **Attendance View (view_absensi.php)**
   - Verify attendance records show up
   - Check lateness calculations
   - Verify status indicators

3. **Shift Calendar (jadwal_shift.php)**
   - Verify shifts display in calendar
   - Check confirmation status
   - Test date navigation

4. **Leave Approval (approve.php)**
   - Verify leave requests appear
   - Test approval/rejection workflow
   - Check status updates

5. **Salary Slips (slip_gaji_management.php)**
   - Verify slips are generated
   - Check calculation breakdown
   - Test download/view functionality

6. **Email Inboxes**
   - Check all 4 email addresses
   - Verify formatting and content
   - Test responsive design

---

## Re-running Tests

### Safe to Re-run
The test suite is **idempotent** - you can run it multiple times:
- Automatically cleans up previous data
- Creates fresh test data each time
- Safe for development/testing environment

### Production Warning
**⚠️ DO NOT run this in production!**
- Uses test email addresses
- Sends real emails
- Modifies database
- Only for dev/staging environments

---

## Test Suite Modifications

### Adding New Test Scenarios
Edit `comprehensive_integration_test.php`:

```php
// Add new test function
function test_my_new_scenario($pdo, $user_id, $test_date) {
    test_section("MY NEW TEST SCENARIO");
    
    // Your test logic here
    $result = some_operation();
    
    test_result("Test operation name", $result !== false);
    
    return $result;
}

// Call in run_all_tests()
$new_results = test_my_new_scenario($pdo, $user1['id'], '2025-11-15');
```

### Changing Test Users
Update the `$test_emails` array at the top:

```php
$test_emails = [
    'your-email-1@example.com',
    'your-email-2@example.com',
    'your-email-3@example.com',
    'your-email-4@example.com'
];
```

### Changing Test Period
Update variables in `run_all_tests()`:

```php
$test_month = 12;  // December
$test_year = 2025;
$base_date = '2025-12-01';
```

---

## Performance Benchmarks

### Expected Test Times:
- **Cleanup:** < 2 seconds
- **User Creation:** < 5 seconds
- **Shift Generation:** < 3 seconds
- **Attendance Creation:** < 2 seconds
- **Salary Generation:** < 10 seconds
- **Email Sending:** ~8 seconds (4 emails × 2s delay)

### Total Expected: **30-40 seconds**

### Slow Test Warning
If tests take longer than 60 seconds:
- Check database performance
- Verify network connection
- Check for database locks
- Review server resources

---

## Success Criteria

### ✅ All Tests Pass
- 46/46 tests passed
- 100% pass rate
- All emails sent successfully
- Database records created correctly

### ⚠️ Some Tests Fail
- Review error messages in output
- Check failed test category
- Investigate root cause
- Fix and re-run

### ❌ Many Tests Fail
- Database schema mismatch likely
- Review recent database changes
- Check `connect.php` configuration
- Verify required tables exist

---

## Quick Commands Reference

```bash
# Run full test suite
php comprehensive_integration_test.php

# Check PHP version
php -v

# Test database connection
php -r "require 'connect.php'; echo 'Connected!';"

# View test results documentation
cat COMPREHENSIVE_TEST_RESULTS.md

# View this status report
cat TEST_STATUS_REPORT.md

# Clean up test data manually (SQL)
mysql -u root aplikasi -e "DELETE FROM register WHERE username LIKE 'test_user_%'"
```

---

## Support & Documentation

### Related Files:
- `comprehensive_integration_test.php` - Main test suite
- `COMPREHENSIVE_TEST_RESULTS.md` - Detailed test documentation
- `TEST_STATUS_REPORT.md` - Production readiness report
- `QUICK_START_GUIDE.md` - This guide

### For Issues:
1. Check error messages in test output
2. Review database schema
3. Verify PHPMailer configuration
4. Check server logs

---

**Last Updated:** November 6, 2025  
**Test Suite Version:** 1.0  
**Status:** ✅ Production Ready
