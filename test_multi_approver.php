<?php
/**
 * TEST SCRIPT: Multi-Approver System
 * 
 * Test sistem untuk verify bahwa approver baru otomatis terdeteksi
 */

require_once 'connect.php';
require_once 'email_helper.php';

echo "========================================\n";
echo "TEST MULTI-APPROVER SYSTEM\n";
echo "========================================\n\n";

// ========================================
// 1. GET CURRENT APPROVERS
// ========================================
echo "1. CURRENT APPROVERS:\n";
$approvers = getApproverEmails($pdo);

echo "\n   📊 HR (" . count($approvers['hr']) . " orang):\n";
if (empty($approvers['hr'])) {
    echo "      ⚠️ Tidak ada HR ditemukan\n";
} else {
    foreach ($approvers['hr'] as $index => $hr) {
        echo "      " . ($index + 1) . ". {$hr['name']} <{$hr['email']}>\n";
    }
}

echo "\n   📊 Kepala Toko/Owner/Manager (" . count($approvers['kepala_toko']) . " orang):\n";
if (empty($approvers['kepala_toko'])) {
    echo "      ⚠️ Tidak ada Kepala Toko ditemukan\n";
} else {
    foreach ($approvers['kepala_toko'] as $index => $kepala) {
        echo "      " . ($index + 1) . ". {$kepala['name']} <{$kepala['email']}>\n";
    }
}

$total_approvers = count($approvers['hr']) + count($approvers['kepala_toko']);
echo "\n   ✅ Total Approver: $total_approvers orang\n\n";

// ========================================
// 2. SHOW ALL USERS WITH POSITIONS
// ========================================
echo "2. ALL USERS IN DATABASE:\n";
$stmt = $pdo->query("SELECT id, nama_lengkap, posisi, email, role FROM register ORDER BY posisi, nama_lengkap");
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];
foreach ($all_users as $user) {
    $posisi = $user['posisi'];
    if (!isset($grouped[$posisi])) {
        $grouped[$posisi] = [];
    }
    $grouped[$posisi][] = $user;
}

foreach ($grouped as $posisi => $users) {
    echo "\n   📍 Posisi: $posisi\n";
    foreach ($users as $user) {
        $email_status = empty($user['email']) ? '❌ NO EMAIL' : '✅ ' . $user['email'];
        $is_approver = false;
        
        // Check if user is approver
        foreach ($approvers['hr'] as $hr) {
            if ($hr['email'] === $user['email']) {
                $is_approver = true;
                break;
            }
        }
        if (!$is_approver) {
            foreach ($approvers['kepala_toko'] as $kepala) {
                if ($kepala['email'] === $user['email']) {
                    $is_approver = true;
                    break;
                }
            }
        }
        
        $approver_badge = $is_approver ? '🔔 APPROVER' : '';
        echo "      - {$user['nama_lengkap']} | $email_status | Role: {$user['role']} $approver_badge\n";
    }
}

// ========================================
// 3. SIMULATION: ADD NEW HR
// ========================================
echo "\n========================================\n";
echo "3. SIMULATION: Tambah HR Baru\n";
echo "========================================\n\n";

echo "Untuk menambah HR baru:\n";
echo "1. Insert ke database:\n\n";
echo "   INSERT INTO register (nama_lengkap, posisi, email, username, password, role, no_whatsapp, outlet, time_created)\n";
echo "   VALUES (\n";
echo "       'Nama HR Baru',\n";
echo "       'HR',\n";
echo "       'hr.baru@example.com',\n";
echo "       'hrbaru',\n";
echo "       PASSWORD('password'),  -- atau hash yang sesuai\n";
echo "       'admin',\n";
echo "       '08123456789',\n";
echo "       'Citraland Gowa',\n";
echo "       CURDATE()\n";
echo "   );\n\n";

echo "2. Sistem OTOMATIS mendeteksi HR baru\n";
echo "3. Run script ini lagi untuk verify\n";
echo "4. Test email dengan: php test_email_notification.php\n\n";

// ========================================
// 4. POSISI PATTERNS
// ========================================
echo "========================================\n";
echo "4. POSISI YANG TERDETEKSI OTOMATIS\n";
echo "========================================\n\n";

echo "HR (pattern):\n";
echo "   - Posisi LIKE '%HR%' OR LIKE '%hr%'\n";
echo "   - Contoh: 'HR', 'HR Manager', 'Staff HR', 'Human Resource'\n\n";

