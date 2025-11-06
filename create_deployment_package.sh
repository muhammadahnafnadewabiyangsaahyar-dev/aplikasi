#!/bin/bash

# ================================================
# DEPLOYMENT PACKAGE CREATOR FOR KAORI HR SYSTEM
# ================================================
# Script ini akan membuat package siap upload ke hosting
# dengan exclude file-file yang tidak perlu

echo "======================================"
echo "KAORI HR - Deployment Package Creator"
echo "======================================"
echo ""

# Set variables
APP_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi"
OUTPUT_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
PACKAGE_NAME="kaori_hr_deployment_${TIMESTAMP}.zip"

echo "📦 Creating deployment package..."
echo "Source: $APP_DIR"
echo "Output: $OUTPUT_DIR/$PACKAGE_NAME"
echo ""

# Check if source directory exists
if [ ! -d "$APP_DIR" ]; then
    echo "❌ Error: Application directory not found: $APP_DIR"
    exit 1
fi

# Go to app directory
cd "$APP_DIR"

# Create ZIP package with exclusions
echo "🗜️  Compressing files..."
zip -r "$OUTPUT_DIR/$PACKAGE_NAME" . \
    -x "*.git/*" \
    -x "*.gitignore" \
    -x "*.md" \
    -x "*.log" \
    -x "backup_*.sh" \
    -x "cleanup_*.sh" \
    -x "test_*.php" \
    -x "debug_*.php" \
    -x "diagnostic_*.php" \
    -x "check_*.sh" \
    -x "demo_*.html" \
    -x "dummy_*.sql" \
    -x "*.tar.gz" \
    -x "node_modules/*" \
    -x "vendor/*" \
    -x "composer.lock" \
    -x "uploads/*" \
    -x "logs/*" \
    -x "deleted_md_backup_*.tar.gz" \
    > /dev/null 2>&1

# Check if ZIP was created successfully
if [ $? -eq 0 ]; then
    echo "✅ Package created successfully!"
    echo ""
    echo "📁 Package details:"
    ls -lh "$OUTPUT_DIR/$PACKAGE_NAME"
    echo ""
    echo "📋 What's included in the package:"
    echo "   ✅ All PHP files (*.php)"
    echo "   ✅ All CSS files (*.css)"
    echo "   ✅ All JavaScript files (*.js)"
    echo "   ✅ Images (logo.png, etc.)"
    echo "   ✅ Whitelist data (datawhitelistpegawai.csv)"
    echo "   ✅ Security helper (security_helper.php)"
    echo ""
    echo "❌ What's excluded:"
    echo "   ❌ Documentation files (*.md)"
    echo "   ❌ Log files (*.log)"
    echo "   ❌ Test/Debug files (test_*.php, debug_*.php)"
    echo "   ❌ Backup scripts (backup_*.sh)"
    echo "   ❌ Git files (.git, .gitignore)"
    echo "   ❌ Uploads folder (create manually on server)"
    echo "   ❌ Logs folder (create manually on server)"
    echo ""
    echo "📝 Next steps:"
    echo "   1. Upload $PACKAGE_NAME to your hosting via cPanel/FTP"
    echo "   2. Extract ZIP in public_html/ directory"
    echo "   3. Create folders: uploads/ and logs/ with chmod 755"
    echo "   4. Import database (see PANDUAN_DEPLOYMENT_HOSTING.md)"
    echo "   5. Update connect.php with production database credentials"
    echo "   6. Test the application"
    echo ""
    echo "📚 For detailed instructions, read: PANDUAN_DEPLOYMENT_HOSTING.md"
    echo ""
    echo "🎉 Ready for deployment!"
else
    echo "❌ Error: Failed to create package"
    exit 1
fi
