#!/bin/bash

# ================================================
# ALL-IN-ONE DEPLOYMENT PREPARATION SCRIPT
# ================================================
# Script ini akan:
# 1. Backup database
# 2. Create deployment package
# 3. Generate checklist
# ================================================

echo "================================================"
echo "   KAORI HR - DEPLOYMENT PREPARATION WIZARD"
echo "================================================"
echo ""
echo "This script will prepare your application for deployment:"
echo "  1. Export production database"
echo "  2. Create deployment package (ZIP)"
echo "  3. Generate deployment checklist"
echo ""
read -p "Press ENTER to continue or Ctrl+C to cancel..."
echo ""

# Set colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Export Database
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}STEP 1: EXPORTING DATABASE${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

bash export_database_for_deployment.sh

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Database export failed. Please fix the error and try again.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Step 1 completed!${NC}"
echo ""
sleep 2

# Step 2: Create Deployment Package
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}STEP 2: CREATING DEPLOYMENT PACKAGE${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

bash create_deployment_package.sh

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Package creation failed. Please fix the error and try again.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Step 2 completed!${NC}"
echo ""
sleep 2

# Step 3: Generate Checklist
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}STEP 3: GENERATING DEPLOYMENT CHECKLIST${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
CHECKLIST_FILE="DEPLOYMENT_CHECKLIST_${TIMESTAMP}.txt"

cat > "$CHECKLIST_FILE" << 'EOF'
================================================
   KAORI HR - DEPLOYMENT CHECKLIST
================================================

Generated: $(date)

PREPARATION (LOCAL):
--------------------
[ ] Database cleaned from dummy data (cleanup_dummy_data.php executed)
[ ] All features tested and working
[ ] Database exported (aplikasi_production_*.sql.gz)
[ ] Deployment package created (kaori_hr_deployment_*.zip)
[ ] Backup of current production saved (if updating existing)

HOSTING SETUP:
--------------
[ ] Hosting account purchased and active
[ ] Domain name configured (DNS pointing to hosting)
[ ] SSL certificate installed (HTTPS enabled)
[ ] PHP version ≥ 7.4 confirmed
[ ] MySQL database created
[ ] Database user created with ALL PRIVILEGES
[ ] Database credentials noted (host, name, user, password)

FILE UPLOAD:
------------
[ ] Deployment package uploaded to hosting
[ ] Files extracted to correct directory (public_html/ or subdirectory)
[ ] Folder 'uploads/' created with chmod 755
[ ] Subfolders created: uploads/tanda_tangan, uploads/surat_izin, uploads/foto_absensi
[ ] Folder 'logs/' created with chmod 755
[ ] File permissions set correctly (755 for folders, 644 for files)

DATABASE:
---------
[ ] Database SQL file uploaded to hosting
[ ] Database imported via phpMyAdmin or MySQL console
[ ] No import errors occurred
[ ] Tables verified in phpMyAdmin (register, absensi, etc.)
[ ] Sample query tested (SELECT * FROM register LIMIT 1)

CONFIGURATION:
--------------
[ ] connect.php updated with production database credentials
[ ] .htaccess file created and configured
[ ] PHP settings configured (upload_max_filesize, etc.)
[ ] Error reporting set to production mode
[ ] Security settings enabled in .htaccess

TESTING:
--------
[ ] Website accessible via domain (https://yourdomain.com)
[ ] Homepage loads without errors
[ ] CSS and images loading correctly
[ ] Admin login working
[ ] User login working
[ ] Session persistence working (page refresh keeps login)
[ ] Logout working
[ ] Already-logged-in redirect working (index.php → mainpage.php)
[ ] Database connection working (data displays correctly)
[ ] File upload working (absensi photo, izin/sakit form)
[ ] Camera access working on mobile
[ ] GPS/location access working
[ ] CSRF token working (forms submitting correctly)
[ ] Rate limiting working (test multiple rapid submissions)
[ ] Session timeout working (2 hours inactive)
[ ] Security logs being written to logs/ folder
[ ] Mobile responsive OK (test on actual mobile device)

SECURITY:
---------
[ ] HTTPS enabled and working (green padlock in browser)
[ ] HTTP to HTTPS redirect working
[ ] connect.php not accessible via browser (should be denied)
[ ] security_helper.php not accessible via browser
[ ] .log files not accessible via browser
[ ] .csv files not accessible via browser
[ ] Directory listing disabled (accessing /uploads/ doesn't show files)
[ ] Error messages don't expose sensitive info
[ ] Database password is strong (16+ chars, mixed case, numbers, symbols)

BACKUP & MONITORING:
--------------------
[ ] Backup script set up (database + files)
[ ] Cron job configured for automatic backups
[ ] Error logs monitoring set up
[ ] Security logs checked regularly
[ ] Admin contact info ready for users

POST-DEPLOYMENT:
----------------
[ ] All users notified about new system URL
[ ] Login credentials distributed securely
[ ] User training conducted or scheduled
[ ] Admin standby for first 24-48 hours
[ ] Feedback collection system ready
[ ] Bug tracking system ready

TROUBLESHOOTING CONTACTS:
-------------------------
Hosting Support:
  - Provider: _______________________
  - Support Email: __________________
  - Support Phone: __________________
  - Live Chat: ______________________

Developer:
  - Name: ___________________________
  - Email: __________________________
  - Phone: __________________________
  - Available: ______________________

================================================
NOTES:
================================================
(Add any specific notes or issues encountered)

_________________________________________________
_________________________________________________
_________________________________________________
_________________________________________________

================================================
SIGN-OFF:
================================================

Prepared by: _____________________ Date: _______

Deployed by: _____________________ Date: _______

Verified by: _____________________ Date: _______

================================================
EOF

# Replace $(date) in the file
sed -i '' "s/\$(date)/$(date)/" "$CHECKLIST_FILE" 2>/dev/null || sed -i "s/\$(date)/$(date)/" "$CHECKLIST_FILE"

echo -e "${GREEN}✅ Checklist generated: $CHECKLIST_FILE${NC}"
echo ""

# Step 4: Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}DEPLOYMENT PREPARATION COMPLETE!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

echo -e "${GREEN}📦 Files Ready for Deployment:${NC}"
echo ""

# Find the latest files
LATEST_ZIP=$(ls -t kaori_hr_deployment_*.zip 2>/dev/null | head -1)
LATEST_SQL=$(ls -t aplikasi_production_*.sql.gz 2>/dev/null | head -1)

if [ -n "$LATEST_ZIP" ]; then
    echo -e "  ${GREEN}✅${NC} Application Package:"
    ls -lh "$LATEST_ZIP"
fi

if [ -n "$LATEST_SQL" ]; then
    echo -e "  ${GREEN}✅${NC} Database Export:"
    ls -lh "$LATEST_SQL"
fi

echo -e "  ${GREEN}✅${NC} Deployment Checklist:"
ls -lh "$CHECKLIST_FILE"

echo ""
echo -e "${YELLOW}📋 NEXT STEPS:${NC}"
echo ""
echo "1. Read the deployment guide:"
echo "   📄 PANDUAN_DEPLOYMENT_HOSTING.md"
echo ""
echo "2. Upload these files to your hosting:"
echo "   📦 $LATEST_ZIP"
echo "   🗄️  $LATEST_SQL"
echo ""
echo "3. Follow the checklist:"
echo "   ✅ $CHECKLIST_FILE"
echo ""
echo "4. Test thoroughly before announcing to users!"
echo ""
echo -e "${GREEN}🎉 Good luck with your deployment!${NC}"
echo ""
echo "================================================"
