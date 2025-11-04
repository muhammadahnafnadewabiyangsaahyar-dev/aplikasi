#!/bin/bash

# Quick Setup: Backup Otomatis yang AMAN
# Script ini akan membantu setup cron job backup

echo "=========================================="
echo "📦 SETUP BACKUP OTOMATIS (AMAN)"
echo "=========================================="
echo ""

echo "⚠️  PENTING:"
echo "   Ini HANYA untuk BACKUP (export)"
echo "   TIDAK akan mengimport atau menghapus data!"
echo ""

# Cek script backup
if [ ! -f "backup_auto.sh" ]; then
    echo "❌ File backup_auto.sh tidak ditemukan!"
    exit 1
fi

# Make executable
chmod +x backup_auto.sh
echo "✅ Script backup_auto.sh sudah executable"
echo ""

# Test backup dulu
echo "🧪 Testing backup script..."
./backup_auto.sh
echo ""

if [ $? -eq 0 ]; then
    echo "✅ Test backup berhasil!"
else
    echo "❌ Test backup gagal! Perbaiki dulu sebelum setup cron."
    exit 1
fi

echo ""
echo "=========================================="
echo "📋 PILIH INTERVAL BACKUP"
echo "=========================================="
echo ""
echo "1. Setiap 1 jam (Recommended untuk Development)"
echo "   - 24 backup/hari"
echo "   - ~12 MB storage/hari"
echo ""
echo "2. Setiap 4 jam (Recommended untuk Production)"
echo "   - 6 backup/hari"
echo "   - ~3 MB storage/hari"
echo ""
echo "3. Setiap 1 hari (jam 2 pagi)"
echo "   - 1 backup/hari"
echo "   - ~500 KB storage/hari"
echo ""
echo "4. Setiap 30 menit (Maximum frequency)"
echo "   - 48 backup/hari"
echo "   - ~24 MB storage/hari"
echo ""
echo "5. Custom (input sendiri)"
echo ""
echo "0. Batal / Exit"
echo ""
read -p "Pilihan Anda (0-5): " choice

case $choice in
    1)
        CRON_SCHEDULE="0 * * * *"
        DESCRIPTION="Setiap 1 jam"
        ;;
    2)
        CRON_SCHEDULE="0 */4 * * *"
        DESCRIPTION="Setiap 4 jam"
        ;;
    3)
        CRON_SCHEDULE="0 2 * * *"
        DESCRIPTION="Setiap hari jam 2 pagi"
        ;;
    4)
        CRON_SCHEDULE="*/30 * * * *"
        DESCRIPTION="Setiap 30 menit"
        ;;
    5)
        echo ""
        echo "Contoh format cron:"
        echo "  */30 * * * *  = Setiap 30 menit"
        echo "  0 * * * *     = Setiap jam"
        echo "  0 */4 * * *   = Setiap 4 jam"
        echo "  0 2 * * *     = Setiap hari jam 2 pagi"
        echo ""
        read -p "Masukkan cron schedule: " CRON_SCHEDULE
        DESCRIPTION="Custom: $CRON_SCHEDULE"
        ;;
    0)
        echo "❌ Setup dibatalkan"
        exit 0
        ;;
    *)
        echo "❌ Pilihan tidak valid!"
        exit 1
        ;;
esac

echo ""
echo "=========================================="
echo "📝 RINGKASAN SETUP"
echo "=========================================="
echo ""
echo "Schedule: $DESCRIPTION"
echo "Cron:     $CRON_SCHEDULE"
echo "Script:   $(pwd)/backup_auto.sh"
echo "Log:      $(pwd)/backups/cron.log"
echo ""
read -p "Lanjutkan setup? (y/n): " confirm

if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
    echo "❌ Setup dibatalkan"
    exit 0
fi

# Backup crontab existing
echo ""
echo "💾 Backup crontab existing..."
crontab -l > "crontab_backup_$(date +%Y%m%d_%H%M%S).txt" 2>/dev/null
echo "✅ Backup crontab tersimpan"

# Tambah cron job
SCRIPT_PATH="$(pwd)/backup_auto.sh"
LOG_PATH="$(pwd)/backups/cron.log"
CRON_LINE="$CRON_SCHEDULE $SCRIPT_PATH >> $LOG_PATH 2>&1"

# Cek apakah sudah ada cron job untuk script ini
if crontab -l 2>/dev/null | grep -q "backup_auto.sh"; then
    echo ""
    echo "⚠️  Cron job untuk backup_auto.sh sudah ada!"
    read -p "Replace dengan yang baru? (y/n): " replace
    
    if [ "$replace" = "y" ] || [ "$replace" = "Y" ]; then
        # Hapus yang lama, tambah yang baru
        (crontab -l 2>/dev/null | grep -v "backup_auto.sh"; echo "$CRON_LINE") | crontab -
        echo "✅ Cron job berhasil di-replace!"
    else
        echo "❌ Setup dibatalkan"
        exit 0
    fi
else
    # Tambah cron job baru
    (crontab -l 2>/dev/null; echo "$CRON_LINE") | crontab -
    echo "✅ Cron job berhasil ditambahkan!"
fi

echo ""
echo "=========================================="
echo "✅ SETUP SELESAI!"
echo "=========================================="
echo ""
echo "📋 Cron Jobs Aktif:"
echo "------------------------------------------"
crontab -l
echo "------------------------------------------"
echo ""
echo "💡 Next Steps:"
echo ""
echo "1. Monitor log backup:"
echo "   tail -f backups/backup.log"
echo ""
echo "2. Monitor log cron:"
echo "   tail -f backups/cron.log"
echo ""
echo "3. Lihat backup files:"
echo "   ls -lh backups/aplikasi_auto_*.sql"
echo ""
echo "4. Test: Tunggu 1 interval dan cek hasilnya!"
echo ""
echo "=========================================="
echo "📦 Backup akan berjalan: $DESCRIPTION"
echo "🗑️  Auto cleanup: Backup >7 hari dihapus otomatis"
echo "✅ Data registrasi tetap AMAN (hanya backup, tidak import!)"
echo "=========================================="
