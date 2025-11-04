#!/bin/bash

# Cleanup script untuk hapus file MD yang tidak diperlukan
# KEEP only essential documentation files

echo "🗑️  Starting cleanup of unnecessary MD files..."

# Navigate to project root
cd /Applications/XAMPP/xamppfiles/htdocs/aplikasi

# List of ESSENTIAL MD files to KEEP
KEEP_FILES=(
    "README.md"
    "MASTER_GUIDE.md"
    "KALENDER/HYBRID_CALENDAR_COMPLETE.md"
)

# Create backup folder for deleted files
BACKUP_DIR="./DELETED_MD_BACKUP_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo "📦 Backup folder: $BACKUP_DIR"
echo ""

# Counter
KEPT=0
DELETED=0

# Find all MD files
while IFS= read -r file; do
    # Get filename
    filename=$(basename "$file")
    filepath="$file"
    
    # Check if file should be kept
    SHOULD_KEEP=0
    for keep in "${KEEP_FILES[@]}"; do
        if [[ "$filepath" == "./$keep" ]]; then
            SHOULD_KEEP=1
            break
        fi
    done
    
    if [ $SHOULD_KEEP -eq 1 ]; then
        echo "✅ KEEPING: $filepath"
        ((KEPT++))
    else
        echo "🗑️  DELETING: $filepath"
        # Move to backup instead of permanent delete
        mkdir -p "$BACKUP_DIR/$(dirname "$file")"
        mv "$file" "$BACKUP_DIR/$file"
        ((DELETED++))
    fi
done < <(find . -name "*.md" -type f)

echo ""
echo "================================================"
echo "✅ Cleanup complete!"
echo "📊 Statistics:"
echo "   - Files kept: $KEPT"
echo "   - Files deleted: $DELETED"
echo "   - Backup location: $BACKUP_DIR"
echo "================================================"
echo ""
echo "💡 Tip: If you need any deleted file, check the backup folder"
echo "To permanently delete backup: rm -rf $BACKUP_DIR"
