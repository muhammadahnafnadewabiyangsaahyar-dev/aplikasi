#!/bin/bash
# ========================================================
# MIGRATION SCRIPT: Move Absen Photos to New Folder
# Purpose: Migrate existing photos from old structure to new
# ========================================================

echo "=========================================="
echo "MIGRATING ABSEN PHOTOS"
echo "Date: $(date)"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Folders
OLD_FOLDER_MASUK="uploads/absen_masuk"
OLD_FOLDER_KELUAR="uploads/absen_keluar"
NEW_FOLDER="uploads/absensi"

# Step 1: Create new folder if not exists
if [ ! -d "$NEW_FOLDER" ]; then
    mkdir -p "$NEW_FOLDER"
    chmod 755 "$NEW_FOLDER"
    echo -e "${GREEN}✓${NC} Created $NEW_FOLDER/"
else
    echo -e "${GREEN}✓${NC} $NEW_FOLDER/ already exists"
fi

echo ""

# Step 2: Count existing files
if [ -d "$OLD_FOLDER_MASUK" ]; then
    MASUK_COUNT=$(ls -1 "$OLD_FOLDER_MASUK" 2>/dev/null | wc -l)
    echo "Found $MASUK_COUNT files in $OLD_FOLDER_MASUK/"
else
    MASUK_COUNT=0
    echo -e "${YELLOW}⚠${NC}  $OLD_FOLDER_MASUK/ not found"
fi

if [ -d "$OLD_FOLDER_KELUAR" ]; then
    KELUAR_COUNT=$(ls -1 "$OLD_FOLDER_KELUAR" 2>/dev/null | wc -l)
    echo "Found $KELUAR_COUNT files in $OLD_FOLDER_KELUAR/"
else
    KELUAR_COUNT=0
    echo -e "${YELLOW}⚠${NC}  $OLD_FOLDER_KELUAR/ not found"
fi

echo ""

# Step 3: Move files (use cp to keep originals safe)
MOVED=0
SKIPPED=0

