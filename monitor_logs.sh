#!/bin/bash
# Real-time Error Log Monitor for Import CSV Debugging

echo "🔍 Starting Real-Time Error Log Monitor"
echo "========================================"
echo ""
echo "Monitoring: /Applications/XAMPP/xamppfiles/logs/error_log"
echo "Press Ctrl+C to stop"
echo ""
echo "Waiting for logs..."
echo "========================================"
echo ""

# Tail error log with color highlighting
tail -f /Applications/XAMPP/xamppfiles/logs/error_log | while read line; do
    # Highlight different log types
    if echo "$line" | grep -q "==="; then
        # Section headers (bold green)
        echo -e "\033[1;32m$line\033[0m"
    elif echo "$line" | grep -q "✅"; then
        # Success (green)
        echo -e "\033[0;32m$line\033[0m"
    elif echo "$line" | grep -q "❌"; then
        # Error (red)
        echo -e "\033[0;31m$line\033[0m"
    elif echo "$line" | grep -q "CSRF"; then
        # CSRF related (yellow)
        echo -e "\033[0;33m$line\033[0m"
    elif echo "$line" | grep -q "POST\|SESSION"; then
        # POST/SESSION related (cyan)
        echo -e "\033[0;36m$line\033[0m"
    else
        # Normal
        echo "$line"
    fi
done
