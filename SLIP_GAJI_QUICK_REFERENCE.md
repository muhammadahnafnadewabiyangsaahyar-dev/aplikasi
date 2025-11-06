# Quick Reference: Slip Gaji System Access

## 🔑 Role-Based Access

| Role | Navbar Link "Slip Gaji" | File | Capabilities |
|------|-------------------------|------|--------------|
| **User** | → `slipgaji.php` | View Only | • See own salary history<br>• Download salary slips<br>• View attendance stats |
| **Admin** | → `slip_gaji_management.php` | Full Management | • Generate salary slips<br>• Edit components<br>• View all employees<br>• Bulk email<br>• Filter & search |

## 📋 File Purposes

### `slipgaji.php` - User View
```
PURPOSE: Read-only salary history for regular employees
ACCESS:  All logged-in users (non-admin)
ACTIONS: View, Download
```

### `slip_gaji_management.php` - Admin Management
```
PURPOSE: Complete salary management system
ACCESS:  Admin only
ACTIONS: Generate, Edit, Email, Filter, Export
```

### `navbar.php` - Smart Routing
```php
// Line 29-40: Role-based URL assignment
if ($_SESSION['role'] == 'admin') {
    $slipgaji_url = 'slip_gaji_management.php';
} else {
    $slipgaji_url = 'slipgaji.php';
}
```

## 🚀 Key Features by Role

### Regular User Features
- ✅ View salary history (own data only)
- ✅ Download salary slip documents
- ✅ See latest salary summary
- ✅ Info about auto-generation schedule
- ❌ Cannot generate slips
- ❌ Cannot edit components
- ❌ Cannot view others' data

### Admin Features (Everything Above Plus)
- ✅ Manual trigger auto-generation
- ✅ View all employees' salaries
- ✅ Edit salary components (kasbon, piutang, bonuses)
- ✅ Send bulk emails to employees
- ✅ Filter by period, employee, batch
- ✅ Track generation batches
- ✅ Audit trail (who updated what)

## 🔒 Security Controls

### User Page (`slipgaji.php`)
```php
// Line 20-26: Security checks
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=notloggedin');
    exit;
}

// Admin redirect
if ($current_user_role === 'admin') {
    header('Location: slip_gaji_management.php');
    exit;
}

// Data restriction
$user_id_to_view = $current_user_id; // Own data only
```

### Admin Page (`slip_gaji_management.php`)
```php
// Line 6-9: Admin-only access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
```

## 📊 Database Columns Used

### User View Needs:
- `riwayat_gaji.periode_bulan`
- `riwayat_gaji.periode_tahun`
- `riwayat_gaji.gaji_bersih`
- `riwayat_gaji.jumlah_hadir`
- `riwayat_gaji.jumlah_terlambat`
- `riwayat_gaji.jumlah_tidak_hadir`
- `riwayat_gaji.overwork`
- `riwayat_gaji.file_slip_gaji`

### Admin View Needs (All Above Plus):
- All salary component columns
- Batch information
- Email status
- Update tracking

## 🧪 Quick Test

### Test User Access
```bash
# Login as regular user
# Click "Slip Gaji" in navbar
# Expected: slipgaji.php loads
# Expected: Only own salary data visible
# Expected: No edit/generate buttons
```

### Test Admin Access
```bash
# Login as admin
# Click "Slip Gaji" in navbar
# Expected: slip_gaji_management.php loads
# Expected: Can see all employees
# Expected: Edit, Generate, Email buttons visible
```

### Test Direct URL Access
```bash
# As regular user, try accessing:
# /slip_gaji_management.php
# Expected: Redirected to login or access denied

# As admin, try accessing:
# /slipgaji.php
# Expected: Auto-redirected to slip_gaji_management.php
```

## 🎨 UI Differences

### User Interface
```
┌─────────────────────────────────┐
│ 📄 Riwayat Slip Gaji Saya      │
├─────────────────────────────────┤
│ ℹ️  Auto-generated info box     │
│ 📊 Latest salary summary        │
│ 📋 Simple salary history table  │
│    [Period] [Amount] [Download] │
└─────────────────────────────────┘
Clean, simple, focused on viewing
```

### Admin Interface
```
┌─────────────────────────────────┐
│ ⚙️  Manajemen Slip Gaji         │
├─────────────────────────────────┤
│ 🔄 Manual Generate              │
│ 🔍 Advanced Filters             │
│ ✉️  Bulk Email                  │
├─────────────────────────────────┤
│ 📊 Comprehensive table          │
│    [Employee] [Components]      │
│    [Edit] [Email] [Status]      │
└─────────────────────────────────┘
Feature-rich, data management focus
```

## ⚡ Performance

| Metric | User Page | Admin Page |
|--------|-----------|------------|
| Query Complexity | Simple SELECT | Complex JOINs |
| Data Volume | Own records only | All employees |
| Load Time | Fast | Moderate |
| Actions Available | 1 (Download) | 5+ (Edit, Email, etc) |

## 🔗 Navigation Flow

```
User Login → Navbar → "Slip Gaji" Click
                          ↓
                    Role Check (navbar.php)
                          ↓
        ┌─────────────────┴─────────────────┐
        ↓                                     ↓
    Is Admin?                            Is User?
        ↓                                     ↓
slip_gaji_management.php              slipgaji.php
(Full Management)                     (View Only)
```

## 📝 Common Tasks

### User: Download Latest Salary Slip
1. Click "Slip Gaji" in navbar
2. See latest salary in summary box
3. Click "Download" button in table
4. File downloads automatically

### Admin: Edit Salary Component
1. Click "Slip Gaji" in navbar
2. Find employee in table
3. Click "Edit" button
4. Update kasbon/piutang/bonuses
5. Click "Update" → Recalculates automatically

### Admin: Send Bulk Emails
1. Click "Slip Gaji" in navbar
2. Click "Kirim Email Masal" button
3. Select period (month/year)
4. Confirm → Emails sent to all employees

## 📞 Support

### User Questions
- "Where is my salary slip?" → Check slipgaji.php
- "I can't see generate button" → Normal, users can't generate
- "I see admin page" → Logout and login again

### Admin Questions
- "How to generate manually?" → Click "Generate Slip Gaji Manual"
- "How to edit components?" → Click edit button in table row
- "How to send emails?" → Use "Kirim Email Masal" button

---

**Quick Access:**
- User Page: `slipgaji.php`
- Admin Page: `slip_gaji_management.php`
- Auto-Generate: `auto_generate_slipgaji.php` (scheduled)
- Documentation: `SLIP_GAJI_ROLE_SEPARATION.md`
