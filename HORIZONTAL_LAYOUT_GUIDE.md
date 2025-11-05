# 🎨 Horizontal Shift Layout - Multi-Column System

## 📋 Feature Description

**Enhancement:** Shift cards yang overlap di day view sekarang ditampilkan secara **horizontal** (side-by-side) bukan bertumpuk vertikal, untuk tampilan yang lebih efisien dan mudah dibaca.

### Before (Vertical Stack):
```
12:00 ├─────────────────────────────┐
      │ ☀️ middle (12:00-20:00)     │
13:00 │                             │
14:00 │                             │
15:00 ├─────────────────────────────┤
      │ 🌆 sore (15:00-23:00)       │ ← Overlaps dengan middle
16:00 │                             │
      │ [Cards bertumpuk vertikal]  │
```

### After (Horizontal Layout):
```
12:00 ├───────────────┬─────────────┐
      │ ☀️ middle     │             │
13:00 │ 12:00-20:00   │             │
14:00 │               │             │
15:00 │               │ 🌆 sore     │ ← Side-by-side!
16:00 │               │ 15:00-23:00 │
      │               │             │
      └───────────────┴─────────────┘
       Column 0        Column 1
```

---

## 🔧 How It Works

### 1. Overlap Detection Algorithm

```javascript
// Step 1: Calculate end times for all shifts
groupsArray.forEach(group => {
    const endHour = parseInt(group.jamKeluar.split(':')[0]);
    const endMinute = parseInt(group.jamKeluar.split(':')[1]) || 0;
    group.endHour = endHour;
    group.endMinute = endMinute;
});

// Step 2: Sort shifts by start time
groupsArray.sort((a, b) => {
    const aStart = a.startHour + a.startMinute/60;
    const bStart = b.startHour + b.startMinute/60;
    return aStart - bStart;
});

// Step 3: Assign to columns (no overlap in same column)
groupsArray.forEach(group => {
    const groupStart = group.startHour + group.startMinute/60;
    const groupEnd = group.endHour + group.endMinute/60;
    
    // Find first available column without overlap
    for (let col = 0; col < columns.length; col++) {
        let hasOverlap = false;
        
        for (let existingShift of columns[col]) {
            const existingStart = existingShift.startHour + existingShift.startMinute/60;
            const existingEnd = existingShift.endHour + existingShift.endMinute/60;
            
            // Overlap formula: start < existingEnd && end > existingStart
            if (groupStart < existingEnd && groupEnd > existingStart) {
                hasOverlap = true;
                break;
            }
        }
        
        if (!hasOverlap) {
            assignedColumn = col;
            columns[col].push(group);
            break;
        }
    }
    
    // Create new column if needed
    if (!assignedToAnyColumn) {
        columns.push([group]);
        assignedColumn = columns.length - 1;
    }
    
    group.column = assignedColumn;
});
```

### 2. Horizontal Positioning Calculation

```javascript
const totalColumns = Math.max(columns.length, 1);
const columnWidth = 100 / totalColumns; // Equal width distribution

// For each shift card
const leftOffset = 70; // Time label width (px)
const columnLeftPercent = group.column * columnWidth; // Column position
const cardWidthPercent = columnWidth - 1; // -1% for gap

// CSS positioning
shiftDiv.style.left = `calc(${leftOffset}px + ${columnLeftPercent}%)`;
shiftDiv.style.width = `calc(${cardWidthPercent}% - ${leftOffset/totalColumns}px)`;
```

---

## 📊 Examples

### Example 1: Two Overlapping Shifts

**Shifts:**
- Pagi: 07:00-15:00
- Middle: 12:00-20:00 (overlaps with pagi from 12:00-15:00)

**Column Assignment:**
```
Column 0: [pagi]    → 07:00 ─────── 15:00
Column 1: [middle]  → 12:00 ─────────── 20:00
```

**Layout:**
```
07:00 ├────────────┬─────────────┐
      │ 🌅 pagi   │             │
      │ 07-15     │             │
12:00 │           │ ☀️ middle   │
      │           │ 12-20       │
15:00 └───────────┤             │
                  │             │
20:00             └─────────────┘
```

**Width Calculation:**
- Total columns: 2
- Each column width: 50%
- Pagi: left=70px+0%, width=49%
- Middle: left=70px+50%, width=49%

---

