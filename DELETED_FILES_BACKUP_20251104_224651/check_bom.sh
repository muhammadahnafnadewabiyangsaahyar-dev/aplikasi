#!/bin/bash

echo "========================================="
echo "CHECK BOM & HIDDEN CHARACTERS"
echo "========================================="
echo ""

files=(
    "connect.php"
    "calculate_status_kehadiran.php"
    "absen_helper.php"
    "navbar.php"
    "index.php"
    "rekapabsen.php"
)

echo "Checking for BOM (EF BB BF) at start of files:"
echo "----------------------------------------------"
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        first_bytes=$(head -c 3 "$file" | od -An -tx1 | tr -d ' ')
        echo -n "$file: "
        if [ "$first_bytes" = "efbbbf" ]; then
            echo "❌ HAS BOM!"
        else
            echo "✅ No BOM (starts with: $first_bytes)"
        fi
    fi
done

echo ""
echo "Checking first 20 bytes of each file:"
echo "----------------------------------------------"
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "$file:"
        head -c 20 "$file" | od -An -tx1c
        echo ""
    fi
done

echo "========================================="
echo "CHECK COMPLETE"
echo "========================================="
