#!/bin/bash

# Script untuk cek registrasi real-time
# Cara pakai: ./check_register_realtime.sh

MYSQL_PATH="/Applications/XAMPP/xamppfiles/bin/mysql"
DB_NAME="aplikasi"

echo "========================================="
echo "🔍 Real-time Register Check"
echo "========================================="
echo ""
echo "Monitoring tabel 'register' setiap 2 detik..."
echo "Tekan Ctrl+C untuk stop"
echo ""
echo "Current time: $(date '+%H:%M:%S')"
echo ""

# Tampilkan data awal
echo "=== Data Awal ==="
$MYSQL_PATH -u root $DB_NAME -e "SELECT id, nama_lengkap, username, email, no_whatsapp, role, time_created FROM register ORDER BY id DESC LIMIT 5;"
echo ""

# Loop monitoring
PREV_COUNT=$($MYSQL_PATH -u root $DB_NAME -N -e "SELECT COUNT(*) FROM register;")
echo "Total users: $PREV_COUNT"
echo ""
echo "Waiting for new registration..."
echo ""

while true; do
    sleep 2
    
    CURRENT_COUNT=$($MYSQL_PATH -u root $DB_NAME -N -e "SELECT COUNT(*) FROM register;")
    
    if [ "$CURRENT_COUNT" -gt "$PREV_COUNT" ]; then
        echo "========================================="
        echo "🎉 NEW USER DETECTED! Time: $(date '+%H:%M:%S')"
        echo "========================================="
        echo ""
        
        # Tampilkan user baru
        $MYSQL_PATH -u root $DB_NAME -e "SELECT id, nama_lengkap, username, email, no_whatsapp, role, time_created FROM register ORDER BY id DESC LIMIT 1;"
        echo ""
        
        # Tampilkan semua users
        echo "=== All Users ==="
        $MYSQL_PATH -u root $DB_NAME -e "SELECT id, nama_lengkap, username, role FROM register ORDER BY id;"
        echo ""
        echo "Total users: $CURRENT_COUNT (was $PREV_COUNT)"
        echo ""
        
        PREV_COUNT=$CURRENT_COUNT
    fi
done
