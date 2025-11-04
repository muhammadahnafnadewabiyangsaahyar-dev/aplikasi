#!/bin/bash

# Script Backup Database Otomatis
# Gunakan ini untuk backup database DENGAN data terbaru

MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql"
MYSQLDUMP="/Applications/XAMPP/xamppfiles/bin/mysqldump"
BACKUP_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi/backups"
DATE=$(date '+%Y%m%d_%H%M%S')
BACKUP_FILE="${BACKUP_DIR}/aplikasi_backup_${DATE}.sql"

# Buat folder backup jika belum ada
mkdir -p "$BACKUP_DIR"

echo "🔄 Starting database backup..."
echo "📂 Backup location: $BACKUP_FILE"
echo ""

# Lakukan backup
$MYSQLDUMP -u root aplikasi > "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "✅ Backup berhasil!"
    echo "📄 File: $BACKUP_FILE"
    echo ""
    
    # Tampilkan statistik
    echo "📊 Statistik Backup:"
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "   Size: $FILE_SIZE"
    
    USER_COUNT=$($MYSQL -u root aplikasi -se "SELECT COUNT(*) FROM register;")
    echo "   Total Users: $USER_COUNT"
    
    echo ""
    echo "💡 Tips:"
    echo "   - File backup disimpan di folder 'backups/'"
    echo "   - Gunakan backup ini untuk restore jika diperlukan"
    echo "   - JANGAN import aplikasi.sql saat development!"
else
    echo "❌ Backup gagal!"
    exit 1
fi
