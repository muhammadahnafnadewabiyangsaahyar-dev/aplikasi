# 🎯 Panduan Auto-Check Shift Assignment

**Tanggal**: 6 November 2025  
**Status**: ✅ SELESAI

## 📋 Fitur Baru

### **Auto-Check Pegawai yang Sudah Punya Shift**

Sekarang, ketika membuka modal "Assign Shift", pegawai yang sudah memiliki shift pada tanggal tersebut akan **otomatis tercentang**. Ini memudahkan admin untuk:
- Melihat siapa saja yang sudah dijadwalkan
- Mengubah atau membatalkan shift dengan mudah
- Menghindari penghapusan shift secara tidak sengaja

### **Pembatalan Shift dengan Unchecking**

Admin dapat **membatalkan shift** dengan cara **menghapus centang** pada checkbox pegawai yang sudah punya shift.

**Catatan**: Pembatalan shift **tidak berlaku** untuk shift dengan status:
- ✅ **Approved** (sudah disetujui)
- 🏥 **Izin**
- 🤒 **Sakit**
- 🔄 **Reschedule**

Shift dengan status tersebut akan **terkunci** (disabled) dan tidak bisa dibatalkan dari modal assign shift.

---

## 🔧 Perubahan Teknis

### 1. **Perubahan Fungsi `checkIfPegawaiHasShift()`**

**Sebelum**:
```javascript
function checkIfPegawaiHasShift(pegawaiId, date) {
    // ...
    return assignments.some(assignment => 
        assignment.user_id == pegawaiId && 
        assignment.shift_date === date
    );
}
```

**Return**: `true` atau `false`

**Sesudah**:
```javascript
function checkIfPegawaiHasShift(pegawaiId, date) {
    // ...
    const assignment = assignments.find(assignment => 
        assignment.user_id == pegawaiId && 
        assignment.shift_date === date
    );
    
    return assignment || null;
}
```

**Return**: `assignment object` atau `null`

**Keuntungan**: Sekarang kita dapat mengakses detail shift seperti `status_konfirmasi`, `shift_type`, dll.

---

### 2. **Perubahan Fungsi `createPegawaiCard()`**

#### a. Deteksi Status Shift
```javascript
const shiftAssignment = checkIfPegawaiHasShift(pegawai.id, date);
const hasShift = !!shiftAssignment;

// Check if shift is locked
let isLocked = false;
let lockReason = '';
if (shiftAssignment) {
    const status = shiftAssignment.status_konfirmasi || 'pending';
    if (status === 'approved' || status === 'izin' || status === 'sakit' || status === 'reschedule') {
        isLocked = true;
        lockReason = status === 'approved' ? 'Approved' : 
                    status === 'izin' ? 'Izin' : 
                    status === 'sakit' ? 'Sakit' : 'Reschedule';
    }
}
```

#### b. Auto-Check dan Disable
```javascript
// Checkbox dengan atribut checked dan disabled
<input type="checkbox" 
       class="pegawai-checkbox" 
       data-pegawai-id="${pegawai.id}" 
       ${hasShift ? 'checked' : ''} 
       ${isLocked ? 'disabled' : ''}>
```

#### c. Badge untuk Status
```javascript
let shiftBadge = '';
if (hasShift) {
    if (isLocked) {
        // Badge merah dengan ikon gembok
        shiftBadge = `<div class="pegawai-card-badge badge-locked">🔒 ${lockReason}</div>`;
    } else {
        // Badge orange biasa
        shiftBadge = '<div class="pegawai-card-badge">Sudah punya shift</div>';
    }
}
```

#### d. Prevent Click pada Locked Card
```javascript
card.addEventListener('click', function(e) {
    // Don't allow interaction with locked shifts
    if (isLocked) {
        return;
    }
    // ... rest of code
});
```

---

### 3. **Perubahan Fungsi `saveDayShiftAssignment()`**

