<?php
// Test to trigger the actual callback processing and see logs

echo "=== LIVE CALLBACK TEST ===\n\n";

// Check if the deep callback monitor log exists and what's in it
$logFile = __DIR__ . "/deep_callback_monitor.log";

if (file_exists($logFile)) {
    echo "Reading existing callback log:\n";
    echo "============================\n";
    $content = file_get_contents($logFile);
    echo $content;
    echo "\n============================\n\n";
} else {
    echo "No callback log exists yet. User needs to click the RK button to generate logs.\n\n";
}

// Check error log for recent RK-related entries
echo "Looking for recent error log entries...\n";
echo "======================================\n";

// Try to find PHP error log
$possibleLogPaths = [
    'C:\php\logs\php_errors.log',
    'C:\xampp\php\logs\php_error_log',
    'C:\wamp\logs\php_error.log',
    __DIR__ . '/error.log',
    __DIR__ . '/../../error.log'
];

$errorLogFound = false;
foreach ($possibleLogPaths as $logPath) {
    if (file_exists($logPath)) {
        echo "Found error log: $logPath\n";
        $errorLogFound = true;
        
        // Read last 50 lines
        $lines = file($logPath);
        $lastLines = array_slice($lines, -50);
        
        // Filter for RK-related entries
        $rkLines = array_filter($lastLines, function($line) {
            return stripos($line, 'УстрЗамРК') !== false || 
                   stripos($line, 'УЗ_РК') !== false ||
                   stripos($line, 'cat_Таб') !== false ||
                   stripos($line, 'CallbackqueryCommand') !== false ||
                   stripos($line, 'TabelHandler') !== false;
        });
        
        if (!empty($rkLines)) {
            echo "Recent RK-related entries:\n";
            foreach ($rkLines as $line) {
                echo "  " . trim($line) . "\n";
            }
        } else {
            echo "No recent RK-related entries found.\n";
        }
        break;
    }
}

if (!$errorLogFound) {
    echo "No PHP error log found in common locations.\n";
}

echo "\n=== INSTRUCTIONS FOR USER ===\n";
echo "1. Click the '🔧 Устранения замечаний РК' button in Telegram\n";
echo "2. Check if the deep_callback_monitor.log file appears\n";
echo "3. Run this test again to see what callback data was actually received\n";
echo "4. Look for any error messages in the log\n\n";

echo "The log will tell us:\n";
echo "- What callback data was actually sent\n";
echo "- Whether it passes the early exit filter\n";
echo "- Which handler (if any) processes it\n";
echo "- Why you get 'unknown command'\n";

?>