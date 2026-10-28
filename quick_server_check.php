<?php
// Quick server check script - run this on the server
echo "=== Quick Server Check ===\n\n";

// 1. Check file
$file = '/var/www/api.ladyzenko.pro/tg/ozna/CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($file)) {
    echo "File: {$file}\n";
    echo "Size: " . filesize($file) . " bytes\n";
    echo "Modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    echo "MD5: " . md5_file($file) . "\n\n";
    
    // Check for our fixes
    $content = file_get_contents($file);
    if (strpos($content, '$result->free();') !== false) {
        $count = substr_count($content, '$result->free();');
        echo "✅ Found \$result->free() calls: {$count} occurrences\n\n";
    } else {
        echo "❌ NO \$result->free() calls found! File not updated.\n\n";
    }
} else {
    echo "❌ File not found!\n\n";
}

// 2. Check PHP error log
$logFile = '/var/log/php/error.log';
if (!file_exists($logFile)) {
    $logFile = '/var/log/php-fpm/error.log';
}
if (!file_exists($logFile)) {
    $logFile = '/var/log/nginx/error.log';
}

if (file_exists($logFile)) {
    echo "Last 30 lines from {$logFile}:\n";
    echo str_repeat("=", 80) . "\n";
    $lines = array_slice(file($logFile), -30);
    foreach ($lines as $line) {
        echo $line;
    }
    echo str_repeat("=", 80) . "\n\n";
} else {
    echo "PHP error log not found at standard locations\n\n";
}

// 3. Check OPcache
echo "OPcache Info:\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "OPcache enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "\n";
        echo "Cache full: " . ($status['cache_full'] ? 'Yes' : 'No') . "\n";
        if (isset($status['scripts'])) {
            $projectHandlerKey = null;
            foreach (array_keys($status['scripts']) as $key) {
                if (strpos($key, 'ProjectHandler.php') !== false) {
                    $projectHandlerKey = $key;
                    break;
                }
            }
            if ($projectHandlerKey) {
                echo "ProjectHandler.php IS cached!\n";
                echo "Cached file: {$projectHandlerKey}\n";
            } else {
                echo "ProjectHandler.php NOT in cache yet\n";
            }
        }
    } else {
        echo "OPcache status not available\n";
    }
} else {
    echo "OPcache extension not loaded\n";
}
echo "\n";

// 4. Test DB connection
echo "Database Connection Test:\n";
try {
    $conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
    if ($conn->connect_error) {
        echo "❌ Connection failed: " . $conn->connect_error . "\n";
    } else {
        echo "✅ Database connected successfully\n";
        
        // Test a query
        $stmt = $conn->prepare("SELECT code FROM project WHERE code = ? LIMIT 1");
        $code = 'ОИ846';
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "✅ Test query OK: Project {$row['code']} found\n";
        } else {
            echo "⚠️ Project ОИ846 not found\n";
        }
        $result->free();
        $stmt->close();
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== End of Check ===\n";