#### a. Deteksi Pembatalan Shift
```javascript
// Get all checkboxes (both checked and unchecked)
const allCheckboxes = document.querySelectorAll('.pegawai-checkbox:not([disabled])');

// Prepare cancellations for unchecked pegawai who had shifts
const cancellations = [];
allCheckboxes.forEach(cb => {
    const pegawaiId = cb.dataset.pegawaiId;
    const isChecked = cb.checked;
    const shiftAssignment = checkIfPegawaiHasShift(pegawaiId, date);
    
    // If pegawai had shift but is now unchecked, mark for cancellation
    if (shiftAssignment && !isChecked) {
        const assignmentKey = Object.keys(shiftAssignments).find(key => {
            const assignment = shiftAssignments[key];
            return assignment.user_id == pegawaiId && assignment.shift_date === date;
        });
        
        if (assignmentKey) {
            cancellations.push({
                assignment_id: shiftAssignments[assignmentKey].id,
                user_id: pegawaiId,
                shift_date: date
            });
        }
    }
});
```

#### b. Konfirmasi Pembatalan
```javascript
if (cancellations.length > 0) {
    const confirmMsg = `⚠️ Anda akan membatalkan shift untuk ${cancellations.length} pegawai.\nApakah Anda yakin?`;
    if (!confirm(confirmMsg)) {
        return;
    }
}
```

#### c. Proses Pembatalan (Delete Shift)
```javascript
// First, handle cancellations (delete shifts)
if (cancellations.length > 0) {
    for (const cancellation of cancellations) {
        const deleteResponse = await fetch('api_shift_calendar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete_shift',
                id: cancellation.assignment_id
            })
        });
        
        const deleteResult = await deleteResponse.json();
        if (deleteResult.status !== 'success') {
            console.error('Failed to delete shift:', cancellation, deleteResult);
        }
    }
}
```

#### d. Proses Assignment Baru
```javascript
// Then, handle new assignments
if (assignments.length > 0) {
    const response = await fetch('api_shift_calendar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'assign_shifts',
            cabang_id: currentCabangId,
            assignments: assignments
        })
    });
    // ...
}
```

#### e. Feedback ke User
```javascript
if (result.status === 'success') {
    alert(`✅ Shift berhasil disimpan!\n- Ditambahkan: ${assignments.length}\n- Dibatalkan: ${cancellations.length}`);
    closeDayAssignModal();
    loadShiftAssignments();
}
```

---

### 4. **Perubahan CSS**

#### a. Badge untuk Shift Status
```css
/* Badge for shift status */
.pegawai-card-badge {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 10px;
    font-weight: bold;
    margin-top: 5px;
    display: inline-block;
    background-color: #ff9800;
    color: white;
}

.pegawai-card-badge.badge-locked {
    background-color: #f44336;
    color: white;
}
```

#### b. Styling untuk Locked Card
```css
/* Locked shift card styling */
.pegawai-card.shift-locked {
    border-left: 4px solid #f44336;
    background-color: #ffebee;
    opacity: 0.7;
    cursor: not-allowed;
}

.pegawai-card.shift-locked:hover {
    border-color: #f44336;
    box-shadow: none;
    transform: none;
}

.pegawai-card.shift-locked input[type="checkbox"] {
    cursor: not-allowed;
}
```

---

## 🎯 Cara Kerja

### Skenario 1: Assign Shift Baru
```
1. Admin membuka kalender, klik tanggal
2. Modal terbuka, menampilkan list pegawai
3. Pegawai yang BELUM punya shift: checkbox KOSONG
4. Pegawai yang SUDAH punya shift: checkbox TERCENTANG
5. Admin pilih shift dari dropdown
6. Admin centang/uncentang pegawai sesuai kebutuhan
7. Klik "Simpan Shift"
8. System menambahkan/membatalkan shift sesuai checkbox
```

