#!/bin/bash

# Quick Check Script - Tabel Register
# Gunakan ini untuk cek data real-time tanpa cache

MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql"

echo "==================================="
echo "📊 Quick Check - Tabel Register"
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
echo "- Jika data tidak muncul di phpMyAdmin, tekan Ctrl+F5"
echo "- JANGAN import aplikasi.sql setelah registrasi!"
echo "- Gunakan ./watch_registration_log.sh untuk monitor"
echo "==================================="
