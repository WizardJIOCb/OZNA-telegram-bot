#!/bin/bash
# Diagnostic script to run on server
echo "=== Server Diagnostics ==="
echo ""

echo "1. PHP-FPM Status:"
systemctl status php-fpm --no-pager -n 20 2>/dev/null || service php7.4-fpm status 2>/dev/null || echo "Cannot check PHP-FPM status"
echo ""

echo "2. Nginx Status:"
systemctl status nginx --no-pager -n 10 2>/dev/null || service nginx status 2>/dev/null || echo "Cannot check Nginx status"
echo ""

echo "3. ProjectHandler.php file info:"
FILE="/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php"
if [ -f "$FILE" ]; then
    echo "  Size: $(stat -c%s "$FILE") bytes"
    echo "  Modified: $(stat -c%y "$FILE")"
    echo "  MD5: $(md5sum "$FILE" | cut -d' ' -f1)"
    echo "  Last 10 lines:"
    tail -10 "$FILE" | sed 's/^/    /'
else
    echo "  ❌ File not found!"
fi
echo ""

echo "4. PHP Error Logs:"
if [ -f /var/log/php/error.log ]; then
    echo "  Last 20 lines from /var/log/php/error.log:"
    tail -20 /var/log/php/error.log | sed 's/^/    /'
elif [ -f /var/log/php-fpm/error.log ]; then
    echo "  Last 20 lines from /var/log/php-fpm/error.log:"
    tail -20 /var/log/php-fpm/error.log | sed 's/^/    /'
else
    echo "  ⚠️ Standard PHP logs not found"
fi
echo ""

echo "5. Nginx Error Logs:"
if [ -f /var/log/nginx/error.log ]; then
    echo "  Last 20 lines from /var/log/nginx/error.log:"
    tail -20 /var/log/nginx/error.log | sed 's/^/    /'
else
    echo "  ⚠️ Nginx error log not found"
fi
echo ""

echo "6. OPcache Status:"
php -r "if (function_exists('opcache_get_status')) { \$s = opcache_get_status(); print_r(\$s); } else { echo 'OPcache not enabled'; }" 2>/dev/null || echo "Cannot check OPcache"
echo ""

echo "7. Bot Process:"
ps aux | grep -E "[p]hp.*bot|[b]ot.*php" | head -5 || echo "No bot process found"
echo ""

echo "8. Restart Services:"
echo "To restart PHP-FPM, run:"
echo "  sudo systemctl restart php-fpm"
echo "  OR"
echo "  sudo service php7.4-fpm restart"
echo ""
echo "To restart Nginx, run:"
echo "  sudo systemctl restart nginx"
