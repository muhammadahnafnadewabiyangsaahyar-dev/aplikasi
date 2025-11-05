# Integrasi Kalender dengan Database (PDO)

## Overview
Kalender shift karyawan sekarang terhubung dengan database menggunakan PDO melalui API `api_shift_calendar.php`.

## Struktur Database

### Tabel: `shift_assignments`
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FK ke register.id)
- cabang_id (INT, FK ke cabang.id)
- tanggal_shift (DATE)
- status_konfirmasi (ENUM: 'pending', 'confirmed', 'rejected')
- created_by (INT, FK ke register.id)
- created_at (DATETIME)
- updated_at (DATETIME)
```

### Relasi:
- `user_id` → `register.id` (pegawai yang di-assign)
- `cabang_id` → `cabang.id` (cabang dengan shift info)
- `created_by` → `register.id` (admin yang membuat assignment)

## File yang Terlibat

### 1. Backend (PHP + PDO)
- **`connect.php`**: Koneksi PDO ke database
- **`api_shift_calendar.php`**: API endpoint untuk CRUD operations
  - `get_cabang`: Mendapatkan daftar cabang dengan shift
  - `get_pegawai`: Mendapatkan daftar pegawai
  - `get_assignments`: Mendapatkan assignments untuk bulan tertentu
  - `create`: Membuat assignment baru
  - `update`: Update assignment (future use)
  - `delete`: Hapus assignment

### 2. Frontend (JavaScript)
- **`script_kalender_database.js`**: JavaScript untuk integrasi database
  - Load cabang dari database
  - Load pegawai berdasarkan cabang
  - Load assignments per bulan
  - Create/delete assignments
  - Render kalender dengan data dari database

- **`script_hybrid.js`**: JavaScript original untuk localStorage (masih berjalan paralel)

### 3. UI (HTML + CSS)
- **`kalender.php`**: Halaman utama kalender
- **`style.css`**: Styling untuk kalender assignments

## Cara Kerja

### 1. Inisialisasi
```javascript
// Saat halaman dimuat:
1. Load daftar cabang → Populate dropdown "Mode Database"
2. Load daftar pegawai → Populate dropdown "Pilih Karyawan"
3. Load assignments bulan current → Render di kalender
```

### 2. Memilih Cabang
```javascript
// User pilih cabang dari dropdown:
1. Set currentCabangId
2. Load pegawai yang sesuai dengan cabang
3. Refresh kalender untuk show assignments cabang tersebut
```

### 3. Membuat Assignment
```javascript
// User klik tanggal di kalender:
1. Check apakah cabang sudah dipilih
2. Check apakah pegawai sudah dipilih
3. Confirm dialog
4. POST ke api_shift_calendar.php (action: create)
5. Refresh kalender
```

### 4. Menghapus Assignment
```javascript
// User klik assignment yang sudah ada:
1. Show info assignment
2. Confirm dialog untuk delete
3. POST ke api_shift_calendar.php (action: delete)
4. Refresh kalender
```

## API Endpoints

### GET Requests
```
GET api_shift_calendar.php?action=get_cabang
GET api_shift_calendar.php?action=get_pegawai&cabang_id={id}
GET api_shift_calendar.php?action=get_assignments&month=YYYY-MM&cabang_id={id}
```

### POST Requests
```json
// Create Assignment
{
  "action": "create",
  "user_id": 1,
  "cabang_id": 2,
  "tanggal_shift": "2025-11-05"
}

// Delete Assignment
{
  "action": "delete",
  "id": 123
}
```

## Response Format
```json
{
  "status": "success" | "error",
  "message": "Success/error message",
  "data": {...} // Optional
}
```

## Setup Database

### Jalankan SQL untuk membuat tabel:
```bash
mysql -u root aplikasi < create_shift_assignments_table.sql
```

Atau manual:
```sql
SOURCE /Applications/XAMPP/xamppfiles/htdocs/aplikasi/create_shift_assignments_table.sql;
```

## Mode Hybrid

Kalender sekarang berjalan dalam mode hybrid:
1. **Mode LocalStorage** (Original): Menggunakan `script_hybrid.js`
2. **Mode Database** (New): Menggunakan `script_kalender_database.js`

User bisa memilih:
- Dropdown pertama: "Mode LocalStorage (Original)" = mode lama
- Dropdown lainnya: Pilih cabang = mode database

## Fitur yang Tersedia

### ✅ Sudah Implementasi:
- Load cabang dari database
- Load pegawai berdasarkan cabang
- Load assignments per bulan
- Create assignment baru
- Delete assignment
- Visual representation di kalender
- Responsive design

### 🔄 Untuk Pengembangan:
- Update assignment (drag & drop)
- Bulk assignment
- Export laporan shift
- Notifikasi ke pegawai
- Konfirmasi shift oleh pegawai
- Filter dan search advanced

## Security

- ✅ Session check untuk admin only
- ✅ PDO prepared statements (prevent SQL injection)
- ✅ Input validation di backend
- ✅ UNIQUE constraint untuk prevent duplicate assignments
- ✅ Foreign key constraints untuk data integrity

## Testing

### Test Flow:
1. Login sebagai admin
2. Buka halaman kalender
3. Pilih cabang dari dropdown "Mode Database"
4. Pilih pegawai
5. Klik tanggal untuk assign shift
6. Verify assignment muncul di kalender
7. Klik assignment untuk delete
8. Verify assignment hilang

## Troubleshooting

### Assignment tidak muncul:
- Check console browser untuk error
- Verify tabel `shift_assignments` sudah dibuat
- Check API response di Network tab

### Error saat create:
- Verify user_id dan cabang_id valid
- Check apakah sudah ada assignment untuk user di tanggal tersebut
- Check session admin

### CSS tidak apply:
- Hard refresh browser (Cmd+Shift+R)
- Check style.css sudah updated
- Verify path ke CSS file

## Notes
- Timezone diset ke Asia/Makassar (WITA, UTC+8)
- Tanggal format: YYYY-MM-DD
- Time format: HH:MM:SS
- All datetime operations use server timezone
