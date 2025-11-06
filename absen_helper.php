<?php
// absen_helper.php

// ========================================================
// FUNGSI: Hitung Jarak antara 2 Koordinat GPS (Haversine Formula)
// ========================================================
function hitungJarak($lat1, $lon1, $lat2, $lon2) {
    // Radius bumi dalam meter
    $earthRadius = 6371000;
    
    // Convert degrees to radians
    $lat1Rad = deg2rad($lat1);
    $lat2Rad = deg2rad($lat2);
    $lon1Rad = deg2rad($lon1);
    $lon2Rad = deg2rad($lon2);
    
    // Haversine formula
    $deltaLat = $lat2Rad - $lat1Rad;
    $deltaLon = $lon2Rad - $lon1Rad;
    
    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
         cos($lat1Rad) * cos($lat2Rad) *
         sin($deltaLon / 2) * sin($deltaLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    $distance = $earthRadius * $c; // dalam meter
    
    return round($distance, 2); // Return jarak dalam meter, rounded
}

function getAbsenStatusToday($pdo, $user_id) {
    $today = date('Y-m-d');
    $sql = "SELECT waktu_masuk, waktu_keluar FROM absensi WHERE user_id = ? AND DATE(tanggal_absensi) = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $today]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $status = ['masuk' => false, 'keluar' => false];
    if ($row) {
        if (!empty($row['waktu_masuk'])) $status['masuk'] = true;
        if (!empty($row['waktu_keluar'])) $status['keluar'] = true;
    }
    return $status;
}