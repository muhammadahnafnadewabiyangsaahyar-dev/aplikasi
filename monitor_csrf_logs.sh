#!/bin/bash

# CSRF Import Log Monitor
# Real-time monitoring of CSRF and import-related logs

echo "================================================"
echo "   CSRF Import Log Monitor"
echo "   Monitoring: /Applications/XAMPP/xamppfiles/logs/error_log"
echo "================================================"
echo ""
echo "Watching for keywords: whitelist, csrf, CSRF, invalid, import, POST"
echo "Press Ctrl+C to stop"
echo ""
echo "------------------------------------------------"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Monitor the log file in real-time
tail -f /Applications/XAMPP/xamppfiles/logs/error_log | while read line; do
    # Check if line contains relevant keywords (case insensitive)
    if echo "$line" | grep -iE "(whitelist|csrf|invalid|import|POST.*received|token)" > /dev/null; then
        
        # Color code based on content
        if echo "$line" | grep -iE "(error|fail|invalid|❌)" > /dev/null; then
            echo -e "${RED}ERROR:${NC} $line"
        elif echo "$line" | grep -iE "(success|passed|✅)" > /dev/null; then
            echo -e "${GREEN}SUCCESS:${NC} $line"
        elif echo "$line" | grep -iE "(warning|warn|⚠️)" > /dev/null; then
            echo -e "${YELLOW}WARNING:${NC} $line"
        elif echo "$line" | grep -iE "(debug|===)" > /dev/null; then
            echo -e "${CYAN}DEBUG:${NC} $line"
        elif echo "$line" | grep -iE "(POST|REQUEST)" > /dev/null; then
            echo -e "${PURPLE}REQUEST:${NC} $line"
        else
            echo -e "${BLUE}INFO:${NC} $line"
        fi
    fi
done
