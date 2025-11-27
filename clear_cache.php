<?php
/**
 * Clear PHP OPcache and display status
 */

echo "<h1>PHP Cache Status</h1>";

// Check if OPcache is enabled
if (function_exists('opcache_get_status')) {
    echo "<h2>OPcache Information:</h2>";
    
    $status = opcache_get_status();
    
    if ($status === false) {
        echo "<p style='color: orange;'>OPcache is installed but disabled</p>";
    } else {
        echo "<p style='color: green;'>OPcache is enabled</p>";
        echo "<pre>";
        echo "Memory usage: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "Free memory: " . round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "Hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "Misses: " . $status['opcache_statistics']['misses'] . "\n";
        echo "</pre>";
        
        // Try to reset OPcache
        echo "<h2>Clearing OPcache...</h2>";
        if (opcache_reset()) {
            echo "<p style='color: green;'>✓ OPcache cleared successfully!</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to clear OPcache (might need restart)</p>";
        }
    }
} else {
    echo "<p style='color: gray;'>OPcache extension not installed</p>";
}

// Check for other caching mechanisms
echo "<h2>Other Cache Information:</h2>";

if (function_exists('apcu_clear_cache')) {
    echo "<p>APCu detected - clearing... ";
    if (apcu_clear_cache()) {
        echo "✓ Cleared</p>";
    } else {
        echo "✗ Failed</p>";
    }
}

if (function_exists('apc_clear_cache')) {
    echo "<p>APC detected - clearing... ";
    apc_clear_cache();
    echo "✓ Cleared</p>";
}

echo "<h2>PHP Information:</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server API: " . PHP_SAPI . "\n";
echo "</pre>";

echo "<hr>";
echo "<p><strong>Action Required:</strong> If OPcache couldn't be cleared, you may need to restart your web server (Apache/Nginx/PHP-FPM).</p>";
