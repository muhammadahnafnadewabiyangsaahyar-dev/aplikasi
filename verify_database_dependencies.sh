#!/bin/bash

# Script: verify_database_dependencies.sh
# Purpose: Comprehensive verification of database dependencies in PHP files
# Author: KAORI HR Development Team
# Date: November 6, 2024

echo "======================================================"
echo "  VERIFIKASI DEPENDENCY DATABASE - SISTEM KAORI HR"
echo "======================================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PROJECT_DIR="/Applications/XAMPP/xamppfiles/htdocs/aplikasi"

cd "$PROJECT_DIR" || exit 1

echo -e "${YELLOW}[1] Checking for VIEW dependencies...${NC}"
echo "================================================"

echo "Views in database:"
echo "  - v_absensi_dengan_shift"
echo "  - v_jadwal_shift_harian"
echo "  - v_ringkasan_gaji"
echo ""

VIEW_USAGE=$(grep -r "v_absensi_dengan_shift\|v_jadwal_shift_harian\|v_ringkasan_gaji" --include="*.php" . 2>/dev/null | wc -l)

if [ "$VIEW_USAGE" -eq 0 ]; then
    echo -e "${GREEN}✅ NO PHP files use database VIEWs${NC}"
else
    echo -e "${RED}⚠️  WARNING: $VIEW_USAGE file(s) may use VIEWs${NC}"
    grep -r "v_absensi_dengan_shift\|v_jadwal_shift_harian\|v_ringkasan_gaji" --include="*.php" .
fi

echo ""
echo -e "${YELLOW}[2] Checking for STORED PROCEDURE calls...${NC}"
echo "================================================"

echo "Procedures in database:"
echo "  - sp_assign_shift"
echo "  - sp_konfirmasi_shift"
echo "  - sp_hitung_kehadiran_periode"
echo ""

SP_USAGE=$(grep -r "CALL sp_\|sp_assign_shift\|sp_konfirmasi_shift\|sp_hitung_kehadiran_periode" --include="*.php" . 2>/dev/null | wc -l)

if [ "$SP_USAGE" -eq 0 ]; then
    echo -e "${GREEN}✅ NO PHP files call STORED PROCEDUREs${NC}"
else
    echo -e "${RED}⚠️  WARNING: $SP_USAGE file(s) may call stored procedures${NC}"
    grep -r "CALL sp_\|sp_assign_shift\|sp_konfirmasi_shift\|sp_hitung_kehadiran_periode" --include="*.php" .
fi

echo ""
echo -e "${YELLOW}[3] Checking for mysqli::multi_query usage...${NC}"
echo "================================================"
echo "(multi_query is often used for calling stored procedures)"
echo ""

MULTI_QUERY=$(grep -r "multi_query" --include="*.php" . 2>/dev/null | wc -l)

if [ "$MULTI_QUERY" -eq 0 ]; then
    echo -e "${GREEN}✅ NO usage of multi_query (typically used for SP calls)${NC}"
else
    echo -e "${YELLOW}ℹ️  Found $MULTI_QUERY file(s) using multi_query${NC}"
    grep -r "multi_query" --include="*.php" . | head -10
fi

echo ""
echo -e "${YELLOW}[4] Checking for TRIGGER dependencies...${NC}"
echo "================================================"
echo "Note: Triggers run automatically, but checking for manual references..."
echo ""

TRIGGER_USAGE=$(grep -r "tr_absensi_calculate_duration" --include="*.php" . 2>/dev/null | wc -l)

if [ "$TRIGGER_USAGE" -eq 0 ]; then
    echo -e "${GREEN}✅ NO explicit TRIGGER references in PHP${NC}"
    echo "   (This is normal - triggers work at database level)"
else
    echo -e "${YELLOW}ℹ️  Found $TRIGGER_USAGE reference(s) to triggers${NC}"
    grep -r "tr_absensi_calculate_duration" --include="*.php" .
fi

echo ""
echo -e "${YELLOW}[5] Checking for potential duration calculation logic...${NC}"
echo "================================================"
echo "Checking if PHP code handles duration calculation..."
echo ""

DURATION_CALC=$(grep -r "durasi_kerja_menit\|durasi_overwork" --include="*.php" . 2>/dev/null | wc -l)

if [ "$DURATION_CALC" -gt 0 ]; then
    echo -e "${GREEN}✅ Found $DURATION_CALC file(s) handling duration calculation${NC}"
    echo "   Sample files:"
    grep -r "durasi_kerja_menit" --include="*.php" . | cut -d: -f1 | sort | uniq | head -5
