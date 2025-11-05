<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?error=unauthorized');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Superinteraktif Shift Karyawan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
    include 'navbar.php';
    ?>
    <h1 style="text-align: center; color: #333; margin-bottom: 30px;">Kalender Superinteraktif untuk Jadwal Shift Karyawan</h1>
    <div id="controls">
        <div id="view-controls">
            <button id="view-day" class="view-btn">Day</button>
            <button id="view-week" class="view-btn">Week</button>
            <button id="view-month" class="view-btn active">Month</button>
            <button id="view-year" class="view-btn">Year</button>
        </div>

        <label for="cabang-select">Mode Database (Optional):</label>
        <select id="cabang-select">
            <option value="">-- Mode LocalStorage (Original) --</option>
        </select>

        <label for="employee-select">Pilih Karyawan:</label>
        <select id="employee-select">
            <option value="">-- Pilih Karyawan --</option>
        </select>

        <label for="shift-select">Pilih Shift:</label>
        <select id="shift-select">
            <option value="">-- Pilih Shift --</option>
            <option value="Shift 1: Pagi">Shift 1: Pagi</option>
            <option value="Shift 2: Siang">Shift 2: Siang</option>
            <option value="Shift 3: Malam">Shift 3: Malam</option>
            <option value="Off">Off</option>
        </select>
        <button id="add-employee">Tambah Karyawan</button>
        <button id="export-schedule">Ekspor Jadwal (CSV)</button>
        <button id="add-holiday">Tambah Hari Libur</button>
        <button id="search-employee">Cari Karyawan</button>
        <button id="filter-status">Filter Status</button>
        <button id="filter-date">Filter Tanggal</button>
        <button id="notify-shifts">Notifikasi Shift</button>
        <button id="alert-low-shifts">Alert Shift Kurang</button>
        <button id="backup-data">Backup Data</button>
        <button id="restore-data">Restore Data</button>
        <button id="set-preferences">Set Preferensi Shift</button>
        <button id="set-timezone">Set Zona Waktu</button>
        <button id="notify-manager">Notify Manager</button>
        <button id="notify-employee-change">Notify Employee Change</button>
        <button id="notify-employee-assigned">Notify Employee Assigned</button>
    </div>
    <div id="navigation">
        <button id="prev-nav">< <span id="prev-label">Bulan Sebelumnya</span></button>
        <span id="current-nav"></span>
        <button id="next-nav"><span id="next-label">Bulan Berikutnya</span> ></button>
    </div>
    <div id="calendar-container">
        <div id="month-year"></div>
    </div>
    <div id="calendar-view">
        <div id="month-view" class="view-container">
            <table id="calendar">
                <thead>
                    <tr>
                        <th>Minggu</th>
                        <th>Senin</th>
                        <th>Selasa</th>
                        <th>Rabu</th>
                        <th>Kamis</th>
                        <th>Jumat</th>
                        <th>Sabtu</th>
                    </tr>
                </thead>
                <tbody id="calendar-body">
                    <!-- Kalender akan dihasilkan di sini -->
                </tbody>
            </table>
        </div>
        <div id="week-view" class="view-container" style="display: none;">
            <div id="week-header">
                <button id="prev-week"><</button>
                <span id="week-range"></span>
                <button id="next-week">></button>
            </div>
            <div id="week-calendar">
                <div id="time-column">
                    <!-- Jam akan diisi oleh JS -->
                </div>
                <div id="days-column">
                    <!-- Hari-hari dalam minggu akan diisi oleh JS -->
                </div>
            </div>
        </div>
        <div id="day-view" class="view-container" style="display: none;">
            <div id="day-header">
                <button id="prev-day"><</button>
                <span id="day-date"></span>
                <button id="next-day">></button>
            </div>
            <div id="day-calendar">
                <div id="day-time-column">
                    <!-- Jam akan diisi oleh JS -->
                </div>
                <div id="day-content">
                    <!-- Konten hari akan diisi oleh JS -->
                </div>
            </div>
        </div>
        <div id="year-view" class="view-container" style="display: none;">
            <div id="year-grid">
                <!-- Bulan-bulan akan diisi oleh JS -->
            </div>
        </div>
    </div>

    <!-- Modal untuk menetapkan shift -->
    <div id="shift-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Tetapkan Shift untuk <span id="modal-date"></span></h2>
            <p>Karyawan: <span id="modal-employee"></span></p>
            <label for="modal-shift">Shift:</label>
            <select id="modal-shift">
                <option value="Shift 1: Pagi">Shift 1: Pagi</option>
                <option value="Shift 2: Siang">Shift 2: Siang</option>
                <option value="Shift 3: Malam">Shift 3: Malam</option>
                <option value="Off">Off</option>
                <option value="pagi">Database: Shift Pagi</option>
                <option value="siang">Database: Shift Siang</option>
                <option value="malam">Database: Shift Malam</option>
                <option value="off">Database: Off</option>
            </select>
            <button id="save-shift">Simpan</button>
        </div>
    </div>
    <!-- Tabel untuk melihat jumlah karyawan, hari kerja, dll. -->
    <button id="toggle-summary">Tampilkan Ringkasan</button>
    <div id="summary-tables" style="display: none;">
        <h2>Ringkasan</h2>
        <div id="summary-navigation">
            <button id="prev-summary">< <span id="prev-summary-label">Bulan Sebelumnya</span></button>
            <span id="current-summary"></span>
            <button id="next-summary"><span id="next-summary-label">Bulan Berikutnya</span> ></button>
        </div>
        <div id="summary-controls">
            <label for="summary-filter">Filter Nama:</label>
            <input type="text" id="summary-filter" placeholder="Cari karyawan...">
            <button id="download-summary">Download Ringkasan</button>
            <select id="download-format">
                <option value="csv">CSV</option>
                <option value="txt">TXT</option>
            </select>
        </div>
        <button id="hide-summary">Sembunyikan Ringkasan</button>
        <table id="employee-summary">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jumlah Shift</th>
                    <th>Jumlah Jam Kerja</th>
                    <th>Hari Kerja</th>
                    <th>Hari Libur</th>
                </tr>
            </thead>
            <tbody id="employee-summary-body">
                <!-- Data akan diisi oleh JS -->
            </tbody>
        </table>
        <table id="shift-summary">
            <thead>
                <tr>
                    <th>Shift</th>
                    <th>Jumlah Karyawan</th>
                </tr>
            </thead>
            <tbody id="shift-summary-body">
                <!-- Data akan diisi oleh JS -->
            </tbody>
        </table>
    </div>

    <script src="script_hybrid.js"></script>
</body>
</html>
