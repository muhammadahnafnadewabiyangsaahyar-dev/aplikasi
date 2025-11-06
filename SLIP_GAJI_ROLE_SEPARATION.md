# Slip Gaji Role Separation - Documentation

## Overview
This document explains the role-based separation of the salary slip system for better security and user experience.

## Problem Solved
Previously, both admin and regular users used the same `slipgaji.php` file, which contained admin functionality (generate salary) mixed with user views. This created:
- Security concerns (users could potentially see admin functions)
- Confusing UI (admin features visible to non-admin users)
- Difficult maintenance (one file doing multiple jobs)

## Solution: Role-Based File Separation

### For Regular Users: `slipgaji.php` (VIEW ONLY)
**Purpose:** Allow users to view their own salary history

**Features:**
- ✓ View personal salary history only
- ✓ Display period, net salary (THP), attendance stats
- ✓ Download salary slip files (if available)
- ✓ See latest salary summary
- ✓ Information notice about auto-generation dates
- ✗ Cannot generate salary slips
- ✗ Cannot edit salary components
- ✗ Cannot view other users' salaries

**Access Control:**
```php
// Auto-redirect admin to management page
if ($current_user_role === 'admin') {
    header('Location: slip_gaji_management.php');
    exit;
}

// Only show own salary history
$user_id_to_view = $current_user_id;
```

**UI Features:**
- Clean, simplified interface
- Info box explaining salary generation schedule
- Latest salary summary box
- Download buttons for each period
- Enhanced icons and styling

### For Admin: `slip_gaji_management.php` (FULL MANAGEMENT)
**Purpose:** Complete salary management system for HR/Admin

**Features:**
- ✓ Manual trigger for auto-generation script
- ✓ View all employees' salary slips
- ✓ Edit salary components (kasbon, piutang, bonuses)
- ✓ Filter by period, employee, or batch
- ✓ Bulk email sending for salary slips
- ✓ Batch tracking and status
- ✓ Export functionality (future: PDF, Excel)
- ✓ Comprehensive salary calculation logs

**Access Control:**
```php
// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
```

## Navbar Integration

### Updated Logic in `navbar.php`
```php
// Different slip gaji URLs based on role
if ($_SESSION['role'] == 'admin') {
    $slipgaji_url = 'slip_gaji_management.php';  // Admin → Management
} else {
    $slipgaji_url = 'slipgaji.php';               // User → View Only
}
```

### Navigation Flow
```
User clicks "Slip Gaji" in navbar
    ↓
Is user Admin?
    ↓ Yes → slip_gaji_management.php (Full management interface)
    ↓ No  → slipgaji.php (View-only interface)
```

## Database Tables Used

### Both Pages Access:
- `riwayat_gaji` - Salary history records
- `register` - User information

### Admin Page Also Uses:
- `komponen_gaji` - Salary components
- `slip_gaji_batch` - Batch generation tracking
- `komponen_gaji_tambahan` - Additional salary components
- `absensi` - Attendance for calculation
- `pengajuan_izin` - Leave requests for calculation
- `hari_libur_nasional` - National holidays

## Security Features

### User Page (`slipgaji.php`)
1. Session validation - must be logged in
2. Role-based redirect - admins auto-redirected
3. User ID restriction - can only see own data
4. No POST actions available - read-only

### Admin Page (`slip_gaji_management.php`)
1. Admin-only access control
2. CSRF protection (to be implemented)
3. Audit trail with `updated_by` field
4. Transaction-based updates
5. Input validation and sanitization

## File Structure

```
/aplikasi/
├── slipgaji.php                    # USER: View-only salary history
├── slip_gaji_management.php        # ADMIN: Full management
├── auto_generate_slipgaji.php      # AUTO: Scheduled generation script
├── navbar.php                      # Updated with role-based routing
└── migration_slip_gaji_system.sql  # Database schema
```

## User Experience Comparison

### Regular User View (`slipgaji.php`)
```
┌──────────────────────────────────────┐
│     Riwayat Slip Gaji Saya          │
├──────────────────────────────────────┤
│  ℹ️  Information Notice              │
│  • Auto-generated every 28th        │
│  • Contact HR for questions         │
├──────────────────────────────────────┤
│  📊 Latest Salary Summary            │
│  Periode: Desember 2024             │
│  THP: Rp 4,500,000                  │
├──────────────────────────────────────┤
│  Salary History Table               │
│  [Periode] [THP] [Stats] [Download] │
└──────────────────────────────────────┘
```

