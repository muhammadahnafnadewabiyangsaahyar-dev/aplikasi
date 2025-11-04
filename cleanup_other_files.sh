#!/bin/bash

# Cleanup script untuk file-file tidak terpakai lainnya
echo "🧹 Cleaning up unused files..."

cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi

# Create backup folder
BACKUP_DIR="./DELETED_FILES_BACKUP_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo "📦 Backup folder: $BACKUP_DIR"
echo ""

# Counter
DELETED=0

# Files to delete (exact matches)
DELETE_FILES=(
    # Old backup SQL files
    "backup_20251104.sql"
    "aplikasi_cleaned.sql"
    "database_schema_shift_system.sql"
    "dummy_data_complete.sql"
    
    # Test and verification files
    "test_shift_calendar.html"
    "verify_calendar_fix.html"
    "test_shift_data.html"
    "test_calendar_fix.sh"
    "test_pegawai_rows_fix.sh"
    "test_daypilot_fix.sh"
    "open_shift_calendar_fix.sh"
    "check_register.sh"
    "check_register_realtime.sh"
    "check_result.txt"
    "debug_upload.txt"
    
    # Debug files
    "debug_csrf.php"
    "debug_session.php"
    "debug_import_forms.php"
    "debug_mainpage_stats.php"
    "debug_shift_calendar.php"
    "debug_whitelist_data.php"
    "debug_whitelist_import.php"
    "diagnostic_import.php"
    "check_duplicate_data.php"
    "check_duplicate_whitelist.php"
    "check_komponen_gaji_structure.php"
    
    # Demo/sample files
    "demo_filter_rupiah.html"
    "dummy_data_reference.html"
    "email_notification_guide.html"
    "email_system_summary.html"
    "DOCUMENTATION_INDEX.html"
    
    # Temporary/backup files
    "~\$MPLATE.docx"
    "crontab_backup_20251103_000600.txt"
    "crontab_backup_20251103_001153.txt"
    "absen.php.backup_20251103_011241"
    "CLEANUP_SUMMARY.txt"
    
    # Old/deprecated scripts
    "absen_helper.php"
    "calculate_status_kehadiran.php"
    "check_bom.sh"
    "cleanup_old_absen_folders.sh"
    "composer-setup.php"
    "disable_auto_import.sh"
    "email_config.php"
    "email_helper.php"
    
    # KALENDER old/test files
    "KALENDER/test_integration.html"
    "KALENDER/verify_features.html"
    "KALENDER/scriptkalender.js"
    "KALENDER/script_database.js"
    
    # Vendor test files (not needed in production)
    "vendor/phpmailer/phpmailer/test_script.php"
)

# Delete files
for file in "${DELETE_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "🗑️  Deleting: $file"
        mkdir -p "$BACKUP_DIR/$(dirname "$file")"
        mv "$file" "$BACKUP_DIR/$file" 2>/dev/null
        ((DELETED++))
    fi
done

echo ""
echo "================================================"
echo "✅ File cleanup complete!"
echo "📊 Statistics:"
echo "   - Files deleted: $DELETED"
echo "   - Backup location: $BACKUP_DIR"
echo "================================================"
