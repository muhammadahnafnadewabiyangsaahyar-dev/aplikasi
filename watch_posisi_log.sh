#!/bin/bash

# Script untuk monitoring log posisi_jabatan.php
# Cara pakai: ./watch_posisi_log.sh

LOG_FILE="/Applications/XAMPP/xamppfiles/logs/php_error_log"

# Cek apakah file log ada
if [ ! -f "$LOG_FILE" ]; then
    echo "❌ Error: Log file tidak ditemukan di $LOG_FILE"
    echo "Coba cari dengan: sudo find /Applications/XAMPP -name 'php_error_log' 2>/dev/null"
    exit 1
fi

echo "========================================="
echo "🔍 Monitor Log - posisi_jabatan.php"
echo "========================================="
echo "Log file: $LOG_FILE"
echo ""
echo "Pilih mode:"
echo "1. Real-time monitoring (semua log)"
echo "2. Real-time monitoring (hanya POSISI)"
echo "3. Real-time monitoring (hanya SUCCESS ✅)"
echo "4. Real-time monitoring (hanya ERROR ❌)"
echo "5. Lihat 50 baris terakhir log POSISI"
echo "6. Export log POSISI hari ini ke file"
echo "7. Clear log file (HATI-HATI!)"
echo ""
read -p "Pilih (1-7): " choice

case $choice in
    1)
        echo "📡 Monitoring semua log..."
        tail -f "$LOG_FILE"
        ;;
    2)
        echo "📡 Monitoring log POSISI saja..."
        tail -f "$LOG_FILE" | grep --line-buffered "POSISI\|MODE:\|✅\|❌\|🔄\|📊\|➕"
        ;;
    3)
        echo "📡 Monitoring SUCCESS saja..."
        tail -f "$LOG_FILE" | grep --line-buffered "✅"
        ;;
    4)
        echo "📡 Monitoring ERROR saja..."
        tail -f "$LOG_FILE" | grep --line-buffered "❌"
        ;;
    5)
        echo "📄 50 baris terakhir log POSISI:"
        echo "========================================="
        grep "POSISI\|MODE:\|✅\|❌\|🔄\|📊\|➕" "$LOG_FILE" | tail -50
        ;;
    6)
        OUTPUT_FILE="posisi_debug_$(date +%Y%m%d_%H%M%S).log"
        TODAY=$(date +"%Y-%m-%d")
        grep "$TODAY" "$LOG_FILE" | grep "POSISI\|MODE:\|✅\|❌\|🔄\|📊\|➕" > "$OUTPUT_FILE"
        echo "✅ Log exported to: $OUTPUT_FILE"
        echo "Total lines: $(wc -l < "$OUTPUT_FILE")"
        ;;
    7)
        read -p "⚠️  YAKIN mau clear log file? (yes/no): " confirm
        if [ "$confirm" = "yes" ]; then
            sudo truncate -s 0 "$LOG_FILE"
            echo "✅ Log file dikosongkan"
        else
            echo "❌ Dibatalkan"
        fi
        ;;
    *)
        echo "❌ Pilihan tidak valid"
        exit 1
        ;;
esac
