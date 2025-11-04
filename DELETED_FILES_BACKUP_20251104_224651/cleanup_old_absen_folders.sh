#!/bin/bash
# ========================================================
# CLEANUP OLD ABSEN FOLDERS
# Purpose: Remove old absen_masuk and absen_keluar folders
# ========================================================

echo "=========================================="
echo "CLEANUP OLD ABSEN FOLDERS"
echo "Date: $(date)"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

OLD_FOLDER_MASUK="uploads/absen_masuk"
OLD_FOLDER_KELUAR="uploads/absen_keluar"
NEW_FOLDER="uploads/absensi"

echo "This script will:"
echo "1. Verify all photos migrated to $NEW_FOLDER/"
echo "2. Create archive of old folders (for safety)"
echo "3. Remove old folders: $OLD_FOLDER_MASUK/ and $OLD_FOLDER_KELUAR/"
echo ""
echo -e "${YELLOW}⚠ WARNING: This action cannot be easily undone!${NC}"
echo ""
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo -e "${RED}✗${NC} Cleanup cancelled."
    exit 0
fi

echo ""

# Step 1: Count files
if [ -d "$OLD_FOLDER_MASUK" ]; then
    MASUK_COUNT=$(ls -1 "$OLD_FOLDER_MASUK" 2>/dev/null | wc -l | tr -d ' ')
    echo "Found $MASUK_COUNT files in $OLD_FOLDER_MASUK/"
else
    MASUK_COUNT=0
    echo -e "${YELLOW}⚠${NC}  $OLD_FOLDER_MASUK/ not found (already deleted?)"
fi

if [ -d "$OLD_FOLDER_KELUAR" ]; then
    KELUAR_COUNT=$(ls -1 "$OLD_FOLDER_KELUAR" 2>/dev/null | wc -l | tr -d ' ')
    echo "Found $KELUAR_COUNT files in $OLD_FOLDER_KELUAR/"
else
    KELUAR_COUNT=0
    echo -e "${YELLOW}⚠${NC}  $OLD_FOLDER_KELUAR/ not found (already deleted?)"
fi

if [ -d "$NEW_FOLDER" ]; then
    NEW_COUNT=$(ls -1 "$NEW_FOLDER" 2>/dev/null | wc -l | tr -d ' ')
    echo "Found $NEW_COUNT files in $NEW_FOLDER/"
else
    echo -e "${RED}✗${NC}  ERROR: $NEW_FOLDER/ not found!"
    echo "Migration may have failed. Aborting cleanup."
    exit 1
fi

echo ""

# Step 2: Verify migration
EXPECTED_TOTAL=$((MASUK_COUNT + KELUAR_COUNT))
if [ $NEW_COUNT -lt $EXPECTED_TOTAL ]; then
    echo -e "${RED}✗${NC}  ERROR: File count mismatch!"
    echo "   Expected at least: $EXPECTED_TOTAL files"
    echo "   Found in new folder: $NEW_COUNT files"
    echo ""
    echo "Migration may be incomplete. Aborting cleanup."
    exit 1
fi

echo -e "${GREEN}✓${NC} File count verification passed"
echo ""

# Step 3: Create archive (optional but recommended)
echo "Creating archive of old folders..."
ARCHIVE_NAME="old_absen_folders_backup_$(date +%Y%m%d_%H%M%S).tar.gz"

if [ $MASUK_COUNT -gt 0 ] || [ $KELUAR_COUNT -gt 0 ]; then
    tar -czf "$ARCHIVE_NAME" "$OLD_FOLDER_MASUK" "$OLD_FOLDER_KELUAR" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} Archive created: $ARCHIVE_NAME"
        ARCHIVE_SIZE=$(du -h "$ARCHIVE_NAME" | cut -f1)
        echo "   Size: $ARCHIVE_SIZE"
    else
        echo -e "${YELLOW}⚠${NC}  Failed to create archive (non-critical, continuing...)"
    fi
else
    echo -e "${YELLOW}⚠${NC}  No files to archive"
fi

echo ""

# Step 4: Remove old folders
echo "Removing old folders..."

if [ -d "$OLD_FOLDER_MASUK" ]; then
    rm -rf "$OLD_FOLDER_MASUK"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} Removed $OLD_FOLDER_MASUK/"
    else
        echo -e "${RED}✗${NC}  Failed to remove $OLD_FOLDER_MASUK/"
    fi
fi

if [ -d "$OLD_FOLDER_KELUAR" ]; then
    rm -rf "$OLD_FOLDER_KELUAR"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} Removed $OLD_FOLDER_KELUAR/"
    else
        echo -e "${RED}✗${NC}  Failed to remove $OLD_FOLDER_KELUAR/"
    fi
fi

echo ""
echo "=========================================="
echo "CLEANUP COMPLETE"
echo "=========================================="
echo ""
echo "Summary:"
echo -e "  ${GREEN}✓${NC} Old folders removed"
echo -e "  ${GREEN}✓${NC} $NEW_COUNT photos available in $NEW_FOLDER/"
if [ -f "$ARCHIVE_NAME" ]; then
    echo -e "  ${GREEN}✓${NC} Backup archive: $ARCHIVE_NAME"
fi
echo ""
echo "Next Steps:"
echo "1. ${YELLOW}Test absensi${NC} on browser to verify photos display correctly"
echo "2. ${YELLOW}Monitor for any issues${NC} over next few days"
echo "3. ${YELLOW}After 1 week${NC}, if no issues, you can delete archive:"
echo "   rm $ARCHIVE_NAME"
echo ""
echo "=========================================="
