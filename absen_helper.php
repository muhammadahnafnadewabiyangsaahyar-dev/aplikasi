<?php
// absen_helper.php
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