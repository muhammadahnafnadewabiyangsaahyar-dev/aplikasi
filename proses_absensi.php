<?php
session_start();
include 'connect.php';
include 'absen_helper.php';
header('Content-Type: application/json');

function send_json($arr) {
    echo json_encode($arr);
    exit();
}

// 1. Keamanan Awal: Cek Login
if (!isset($_SESSION['user_id'])) {
    send_json(['status'=>'error','message'=>'Not logged in']);
}
$user_id = $_SESSION['user_id'];

$home_url = ($_SESSION['role'] ?? '') === 'admin' ? 'mainpageadmin.php' : 'mainpageuser.php';

// 2. Proses hanya jika metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['status'=>'error','message'=>'Invalid method']);
}

// 3. Ambil Data dari POST
$latitude_pengguna = $_POST['latitude'] ?? null;
$longitude_pengguna = $_POST['longitude'] ?? null;
$foto_base64 = $_POST['foto_absensi_base64'] ?? '';
$tipe_absen = $_POST['tipe_absen'] ?? ''; // 'masuk' atau 'keluar'
$waktu_absen_sekarang_ts = strtotime(date('H:i:s')); 

// 4. Validasi Input Awal
if ($latitude_pengguna === null || $longitude_pengguna === null || empty($tipe_absen) || !in_array($tipe_absen, ['masuk', 'keluar'])) {
    send_json(['status'=>'error','message'=>'Data tidak lengkap']);
}
if ($tipe_absen == 'masuk' && empty($foto_base64)) {
    send_json(['status'=>'error','message'=>'Foto wajib untuk absen masuk']);
}


