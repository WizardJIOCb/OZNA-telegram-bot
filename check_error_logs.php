<?php
// Script to check for TabelHandler error logs

echo "=== Checking TabelHandler Error Logs ===\n\n";

// Look for PHP error logs - common locations on Windows
$logFiles = [
    'C:\xampp\php\logs\php_error_log',
    'C:\php\logs\php_error_log',
    'C:\Windows\Temp\php_errors.log',
    __DIR__ . '\php_error_log',
    __DIR__ . '\error_log',
    getenv('TEMP') . '\php_errors.log'
];

$foundLogs = false;

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "Checking log file: $logFile\n";
        
        // Get the last 50 lines of the log file
        $lines = file($logFile);
        $lastLines = array_slice($lines, -50);
        
        $tabelHandlerLines = [];
        foreach ($lastLines as $line) {
            if (strpos($line, 'TabelHandler') !== false) {
                $tabelHandlerLines[] = $line;
            }
        }
        
        if (!empty($tabelHandlerLines)) {
            echo "Found TabelHandler entries:\n";
            foreach ($tabelHandlerLines as $line) {
                echo "  $line";
            }
            $foundLogs = true;
        } else {
            echo "No TabelHandler entries found in this log file.\n";
        }
        
        echo "\n";
    }
}

if (!$foundLogs) {
    echo "No error logs found or no TabelHandler entries in logs.\n";
    echo "Try checking your web server's error log location.\n";
}

echo "=== Log Check Complete ===\n";
?>