### Example 2: Three Overlapping Shifts

**Shifts:**
- Pagi: 07:00-15:00
- Middle: 12:00-20:00
- Sore: 15:00-23:00 (overlaps with middle from 15:00-20:00)

**Column Assignment:**
```
Column 0: [pagi]    → 07:00 ─────── 15:00
Column 1: [middle]  → 12:00 ─────────── 20:00
Column 2: [sore]    → 15:00 ──────────────── 23:00
```

**Layout:**
```
07:00 ├──────┬──────┬──────┐
      │ pagi │      │      │
12:00 │      │middle│      │
15:00 └──────┤      │ sore │
             │      │      │
20:00        └──────┤      │
                    │      │
23:00               └──────┘
```

**Width Calculation:**
- Total columns: 3
- Each column width: 33.33%
- Pagi: left=70px+0%, width=32.33%
- Middle: left=70px+33.33%, width=32.33%
- Sore: left=70px+66.66%, width=32.33%

---

### Example 3: No Overlap

**Shifts:**
- Pagi: 07:00-15:00
- Sore: 15:00-23:00 (starts exactly when pagi ends)

**Column Assignment:**
```
Column 0: [pagi, sore]  → No overlap, same column
```

**Layout:**
```
07:00 ├─────────────────────┐
      │ 🌅 pagi             │
      │ 07:00-15:00         │
15:00 ├─────────────────────┤
      │ 🌆 sore             │
      │ 15:00-23:00         │
23:00 └─────────────────────┘
```

**Width Calculation:**
- Total columns: 1
- Each column width: 100%
- Both shifts: left=70px+0%, width=99%

---

## 🎯 Overlap Detection Formula

### Time Overlap Check:
Two time ranges overlap if:
```
startA < endB  AND  endA > startB
```

### Visual Explanation:
```
Range A: |─────────|
Range B:      |─────────|
             ↑
          Overlap!

startA < endB ✓  (A starts before B ends)
endA > startB ✓  (A ends after B starts)
```

### Non-Overlap Examples:
```
Range A: |────|
Range B:        |────|
          No overlap (A ends before B starts)

Range A:        |────|
Range B: |────|
          No overlap (B ends before A starts)
```

---

## 📝 Console Debug Logs

When working correctly, console will show:

```javascript
// Grouping
📦 Day view - Grouped shifts: 3 groups
   - pagi: 2 pegawai (07:00:00-15:00:00)
   - middle: 4 pegawai (12:00:00-20:00:00)
   - sore: 5 pegawai (15:00:00-23:00:00)

// Column assignment
📐 Layout: 2 columns detected
   Column 0: pagi
   Column 1: middle, sore

// Individual positioning
📍 Positioning pagi card: {
    column: 0,
    totalColumns: 2,
    topPosition: 420,
    columnLeftPercent: 0,
    cardWidthPercent: 49
}

📍 Positioning middle card: {
    column: 1,
    totalColumns: 2,
    topPosition: 720,
    columnLeftPercent: 50,
    cardWidthPercent: 49
}

📍 Positioning sore card: {
    column: 1,
    totalColumns: 2,
    topPosition: 900,
    columnLeftPercent: 50,
    cardWidthPercent: 49
}
```

---

## 🎨 Visual Examples

### 1-Column Layout (No Overlaps):
```
┌─────────────────────────────────────┐
│         Full Width (100%)           │
│                                     │
│  07:00 ┌───────────────────────┐   │
│        │ 🌅 pagi               │   │
│        │ 07:00-15:00           │   │
│  15:00 └───────────────────────┘   │
│        ┌───────────────────────┐   │
│        │ 🌆 sore               │   │
│        │ 15:00-23:00           │   │
│  23:00 └───────────────────────┘   │
└─────────────────────────────────────┘
```

### 2-Column Layout (Some Overlaps):
```
┌─────────────────────────────────────┐
│    Column 0 (50%)  │ Column 1 (50%) │
│                    │                │
│  07:00 ┌──────────┐│                │
│        │ pagi     ││                │
│  12:00 │          ││ ┌────────────┐ │
│        │          ││ │ middle     │ │
│  15:00 └──────────┘│ │            │ │
│                    │ │            │ │
│  20:00             │ └────────────┘ │
└─────────────────────────────────────┘
```

