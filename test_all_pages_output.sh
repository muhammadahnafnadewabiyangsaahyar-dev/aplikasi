#!/bin/bash

echo "========================================="
echo "TEST OUTPUT UNTUK SEMUA FILE PHP"
echo "========================================="
echo ""

files=(
    "connect.php"
    "calculate_status_kehadiran.php"
    "absen_helper.php"
    "navbar.php"
)

echo "1. Test File Include (harus tidak ada output):"
echo "----------------------------------------------"
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo -n "Testing $file... "
        output=$(php -r "ob_start(); include '$file'; \$out = ob_get_clean(); if (!empty(\$out)) echo \$out;")
        if [ -z "$output" ]; then
            echo "✅ OK"
        else
            echo "❌ ERROR - Ada output:"
            echo "$output" | od -c
        fi
    fi
done

echo ""
echo "2. Test Syntax PHP Files:"
echo "----------------------------------------------"
for file in index.php rekapabsen.php view_absensi.php absen.php; do
    if [ -f "$file" ]; then
        echo -n "Checking syntax $file... "
        if php -l "$file" > /dev/null 2>&1; then
            echo "✅ OK"
        else
            echo "❌ SYNTAX ERROR"
            php -l "$file"
        fi
    fi
done

echo ""
echo "3. Test End of File (tidak boleh ada ?> di file include):"
echo "----------------------------------------------"
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        last_chars=$(tail -c 10 "$file" | od -An -tx1)
        echo "$file: $last_chars"
    fi
done

echo ""
echo "========================================="
echo "TEST SELESAI"
echo "========================================="
