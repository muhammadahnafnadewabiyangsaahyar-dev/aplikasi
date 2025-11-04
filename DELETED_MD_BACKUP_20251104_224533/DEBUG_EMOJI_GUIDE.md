# Quick Reference - Debug Log Emoji Guide

## Emoji Legend

| Emoji | Meaning | Usage |
|-------|---------|-------|
| ✅ | Success | Operasi berhasil (query sukses, validasi passed, dll) |
| ❌ | Error/Failed | Operasi gagal (query failed, validasi gagal, dll) |
| 🔄 | Processing/Redirect | Sedang memproses atau melakukan redirect |
| 📊 | Verification | Verifikasi data setelah operasi (SELECT setelah UPDATE/INSERT) |
| ➕ | Insert Mode | Mode INSERT - menambah data baru |
| 🔍 | Search/Fetch | Mengambil/mencari data dari database |
| ⚠️  | Warning | Peringatan (misal: trying to delete default position) |

## Log Section Markers

- `=== START ===` - Awal section
- `=== END ===` - Akhir section

## Common Log Patterns

### Pattern 1: Sukses Update
```
✅ CSRF TOKEN VALID
✅ Validasi input passed
✅ No duplicate, proceeding...
🔄 MODE: UPDATE
✅ UPDATE posisi_jabatan SUCCESS
📊 VERIFY UPDATE - Data after update: ...
🔄 REDIRECTING to posisi_jabatan.php?success=update
```

### Pattern 2: Gagal Validasi
```
✅ CSRF TOKEN VALID
❌ Validasi gagal: Nama posisi kosong
```

### Pattern 3: Duplikat
```
✅ CSRF TOKEN VALID
✅ Validasi input passed
❌ Nama posisi sudah ada (duplikat)
```

### Pattern 4: Token Gagal
```
❌ CSRF TOKEN VALIDATION FAILED!
```

## Grep Commands

### Lihat hanya sukses
```bash
tail -f /path/to/error.log | grep "✅"
```

### Lihat hanya error
```bash
tail -f /path/to/error.log | grep "❌"
```

### Lihat operasi UPDATE saja
```bash
tail -f /path/to/error.log | grep "MODE: UPDATE"
```

### Lihat operasi INSERT saja
```bash
tail -f /path/to/error.log | grep "MODE: INSERT"
```

### Lihat semua operasi POSISI
```bash
tail -f /path/to/error.log | grep "POSISI"
```

### Lihat data yang dikirim POST
```bash
tail -f /path/to/error.log | grep "POST Data:"
```

### Lihat hasil verifikasi
```bash
tail -f /path/to/error.log | grep "VERIFY"
```

## Analysis Tips

### 1. Tracking Full Request Flow
Untuk tracking satu request lengkap, ambil Session ID dan filter:
```bash
grep "Session ID: abc123" /path/to/error.log
```

### 2. Finding Last Error
```bash
grep "❌" /path/to/error.log | tail -1
```

### 3. Count Operations
```bash
grep "UPDATE posisi_jabatan SUCCESS" /path/to/error.log | wc -l
grep "INSERT posisi_jabatan SUCCESS" /path/to/error.log | wc -l
grep "DELETE SUCCESS" /path/to/error.log | wc -l
```

### 4. Last 10 Operations
```bash
grep "MODE:" /path/to/error.log | tail -10
```

## Using watch_posisi_log.sh Script

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
./watch_posisi_log.sh
```

Then choose:
- **1**: Monitor all logs in real-time
- **2**: Monitor only POSISI-related logs (recommended)
- **3**: Monitor only successful operations
- **4**: Monitor only errors
- **5**: View last 50 POSISI log lines
- **6**: Export today's POSISI logs to file
- **7**: Clear log file (use with caution!)

## Best Practices

1. **Before Testing**: Clear old logs or note the current timestamp
2. **During Testing**: Run `./watch_posisi_log.sh` option 2 in a separate terminal
3. **After Issue**: Export logs with option 6 for later analysis
4. **Share Logs**: When asking for help, share the exported log file

## Example Analysis Session

```bash
# Terminal 1: Start monitoring
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi
./watch_posisi_log.sh
# Choose option 2

# Terminal 2: Reproduce the issue
# Open browser and perform the action

# Terminal 1: Watch the logs flow
# Look for ❌ markers or unexpected behavior

# If you see an issue, export logs:
# Ctrl+C to stop monitoring
./watch_posisi_log.sh
# Choose option 6

# Analyze the exported file
cat posisi_debug_*.log | grep -A 10 "❌"
```