echo "Kepala Toko/Owner/Manager (pattern):\n";
echo "   - Posisi LIKE '%owner%' OR '%Owner%'\n";
echo "   - Posisi LIKE '%kepala%' OR '%Kepala%'\n";
echo "   - Posisi LIKE '%manager%' OR '%Manager%'\n";
echo "   - Contoh: 'Owner', 'Kepala Toko', 'General Manager'\n\n";

// ========================================
// 5. TEST EMAIL DISTRIBUTION
// ========================================
echo "========================================\n";
echo "5. EMAIL DISTRIBUTION TEST\n";
echo "========================================\n\n";

if ($total_approvers === 0) {
    echo "⚠️ TIDAK ADA APPROVER!\n";
    echo "Email akan gagal dikirim karena tidak ada recipient.\n\n";
    
    // Check if there's any admin as fallback
    $stmt_admin = $pdo->query("SELECT COUNT(*) FROM register WHERE role = 'admin' AND email IS NOT NULL AND email != ''");
    $admin_count = $stmt_admin->fetchColumn();
    
    if ($admin_count > 0) {
        echo "✅ Fallback: Ada $admin_count admin dengan email.\n";
        echo "Sistem akan menggunakan admin sebagai recipient.\n\n";
    } else {
        echo "❌ CRITICAL: Tidak ada admin dengan email!\n";
        echo "Sistem tidak bisa mengirim email notification.\n\n";
    }
} else {
    echo "✅ Email akan dikirim ke $total_approvers approver:\n\n";
    
    if (!empty($approvers['hr'])) {
        echo "   📧 HR Recipients:\n";
        foreach ($approvers['hr'] as $index => $hr) {
            echo "      " . ($index + 1) . ". TO: {$hr['name']} <{$hr['email']}>\n";
        }
    }
    
    if (!empty($approvers['kepala_toko'])) {
        echo "\n   📧 Kepala Toko Recipients:\n";
        foreach ($approvers['kepala_toko'] as $index => $kepala) {
            echo "      " . ($index + 1) . ". TO: {$kepala['name']} <{$kepala['email']}>\n";
        }
    }
    
    echo "\n";
}

// ========================================
// 6. RECOMMENDATIONS
// ========================================
echo "========================================\n";
echo "6. REKOMENDASI\n";
echo "========================================\n\n";

$issues = [];

if (count($approvers['hr']) === 0) {
    $issues[] = "⚠️ Tidak ada HR. Tambah user dengan posisi 'HR'";
}

if (count($approvers['kepala_toko']) === 0) {
    $issues[] = "ℹ️ Tidak ada Kepala Toko/Owner. Tambah jika perlu.";
}

if (count($approvers['hr']) === 1) {
    $issues[] = "ℹ️ Hanya 1 HR. Pertimbangkan tambah backup HR.";
}

// Check users without email
$stmt_no_email = $pdo->query("SELECT COUNT(*) FROM register WHERE email IS NULL OR email = ''");
$no_email_count = $stmt_no_email->fetchColumn();
if ($no_email_count > 0) {
    $issues[] = "⚠️ Ada $no_email_count user tanpa email. Update email mereka jika perlu notifikasi.";
}

if (empty($issues)) {
    echo "✅ Sistem sudah optimal!\n";
    echo "✅ Semua approver sudah terkonfigurasi dengan baik.\n\n";
} else {
    foreach ($issues as $issue) {
        echo "$issue\n";
    }
    echo "\n";
}

// ========================================
// SUMMARY
// ========================================
echo "========================================\n";
echo "✅ TEST COMPLETE!\n";
echo "========================================\n\n";

echo "SUMMARY:\n";
echo "- Total Approver: $total_approvers orang\n";
echo "- HR: " . count($approvers['hr']) . " orang\n";
echo "- Kepala Toko: " . count($approvers['kepala_toko']) . " orang\n\n";

echo "NEXT STEPS:\n";
echo "1. Jika perlu tambah approver, ikuti guide di: MENAMBAH_HR_KEPALA_TOKO.md\n";
echo "2. Test email dengan: php test_email_notification.php\n";
echo "3. Test via web: http://localhost/aplikasi/test_email_notification_web.php\n\n";

echo "========================================\n";
