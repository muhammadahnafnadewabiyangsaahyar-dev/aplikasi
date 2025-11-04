#!/bin/bash

# Auto-Refresh Check Script - Tabel Register
# Script ini akan otomatis refresh setiap 2 detik
# Tekan Ctrl+C untuk stop

MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql"

# Fungsi untuk clear screen dengan cara yang kompatibel
clear_screen() {
    printf "\033c"
}

# Fungsi untuk cek data register
check_register() {
    echo "==================================="
    echo "📊 Auto-Refresh Check - Tabel Register"
    echo "==================================="
    echo ""
    echo "🕐 Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
    echo ""
    echo "📈 Total Users:"
    $MYSQL -u root aplikasi -e "SELECT COUNT(*) as total FROM register;" 2>/dev/null | tail -1
    echo ""
    echo "👥 Latest 5 Users:"
    $MYSQL -u root aplikasi -e "SELECT id, nama_lengkap, username, email, time_created FROM register ORDER BY id DESC LIMIT 5;"
    echo ""
    echo "⚙️  Table Status:"
    $MYSQL -u root aplikasi -e "SHOW TABLE STATUS LIKE 'register'\G" 2>/dev/null | grep -E "Name:|Rows:|Auto_increment:|Update_time:"
    echo ""
    echo "==================================="
    echo "💡 Tips:"
    echo "- Refresh otomatis setiap 2 detik"
    echo "- Tekan Ctrl+C untuk stop monitoring"
    echo "- JANGAN import aplikasi.sql saat testing!"
    echo "==================================="
}

# Loop untuk auto-refresh
echo "🚀 Starting auto-refresh monitoring..."
echo "📡 Checking every 2 seconds..."
echo ""
sleep 1

while true; do
    clear_screen
    check_register
    sleep 2
done
