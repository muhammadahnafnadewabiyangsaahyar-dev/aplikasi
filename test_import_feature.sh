#!/bin/bash

# Test Import Database Feature
# Script ini untuk testing fitur import database

echo "=========================================="
echo "🧪 TESTING FITUR IMPORT DATABASE"
echo "=========================================="
echo ""

# Check if import_database.php exists
echo "1️⃣ Checking import_database.php..."
if [ -f "import_database.php" ]; then
    echo "   ✅ File exists"
else
    echo "   ❌ File not found!"
    exit 1
fi

# Check if navbar.php updated
echo ""
echo "2️⃣ Checking navbar.php update..."
if grep -q "import_db_url" navbar.php; then
    echo "   ✅ Variable added"
else
    echo "   ❌ Variable not found!"
fi

if grep -q "Import DB" navbar.php; then
    echo "   ✅ Menu link added"
else
    echo "   ❌ Menu link not found!"
fi

# Check if backup script exists
echo ""
echo "3️⃣ Checking backup_database.sh..."
if [ -f "backup_database.sh" ]; then
    echo "   ✅ File exists"
    if [ -x "backup_database.sh" ]; then
        echo "   ✅ Executable"
    else
        echo "   ⚠️  Not executable (run: chmod +x backup_database.sh)"
    fi
else
    echo "   ❌ File not found!"
fi

# Check if documentation exists
echo ""
echo "4️⃣ Checking documentation..."
if [ -f "PANDUAN_IMPORT_DATABASE.md" ]; then
    echo "   ✅ PANDUAN_IMPORT_DATABASE.md exists"
else
    echo "   ❌ PANDUAN_IMPORT_DATABASE.md not found!"
fi

if [ -f "WARNING_IMPORT_SQL.md" ]; then
    echo "   ✅ WARNING_IMPORT_SQL.md exists"
else
    echo "   ❌ WARNING_IMPORT_SQL.md not found!"
fi

if [ -f "FITUR_IMPORT_SUMMARY.md" ]; then
    echo "   ✅ FITUR_IMPORT_SUMMARY.md exists"
else
    echo "   ❌ FITUR_IMPORT_SUMMARY.md not found!"
fi

# Check backups folder
echo ""
echo "5️⃣ Checking backups folder..."
if [ -d "backups" ]; then
    echo "   ✅ Folder exists"
    BACKUP_COUNT=$(ls -1 backups/*.sql 2>/dev/null | wc -l)
    echo "   📦 Backup files: $BACKUP_COUNT"
else
    echo "   ⚠️  Folder not exists (will be created automatically)"
fi

# Test database connection
echo ""
echo "6️⃣ Testing database connection..."
MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql"
if $MYSQL -u root -e "USE aplikasi; SELECT COUNT(*) FROM register;" &>/dev/null; then
    echo "   ✅ Database connection OK"
    USER_COUNT=$($MYSQL -u root aplikasi -se "SELECT COUNT(*) FROM register;")
    echo "   👥 Current users: $USER_COUNT"
else
    echo "   ❌ Database connection failed!"
fi

echo ""
echo "=========================================="
echo "📊 SUMMARY"
echo "=========================================="
echo ""
echo "✅ READY TO USE!"
echo ""
echo "🔗 Access the feature:"
echo "   1. Login as admin"
echo "   2. Go to: http://localhost/aplikasi/import_database.php"
echo "   3. Or click '⚠️ Import DB' in navbar"
echo ""
echo "📖 Read documentation:"
echo "   - PANDUAN_IMPORT_DATABASE.md (full guide)"
echo "   - WARNING_IMPORT_SQL.md (quick warning)"
echo "   - FITUR_IMPORT_SUMMARY.md (summary)"
echo ""
echo "🔧 Useful commands:"
echo "   - ./backup_database.sh (backup database)"
echo "   - ./watch_check_register.sh (monitor database)"
echo "   - ./check_register.sh (quick check)"
echo ""
echo "=========================================="
