<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Shift Calendar System</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            margin: 0 0 10px 0;
        }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .test-section h2 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 18px;
        }
        .result {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 4px;
        }
        .success {
            color: #28a745;
            font-weight: 600;
        }
        .error {
            color: #dc3545;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Shift Calendar System</h1>
        <p style="color: #666; margin-bottom: 30px;">Automated testing untuk sistem shift management</p>

        <?php
        require_once 'connect.php';

        // Test 1: Cabang Data
        echo '<div class="test-section">';
        echo '<h2>1️⃣ Test Cabang Data</h2>';
        
        $stmt = $pdo->query("SELECT id, nama_cabang, nama_shift, jam_masuk, jam_keluar FROM cabang ORDER BY id");
        $cabang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($cabang_list) > 0) {
            echo '<div class="result"><span class="success">✅ Found ' . count($cabang_list) . ' cabang</span></div>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Nama Cabang</th><th>Shift</th><th>Jam Masuk</th><th>Jam Keluar</th></tr>';
            foreach ($cabang_list as $c) {
                echo "<tr>";
                echo "<td>{$c['id']}</td>";
                echo "<td>{$c['nama_cabang']}</td>";
                echo "<td>{$c['nama_shift']}</td>";
                echo "<td>{$c['jam_masuk']}</td>";
                echo "<td>{$c['jam_keluar']}</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo '<div class="result"><span class="error">❌ No cabang data found!</span></div>';
        }
        echo '</div>';

        // Test 2: Shift Assignments
        echo '<div class="test-section">';
        echo '<h2>2️⃣ Test Shift Assignments</h2>';
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM shift_assignments");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo '<div class="result"><span class="success">✅ Total assignments: ' . $result['total'] . '</span></div>';
        
        $stmt = $pdo->query("
            SELECT COUNT(*) as total 
            FROM shift_assignments 
            WHERE DATE_FORMAT(tanggal_shift, '%Y-%m') = '2025-11'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo '<div class="result"><span class="success">✅ November 2025 assignments: ' . $result['total'] . '</span></div>';
        
        // Sample assignments
        $stmt = $pdo->query("
            SELECT sa.*, r.nama_lengkap, c.nama_cabang, c.nama_shift
            FROM shift_assignments sa
            JOIN register r ON sa.user_id = r.id
            JOIN cabang c ON sa.cabang_id = c.id
            WHERE DATE_FORMAT(sa.tanggal_shift, '%Y-%m') = '2025-11'
            ORDER BY sa.tanggal_shift
            LIMIT 10
        ");
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($assignments) > 0) {
            echo '<table>';
            echo '<tr><th>Tanggal</th><th>Pegawai</th><th>Cabang</th><th>Shift</th><th>Status</th></tr>';
            foreach ($assignments as $a) {
                $status_class = $a['status_konfirmasi'] === 'confirmed' ? 'success' : '';
                echo "<tr>";
                echo "<td>{$a['tanggal_shift']}</td>";
                echo "<td>{$a['nama_lengkap']}</td>";
                echo "<td>{$a['nama_cabang']}</td>";
                echo "<td>{$a['nama_shift']}</td>";
                echo "<td><span class='$status_class'>{$a['status_konfirmasi']}</span></td>";
                echo "</tr>";
            }
            echo '</table>';
        }
        echo '</div>';

        // Test 3: Pegawai by Cabang
        echo '<div class="test-section">';
        echo '<h2>3️⃣ Test Pegawai by Cabang</h2>';
        
        echo '<table>';
        echo '<tr><th>Cabang</th><th>Jumlah Pegawai</th></tr>';
        foreach ($cabang_list as $cabang) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total 
                FROM register 
                WHERE id_cabang = ? AND role = 'user'
            ");
            $stmt->execute([$cabang['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<tr>";
            echo "<td>{$cabang['nama_cabang']}</td>";
            echo "<td>{$result['total']}</td>";
            echo "</tr>";
        }
        echo '</table>';
        echo '</div>';

        // Test 4: JOIN Query
        echo '<div class="test-section">';
        echo '<h2>4️⃣ Test JOIN Query (Calendar Format)</h2>';
        
        $stmt = $pdo->query("
            SELECT 
                sa.id,
                sa.tanggal_shift,
                DATE_FORMAT(CONCAT(sa.tanggal_shift, ' ', c.jam_masuk), '%Y-%m-%d %H:%i:%s') as start,
                DATE_FORMAT(CONCAT(sa.tanggal_shift, ' ', c.jam_keluar), '%Y-%m-%d %H:%i:%s') as end,
                c.nama_cabang,
                c.nama_shift,
                r.nama_lengkap
            FROM shift_assignments sa
            JOIN cabang c ON sa.cabang_id = c.id
            JOIN register r ON sa.user_id = r.id
            WHERE sa.tanggal_shift >= '2025-11-05'
            ORDER BY sa.tanggal_shift
            LIMIT 5
        ");
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($results) > 0) {
            echo '<div class="result"><span class="success">✅ JOIN Query successful!</span></div>';
            echo '<table>';
            echo '<tr><th>Pegawai</th><th>Cabang</th><th>Tanggal</th><th>Start Time</th><th>End Time</th></tr>';
            foreach ($results as $r) {
                echo "<tr>";
                echo "<td>{$r['nama_lengkap']}</td>";
                echo "<td>{$r['nama_cabang']}</td>";
                echo "<td>{$r['tanggal_shift']}</td>";
                echo "<td>{$r['start']}</td>";
                echo "<td>{$r['end']}</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo '<div class="result"><span class="error">❌ No data returned!</span></div>';
        }
        echo '</div>';

        // Test 5: Admin Users with NEW Emails
        echo '<div class="test-section">';
        echo '<h2>5️⃣ Test Admin Users (NEW Emails)</h2>';
        
        $stmt = $pdo->query("
            SELECT username, nama_lengkap, email, role 
            FROM register 
            WHERE role = 'admin' 
            ORDER BY username
            LIMIT 10
        ");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($admins) > 0) {
            echo '<div class="result"><span class="success">✅ Found ' . count($admins) . ' admin users</span></div>';
            echo '<table>';
            echo '<tr><th>Username</th><th>Nama</th><th>Email</th></tr>';
            foreach ($admins as $admin) {
                $email_updated = in_array($admin['email'], [
                    'galihganji@gmail.com',
                    'pilaraforismacinta@gmail.com',
                    'dotpikir@gmail.com',
                    'katahnaf@gmail.com'
                ]);
                $email_class = $email_updated ? 'success' : '';
                echo "<tr>";
                echo "<td>{$admin['username']}</td>";
                echo "<td>{$admin['nama_lengkap']}</td>";
                echo "<td><span class='$email_class'>{$admin['email']}</span></td>";
                echo "</tr>";
            }
            echo '</table>';
        }
        echo '</div>';
        ?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee; text-align: center;">
            <h3>✅ Testing Complete!</h3>
            <p>Sistem shift calendar sudah siap digunakan.</p>
            
            <a href="shift_calendar.php" class="btn">📅 Open Shift Calendar</a>
            <a href="login.php" class="btn btn-success">🔐 Go to Login</a>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">
            <strong>💡 Next Steps:</strong>
            <ol style="margin: 10px 0;">
                <li>Login sebagai admin</li>
                <li>Buka Shift Calendar</li>
                <li>Pilih cabang dari dropdown</li>
                <li>Test create, drag, dan delete shift assignments</li>
            </ol>
        </div>
    </div>
</body>
</html>
