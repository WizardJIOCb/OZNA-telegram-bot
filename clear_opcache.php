<?php
/**
 * Clear OPcache - run this via web browser!
 * Access: http://your-domain/clear_opcache.php
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Clearing PHP OPcache</h1>";

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<p style='color: green; font-size: 20px;'><b>✓ OPcache cleared successfully!</b></p>";
        echo "<p>Now test the bot again - it should use the new code.</p>";
    } else {
        echo "<p style='color: red;'><b>✗ Failed to clear OPcache</b></p>";
        echo "<p>You may need to restart PHP-FPM or Apache.</p>";
    }
} else {
    echo "<p style='color: orange;'><b>⚠ OPcache is not enabled</b></p>";
    echo "<p>The problem is something else.</p>";
}

echo "<hr>";
echo "<h2>Alternative: Restart your web server</h2>";
echo "<p>Run one of these commands:</p>";
echo "<pre>";
echo "# For Apache:\n";
echo "service apache2 restart\n\n";
echo "# For Nginx + PHP-FPM:\n";
echo "service php8.1-fpm restart\n";
echo "service nginx restart\n";
echo "</pre>";
