#!/bin/bash

# Script untuk Menonaktifkan Auto-Import yang Berbahaya
# Script ini akan menghapus cron job yang mengimport database setiap menit

echo "=========================================="
echo "🚨 MENONAKTIFKAN AUTO-IMPORT BERBAHAYA"
echo "=========================================="
echo ""

echo "📋 Cron jobs saat ini:"
echo "------------------------------------------"
crontab -l 2>&1
echo "------------------------------------------"
echo ""

# Backup crontab dulu
echo "💾 Backup crontab saat ini..."
crontab -l > crontab_backup_$(date +%Y%m%d_%H%M%S).txt
echo "✅ Backup tersimpan"
echo ""

# Hapus cron jobs yang bermasalah
echo "🔧 Menghapus cron jobs berbahaya..."

# Buat temporary file tanpa 2 cron job tersebut
crontab -l | grep -v "import_auto.sh" | grep -v "mysqldump.*aplikasi.sql" > /tmp/new_crontab.txt

# Install crontab baru
crontab /tmp/new_crontab.txt

# Cleanup
rm /tmp/new_crontab.txt

echo "✅ Cron jobs berhasil dihapus!"
echo ""

echo "📋 Cron jobs setelah dihapus:"
echo "------------------------------------------"
crontab -l 2>&1
echo "------------------------------------------"
echo ""

echo "=========================================="
echo "✅ AUTO-IMPORT BERHASIL DINONAKTIFKAN!"
echo "=========================================="
echo ""
echo "💡 Yang dilakukan:"
echo "   ❌ Dihapus: Import otomatis setiap menit"
echo "   ❌ Dihapus: Export database setiap 15 menit"
echo ""
echo "📦 Backup crontab tersimpan di:"
echo "   crontab_backup_YYYYMMDD_HHMMSS.txt"
echo ""
echo "✅ Sekarang data registrasi akan AMAN!"
echo ""
echo "🔍 Untuk monitoring, gunakan:"
echo "   ./watch_check_register.sh"
echo ""
echo "=========================================="
