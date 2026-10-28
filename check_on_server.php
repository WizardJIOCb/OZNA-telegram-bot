<?php
echo "=== Server Diagnostics ===\n\n";

// Check file
$file = '/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($file)) {
    echo "1. File Info:\n";
    echo "   Size: " . filesize($file) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    echo "   MD5: " . md5_file($file) . "\n";
    $content = file_get_contents($file);
    $count = substr_count($content, '$result->free();');
    echo "   \$result->free() count: {$count}\n";
    if ($count < 10) {
        echo "   WARNING: File not updated! Expected ~15+ occurrences.\n";
    } else {
        echo "   OK: File appears updated\n";
    }
} else {
    echo "   ❌ File not found!\n";
}
echo "\n";

// Check logs
$log = '/var/log/php/error.log';
if (!file_exists($log)) $log = '/var/log/php-fpm/error.log';
if (!file_exists($log)) $log = '/var/log/nginx/error.log';
if (file_exists($log)) {
    echo "2. Last 30 log lines from {$log}:\n";
    $lines = array_slice(file($log), -30);
    foreach ($lines as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "2. No error logs found\n";
}
echo "\n";

// OPcache
echo "3. OPcache Status:\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status && isset($status['scripts'])) {
        foreach (array_keys($status['scripts']) as $key) {
            if (strpos($key, 'ProjectHandler.php') !== false) {
                echo "   ProjectHandler.php IS cached!\n";
                break;
            }
        }
        echo "   OPcache enabled\n";
    } else {
        echo "   No scripts cached or OPcache disabled\n";
    }
} else {
    echo "   OPcache not available\n";
}
echo "\n";

echo "=== Done ===\n";
?>
