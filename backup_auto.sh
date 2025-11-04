#!/bin/bash

# Script Backup Database Berkala (AMAN)
# Export database dengan timestamp, tidak menimpa file lama
# Recommended: Jalankan setiap 1 jam atau 1 hari

MYSQL_USER="root"
MYSQL_PASS=""
DB_NAME="aplikasi"
BACKUP_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi/backups"
MYSQLDUMP="/Applications/XAMPP/xamppfiles/bin/mysqldump"

# Buat folder backup jika belum ada
mkdir -p "$BACKUP_DIR"

# Generate timestamp
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_FILE="${BACKUP_DIR}/aplikasi_auto_${TIMESTAMP}.sql"

# Export database
echo "📦 Starting backup: $TIMESTAMP"
$MYSQLDUMP -u "$MYSQL_USER" "$DB_NAME" > "$BACKUP_FILE" 2>&1

# Cek hasil
if [ $? -eq 0 ]; then
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "✅ Backup sukses: $BACKUP_FILE ($FILE_SIZE)"
    
    # Log ke file
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Backup sukses: $BACKUP_FILE ($FILE_SIZE)" >> "${BACKUP_DIR}/backup.log"
    
    # Cleanup: Hapus backup lama (>7 hari)
    find "$BACKUP_DIR" -name "aplikasi_auto_*.sql" -mtime +7 -delete
    echo "🗑️  Cleanup: Hapus backup > 7 hari"
    
else
    echo "❌ Backup gagal!"
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Backup GAGAL" >> "${BACKUP_DIR}/backup.log"
    exit 1
fi

# Hitung total backup
BACKUP_COUNT=$(ls -1 "$BACKUP_DIR"/aplikasi_auto_*.sql 2>/dev/null | wc -l)
echo "📊 Total backup tersimpan: $BACKUP_COUNT file"
