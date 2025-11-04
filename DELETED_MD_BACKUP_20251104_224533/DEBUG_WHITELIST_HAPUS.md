# 🔧 DEBUG: Error Hapus User di Whitelist

## ❌ Error Yang Terjadi:
```
http://localhost/aplikasi/whitelist.php?error=Invalid+request.+Please+try+again.
```

## 🔍 Perbaikan Yang Sudah Dilakukan:

### 1. **Menambahkan value="1" pada button hapus**
   - Button submit harus punya value agar `isset()` berfungsi
   - File: `whitelist.php` baris ~376

### 2. **Memperbaiki struktur kondisi POST handler**
   - Urutan: import → edit → hapus → tambah baru → catch-all
   - Handler hapus tidak lagi jatuh ke handler tambah baru

### 3. **Menambahkan debug logging**
   - Log di awal POST handler
   - Log di handler hapus
   - Log di catch-all

## 📋 LANGKAH TESTING:

### 1. **Coba Hapus User di Browser**
   - Buka: http://localhost/aplikasi/whitelist.php
   - Login sebagai admin
   - Klik tombol "Hapus" pada salah satu user
   - Konfirmasi dialog "Yakin hapus pegawai ini?"

### 2. **Lihat Error Log**
   ```bash
   tail -n 100 /Applications/XAMPP/xamppfiles/logs/error_log | grep -E "(POST received|HAPUS HANDLER|CATCH-ALL)"
   ```

### 3. **Analisis Output Log**

   **Jika masuk ke HAPUS HANDLER:**
   ```
   [timestamp] POST received: Array(...)
   [timestamp] HAPUS HANDLER: hapus=1
   [timestamp] HAPUS HANDLER: hapus_nama=John Doe
   [timestamp] HAPUS HANDLER: Mencoba hapus: John Doe
   [timestamp] HAPUS HANDLER: Berhasil hapus, redirecting dengan success
   ```
   ✅ **BERARTI BERHASIL!**

   **Jika masuk ke CATCH-ALL:**
   ```
   [timestamp] POST received: Array(...)
   [timestamp] CATCH-ALL: POST tidak dikenali!
   [timestamp] CATCH-ALL: isset import=NO
   [timestamp] CATCH-ALL: isset edit=NO
   [timestamp] CATCH-ALL: isset hapus=NO  ← PROBLEM DI SINI
   [timestamp] CATCH-ALL: isset nama_lengkap=NO
   ```
   ❌ **BERARTI ADA MASALAH**

   **Kemungkinan Penyebab:**
   - Button submit tidak mengirim `name="hapus"`
   - JavaScript mencegah form submit
   - Browser issue (cache, addon)
   - CSRF token invalid

## 🛠️ SOLUSI ALTERNATIF (Jika Masih Error):

### Opsi 1: Ubah Button Hapus Menjadi Link dengan Konfirmasi

Edit file `whitelist.php` baris ~369-377:

**GANTI:**
```php
<td><a href="whitelist.php?edit_nama=<?=urlencode($row['nama_lengkap'])?>">Edit</a> |
    <form method="post" action="whitelist.php" style="display:inline;" onsubmit="return confirm('Yakin hapus pegawai ini?');">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="hapus_nama" value="<?= htmlspecialchars($row['nama_lengkap']) ?>">
        <button type="submit" name="hapus" value="1" style="color:red;background:none;border:none;cursor:pointer;">Hapus</button>
    </form>
</td>
```

**DENGAN:**
```php
<td>
    <a href="whitelist.php?edit_nama=<?=urlencode($row['nama_lengkap'])?>">Edit</a> | 
    <a href="whitelist.php?hapus_nama=<?=urlencode($row['nama_lengkap'])?>&csrf=<?=$_SESSION['csrf_token']?>" 
       onclick="return confirm('Yakin hapus pegawai <?=htmlspecialchars($row['nama_lengkap'])?>?');" 
       style="color:red;">Hapus</a>
</td>
```

**Dan ubah handler di baris ~48 (sebelum POST handler):**
```php
// Handler hapus via GET (dengan CSRF protection)
if (isset($_GET['hapus_nama']) && isset($_GET['csrf'])) {
    if ($_GET['csrf'] === $_SESSION['csrf_token']) {
        $hapus_nama = trim($_GET['hapus_nama']);
        if ($hapus_nama !== '') {
            try {
                $stmt = $pdo->prepare("DELETE FROM pegawai_whitelist WHERE nama_lengkap = ?");
                $stmt->execute([$hapus_nama]);
                header('Location: whitelist.php?success=' . urlencode('Pegawai berhasil dihapus dari whitelist.'));
                exit;
            } catch (PDOException $e) {
                header('Location: whitelist.php?error=' . urlencode('Gagal menghapus pegawai: ' . $e->getMessage()));
                exit;
            }
        } else {
            header('Location: whitelist.php?error=' . urlencode('Nama pegawai tidak valid.'));
            exit;
        }
    } else {
        header('Location: whitelist.php?error=' . urlencode('Invalid CSRF token.'));
        exit;
    }
}
```

### Opsi 2: Test Browser Lain

Kadang addon browser (ad blocker, form filler, security extension) bisa mengubah POST data.

**Test di browser berbeda:**
- Safari (private mode)
- Chrome (incognito mode)
- Firefox (private mode)

### Opsi 3: Test dengan cURL

```bash
# Get CSRF token dulu
curl -c cookies.txt "http://localhost/aplikasi/login.php" -d "username=admin&password=yourpassword"

# Test hapus user
curl -b cookies.txt "http://localhost/aplikasi/whitelist.php" \
  -d "csrf_token=YOUR_CSRF_TOKEN_HERE" \
  -d "hapus_nama=John+Doe" \
  -d "hapus=1"
```

## 📊 CHECKLIST DEBUG:

- [ ] Test hapus user di browser
- [ ] Cek error log untuk melihat alur POST
- [ ] Pastikan `isset($_POST['hapus'])` = YES
- [ ] Pastikan `$_POST['hapus_nama']` berisi nama user
- [ ] Pastikan CSRF token valid
- [ ] Test di browser lain (jika perlu)
- [ ] Implementasi Opsi 1 (GET method) jika POST tetap gagal

## 🎯 EXPECTED RESULT:

**Setelah klik "Hapus" dan konfirmasi:**
```
http://localhost/aplikasi/whitelist.php?success=Pegawai+berhasil+dihapus+dari+whitelist.
```

Dan user terhapus dari tabel whitelist.

---

## 🔄 CLEANUP SETELAH SELESAI:

**Hapus debug logging dari whitelist.php:**

1. Hapus baris ~51:
   ```php
   error_log("POST received: " . print_r($_POST, true));
   ```

2. Hapus baris ~171-174 (error_log di HAPUS HANDLER)

3. Hapus baris ~208-213 (error_log di CATCH-ALL)

---

📅 **Created:** 2025-11-03
🎯 **Status:** DEBUGGING
🔧 **File:** whitelist.php (dengan debug logging)