if [ $MASUK_COUNT -gt 0 ]; then
    echo "Copying files from $OLD_FOLDER_MASUK/..."
    for file in "$OLD_FOLDER_MASUK"/*; do
        if [ -f "$file" ]; then
            filename=$(basename "$file")
            # Rename file to include 'masuk_' prefix if not already
            if [[ ! "$filename" =~ ^masuk_ ]]; then
                new_name="masuk_$filename"
            else
                new_name="$filename"
            fi
            
            if [ ! -f "$NEW_FOLDER/$new_name" ]; then
                cp "$file" "$NEW_FOLDER/$new_name"
                echo "  → $new_name"
                ((MOVED++))
            else
                echo "  ⊘ $new_name (already exists, skipped)"
                ((SKIPPED++))
            fi
        fi
    done
fi

if [ $KELUAR_COUNT -gt 0 ]; then
    echo ""
    echo "Copying files from $OLD_FOLDER_KELUAR/..."
    for file in "$OLD_FOLDER_KELUAR"/*; do
        if [ -f "$file" ]; then
            filename=$(basename "$file")
            # Rename file to include 'keluar_' prefix if not already
            if [[ ! "$filename" =~ ^keluar_ ]]; then
                new_name="keluar_$filename"
            else
                new_name="$filename"
            fi
            
            if [ ! -f "$NEW_FOLDER/$new_name" ]; then
                cp "$file" "$NEW_FOLDER/$new_name"
                echo "  → $new_name"
                ((MOVED++))
            else
                echo "  ⊘ $new_name (already exists, skipped)"
                ((SKIPPED++))
            fi
        fi
    done
fi

echo ""
echo "=========================================="
echo "MIGRATION SUMMARY"
echo "=========================================="
echo -e "${GREEN}✓${NC} $MOVED files copied to $NEW_FOLDER/"
if [ $SKIPPED -gt 0 ]; then
    echo -e "${YELLOW}⊘${NC} $SKIPPED files skipped (already exist)"
fi

echo ""
echo "Next Steps:"
echo "1. ${YELLOW}Verify files${NC} in $NEW_FOLDER/"
echo "2. ${YELLOW}Update database paths${NC} (run SQL script below)"
echo "3. ${YELLOW}Test absensi${NC} on browser"
echo "4. ${YELLOW}After verification${NC}, you can delete old folders:"
echo "   rm -rf $OLD_FOLDER_MASUK/"
echo "   rm -rf $OLD_FOLDER_KELUAR/"
echo ""

# Step 4: Generate SQL for database path update
cat > update_absen_photo_paths.sql << 'EOF'
-- ========================================================
-- UPDATE DATABASE PATHS FOR MIGRATED PHOTOS
-- Purpose: Update foto_absen paths to match new folder structure
-- ========================================================

USE aplikasi;

-- Backup current paths first
CREATE TABLE IF NOT EXISTS absensi_paths_backup AS
SELECT id, foto_absen, foto_absen_keluar FROM absensi 
WHERE foto_absen IS NOT NULL OR foto_absen_keluar IS NOT NULL;

-- Update foto_absen (remove absen_masuk/ prefix if exists)
UPDATE absensi 
SET foto_absen = REPLACE(foto_absen, 'absen_masuk/', '')
WHERE foto_absen LIKE 'absen_masuk/%';

UPDATE absensi 
SET foto_absen = REPLACE(foto_absen, 'uploads/absen_masuk/', '')
WHERE foto_absen LIKE 'uploads/absen_masuk/%';

-- Add masuk_ prefix if not exists (for old format)
UPDATE absensi 
SET foto_absen = CONCAT('masuk_', foto_absen)
WHERE foto_absen IS NOT NULL 
  AND foto_absen != ''
  AND foto_absen NOT LIKE 'masuk_%'
  AND foto_absen NOT LIKE 'keluar_%';

-- Update foto_absen_keluar (remove absen_keluar/ prefix if exists)
UPDATE absensi 
SET foto_absen_keluar = REPLACE(foto_absen_keluar, 'absen_keluar/', '')
WHERE foto_absen_keluar LIKE 'absen_keluar/%';

UPDATE absensi 
SET foto_absen_keluar = REPLACE(foto_absen_keluar, 'uploads/absen_keluar/', '')
WHERE foto_absen_keluar LIKE 'uploads/absen_keluar/%';

-- Add keluar_ prefix if not exists (for old format)
UPDATE absensi 
SET foto_absen_keluar = CONCAT('keluar_', foto_absen_keluar)
WHERE foto_absen_keluar IS NOT NULL 
  AND foto_absen_keluar != ''
  AND foto_absen_keluar NOT LIKE 'keluar_%'
  AND foto_absen_keluar NOT LIKE 'masuk_%';

-- Verification queries
SELECT 
    COUNT(*) as total_with_foto_masuk,
    COUNT(DISTINCT foto_absen) as unique_masuk
FROM absensi 
WHERE foto_absen IS NOT NULL AND foto_absen != '';

SELECT 
    COUNT(*) as total_with_foto_keluar,
    COUNT(DISTINCT foto_absen_keluar) as unique_keluar
FROM absensi 
WHERE foto_absen_keluar IS NOT NULL AND foto_absen_keluar != '';

-- Show sample updated paths
SELECT id, foto_absen, foto_absen_keluar 
FROM absensi 
WHERE foto_absen IS NOT NULL OR foto_absen_keluar IS NOT NULL
LIMIT 10;
EOF

echo "Generated SQL script: ${GREEN}update_absen_photo_paths.sql${NC}"
echo ""
echo "Run: ${YELLOW}mysql -u root aplikasi < update_absen_photo_paths.sql${NC}"
echo ""
echo "=========================================="
