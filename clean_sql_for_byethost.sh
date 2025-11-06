#!/bin/bash

# ================================================
# CLEAN SQL FILE FOR BYETHOST DEPLOYMENT
# ================================================
# Script ini akan menghapus semua CREATE VIEW, TRIGGER, dan PROCEDURE
# yang tidak didukung oleh free hosting seperti ByetHost

echo "================================================"
echo "   CLEAN SQL FILE FOR BYETHOST"
echo "================================================"
echo ""

# Find the latest production SQL file
LATEST_SQL=$(ls -t aplikasi_production_*.sql.gz 2>/dev/null | head -1)

if [ -z "$LATEST_SQL" ]; then
    echo "❌ Error: No production SQL file found!"
    echo "Please run export_database_for_deployment.sh first."
    exit 1
fi

echo "📦 Found SQL file: $LATEST_SQL"
echo ""

# Extract if compressed
if [[ $LATEST_SQL == *.gz ]]; then
    echo "🗜️  Extracting compressed SQL file..."
    gunzip -c "$LATEST_SQL" > temp_extracted.sql
    SQL_FILE="temp_extracted.sql"
else
    SQL_FILE="$LATEST_SQL"
fi

# Create cleaned version
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
OUTPUT_FILE="aplikasi_byethost_${TIMESTAMP}.sql"

echo "🧹 Cleaning SQL file..."
echo "   Removing: CREATE VIEW, CREATE TRIGGER, CREATE PROCEDURE"
echo ""

# Remove CREATE VIEW statements
# Remove CREATE TRIGGER statements
# Remove CREATE PROCEDURE/FUNCTION statements
# Keep everything else

grep -v "CREATE.*VIEW" "$SQL_FILE" | \
grep -v "CREATE.*TRIGGER" | \
grep -v "CREATE.*PROCEDURE" | \
grep -v "CREATE.*FUNCTION" | \
grep -v "DEFINER=" > "$OUTPUT_FILE"

# Check if output file was created
if [ $? -eq 0 ]; then
    echo "✅ Cleaned SQL file created successfully!"
    echo ""
    echo "📁 File details:"
    ls -lh "$OUTPUT_FILE"
    echo ""
    
    # Compress the cleaned file
    echo "🗜️  Compressing cleaned SQL file..."
    gzip "$OUTPUT_FILE"
    
    if [ $? -eq 0 ]; then
        echo "✅ Compression successful!"
        echo ""
        echo "📁 Final file:"
        ls -lh "${OUTPUT_FILE}.gz"
        echo ""
    fi
    
    # Clean up temp file
    if [ -f "temp_extracted.sql" ]; then
        rm temp_extracted.sql
    fi
    
    echo "📋 Summary:"
    echo "   ✅ Removed all CREATE VIEW statements"
    echo "   ✅ Removed all CREATE TRIGGER statements"
    echo "   ✅ Removed all CREATE PROCEDURE/FUNCTION statements"
    echo "   ✅ File compressed with gzip"
    echo ""
    echo "📝 Next steps:"
    echo "   1. Upload ${OUTPUT_FILE}.gz to ByetHost"
    echo "   2. Import via phpMyAdmin"
    echo "   3. No more CREATE VIEW errors!"
    echo ""
    echo "🎉 File ready for ByetHost deployment!"
else
    echo "❌ Error: Failed to clean SQL file"
    exit 1
fi