### Skenario 2: Membatalkan Shift
```
1. Admin buka tanggal yang sudah ada shift
2. Modal terbuka, pegawai dengan shift sudah TERCENTANG
3. Admin UNCHECK pegawai yang shiftnya mau dibatalkan
4. Klik "Simpan Shift"
5. System tanya konfirmasi: "Anda akan membatalkan shift untuk X pegawai"
6. Admin klik "OK"
7. Shift dibatalkan (dihapus dari database)
```

### Skenario 3: Shift yang Locked
```
1. Admin buka tanggal, ada pegawai dengan shift status "Approved"
2. Modal terbuka, pegawai tersebut:
   - Checkbox TERCENTANG dan DISABLED (tidak bisa diubah)
   - Badge merah "🔒 Approved"
   - Card berwarna merah muda (ffebee)
   - Cursor: not-allowed
3. Admin TIDAK BISA uncheck atau mengubah shift tersebut
4. Shift tersebut terlindungi dari perubahan
```

---

## 🔍 Status yang Mengunci Shift

| Status | Simbol | Warna Badge | Bisa Dibatalkan? |
|--------|--------|-------------|------------------|
| **Approved** | 🔒 | Merah | ❌ Tidak |
| **Izin** | 🔒 | Merah | ❌ Tidak |
| **Sakit** | 🔒 | Merah | ❌ Tidak |
| **Reschedule** | 🔒 | Merah | ❌ Tidak |
| **Pending** | - | Orange | ✅ Ya |
| **Declined** | - | Orange | ✅ Ya |

---

## ✅ Hasil Akhir

### Fitur yang Berhasil Ditambahkan:

1. ✅ **Auto-check pegawai yang sudah punya shift**
   - Checkbox otomatis tercentang saat modal dibuka
   - Tidak perlu manual select ulang

2. ✅ **Pembatalan shift dengan uncheck**
   - Uncheck = batalkan shift
   - Konfirmasi sebelum dibatalkan
   - Feedback jumlah shift yang dibatalkan

3. ✅ **Proteksi untuk shift tertentu**
   - Approved, Izin, Sakit, Reschedule terlindungi
   - Checkbox disabled
   - Visual feedback (badge merah, background merah muda)

4. ✅ **Feedback yang jelas**
   - Alert menampilkan jumlah shift ditambahkan & dibatalkan
   - Konfirmasi sebelum pembatalan
   - Badge visual untuk status shift

---

## 🧪 Cara Testing

### Test 1: Auto-Check
```
1. Assign shift ke "John Doe" pada tanggal 10 Nov
2. Tutup modal
3. Buka lagi modal untuk tanggal 10 Nov
4. ✓ "John Doe" harus sudah tercentang
```

### Test 2: Pembatalan Shift
```
1. Buka modal tanggal yang sudah ada shift
2. Uncheck salah satu pegawai yang tercentang
3. Klik "Simpan Shift"
4. ✓ Muncul konfirmasi "Anda akan membatalkan shift untuk 1 pegawai"
5. Klik OK
6. ✓ Shift berhasil dibatalkan
7. Refresh kalender
8. ✓ Shift pegawai tersebut sudah tidak ada
```

### Test 3: Shift Locked
```
1. Buat shift dengan status "Approved" (manual di database atau lewat approval)
2. Buka modal tanggal tersebut
3. ✓ Pegawai dengan shift approved:
   - Checkbox tercentang dan disabled
   - Badge merah "🔒 Approved"
   - Card berwarna merah muda
   - Tidak bisa diklik
4. Coba save dengan pegawai lain
5. ✓ Shift approved tidak berubah
```

