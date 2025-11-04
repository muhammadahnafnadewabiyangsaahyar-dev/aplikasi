<?php
session_start();
require_once 'connect.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Admin';

// Get all branches for dropdown
$stmt_branches = $pdo->query("SELECT id, nama_cabang, nama_shift, jam_masuk, jam_keluar FROM cabang ORDER BY nama_cabang");
$branches = $stmt_branches->fetchAll(PDO::FETCH_ASSOC);

// Get all employees for table mode
$stmt_employees = $pdo->query("SELECT id, nama_lengkap, posisi, outlet, id_cabang FROM register WHERE role = 'user' ORDER BY nama_lengkap");
$employees = $stmt_employees->fetchAll(PDO::FETCH_ASSOC);

// Get assignments for current month
$current_month = date('Y-m');
$stmt_assignments = $pdo->prepare("
    SELECT sa.*, r.nama_lengkap, c.nama_cabang, c.nama_shift, c.jam_masuk, c.jam_keluar
    FROM shift_assignments sa
    JOIN register r ON sa.user_id = r.id
    JOIN cabang c ON sa.cabang_id = c.id
    WHERE DATE_FORMAT(sa.tanggal_shift, '%Y-%m') = ?
    ORDER BY sa.tanggal_shift DESC, r.nama_lengkap
");
$stmt_assignments->execute([$current_month]);
$assignments = $stmt_assignments->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Management - Admin</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- DayPilot Scheduler Library -->
    <script src="daypilot-all.min.js"></script>
    
    <style>
        body {
            font-family: -apple-system, system-ui, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .header-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 30px;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        
        .header-container h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header-container .subtitle {
            opacity: 0.95;
            font-size: 14px;
            margin: 0;
        }
        
        .main-content {
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .view-toggle {
            background: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .view-toggle button {
            padding: 10px 20px;
            border: 2px solid #667eea;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
            color: #667eea;
            font-weight: 500;
        }
        
        .view-toggle button.active {
            background: #667eea;
            color: white;
        }
        
        .view-toggle button:hover {
            background: #667eea;
            color: white;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #1976d2;
            font-size: 16px;
        }
        
        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .info-box li {
            margin: 6px 0;
            color: #555;
            line-height: 1.5;
        }
        
        .toolbar {
            background: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .toolbar label {
            font-weight: 600;
            color: #333;
            margin-right: 8px;
        }
        
        .toolbar select,
        .toolbar input[type="month"],
        .toolbar input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 180px;
        }
        
        .toolbar button, .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        /* Calendar View Styles */
        .calendar-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            display: none;
        }
        
        .calendar-container.active {
            display: block;
        }
        
        /* Table View Styles */
        .table-view {
            display: none;
        }
        
        .table-view.active {
            display: block;
        }
        
        .form-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-section h2 {
            color: #333;
            margin: 0 0 20px 0;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        .table-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table-section h2 {
            color: #333;
            margin: 0 0 20px 0;
            font-size: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-pending {
            background: #FFF3CD;
            color: #856404;
        }
        
        .badge-confirmed {
            background: #D4EDDA;
            color: #155724;
        }
        
        .badge-declined {
            background: #F8D7DA;
            color: #721C24;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .hidden {
            display: none;
        }
        
        .legend {
            background: white;
            padding: 15px 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .legend h3 {
            margin: 0 0 12px 0;
            font-size: 16px;
            color: #333;
        }
        
        .legend-items {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .legend-color {
            width: 35px;
            height: 22px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="header-container">
        <h1>📅 Shift Management</h1>
        <p class="subtitle">Kelola jadwal shift pegawai dengan drag & drop atau form</p>
    </div>
    
    <div class="main-content">
        <!-- View Toggle -->
        <div class="view-toggle">
            <button id="btn-calendar-view" class="active" onclick="switchView('calendar')">
                📅 Calendar View
            </button>
            <button id="btn-table-view" onclick="switchView('table')">
                📋 Table View
            </button>
            <div style="flex: 1"></div>
            <button class="btn-secondary" onclick="location.href='mainpage.php'">← Kembali</button>
        </div>
        
        <div id="alert-message" class="alert hidden"></div>
        
        <!-- CALENDAR VIEW -->
        <div id="calendar-view" class="calendar-container active">
            <div class="info-box">
                <h4>📌 Cara Menggunakan Calendar View:</h4>
                <ul>
                    <li><strong>Pilih Cabang:</strong> Wajib pilih cabang terlebih dahulu untuk melihat pegawai dan shift yang sesuai</li>
                    <li><strong>Pilih pegawai dan tanggal:</strong> Klik dan drag pada baris pegawai di tanggal yang diinginkan</li>
                    <li><strong>Assign Shift:</strong> Shift dari cabang terpilih akan otomatis di-assign dengan jam kerja sesuai cabang</li>
                    <li><strong>Pindah Shift:</strong> Drag shift ke pegawai lain atau tanggal lain (shift tetap menggunakan jam kerja cabang yang sama)</li>
                    <li><strong>Hapus Shift:</strong> Klik tombol X merah di pojok shift untuk menghapus assignment</li>
                </ul>
            </div>
            
            <div class="toolbar">
                <div>
                    <label for="filter-cabang-cal">🏢 Cabang:</label>
                    <select id="filter-cabang-cal">
                        <option value="">-- Pilih Cabang --</option>
                        <?php foreach ($branches as $branch): ?>
                        <option value="<?= $branch['id'] ?>">
                            <?= htmlspecialchars($branch['nama_cabang']) ?> - 
                            <?= htmlspecialchars($branch['nama_shift']) ?>
                            (<?= substr($branch['jam_masuk'], 0, 5) ?> - <?= substr($branch['jam_keluar'], 0, 5) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="month-selector">📆 Bulan:</label>
                    <input type="month" id="month-selector" value="<?= date('Y-m') ?>">
                </div>
                
                <button class="btn-primary" onclick="loadCalendar()">🔄 Refresh</button>
            </div>
            
            <div id="dp"></div>
            
            <div class="legend">
                <h3>🎨 Keterangan Warna Cabang:</h3>
                <div class="legend-items" id="legend-colors">
                    <!-- Will be populated dynamically based on cabang -->
                </div>
                <div style="margin-top: 15px;">
                    <strong>Cara Pakai:</strong>
                    <ul style="margin: 5px 0 0 20px; font-size: 13px;">
                        <li><strong>Pilih Cabang</strong> terlebih dahulu untuk melihat pegawai dan shift</li>
                        <li><strong>Klik & Drag</strong> pada tanggal untuk assign shift ke pegawai</li>
                        <li><strong>Drag shift</strong> untuk pindah pegawai atau tanggal</li>
                        <li><strong>Klik tombol X</strong> pada shift untuk hapus</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- TABLE VIEW -->
        <div id="table-view" class="table-view">
            <div class="info-box">
                <h4>📌 Cara Menggunakan Table View:</h4>
                <ul>
                    <li><strong>Form Assignment:</strong> Pilih pegawai, cabang, dan tanggal untuk assign shift</li>
                    <li><strong>Tabel:</strong> Lihat semua assignment dengan status konfirmasi</li>
                    <li><strong>Hapus:</strong> Klik tombol Hapus untuk menghapus assignment</li>
                </ul>
            </div>
            
            <!-- Form Assign Shift -->
            <div class="form-section">
                <h2>Assign Shift ke Pegawai</h2>
                <form id="form-assign-shift">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pegawai_id">Pegawai *</label>
                            <select id="pegawai_id" name="pegawai_id" required>
                                <option value="">-- Pilih Pegawai --</option>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>" data-cabang="<?= $emp['id_cabang'] ?>">
                                    <?= htmlspecialchars($emp['nama_lengkap']) ?> - <?= htmlspecialchars($emp['posisi']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cabang_id">Cabang/Shift *</label>
                            <select id="cabang_id" name="cabang_id" required>
                                <option value="">-- Pilih Cabang --</option>
                                <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['id'] ?>">
                                    <?= htmlspecialchars($branch['nama_cabang']) ?> - 
                                    <?= htmlspecialchars($branch['nama_shift']) ?> 
                                    (<?= substr($branch['jam_masuk'], 0, 5) ?> - <?= substr($branch['jam_keluar'], 0, 5) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_shift">Tanggal *</label>
                            <input type="date" id="tanggal_shift" name="tanggal_shift" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">✓ Assign Shift</button>
                    </div>
                </form>
            </div>
            
            <!-- Table Assignment -->
            <div class="table-section">
                <h2>Shift Assignments - <?= date('F Y') ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pegawai</th>
                            <th>Cabang</th>
                            <th>Shift</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Konfirmasi</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #999; padding: 30px;">
                                Belum ada shift assignment untuk bulan ini
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($assignments as $assign): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($assign['tanggal_shift'])) ?></td>
                            <td><?= htmlspecialchars($assign['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($assign['nama_cabang']) ?></td>
                            <td><?= htmlspecialchars($assign['nama_shift']) ?></td>
                            <td><?= substr($assign['jam_masuk'], 0, 5) ?> - <?= substr($assign['jam_keluar'], 0, 5) ?></td>
                            <td>
                                <?php
                                $status_class = 'badge-pending';
                                $status_text = 'Pending';
                                if ($assign['status_konfirmasi'] === 'confirmed') {
                                    $status_class = 'badge-confirmed';
                                    $status_text = 'Dikonfirmasi';
                                }
                                if ($assign['status_konfirmasi'] === 'declined') {
                                    $status_class = 'badge-declined';
                                    $status_text = 'Ditolak';
                                }
                                ?>
                                <span class="badge <?= $status_class ?>">
                                    <?= $status_text ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($assign['waktu_konfirmasi']): ?>
                                <?= date('d/m H:i', strtotime($assign['waktu_konfirmasi'])) ?>
                                <?php else: ?>
                                <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-danger" style="padding: 6px 12px; font-size: 13px;" onclick="deleteAssignment(<?= $assign['id'] ?>)">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        let dp;
        let cabangList = [];
        let selectedCabang = null;
        
        // Load cabang list
        async function loadCabang() {
            try {
                console.log('Loading cabang list...');
                const response = await fetch('api_shift_calendar.php?action=get_cabang');
                const data = await response.json();
                
                console.log('Cabang API response:', data);
                
                if (data.status === 'success') {
                    cabangList = data.data;
                    console.log('Cabang count:', cabangList.length);
                    
                    const selectCal = document.getElementById('filter-cabang-cal');
                    if (!selectCal) {
                        console.error('Element filter-cabang-cal not found!');
                        return;
                    }
                    
                    selectCal.innerHTML = '<option value="">-- Pilih Cabang --</option>';
                    
                    // Build legend
                    const legendContainer = document.getElementById('legend-colors');
                    if (!legendContainer) {
                        console.error('Element legend-colors not found!');
                    }
                    
                    if (legendContainer) {
                        legendContainer.innerHTML = '';
                    }
                    
                    cabangList.forEach((cabang, index) => {
                        const option = document.createElement('option');
                        option.value = cabang.id;
                        option.textContent = `${cabang.nama_cabang} - ${cabang.nama_shift} (${cabang.jam_masuk.substring(0,5)} - ${cabang.jam_keluar.substring(0,5)})`;
                        option.dataset.jamMasuk = cabang.jam_masuk;
                        option.dataset.jamKeluar = cabang.jam_keluar;
                        selectCal.appendChild(option);
                        
                        // Add to legend
                        if (legendContainer) {
                            const legendItem = document.createElement('div');
                            legendItem.className = 'legend-item';
                            legendItem.innerHTML = `
                                <div class="legend-color" style="background: ${getCabangColor(cabang.id)};"></div>
                                <span>${cabang.nama_cabang} (${cabang.nama_shift})</span>
                            `;
                            legendContainer.appendChild(legendItem);
                        }
                    });
                    
                    console.log('✅ Cabang loaded successfully!');
                } else {
                    console.error('❌ Cabang API error:', data.message);
                    alert('Error loading cabang: ' + data.message);
                }
            } catch (error) {
                console.error('❌ Error loading cabang:', error);
                alert('Error loading cabang data. Please check console for details.');
            }
        }
        
        // Get selected cabang data
        function getSelectedCabang() {
            const selectCal = document.getElementById('filter-cabang-cal');
            const cabangId = selectCal.value;
            if (!cabangId) return null;
            
            return cabangList.find(c => c.id == cabangId);
        }
        
        // Initialize DayPilot Scheduler
        function initCalendar() {
            const monthSelector = document.getElementById('month-selector');
            const selectedMonth = monthSelector ? monthSelector.value : null;
            
            // Validate month selector exists and has value
            if (!monthSelector || !selectedMonth) {
                console.error('Month selector not found or no value');
                return;
            }
            
            // Parse date safely
            let startDate;
            try {
                startDate = new DayPilot.Date(selectedMonth + '-01');
            } catch (e) {
                console.error('Error parsing date:', e);
                return;
            }
            
            const daysInMonth = startDate.daysInMonth();
            
            // If dp already exists, dispose it first to avoid "already initialized" error
            if (dp && dp.dispose) {
                console.log('Disposing existing DayPilot instance...');
                dp.dispose();
                dp = null;
            }
            
            dp = new DayPilot.Scheduler("dp", {
                startDate: startDate,
                days: daysInMonth,
                scale: "Day",
                cellWidth: 120,
                cellHeight: 40,
                eventHeight: 35,
                headerHeight: 30,
                rowHeaderWidth: 200,
                treeEnabled: false,
                allowEventOverlap: false,
                eventResizeHandling: "Disabled",
                rows: [],
                events: [],
                timeHeaders: [
                    {groupBy: "Month"},
                    {groupBy: "Day", format: "d MMM"}
                ],
                onTimeRangeSelected: async (args) => {
                    try {
                        const cabang = getSelectedCabang();
                        if (!cabang) {
                            alert("Silakan pilih cabang terlebih dahulu!");
                            if (dp && dp.clearSelection) dp.clearSelection();
                            return;
                        }
                        
                        const confirmed = confirm(`Assign shift ${cabang.nama_shift} (${cabang.jam_masuk.substring(0,5)}-${cabang.jam_keluar.substring(0,5)}) untuk pegawai ini?`);
                        if (dp && dp.clearSelection) dp.clearSelection();
                        
                        if (!confirmed) {
                            return;
                        }
                        
                        // Use date from selection + shift time from cabang
                        const tanggalShift = args.start.toString("yyyy-MM-dd");
                        
                        const params = {
                            action: 'create',
                            user_id: args.resource,
                            cabang_id: cabang.id,
                            tanggal_shift: tanggalShift
                        };
                        
                        const response = await fetch('api_shift_calendar.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(params)
                        });
                        
                        const result = await response.json();
                        
                        if (result.status === 'success') {
                            // Reload to show the new assignment with correct times
                            await loadCalendar();
                            if (dp && dp.message) dp.message("✓ Shift berhasil di-assign!");
                        } else {
                            alert("Error: " + result.message);
                        }
                    } catch (error) {
                        console.error('Error in onTimeRangeSelected:', error);
                        alert("Error: " + error.message);
                    }
                },
                onEventMove: async (args) => {
                    try {
                        // Extract date from new position
                        const newTanggalShift = args.newStart.toString("yyyy-MM-dd");
                        
                        const params = {
                            action: 'update',
                            id: args.e.id(),
                            user_id: args.newResource,
                            tanggal_shift: newTanggalShift
                        };
                        
                        const response = await fetch('api_shift_calendar.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(params)
                        });
                        
                        const result = await response.json();
                        
                        if (result.status === 'success') {
                            // Reload to show correct times
                            await loadCalendar();
                            if (dp && dp.message) dp.message("✓ Shift berhasil dipindah!");
                        } else {
                            alert("Error: " + result.message);
                            if (args.preventDefault) args.preventDefault();
                        }
                    } catch (error) {
                        console.error('Error in onEventMove:', error);
                        alert("Error: " + error.message);
                        if (args.preventDefault) args.preventDefault();
                    }
                },
                onBeforeEventRender: (args) => {
                    try {
                        // Color code by cabang - use a simple hash-based color
                        const cabangId = args.data.cabang_id || 0;
                        args.data.backColor = getCabangColor(cabangId);
                        args.data.borderColor = "#555";
                        args.data.fontColor = "#000";
                        
                        // Add shift name and time to display
                        const shiftInfo = args.data.text || '';
                        const jamMasuk = args.data.jam_masuk ? args.data.jam_masuk.substring(0,5) : '';
                        const jamKeluar = args.data.jam_keluar ? args.data.jam_keluar.substring(0,5) : '';
                        
                        if (jamMasuk && jamKeluar) {
                            args.data.html = `<div style="padding: 5px;">
                                <strong>${shiftInfo}</strong><br>
                                <small>${jamMasuk} - ${jamKeluar}</small>
                            </div>`;
                        }
                        
                        // Add delete button
                        args.data.areas = [
                            {
                                right: 2,
                                top: 2,
                                height: 18,
                                width: 18,
                                symbol: "icons/daypilot.svg#x",
                                fontColor: "#fff",
                                backColor: "#ff5252",
                                style: "border-radius: 9px; cursor: pointer;",
                                visibility: "Visible",
                                onClick: async (clickArgs) => {
                                    try {
                                        const confirmed = confirm("Hapus shift assignment ini?");
                                        if (!confirmed) {
                                            return;
                                        }
                                        
                                        const params = {
                                            action: 'delete',
                                            id: clickArgs.source.id()
                                        };
                                        
                                        const response = await fetch('api_shift_calendar.php', {
                                            method: 'POST',
                                            headers: {'Content-Type': 'application/json'},
                                            body: JSON.stringify(params)
                                        });
                                        
                                        const result = await response.json();
                                        
                                        if (result.status === 'success') {
                                            if (dp && dp.events && dp.events.remove) {
                                                dp.events.remove(clickArgs.source);
                                            }
                                            if (dp && dp.message) dp.message("✓ Shift berhasil dihapus!");
                                        } else {
                                            alert("Error: " + result.message);
                                        }
                                    } catch (error) {
                                        console.error('Error in delete onClick:', error);
                                        alert("Error: " + error.message);
                                    }
                                }
                            }
                        ];
                    } catch (error) {
                        console.error('Error in onBeforeEventRender:', error);
                    }
                }
            });
            
            // Initialize the scheduler
            try {
                dp.init();
                console.log('DayPilot Scheduler initialized successfully');
            } catch (error) {
                console.error('Error initializing DayPilot Scheduler:', error);
            }
        }
        
        // Load data
        let isLoadingCalendar = false;
        
        async function loadCalendar() {
            // Prevent concurrent calls
            if (isLoadingCalendar) {
                console.log('Calendar is already loading, skipping...');
                return;
            }
            
            // Check if dp is initialized
            if (!dp) {
                console.error('DayPilot Scheduler not initialized yet');
                return;
            }
            
            const cabangSelect = document.getElementById('filter-cabang-cal');
            const monthSelector = document.getElementById('month-selector');
            
            if (!cabangSelect || !monthSelector) {
                console.error('Required elements not found');
                return;
            }
            
            const cabangId = cabangSelect.value;
            const selectedMonth = monthSelector.value;
            
            if (!cabangId) {
                // Clear calendar if no cabang selected
                if (dp && dp.rows) {
                    dp.rows.list = [];
                }
                if (dp && dp.events) {
                    dp.events.list = [];
                }
                if (dp && dp.update) {
                    dp.update();
                }
                return;
            }
            
            isLoadingCalendar = true;
            console.log(`Loading calendar data for cabang_id: ${cabangId}, month: ${selectedMonth}`);
            
            try {
                // Load pegawai (rows) - filtered by selected cabang
                let url = `api_shift_calendar.php?action=get_pegawai&cabang_id=${cabangId}`;
                console.log('Fetching pegawai from:', url);
                
                const responsePegawai = await fetch(url);
                const dataPegawai = await responsePegawai.json();
                console.log('Pegawai response:', dataPegawai);
                
                if (dataPegawai.status === 'success' && dp && dp.rows) {
                    console.log('Setting rows.list with', dataPegawai.data.length, 'pegawai');
                    dp.rows.list = dataPegawai.data;
                    console.log('Rows after set:', dp.rows.list);
                    if (dp.update) {
                        console.log('Calling dp.update() for rows...');
                        dp.update();
                        console.log('✅ Rows updated successfully!');
                    }
                } else {
                    console.error('❌ Failed to set rows:', dataPegawai);
                }
                
                // Load assignments (events) - filtered by selected cabang
                url = `api_shift_calendar.php?action=get_assignments&month=${selectedMonth}&cabang_id=${cabangId}`;
                console.log('Fetching assignments from:', url);
                
                const responseAssignments = await fetch(url);
                const dataAssignments = await responseAssignments.json();
                console.log('Assignments response:', dataAssignments);
                
                if (dataAssignments.status === 'success' && dp && dp.events) {
                    console.log('Setting events.list with', dataAssignments.data.length, 'assignments');
                    dp.events.list = dataAssignments.data.map(item => ({
                        id: item.id,
                        start: item.start,
                        end: item.end,
                        resource: item.user_id,
                        text: item.nama_shift,
                        cabang_id: item.cabang_id,
                        jam_masuk: item.jam_masuk,
                        jam_keluar: item.jam_keluar
                    }));
                    console.log('Events after set:', dp.events.list);
                    if (dp.update) {
                        console.log('Calling dp.update() for events...');
                        dp.update();
                        console.log('✅ Events updated successfully!');
                    }
                } else {
                    console.error('❌ Failed to set events:', dataAssignments);
                }
            } catch (error) {
                console.error('Error loading calendar:', error);
                alert('Error loading data: ' + error.message);
            } finally {
                isLoadingCalendar = false;
            }
        }
        
        // Get color for cabang (simple hash-based color generator)
        function getCabangColor(cabangId) {
            const colors = [
                '#bfd9a9', // Light green
                '#b3d9ff', // Light blue
                '#ffccb3', // Light orange
                '#e6b3ff', // Light purple
                '#ffffb3', // Light yellow
                '#ffb3d9', // Light pink
                '#b3ffff', // Light cyan
                '#d9b3ff'  // Light violet
            ];
            return colors[cabangId % colors.length];
        }
        
        // Switch between calendar and table view
        function switchView(view) {
            const calendarView = document.getElementById('calendar-view');
            const tableView = document.getElementById('table-view');
            const btnCalendar = document.getElementById('btn-calendar-view');
            const btnTable = document.getElementById('btn-table-view');
            
            if (view === 'calendar') {
                calendarView.classList.add('active');
                tableView.classList.remove('active');
                btnCalendar.classList.add('active');
                btnTable.classList.remove('active');
            } else {
                calendarView.classList.remove('active');
                tableView.classList.add('active');
                btnCalendar.classList.remove('active');
                btnTable.classList.add('active');
            }
        }
        
        // Table view: Form submit handler
        document.addEventListener('DOMContentLoaded', () => {
            const formAssign = document.getElementById('form-assign-shift');
            if (formAssign) {
                formAssign.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const pegawaiId = document.getElementById('pegawai_id').value;
                    const cabangId = document.getElementById('cabang_id').value;
                    const tanggalShift = document.getElementById('tanggal_shift').value;
                    
                    if (!pegawaiId || !cabangId || !tanggalShift) {
                        showAlert('Semua field harus diisi!', 'error');
                        return;
                    }
                    
                    const params = {
                        action: 'create',
                        user_id: pegawaiId,
                        cabang_id: cabangId,
                        tanggal_shift: tanggalShift
                    };
                    
                    try {
                        const response = await fetch('api_shift_calendar.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(params)
                        });
                        
                        const result = await response.json();
                        
                        if (result.status === 'success') {
                            showAlert('✓ Shift berhasil di-assign!', 'success');
                            formAssign.reset();
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('Error: ' + result.message, 'error');
                        }
                    } catch (error) {
                        showAlert('Error: ' + error.message, 'error');
                    }
                });
            }
        });
        
        // Delete assignment (table view)
        async function deleteAssignment(id) {
            if (!confirm('Yakin ingin menghapus shift assignment ini?')) {
                return;
            }
            
            const params = {
                action: 'delete',
                id: id
            };
            
            try {
                const response = await fetch('api_shift_calendar.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(params)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert('✓ Shift berhasil dihapus!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'error');
            }
        }
        
        // Show alert message
        function showAlert(message, type) {
            const alertBox = document.getElementById('alert-message');
            alertBox.textContent = message;
            alertBox.className = `alert alert-${type}`;
            alertBox.classList.remove('hidden');
            
            setTimeout(() => {
                alertBox.classList.add('hidden');
            }, 5000);
        }
        
        // Initialize on page load
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                console.log('Initializing shift calendar...');
                
                // Load cabang list first
                await loadCabang();
                
                // Initialize the calendar scheduler
                initCalendar();
                
                // DON'T load calendar data initially - wait for cabang selection
                
                // Event listeners for calendar view
                const filterCabang = document.getElementById('filter-cabang-cal');
                const monthSelector = document.getElementById('month-selector');
                
                if (filterCabang) {
                    filterCabang.addEventListener('change', () => {
                        console.log('Cabang changed to:', filterCabang.value);
                        loadCalendar();
                    });
                    
                    // Check if there's a preselected cabang (e.g., from PHP)
                    if (filterCabang.value) {
                        console.log('Initial cabang already selected:', filterCabang.value);
                        console.log('Loading initial calendar data...');
                        setTimeout(() => {
                            loadCalendar();
                        }, 500);
                    }
                }
                
                if (monthSelector) {
                    monthSelector.addEventListener('change', () => {
                        console.log('Month changed, updating calendar...');
                        const selectedMonth = monthSelector.value;
                        const startDate = new DayPilot.Date(selectedMonth + '-01');
                        const daysInMonth = startDate.daysInMonth();
                        
                        if (dp && dp.update) {
                            // Update existing instance instead of reinitializing
                            dp.startDate = startDate;
                            dp.days = daysInMonth;
                            dp.update();
                            
                            // Reload data for new month
                            setTimeout(() => {
                                loadCalendar();
                            }, 50);
                        } else {
                            // If dp doesn't exist, initialize it
                            initCalendar();
                            setTimeout(() => {
                                loadCalendar();
                            }, 100);
                        }
                    });
                }
                
                console.log('Shift calendar initialization complete');
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        });
    </script>
</body>
</html>
