<?php
// Endpoint AJAX cek whitelist (harus di paling atas, sebelum session_start)
if (isset($_GET['check']) && isset($_GET['nama'])) {
    include 'connect.php';
    $nama = trim($_GET['nama']);
    $stmt = $pdo->prepare("SELECT nama_lengkap, posisi, status_registrasi FROM pegawai_whitelist WHERE nama_lengkap = ?");
    $stmt->execute([$nama]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['status_registrasi'] !== 'terdaftar') {
        echo json_encode([
            'found' => true,
            'nama_lengkap' => $row['nama_lengkap'],
            'posisi' => $row['posisi'],
            'status' => $row['status_registrasi']
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
    exit;
}

session_start();
include 'connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['import'])) {
        // Proses import file
        $file = $_FILES['import_file'] ?? null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $filename = $file['tmp_name'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            
            // Validasi ekstensi file
            if (!in_array($extension, ['csv', 'txt'])) {
                $error = 'Hanya diperbolehkan mengupload file dengan ekstensi CSV atau TXT.';
            } else {
                // Proses sesuai dengan jenis file
                try {
                    if ($extension === 'csv' || $extension === 'txt') {
                        $handle = fopen($filename, "r");
                        if ($handle) {
                            $imported = 0;
                            $skipped = 0;
                            $skippedRows = [];
                            $rowNum = 0;
                            while (($row = fgetcsv($handle, 1000, ";")) !== false) {
                                $rowNum++;
                                // Skip header if first row contains 'nama' or 'posisi'
                                if ($rowNum === 1 && (stripos($row[1] ?? '', 'nama') !== false || stripos($row[2] ?? '', 'posisi') !== false)) {
                                    continue;
                                }
                                $nama = trim($row[1] ?? '');
                                $posisi = trim($row[2] ?? '');
                                if ($nama === '') { $skipped++; $skippedRows[] = $rowNum; continue; }
                                // Cek duplikat
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM pegawai_whitelist WHERE nama_lengkap = ?");
                                $stmt->execute([$nama]);
                                if ($stmt->fetchColumn() > 0) { $skipped++; $skippedRows[] = $rowNum; continue; }
                                // Insert
                                $stmt = $pdo->prepare("INSERT INTO pegawai_whitelist (nama_lengkap, posisi, status_registrasi) VALUES (?, ?, 'pending')");
                                $stmt->execute([$nama, $posisi]);
                                $imported++;
                            }
                            fclose($handle);
                            $success = "Import selesai: $imported data ditambah, $skipped dilewati.";
                            if ($skipped > 0) {
                                $success .= " Baris dilewati: " . implode(", ", $skippedRows) . ".";
                            }
                        } else {
                            $error = "Gagal membaca file.";
                        }
                    }
                } catch (Exception $e) {
                    $error = 'Gagal mengimpor data: ' . $e->getMessage();
                }
            }
        } else {
            $error = 'Gagal mengupload file.';
        }
    } else {
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $posisi = trim($_POST['posisi'] ?? '');
        if ($nama === '') {
            $error = 'Nama tidak boleh kosong.';
        } else {
            try {
                // Cek apakah sudah ada
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM pegawai_whitelist WHERE nama_lengkap = ?");
                $stmt->execute([$nama]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Nama sudah ada di whitelist.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO pegawai_whitelist (nama_lengkap, posisi, status_registrasi) VALUES (?, ?, 'pending')");
                    $stmt->execute([$nama, $posisi !== '' ? $posisi : null]);
                    $success = 'Nama berhasil ditambahkan ke whitelist.';
                }
            } catch (PDOException $e) {
                $error = 'Gagal menambah data: ' . $e->getMessage();
            }
        }
    }
}

// Ambil data whitelist untuk ditampilkan
$data = $pdo->query("SELECT nama_lengkap, posisi, status_registrasi FROM pegawai_whitelist ORDER BY nama_lengkap ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Whitelist Pegawai</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="headercontainer">
    <?php include 'navbar.php'; ?>
</div>
<div class="main-title">Teman KAORI</div>
<div class="subtitle-container">
    <p class="subtitle">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?> [<?= htmlspecialchars($_SESSION['role']); ?>]</p>
</div>
<div class="container">
    <h1>Whitelist Pegawai</h1>
    <?php if ($success): ?>
        <div style="color:green;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="color:red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" style="margin-bottom:20px;">
        <label for="nama_lengkap">Nama Lengkap:</label>
        <input type="text" name="nama_lengkap" id="nama_lengkap" required>
        <label for="posisi">Posisi</label>
        <input type="text" name="posisi" id="posisi" required>
        <button type="submit">Tambah</button>
    </form>
    <form method="post" enctype="multipart/form-data" style="margin-bottom:20px;">
        <label for="import_file">Import file (CSV, TXT):</label>
        <input type="file" name="import_file" id="import_file" accept=".csv,.txt" required>
        <button type="submit" name="import" value="1">Import</button>
    </form>
    <h2>Daftar Whitelist</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Nama Lengkap</th>
            <th>Posisi</th>
            <th>Status Registrasi</th>
        </tr>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($row['posisi']) ?></td>
            <td><?= htmlspecialchars($row['status_registrasi']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<script>
document.getElementById('nama_lengkap').addEventListener('blur', function() {
    var nama = this.value.trim();
    if (nama !== '') {
        fetch('whitelist.php?check=1&nama=' + encodeURIComponent(nama))
            .then(response => response.json())
            .then(data => {
                if (data.found) {
                    document.getElementById('posisi').value = data.posisi;
                    alert('Nama sudah ada di whitelist dengan status: ' + data.status);
                } else {
                    document.getElementById('posisi').value = '';
                }
            });
    }
});
</script>
</body>
</html>
