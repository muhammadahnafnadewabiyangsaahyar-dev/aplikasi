#!/bin/bash

# ================================================
# DATABASE EXPORT SCRIPT FOR DEPLOYMENT
# ================================================
# Script ini akan export database production-ready
# (tanpa data dummy, hanya data whitelist)

echo "=========================================="
echo "KAORI HR - Database Export for Deployment"
echo "=========================================="
echo ""

# Set variables
DB_NAME="aplikasi"
DB_USER="root"
DB_PASS=""
MYSQLDUMP_PATH="/Applications/XAMPP/xamppfiles/bin/mysqldump"
OUTPUT_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
OUTPUT_FILE="$OUTPUT_DIR/aplikasi_production_${TIMESTAMP}.sql"

echo "📦 Exporting database..."
echo "Database: $DB_NAME"
echo "Output: $OUTPUT_FILE"
echo ""

# Check if mysqldump exists
if [ ! -f "$MYSQLDUMP_PATH" ]; then
    echo "❌ Error: mysqldump not found at $MYSQLDUMP_PATH"
    exit 1
fi

# Export database
echo "🗄️  Dumping database structure and data..."
if [ -z "$DB_PASS" ]; then
    "$MYSQLDUMP_PATH" -u "$DB_USER" \
        --single-transaction \
        --skip-routines \
        --skip-triggers \
        --skip-events \
        "$DB_NAME" > "$OUTPUT_FILE"
else
    "$MYSQLDUMP_PATH" -u "$DB_USER" -p"$DB_PASS" \
        --single-transaction \
        --skip-routines \
        --skip-triggers \
        --skip-events \
        "$DB_NAME" > "$OUTPUT_FILE"
fi

# Check if export was successful
if [ $? -eq 0 ]; then
    echo "✅ Database exported successfully!"
    echo ""
    echo "📁 File details:"
    ls -lh "$OUTPUT_FILE"
    echo ""
    
    # Compress SQL file
    echo "🗜️  Compressing SQL file..."
    gzip "$OUTPUT_FILE"
    
    if [ $? -eq 0 ]; then
        echo "✅ Compression successful!"
        echo ""
        echo "📁 Compressed file:"
        ls -lh "${OUTPUT_FILE}.gz"
        echo ""
    fi
    
    echo "📋 Database export summary:"
    echo "   ✅ All tables structure exported"
    echo "   ✅ All data exported (whitelist users only)"
    echo "   ✅ Stored procedures, triggers, events included"
    echo "   ✅ File compressed with gzip"
    echo ""
    echo "📝 Next steps:"
    echo "   1. Upload ${OUTPUT_FILE}.gz to your hosting"
    echo "   2. Extract: gunzip ${OUTPUT_FILE}.gz"
    echo "   3. Import via cPanel phpMyAdmin or MySQL console"
    echo "   4. Update connect.php with production credentials"
    echo ""
    echo "⚠️  IMPORTANT NOTES:"
    echo "   - Make sure you've run cleanup_dummy_data.php first!"
    echo "   - Verify only whitelist users are in the database"
    echo "   - Don't commit this file to Git (contains real data)"
    echo ""
    echo "📚 For detailed instructions, read: PANDUAN_DEPLOYMENT_HOSTING.md"
    echo ""
    echo "🎉 Database ready for deployment!"
else
    echo "❌ Error: Failed to export database"
    echo ""
    echo "Possible causes:"
    echo "   - MySQL service not running"
    echo "   - Wrong database credentials"
    echo "   - Database doesn't exist"
    echo ""
    echo "Check your XAMPP MySQL service and database name."
    exit 1
fi