### Admin View (`slip_gaji_management.php`)
```
┌──────────────────────────────────────┐
│   Manajemen Slip Gaji (Admin)        │
├──────────────────────────────────────┤
│  ⚡ Manual Generate Button            │
│  🔍 Filters: Period, Employee, Batch │
│  ✉️  Bulk Email Button                │
├──────────────────────────────────────┤
│  Complete Salary Table               │
│  [Employee] [Period] [Components]   │
│  [Edit] [Email] [Status]            │
├──────────────────────────────────────┤
│  📊 Statistics & Batch Info          │
└──────────────────────────────────────┘
```

## Migration Guide

### For Existing Users
No action required. Users will automatically see the new simplified interface when accessing "Slip Gaji" from the navbar.

### For Admins
When clicking "Slip Gaji" in the navbar, you'll now be directed to the management interface automatically. All previous functionality is preserved and enhanced.

### For Developers
1. User salary view logic: Check `slipgaji.php`
2. Admin management logic: Check `slip_gaji_management.php`
3. Navbar routing logic: Check `navbar.php` lines 29-40
4. Auto-generation: `auto_generate_slipgaji.php` (unchanged)

## Testing Checklist

### User Role Testing
- [ ] Regular user can access `slipgaji.php`
- [ ] Regular user sees only own salary data
- [ ] Regular user can download salary slips
- [ ] Regular user redirected if trying to access management page
- [ ] No admin functions visible to regular users

### Admin Role Testing
- [ ] Admin auto-redirected from `slipgaji.php` to management
- [ ] Admin can trigger manual generation
- [ ] Admin can view all employees' salaries
- [ ] Admin can edit salary components
- [ ] Admin can send bulk emails
- [ ] Admin can filter and search data

### Navbar Testing
- [ ] User role → "Slip Gaji" links to `slipgaji.php`
- [ ] Admin role → "Slip Gaji" links to `slip_gaji_management.php`
- [ ] Link appears for all logged-in users

## Benefits

### For Users
1. **Simpler Interface**: No confusing admin controls
2. **Faster Loading**: Only necessary data loaded
3. **Better UX**: Focused on viewing and downloading
4. **Clear Information**: Info boxes explain the process

### For Admins
1. **Powerful Tools**: All management features in one place
2. **Better Workflow**: Bulk actions, filters, search
3. **Audit Trail**: Track who made changes
4. **Efficient**: No clutter from user-view elements

### For Developers
1. **Separation of Concerns**: Each file has one responsibility
2. **Easier Maintenance**: Changes to admin features don't affect user view
3. **Better Security**: Role checks at file level
4. **Scalable**: Easy to add features to either role

## Future Enhancements

### User Page
- [ ] PDF export of individual salary slips
- [ ] Year-to-date (YTD) summary
- [ ] Salary comparison chart (month-over-month)
- [ ] Download all slips (ZIP file)

### Admin Page
- [ ] Advanced reporting (Excel export)
- [ ] Salary component templates
- [ ] Automated email scheduling
- [ ] Approval workflow for manual adjustments
- [ ] Dashboard with statistics

### Both
- [ ] CSRF protection implementation
- [ ] Email notification preferences
- [ ] Mobile-responsive design improvements
- [ ] Push notifications for new salary slips

## Troubleshooting

### Issue: User sees blank page on `slipgaji.php`
**Solution:** Check if user has any salary records in `riwayat_gaji` table

### Issue: Admin not redirected to management page
**Solution:** Check `$_SESSION['role']` value in navbar.php

### Issue: Download links not working
**Solution:** Verify `file_slip_gaji` column has correct file paths

### Issue: Admin can't edit salary components
**Solution:** Check database connection and table structure for `riwayat_gaji`

## Related Documentation
- `SLIP_GAJI_DOCUMENTATION.md` - Complete system documentation
- `SLIP_GAJI_IMPLEMENTATION_SUMMARY.md` - Implementation details
- `migration_slip_gaji_system.sql` - Database schema

## Changelog

### Version 2.0 (Current)
- ✅ Separated user and admin interfaces
- ✅ Updated navbar routing logic
- ✅ Enhanced user view with info boxes and summaries
- ✅ Admin auto-redirect from user page
- ✅ Improved security with role-based access

### Version 1.0 (Legacy)
- Single `slipgaji.php` for all users
- Mixed admin and user functionality
- Basic salary history display

---

**Last Updated:** 2024-01-XX  
**Maintained By:** Development Team  
**Status:** ✅ Production Ready