try {
    // --- 5. Ambil Data SEMUA Cabang (TERMASUK JAM SHIFT) ---
    // PERBAIKAN: Query ke tabel yang benar `cabang`
    $sql_all_branches = "SELECT id, latitude, longitude, radius_meter, jam_masuk, jam_keluar FROM cabang";
    $all_branches = $pdo->query($sql_all_branches)->fetchAll();

    if (empty($all_branches)) {
        send_json(['status'=>'error','message'=>'Data cabang tidak ada']);
    }

    // ========================================================
    // --- BLOK PERBAIKAN 1: Cari SEMUA LOKASI VALID ---
    // ========================================================
    $cabang_valid_berdasarkan_lokasi = []; 
    foreach ($all_branches as $cabang) {
        $jarak = haversineGreatCircleDistance($latitude_pengguna, $longitude_pengguna, $cabang['latitude'], $cabang['longitude']);
        if ($jarak !== false && $jarak <= (int)$cabang['radius_meter']) {
            $cabang_valid_berdasarkan_lokasi[] = $cabang;
        }
    }

    if (empty($cabang_valid_berdasarkan_lokasi)) {
        send_json(['status'=>'error','message'=>'Lokasi tidak sah']);
    }
    
    // ========================================================
    // --- BLOK PERBAIKAN 2: Cari SHIFT TERBAIK dari Lokasi Valid ---
    // ========================================================
    $shift_terpilih = null;
    $selisih_terkecil = PHP_INT_MAX; 

    $waktu_absen_key = ($tipe_absen == 'masuk') ? 'jam_masuk' : 'jam_keluar';

    foreach ($cabang_valid_berdasarkan_lokasi as $cabang) {
        $jam_shift_ts = strtotime($cabang[$waktu_absen_key]);
        $selisih = abs($waktu_absen_sekarang_ts - $jam_shift_ts);

        if ($selisih < $selisih_terkecil) {
            $selisih_terkecil = $selisih;
            $shift_terpilih = $cabang;
        }
    }
    
    if ($shift_terpilih === null) {
        // Fallback jika terjadi error, ambil yang pertama
        $shift_terpilih = $cabang_valid_berdasarkan_lokasi[0];
    }

    // --- 6. Gunakan Data Shift Terpilih ---
    $status_lokasi = 'Valid';
    $jam_masuk_cabang_ini_str = $shift_terpilih['jam_masuk'];
    
    // --- 7. Proses dan Simpan Foto (Hanya saat absen masuk) ---
    $nama_file_foto = null;
    if ($tipe_absen == 'masuk') {
        if (preg_match('/^data:image\/(\w+);base64,/', $foto_base64, $type)) {
            $data_gambar_base64 = substr($foto_base64, strpos($foto_base64, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                 send_json(['status'=>'error','message'=>'Tipe foto tidak valid']);
            }
            $data_gambar_biner = base64_decode($data_gambar_base64);
            if ($data_gambar_biner === false) {
                 send_json(['status'=>'error','message'=>'Gagal decode foto']);
            }

            $nama_file_foto = 'absen_' . $user_id . '_' . time() . '.' . ($type == 'jpeg' ? 'jpg' : $type);
            $path_simpan_foto = 'uploads/' . $nama_file_foto;
            
            // PENTING: Pastikan folder 'uploads' ada dan writable
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }

            if (!file_put_contents($path_simpan_foto, $data_gambar_biner)) {
                send_json(['status'=>'error','message'=>'Gagal simpan foto']);
            }
        } else {
            send_json(['status'=>'error','message'=>'Format foto tidak valid']);
        }
    }

    // --- 8. Simpan Catatan Absensi ke Database ---
    $tanggal_hari_ini = date('Y-m-d');

    if ($tipe_absen == 'masuk') {
        
        // Hitung Keterlambatan
        $menit_terlambat = 0;
        $status_keterlambatan = 'tepat waktu';
        $jam_masuk_ts = strtotime($jam_masuk_cabang_ini_str);
        $selisih_detik = $waktu_absen_sekarang_ts - $jam_masuk_ts;
        
        if ($selisih_detik > 0) { 
            $menit_terlambat = ceil($selisih_detik / 60);
            if ($menit_terlambat > 20) {
                $status_keterlambatan = 'terlambat lebih dari 20 menit';
            } else {
                $status_keterlambatan = 'terlambat kurang dari 20 menit';
            }
        }
        
        // Cek duplikat absen masuk
        $sql_cek = "SELECT id FROM absensi WHERE user_id = ? AND tanggal_absensi = ?";
        $stmt_cek = $pdo->prepare($sql_cek);
        $stmt_cek->execute([$user_id, $tanggal_hari_ini]);
        
        if ($stmt_cek->fetch()) {
            if ($nama_file_foto && file_exists('uploads/' . $nama_file_foto)) unlink('uploads/' . $nama_file_foto); 
            send_json(['status'=>'error','message'=>'Sudah absen masuk hari ini']);
        }

        // INSERT data absen masuk 
        $sql_insert = "INSERT INTO absensi 
                       (user_id, waktu_masuk, status_lokasi, latitude_absen, longitude_absen, foto_absen, tanggal_absensi, menit_terlambat, status_keterlambatan) 
                       VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([
            $user_id, $status_lokasi, $latitude_pengguna, $longitude_pengguna, 
            $nama_file_foto, $tanggal_hari_ini, $menit_terlambat, $status_keterlambatan
        ]);
        send_json(['status'=>'success','next'=>'keluar']);
    } elseif ($tipe_absen == 'keluar') {
        
        // Cek apakah sudah absen masuk hari ini DAN belum absen keluar
        $sql_cek_keluar = "SELECT id FROM absensi WHERE user_id = ? AND tanggal_absensi = ? AND waktu_keluar IS NULL";
        $stmt_cek_keluar = $pdo->prepare($sql_cek_keluar);
        $stmt_cek_keluar->execute([$user_id, $tanggal_hari_ini]);
        $data_absen_masuk = $stmt_cek_keluar->fetch();

        if (!$data_absen_masuk) {
            send_json(['status'=>'error','message'=>'Belum absen masuk atau sudah absen keluar']);
        }
        
        $absen_id_yang_diupdate = $data_absen_masuk['id'];

        // --- Ambil data shift user hari ini ---
        $sql_shift = "SELECT jam_keluar FROM cabang WHERE id = ?";
        $stmt_shift = $pdo->prepare($sql_shift);
        $stmt_shift->execute([$shift_terpilih['id']]);
        $shift = $stmt_shift->fetch();
        $jam_keluar_shift = $shift ? $shift['jam_keluar'] : null;

        $waktu_keluar_sekarang = date('H:i:s');
        $is_overwork = false;
        if ($jam_keluar_shift && $waktu_keluar_sekarang > $jam_keluar_shift) {
            $is_overwork = true;
        }

        // UPDATE waktu_keluar dan status_lembur
        $sql_update = "UPDATE absensi SET waktu_keluar = NOW(), status_lembur = ? WHERE id = ?";
        $status_lembur = $is_overwork ? 'Pending' : 'Not Applicable';
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$status_lembur, $absen_id_yang_diupdate]);
        send_json(['status'=>'success','next'=>'done']);
    }

} catch (PDOException $e) {
    error_log("Proses Absensi Gagal: " . $e->getMessage());
    send_json(['status'=>'error','message'=>'DB error']);
}

function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}
?>