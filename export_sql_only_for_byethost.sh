#!/bin/bash

# ================================================
# EXPORT SQL ONLY FOR BYETHOST
# ================================================
# Script ini HANYA mengexport database SQL (bersih dari VIEW/TRIGGER/PROCEDURE)
# Tidak ada file PHP, shell script, atau markdown
# Khusus untuk import ke phpMyAdmin ByetHost

echo "================================================"
echo "   EXPORT SQL ONLY FOR BYETHOST"
echo "================================================"
echo ""

# Database credentials
DB_HOST="localhost"
DB_USER="root"
DB_PASS=""
DB_NAME="aplikasi"

# Output filename with timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
OUTPUT_FILE="kaori_hr_byethost_${TIMESTAMP}.sql"

echo "📦 Exporting database: $DB_NAME"
echo ""

# Export database with mysqldump
echo "🔄 Running mysqldump..."
mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" > "$OUTPUT_FILE" 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Error: mysqldump failed!"
    exit 1
fi

echo "✅ Database exported successfully!"
echo ""

# Remove VIEW, TRIGGER, PROCEDURE, FUNCTION
echo "🧹 Cleaning SQL file..."
echo "   Removing: CREATE VIEW, TRIGGER, PROCEDURE, FUNCTION, DEFINER"
echo ""

# Create temporary cleaned file
grep -v "CREATE.*VIEW" "$OUTPUT_FILE" | \
grep -v "CREATE.*TRIGGER" | \
grep -v "CREATE.*PROCEDURE" | \
grep -v "CREATE.*FUNCTION" | \
grep -v "DEFINER=" | \
grep -v "^\/\*!50001 DROP VIEW" | \
grep -v "^\/\*!50001 CREATE" | \
grep -v "^\/\*!50003 DROP PROCEDURE" | \
grep -v "^\/\*!50003 CREATE" | \
grep -v "^DELIMITER" > "${OUTPUT_FILE}.tmp"

# Replace original with cleaned version
mv "${OUTPUT_FILE}.tmp" "$OUTPUT_FILE"

if [ $? -eq 0 ]; then
    echo "✅ SQL cleaned successfully!"
    echo ""
else
    echo "❌ Error: Failed to clean SQL file"
    exit 1
fi

# Show file info
echo "📁 File details:"
ls -lh "$OUTPUT_FILE"
echo ""

# File size check
FILE_SIZE=$(stat -f%z "$OUTPUT_FILE" 2>/dev/null || stat -c%s "$OUTPUT_FILE" 2>/dev/null)
FILE_SIZE_MB=$(echo "scale=2; $FILE_SIZE / 1024 / 1024" | bc)

echo "📊 File size: ${FILE_SIZE_MB} MB"
echo ""

# Check if file is too large for ByetHost (usually 10MB limit)
if (( $(echo "$FILE_SIZE_MB > 10" | bc -l) )); then
    echo "⚠️  WARNING: File is larger than 10MB!"
    echo "   ByetHost phpMyAdmin may have import limit"
    echo "   Consider:"
    echo "   1. Upload via FTP and import from server"
    echo "   2. Split into smaller files"
    echo "   3. Remove old data before export"
    echo ""
fi

echo "📋 Summary:"
echo "   ✅ Pure SQL file (no PHP, no shell scripts)"
echo "   ✅ Removed all CREATE VIEW statements"
echo "   ✅ Removed all CREATE TRIGGER statements"
echo "   ✅ Removed all CREATE PROCEDURE/FUNCTION statements"
echo "   ✅ Removed all DEFINER statements"
echo "   ✅ Compatible with ByetHost phpMyAdmin"
echo ""

echo "📝 Import Instructions:"
echo ""
echo "1. Login to ByetHost Control Panel"
echo "   URL: https://byethost.com/vcp/"
echo ""
echo "2. Open phpMyAdmin"
echo "   (Look for 'MySQL Databases' icon)"
echo ""
echo "3. Create new database (if not exists)"
echo "   - Click 'New' in left sidebar"
echo "   - Enter database name: kaori_hr"
echo "   - Click 'Create'"
echo ""
echo "4. Import SQL file"
echo "   - Click on database name 'kaori_hr'"
echo "   - Click 'Import' tab"
echo "   - Click 'Choose File' button"
echo "   - Select: $OUTPUT_FILE"
echo "   - Click 'Go' button at bottom"
echo ""
echo "5. Wait for import to complete"
echo "   - Should see: 'Import has been successfully finished'"
echo "   - Number of queries executed will be shown"
echo ""
echo "6. Verify tables imported"
echo "   - Check left sidebar for tables list"
echo "   - Should see: absensi, registrasi, cabang, etc."
echo ""
echo "🎉 File ready for ByetHost import!"
echo ""
echo "📁 Upload this file: $OUTPUT_FILE"
echo ""
