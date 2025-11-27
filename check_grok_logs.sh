#!/bin/bash
# Script to check Grok AI logs on production server

echo "=== GROK AI LOGS CHECK ==="
echo ""

# Check Apache error logs for GrokService entries
echo "1. GrokService logs (last 50 lines):"
echo "-----------------------------------"
tail -n 50 /var/log/apache2/error.log | grep "GrokService"
echo ""

# Check Apache error logs for GrokHandler entries
echo "2. GrokHandler logs (last 50 lines):"
echo "-----------------------------------"
tail -n 50 /var/log/apache2/error.log | grep "GrokHandler"
echo ""

# Check Apache error logs for GenericmessageCommand Grok entries
echo "3. GenericmessageCommand Grok logs (last 50 lines):"
echo "-----------------------------------"
tail -n 50 /var/log/apache2/error.log | grep "GenericmessageCommand.*Grok"
echo ""

# Check for CURL errors
echo "4. CURL-related errors (last 20 lines):"
echo "-----------------------------------"
tail -n 100 /var/log/apache2/error.log | grep -i "curl"
echo ""

# Check for any PHP fatal errors in last 100 lines
echo "5. Recent PHP fatal errors:"
echo "-----------------------------------"
tail -n 100 /var/log/apache2/error.log | grep -i "fatal"
echo ""

# Live monitoring (optional - run separately)
# echo "6. Live monitoring (Ctrl+C to stop):"
# echo "-----------------------------------"
# tail -f /var/log/apache2/error.log | grep --line-buffered "Grok"

echo "=== CHECK COMPLETE ==="
echo ""
echo "To monitor in real-time, run:"
echo "  tail -f /var/log/apache2/error.log | grep --line-buffered Grok"
