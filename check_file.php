<?php
/**
 * Quick diagnostic - save as check_file.php and open in browser
 * URL: http://api.ladyzenko.pro/tg/ozna/check_file.php
 */
header('Content-Type: text/plain; charset=utf-8');

$remoteFile = '/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php';

echo "=== Remote File Check ===\n\n";

if (file_exists($remoteFile)) {
    $size = filesize($remoteFile);
    $mtime = filemtime($remoteFile);
    $md5 = md5_file($remoteFile);
    $content = file_get_contents($remoteFile);
    $freeCount = substr_count($content, '$result->free();');
    
    echo "File: ProjectHandler.php\n";
    echo "Size: {$size} bytes\n";
    echo "Modified: " . date('Y-m-d H:i:s', $mtime) . "\n";
    echo "MD5: {$md5}\n";
    echo "\$result->free() calls: {$freeCount}\n\n";
    
    if ($freeCount < 10) {
        echo "❌ FILE NOT UPDATED! Expected 15+ occurrences.\n";
        echo "Please upload the file via SFTP.\n\n";
    } else {
        echo "✅ File appears to be updated.\n\n";
    }
    
    // Check last few lines for our fixes
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -20);
    echo "Last 20 lines of file:\n";
    foreach ($lastLines as $i => $line) {
        echo sprintf("%4d: %s\n", count($lines) - 20 + $i + 1, $line);
    }
    
} else {
    echo "❌ File not found at: {$remoteFile}\n";
}

echo "\n=== Logs ===\n\n";

// Try to read logs
$logFiles = [
    '/var/log/nginx/error.log',
    '/var/log/php/error.log',
    '/var/log/php-fpm/error.log'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "Last 30 lines from {$logFile}:\n";
        echo str_repeat('-', 80) . "\n";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -30);
        foreach ($lastLines as $line) {
            echo trim($line) . "\n";
        }
        echo str_repeat('-', 80) . "\n\n";
        break;
    }
}

echo "\n=== OPcache ===\n\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status && isset($status['scripts'])) {
        $found = false;
        foreach (array_keys($status['scripts']) as $key) {
            if (strpos($key, 'ProjectHandler.php') !== false) {
                echo "⚠️ ProjectHandler.php IS CACHED!\n";
                echo "Path: {$key}\n";
                echo "\nTo clear cache, run on server:\n";
                echo "  sudo systemctl restart php-fpm\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "ℹ️ ProjectHandler.php not in cache yet\n";
        }
    } else {
        echo "OPcache enabled but no scripts info\n";
    }
} else {
    echo "OPcache not available\n";
}
?>
