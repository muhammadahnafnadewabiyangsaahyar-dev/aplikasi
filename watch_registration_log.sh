#!/bin/bash

# Script untuk monitoring log registrasi
# Cara pakai: ./watch_registration_log.sh

LOG_FILE="/Applications/XAMPP/xamppfiles/logs/php_error_log"

# Cek apakah file log ada
if [ ! -f "$LOG_FILE" ]; then
    echo "❌ Error: Log file tidak ditemukan di $LOG_FILE"
    echo "Coba cari dengan: sudo find /Applications/XAMPP -name 'php_error_log' 2>/dev/null"
    exit 1
fi

echo "========================================="
echo "🔍 Monitor Log - Registrasi (index.php)"
echo "========================================="
echo "Log file: $LOG_FILE"
echo ""
echo "Pilih mode:"
echo "1. Real-time monitoring (semua log registrasi)"
echo "2. Lihat 50 baris terakhir log REGISTRATION"
echo "3. Lihat hanya ERROR registrasi"
echo "4. Lihat hanya SUCCESS registrasi"
echo "5. Export log registrasi hari ini ke file"
echo "6. Clear log file (HATI-HATI!)"
echo ""
read -p "Pilih (1-6): " choice

case $choice in
    1)
        echo "📡 Monitoring log REGISTRATION real-time..."
        tail -f "$LOG_FILE" | grep --line-buffered "REGISTRATION\|Validation\|INSERT\|UPDATE\|✅\|❌\|🔄"
        ;;
    2)
        echo "📄 50 baris terakhir log REGISTRATION:"
        echo "========================================="
        grep "REGISTRATION\|Validation\|INSERT\|UPDATE\|✅\|❌\|🔄" "$LOG_FILE" | tail -50
        ;;
    3)
        echo "📄 ERROR registrasi:"
        echo "========================================="
        grep "REGISTRATION" "$LOG_FILE" | grep "❌\|ERROR\|FAILED" | tail -20
        ;;
    4)
        echo "📄 SUCCESS registrasi:"
        echo "========================================="
        grep "REGISTRATION" "$LOG_FILE" | grep "✅\|SUCCESS" | tail -20
        ;;
    5)
        OUTPUT_FILE="registration_debug_$(date +%Y%m%d_%H%M%S).log"
        TODAY=$(date +"%Y-%m-%d")
        grep "$TODAY" "$LOG_FILE" | grep "REGISTRATION\|Validation\|INSERT\|UPDATE\|✅\|❌\|🔄" > "$OUTPUT_FILE"
        echo "✅ Log exported to: $OUTPUT_FILE"
        echo "Total lines: $(wc -l < "$OUTPUT_FILE")"
        ;;
    6)
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
