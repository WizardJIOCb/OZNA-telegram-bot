<?php
// Script to check recent error logs and webhook activity
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Recent Error Logs Check ===\n\n";

// Check different possible log locations
$logFiles = [
    'webhook_error.log',
    'webhook_input.log', 
    'error.log',
    'php_error.log',
    'telegram_bot.log'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "Found log file: {$logFile}\n";
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        $recentLines = array_slice($lines, -20); // Last 20 lines
        
        echo "Last 20 lines from {$logFile}:\n";
        echo str_repeat("-", 50) . "\n";
        foreach ($recentLines as $line) {
            if (!empty(trim($line))) {
                echo $line . "\n";
            }
        }
        echo str_repeat("-", 50) . "\n\n";
    }
}

// Also check PHP error log if it exists
$phpErrorLog = ini_get('error_log');
if ($phpErrorLog && file_exists($phpErrorLog)) {
    echo "Checking PHP error log: {$phpErrorLog}\n";
    $content = file_get_contents($phpErrorLog);
    $lines = explode("\n", $content);
    $recentLines = array_slice($lines, -10);
    
    echo "Last 10 lines from PHP error log:\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($recentLines as $line) {
        if (!empty(trim($line))) {
            echo $line . "\n";
        }
    }
    echo str_repeat("-", 50) . "\n\n";
}

echo "=== End of Log Check ===\n";
?>