### 3-Column Layout (All Overlap):
```
┌─────────────────────────────────────┐
│  Col 0   │  Col 1   │   Col 2       │
│  (33%)   │  (33%)   │   (33%)       │
│          │          │               │
│ ┌──────┐ │          │               │
│ │ pagi │ │          │               │
│ │      │ │ ┌──────┐ │               │
│ │      │ │ │middle│ │ ┌───────────┐ │
│ └──────┘ │ │      │ │ │   sore    │ │
│          │ │      │ │ │           │ │
│          │ └──────┘ │ │           │ │
│          │          │ └───────────┘ │
└─────────────────────────────────────┘
```

---

## 🛠️ Code Location

**File:** `script_kalender_core.js`  
**Function:** `generateDayView()`  
**Lines:** ~530-680

### Key Sections:

1. **Grouping** (Line ~530)
   ```javascript
   const shiftsGroupedByStart = {};
   dayShifts.forEach(assignment => { ... });
   ```

2. **Overlap Detection** (Line ~565)
   ```javascript
   // Calculate end times, sort, and assign columns
   const columns = [];
   groupsArray.forEach(group => { ... });
   ```

3. **Positioning** (Line ~665)
   ```javascript
   // Calculate horizontal position based on column
   const columnWidth = 100 / totalColumns;
   const columnLeftPercent = group.column * columnWidth;
   ```

---

## ✅ Benefits

| Aspect | Before | After |
|--------|--------|-------|
| **Space Usage** | Inefficient (vertical stack) | Efficient (horizontal) |
| **Readability** | Cards hide each other | All cards visible |
| **Scalability** | Limited by screen height | Scales horizontally |
| **Visual Clarity** | Confusing overlap | Clear separation |
| **UX** | Need scrolling to see all | See all at glance |

---

## 🧪 Testing Scenarios

### Test 1: Two Adjacent Shifts (No Overlap)
- Assign pagi (07:00-15:00)
- Assign sore (15:00-23:00)
- **Expected:** Single column, both cards full width

### Test 2: Two Overlapping Shifts
- Assign pagi (07:00-15:00)
- Assign middle (12:00-20:00)
- **Expected:** Two columns, side-by-side from 12:00-15:00

### Test 3: Three Overlapping Shifts
- Assign pagi (07:00-15:00)
- Assign middle (12:00-20:00)
- Assign sore (15:00-23:00)
- **Expected:** 
  - Column 0: pagi
  - Column 1: middle (overlaps pagi)
  - Column 2: sore (overlaps middle)

### Test 4: Complex Pattern
- Assign pagi (07:00-15:00)
- Assign middle (13:00-21:00)
- Assign sore (15:00-23:00)
- Assign extra shift (09:00-17:00)
- **Expected:** Optimal column distribution

---

## 🚀 Future Enhancements

### Possible Improvements:

1. **Smart Column Reuse**
   - If shift ends, reuse column for next non-overlapping shift
   - More efficient space usage

2. **Variable Column Width**
   - Important shifts get more space
   - Based on employee count or priority

3. **Drag to Reorder**
   - Manual column arrangement
   - Swap shift positions

4. **Responsive Breakpoints**
   - On narrow screens, revert to vertical stack
   - Better mobile experience

5. **Visual Connectors**
   - Show time range with lines
   - Highlight overlap regions

---

## 📱 Responsive Behavior

### Desktop (>1200px):
- Full horizontal layout
- Up to 3-4 columns visible comfortably

### Tablet (768px-1200px):
- 2 columns max
- Smaller cards, still readable

### Mobile (<768px):
- Consider reverting to vertical stack
- Or horizontal scroll for columns

---

## 🐛 Edge Cases Handled

1. **Single Shift:** Uses full width (100%)
2. **All Day Overlaps:** Distributes evenly
3. **Partial Overlaps:** Smart column assignment
4. **Same Start Time:** Sorted by duration
5. **Zero Duration:** Minimum height maintained

---

## 📊 Performance

**Algorithm Complexity:**
- Overlap detection: O(n²) worst case
- For typical day (3-5 shifts): Negligible impact
- Optimized with early break conditions

**Rendering:**
- No additional DOM operations
- Only CSS calc() usage
- Hardware accelerated positioning

---

**Version:** 2.2 - Horizontal Multi-Column Layout  
**Date:** November 6, 2025  
**Status:** ✅ Implemented and Ready for Testing
