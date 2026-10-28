#!/bin/bash
# Server restart script - run this via SSH
echo "=== Restarting PHP-FPM and Clearing OPcache ==="
echo ""

# Method 1: systemctl
echo "1. Trying systemctl..."
sudo systemctl restart php-fpm
if [ $? -eq 0 ]; then
    echo "   ✅ PHP-FPM restarted via systemctl"
else
    echo "   ⚠️ systemctl failed, trying service..."
    sudo service php7.4-fpm restart
fi

# Method 2: Create PHP script to clear cache
echo ""
echo "2. Creating cache clear script..."
cat > /var/www/api.ladyzenko.pro/tg/ozna/clear_opcache.php << 'PHPEOF'
<?php
header('Content-Type: text/plain');
echo "Clearing OPcache...\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
} else {
    echo "ℹ️ OPcache not enabled\n";
}
echo "Done.\n";
?>
PHPEOF

chmod 644 /var/www/api.ladyzenko.pro/tg/ozna/clear_opcache.php
echo "   ✅ Created clear_opcache.php"

# Method 3: Touch the file to force reload
echo ""
echo "3. Touching ProjectHandler.php to force reload..."
touch /var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php

# Verify
echo ""
echo "4. Verification:"
FILE="/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php"
if [ -f "$FILE" ]; then
    FREE_COUNT=$(grep -c '\$result->free()' "$FILE" 2>/dev/null || echo "0")
    BIND9=$(grep -c "bind_param('sssssssss'" "$FILE" 2>/dev/null || echo "0")
    BIND11=$(grep -c "bind_param('sssssssssss'" "$FILE" 2>/dev/null || echo "0")
    
    echo "   File size: $(stat -c%s $FILE) bytes"
    echo "   Modified: $(stat -c%y $FILE)"
    echo "   \$result->free(): {$FREE_COUNT}"
    echo "   bind_param (9 params): {$BIND9}"
    echo "   bind_param (11 params - OLD): {$BIND11}"
    
    if [ "$BIND11" -gt "0" ]; then
        echo ""
        echo "   ❌ WARNING: File has OLD code (11-param bind_param)!"
        echo "      SFTP sync may have failed."
    elif [ "$FREE_COUNT" -ge "15" ] && [ "$BIND9" -ge "3" ]; then
        echo ""
        echo "   ✅ File looks correct!"
    fi
fi

echo ""
echo "=== Done ==="
echo ""
echo "Now try opening project card OI846 in the bot."
echo "If still failing, open in browser:"
echo "http://api.ladyzenko.pro/tg/ozna/clear_opcache.php"
