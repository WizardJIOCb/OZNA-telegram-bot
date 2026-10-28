<?php
/**
 * Server diagnostic script
 * Upload this to http://api.ladyzenko.pro/tg/ozna/check.php
 * Then visit: http://api.ladyzenko.pro/tg/ozna/check.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Server Diagnostics ===\n\n";

// 1. Check file
$file = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($file)) {
    echo "1. File Info:\n";
    echo "   Size: " . filesize($file) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    echo "   MD5: " . md5_file($file) . "\n";
    $content = file_get_contents($file);
    $count = substr_count($content, '$result->free();');
    echo "   \$result->free() count: {$count}\n";
    if ($count < 10) {
        echo "   ⚠️ WARNING: File not updated! Expected ~15+ occurrences.\n";
    } else {
        echo "   ✅ OK: File appears updated\n";
    }
} else {
    echo "   ❌ File not found!\n";
}
echo "\n";

// 2. Check logs
$logFiles = [
    '/var/log/php/error.log',
    '/var/log/php-fpm/error.log', 
    '/var/log/nginx/error.log'
];
$logFound = false;
foreach ($logFiles as $log) {
    if (file_exists($log)) {
        echo "2. Last 30 log lines from {$log}:\n";
        $lines = array_slice(file($log), -30);
        foreach ($lines as $line) {
            echo "   " . trim($line) . "\n";
        }
        $logFound = true;
        break;
    }
}
if (!$logFound) {
    echo "2. No error logs found at standard locations\n";
}
echo "\n";

// 3. OPcache
echo "3. OPcache Status:\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status && isset($status['scripts'])) {
        $cached = false;
        foreach (array_keys($status['scripts']) as $key) {
            if (strpos($key, 'ProjectHandler.php') !== false) {
                echo "   ⚠️ ProjectHandler.php IS cached!\n";
                echo "   Cached file: {$key}\n";
                $cached = true;
                break;
            }
        }
        if (!$cached) {
            echo "   ℹ️ ProjectHandler.php NOT in cache\n";
        }
        echo "   OPcache enabled\n";
        
        if ($cached) {
            echo "\n   To clear cache, run on server:\n";
            echo "   sudo systemctl restart php-fpm\n";
        }
    } else {
        echo "   ℹ️ No scripts cached or OPcache disabled\n";
    }
} else {
    echo "   ℹ️ OPcache not available\n";
}
echo "\n";

// 4. Test DB
echo "4. Database Test:\n";
try {
    $conn = new mysqli('localhost', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
    if ($conn->connect_error) {
        echo "   ❌ Connection failed: " . $conn->connect_error . "\n";
    } else {
        echo "   ✅ Database connected\n";
        $stmt = $conn->prepare("SELECT code FROM project WHERE code = ? LIMIT 1");
        $code = 'ОИ846';
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "   ✅ Test query OK: Project {$row['code']}\n";
        } else {
            echo "   ⚠️ Project ОИ846 not found\n";
        }
        $result->free();
        $stmt->close();
        $conn->close();
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Done ===\n";
?>
