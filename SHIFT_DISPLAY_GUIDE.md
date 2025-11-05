# Shift Display Guide - Kalender System

## Overview
The Kalender system displays all shift types dynamically based on actual shift assignments from the database. Shifts are not hardcoded - they are loaded from the `cabang` and `shift_assignments` tables.

## Available Shift Types in Database

Based on the database schema, the following shift types exist:

1. **pagi** (Morning Shift)
   - Times: 07:00-15:00 or 08:00-15:00
   - Duration: 8 hours

2. **middle** (Midday Shift)
   - Times: 12:00-20:00 or 13:00-21:00
   - Duration: 8 hours

3. **sore** (Evening Shift)
   - Times: 15:00-23:00
   - Duration: 8 hours

## How Shift Display Works

### 1. Data Loading Process
```
User selects Cabang → 
  API loads shift types for that cabang → 
    API loads shift assignments → 
      Calendar displays shifts that have assignments
```

### 2. Week View Display
- Shows 7 days (Monday to Sunday)
- Groups shifts by their start and end times
- Displays shift name, time range, and employee count
- **Only shows shifts that have assignments for that week**

### 3. Day View Display
- Shows 24-hour timeline
- Positions shift cards at their actual start times
- Card height represents shift duration
- Groups employees by shift time
- **Only shows shifts that have assignments for that specific day**

### 4. Month View Display
- Shows shift count per day
- Click on a day to see detailed shift information in day view

## Why Shifts May Not Appear

If you're not seeing 'sore' or 'middle' shifts, check:

1. **No Assignments**: The shift type exists in the database but no employees have been assigned to it for the visible dates
   ```sql
   -- Check assignments for a specific date
   SELECT * FROM shift_assignments 
   WHERE shift_date = '2024-11-06' 
   AND cabang_id = 1;
   ```

2. **Wrong Cabang Selected**: The selected cabang may not have that shift type configured
   ```sql
   -- Check available shifts for a cabang
   SELECT nama_shift, jam_masuk, jam_keluar 
   FROM cabang 
   WHERE nama_cabang = 'Citraland Gowa';
   ```

3. **Date Range**: You're viewing dates that don't have assignments yet
   - Try navigating to different dates
   - Check if assignments exist in the database for those dates

## Debug Console Logs

The system includes comprehensive debug logging. Open browser DevTools Console to see:

```javascript
// When cabang is selected:
📥 Loading shift list and assignments...
✅ Shift list loaded: [Array]
📋 Shift list count: 3
📝 Shift names: ['pagi', 'middle', 'sore']
✅ Shift assignments loaded: {Object}
📊 Total assignments: 15
🔍 Unique shift types in assignments: ['pagi', 'sore', 'middle']

// In week view:
📅 Week view - 2024-11-06: 5 shifts
   Shifts: pagi, middle, sore, pagi, middle
   Grouped into 3 shift groups
   - pagi: 2 pegawai (07:00:00-15:00:00)
   - middle: 2 pegawai (13:00:00-21:00:00)
   - sore: 1 pegawai (15:00:00-23:00:00)

// In day view:
📅 Day view - Date: 2024-11-06
📦 Day view - shiftAssignments object: {Object}
📋 Day view - Total assignments in memory: 15
📊 Day view - Found 5 shifts for 2024-11-06
📦 Day view - Grouped shifts: 3 groups
   - pagi: 2 pegawai (07:00:00-15:00:00)
   - middle: 2 pegawai (13:00:00-21:00:00)
   - sore: 1 pegawai (15:00:00-23:00:00)
```

## Verification Steps

To verify all shift types are working:

### Step 1: Check Database
```sql
-- View all shift types in cabang table
SELECT DISTINCT nama_shift FROM cabang;
-- Should show: pagi, middle, sore

-- View all assignments with shift types
SELECT 
    sa.shift_date,
    c.nama_shift,
    COUNT(*) as assignment_count
FROM shift_assignments sa
JOIN cabang c ON sa.cabang_id = c.id
GROUP BY sa.shift_date, c.nama_shift
ORDER BY sa.shift_date, c.nama_shift;
```

### Step 2: Assign Test Shifts
Using the Kalender interface:
1. Select a cabang (e.g., "Citraland Gowa")
2. Switch to Day view
3. Click on different time slots to assign:
   - 07:00 → Assign 'pagi' shift
   - 13:00 → Assign 'middle' shift
   - 15:00 → Assign 'sore' shift
4. Verify all three shifts appear in the day view
5. Switch to Week view and verify all shifts are grouped correctly

### Step 3: Check Console Logs
1. Open browser DevTools (F12)
2. Go to Console tab
3. Select a cabang
4. Look for the log messages showing:
   - Shift list count
   - Shift names array
   - Assignments count
   - Unique shift types in assignments

## Code References

### Loading Shifts
- **API Endpoint**: `api_shift_calendar.php` → `getShifts()` function
- **Frontend**: `script_kalender_api.js` → `loadShiftList()` function
- **Storage**: Stored in `shiftList` array

### Loading Assignments
- **API Endpoint**: `api_shift_calendar.php` → `getAssignments()` function
- **Frontend**: `script_kalender_api.js` → `loadShiftAssignments()` function
- **Storage**: Stored in `shiftAssignments` object (keyed by assignment ID)

### Displaying Shifts
- **Week View**: `script_kalender_core.js` → `generateWeekView()` function
  - Line ~330: Groups shifts by time
  - Line ~357: Displays grouped shifts
- **Day View**: `script_kalender_core.js` → `generateDayView()` function
  - Line ~520: Groups shifts by start time
  - Line ~545: Creates shift cards

## Expected Behavior

✅ **Correct**: Shifts only appear when there are assignments
- Empty days show "Tidak ada shift"
- Empty weeks show "Tidak ada shift" for each day
- This is intentional - the calendar shows actual scheduled shifts, not potential shifts

❌ **Incorrect**: All shift types always visible
- Shifts should NOT appear just because they exist in the cabang table
- They should ONLY appear when employees are assigned to them

## Summary

The system is working correctly as designed:
- All shift types (pagi, middle, sore) are supported
- Shifts display dynamically based on actual assignments
- The calendar shows "what's scheduled" not "what's possible"
- Use the assign modal to create new shift assignments
- Check console logs for detailed debugging information

If shifts still don't appear after assignment, check:
1. Database connection (`api_shift_calendar.php`)
2. API responses (Network tab in DevTools)
3. Console errors (Console tab in DevTools)
4. Assignment data structure matches expected format