else
    echo -e "${YELLOW}⚠️  No PHP duration calculation found${NC}"
    echo "   May rely on database TRIGGER (needs attention for free hosting)"
fi

echo ""
echo -e "${YELLOW}[6] Analyzing SQL export compatibility...${NC}"
echo "================================================"

if [ -f "aplikasi_byethost_clean.sql" ]; then
    echo "Checking cleaned SQL file..."
    
    VIEW_IN_CLEAN=$(grep -c "CREATE VIEW" aplikasi_byethost_clean.sql 2>/dev/null || echo 0)
    PROC_IN_CLEAN=$(grep -c "CREATE PROCEDURE" aplikasi_byethost_clean.sql 2>/dev/null || echo 0)
    TRIG_IN_CLEAN=$(grep -c "CREATE TRIGGER" aplikasi_byethost_clean.sql 2>/dev/null || echo 0)
    
    if [ "$VIEW_IN_CLEAN" -eq 0 ] && [ "$PROC_IN_CLEAN" -eq 0 ] && [ "$TRIG_IN_CLEAN" -eq 0 ]; then
        echo -e "${GREEN}✅ Cleaned SQL file is free from VIEWs/PROCEDUREs/TRIGGERs${NC}"
    else
        echo -e "${RED}⚠️  Cleaned SQL still contains:${NC}"
        echo "   - VIEWs: $VIEW_IN_CLEAN"
        echo "   - PROCEDUREs: $PROC_IN_CLEAN"
        echo "   - TRIGGERs: $TRIG_IN_CLEAN"
    fi
else
    echo -e "${YELLOW}ℹ️  No cleaned SQL file found. Run: ./clean_sql_for_byethost.sh${NC}"
fi

echo ""
echo -e "${YELLOW}[7] Critical files analysis...${NC}"
echo "================================================"

CRITICAL_FILES=(
    "absen.php"
    "proses_approve.php"
    "calculate_salary.php"
    "api_shift_management.php"
    "mainpage.php"
)

echo "Checking critical files for database operations..."
for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        INSERT_COUNT=$(grep -c "INSERT INTO" "$file" 2>/dev/null || echo 0)
        UPDATE_COUNT=$(grep -c "UPDATE " "$file" 2>/dev/null || echo 0)
        SELECT_COUNT=$(grep -c "SELECT " "$file" 2>/dev/null || echo 0)
        
        echo "  $file:"
        echo "    - INSERT: $INSERT_COUNT"
        echo "    - UPDATE: $UPDATE_COUNT"
        echo "    - SELECT: $SELECT_COUNT"
    fi
done

echo ""
echo "======================================================"
echo -e "${YELLOW}  SUMMARY - DEPLOYMENT READINESS${NC}"
echo "======================================================"
echo ""

# Calculate final score
ISSUES=0

if [ "$VIEW_USAGE" -gt 0 ]; then
    ((ISSUES++))
fi

if [ "$SP_USAGE" -gt 0 ]; then
    ((ISSUES++))
fi

if [ "$DURATION_CALC" -eq 0 ]; then
    echo -e "${YELLOW}⚠️  MINOR: No PHP duration calculation (may rely on TRIGGER)${NC}"
    echo "   Recommendation: Add duration calculation in PHP"
fi

if [ "$ISSUES" -eq 0 ]; then
    echo -e "${GREEN}"
    echo "╔════════════════════════════════════════════════════╗"
    echo "║                                                    ║"
    echo "║         ✅ SYSTEM READY FOR DEPLOYMENT            ║"
    echo "║                                                    ║"
    echo "║   No PHP dependencies on VIEWs or PROCEDUREs      ║"
    echo "║   Safe to deploy to free hosting (ByetHost)       ║"
    echo "║                                                    ║"
    echo "╚════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo ""
    echo "Next steps:"
    echo "  1. Run: ./clean_sql_for_byethost.sh"
    echo "  2. Upload aplikasi_byethost_clean.sql to hosting"
    echo "  3. Run: ./create_deployment_package.sh"
    echo "  4. Upload files to hosting"
    echo "  5. Update connect.php with hosting credentials"
    echo ""
    exit 0
else
    echo -e "${RED}"
    echo "╔════════════════════════════════════════════════════╗"
    echo "║                                                    ║"
    echo "║         ⚠️  ISSUES FOUND - REVIEW NEEDED          ║"
    echo "║                                                    ║"
    echo "║   Found $ISSUES potential issue(s)                        ║"
    echo "║   Review the warnings above                        ║"
    echo "║                                                    ║"
    echo "╚════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo ""
    echo "Please review the issues above before deployment."
    echo ""
    exit 1
fi