### Test 4: Mixed Scenario
```
1. Tanggal 15 Nov ada:
   - "John Doe": Shift Pagi (Pending)
   - "Jane Smith": Shift Siang (Approved)
   - "Bob Johnson": Tidak ada shift
2. Buka modal tanggal 15 Nov
3. ✓ "John Doe" tercentang (bisa diubah)
4. ✓ "Jane Smith" tercentang dan disabled
5. ✓ "Bob Johnson" tidak tercentang
6. Uncheck "John Doe"
7. Check "Bob Johnson"
8. Klik "Simpan Shift"
9. ✓ Alert: "Ditambahkan: 1, Dibatalkan: 1"
10. ✓ Shift John dihapus, shift Bob ditambahkan
11. ✓ Shift Jane tidak berubah
```

---

## 📝 Catatan Penting

### 1. **Status Konfirmasi**
Status `status_konfirmasi` di database memiliki nilai:
- `pending`: Shift baru, belum dikonfirmasi
- `approved`: Sudah disetujui (locked)
- `declined`: Ditolak (bisa diubah)
- `izin`: Pegawai izin (locked)
- `sakit`: Pegawai sakit (locked)
- `reschedule`: Dijadwal ulang (locked)

### 2. **Delete vs Update**
Pembatalan shift menggunakan **DELETE**, bukan UPDATE status:
```javascript
action: 'delete_shift',
id: assignment_id
```

Ini memastikan shift benar-benar dihapus dari database.

### 3. **Seleksi Checkbox**
Query selector menggunakan `:not([disabled])` untuk mengabaikan shift yang locked:
```javascript
const allCheckboxes = document.querySelectorAll('.pegawai-checkbox:not([disabled])');
```

### 4. **Performance**
Untuk cabang dengan banyak pegawai (>100), proses pembatalan dilakukan **sequential** (satu per satu) untuk menghindari race condition:
```javascript
for (const cancellation of cancellations) {
    await fetch(...);  // Sequential
}
```

---

## 🚀 Peningkatan di Masa Depan

Fitur yang bisa ditambahkan:
- [ ] Batch delete untuk performa lebih baik
- [ ] Undo pembatalan shift
- [ ] Log history perubahan shift
- [ ] Filter pegawai berdasarkan status shift
- [ ] Bulk edit untuk multiple dates
- [ ] Reason field untuk pembatalan shift

---

**Update Terakhir**: 6 November 2025  
**Developer**: GitHub Copilot Assistant  
**Status**: Production Ready ✅

---

## 📸 Screenshot Visual

### Before (Sebelum):
```
[ ] John Doe - Stock Keeper • Adhyaksa
[ ] Jane Smith - Kasir • Adhyaksa
[ ] Bob Johnson - Staff Toko • Adhyaksa
```
*Semua checkbox kosong meskipun ada yang sudah punya shift*

### After (Sesudah):
```
[✓] John Doe - Stock Keeper • Adhyaksa
    [Sudah punya shift]

[✓] Jane Smith - Kasir • Adhyaksa  (DISABLED)
    [🔒 Approved]

[ ] Bob Johnson - Staff Toko • Adhyaksa
```
*Checkbox auto-checked, status jelas, locked shift tidak bisa diubah*

---

## 🐛 Troubleshooting

### Problem: Checkbox tidak auto-checked
**Solusi**: 
1. Cek console log: `console.log('shiftAssignment:', shiftAssignment)`
2. Pastikan `checkIfPegawaiHasShift()` return object, bukan boolean
3. Pastikan date format sama: `YYYY-MM-DD`

### Problem: Shift terhapus padahal status approved
**Solusi**:
1. Cek `isLocked` logic di `createPegawaiCard()`
2. Pastikan `status_konfirmasi` di database benar
3. Pastikan checkbox memiliki attribute `disabled`

### Problem: Pembatalan tidak bekerja
**Solusi**:
1. Cek API endpoint `delete_shift` di `api_shift_calendar.php`
2. Cek permission di database (foreign key cascade?)
3. Cek console log untuk error message

---

**🎉 Fitur ini meningkatkan user experience dan mengurangi human error dalam manajemen shift!**
