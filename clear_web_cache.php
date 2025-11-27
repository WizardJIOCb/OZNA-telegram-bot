<?php
/**
 * Web-accessible cache clearing script
 * Access via browser to clear opcache
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>PHP Cache Clearing Script</h1>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";

// Clear OPcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<p style='color: green;'><strong>✓ OPcache cleared successfully!</strong></p>";
        echo "<p>OPcache statistics:</p>";
        echo "<pre>";
        print_r(opcache_get_status());
        echo "</pre>";
    } else {
        echo "<p style='color: red;'><strong>✗ Failed to clear OPcache</strong></p>";
    }
} else {
    echo "<p style='color: gray;'>OPcache extension not installed</p>";
}

// Clear APCu
if (function_exists('apcu_clear_cache')) {
    if (apcu_clear_cache()) {
        echo "<p style='color: green;'><strong>✓ APCu cache cleared successfully!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>✗ Failed to clear APCu cache</strong></p>";
    }
} else {
    echo "<p style='color: gray;'>APCu not installed</p>";
}

// Show PHP info
echo "<h2>Other Cache Information:</h2>";
echo "<p>Realpath cache size: " . realpath_cache_size() . "</p>";

// File modification time check
$handlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectManagementHandler.php';
if (file_exists($handlerFile)) {
    echo "<h2>ProjectManagementHandler.php Status:</h2>";
    echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($handlerFile)) . "</p>";
    echo "<p>File size: " . filesize($handlerFile) . " bytes</p>";
    
    // Check if file contains the Objects button
    $content = file_get_contents($handlerFile);
    if (strpos($content, 'Объекты') !== false) {
        echo "<p style='color: green;'><strong>✓ File contains 'Объекты' button</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>✗ File does NOT contain 'Объекты' button</strong></p>";
    }
}

echo "<h2>PHP Information:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server API: " . php_sapi_name() . "\n";
echo "</pre>";

echo "<hr>";
echo "<p><strong>Action Required:</strong> If OPcache couldn't be cleared, you may need to restart your web server (Apache/Nginx/PHP-FPM).</p>";
echo "<p><a href='javascript:location.reload()'>Reload this page</a> | <a href='/'>Go to Homepage</a></p>";
