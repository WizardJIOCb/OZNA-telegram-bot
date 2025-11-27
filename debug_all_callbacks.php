<?php
// Simple debugging script to check all callbacks
header('Content-Type: text/plain; charset=utf-8');

echo "=== CALLBACK DEBUG CHECKER ===\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";

// Read the last 50 lines from error log
$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    echo "Reading last 50 lines from error log:\n";
    echo "=====================================\n";
    
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastLines = array_slice($lines, -50);
    
    foreach ($lastLines as $line) {
        // Look for callback-related entries
        if (strpos($line, 'DEEP CALLBACK') !== false || 
            strpos($line, 'callback_data') !== false ||
            strpos($line, 'ШМР') !== false ||
            strpos($line, 'РЁРњР') !== false) {
            echo $line . "\n";
        }
    }
    
    echo "\n=====================================\n";
    echo "Total log file size: " . filesize($logFile) . " bytes\n";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n";
} else {
    echo "ERROR: Log file not found at: $logFile\n";
}

echo "\n=== SUPERVISION KEYBOARD CHECK ===\n";
// Check if MessageService has the correct keyboard
$messageServiceFile = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    if (strpos($content, 'createSupervisionWorksKeyboard') !== false) {
        echo "✅ MessageService has createSupervisionWorksKeyboard method\n";
        
        // Check for supervision callback patterns
        $patterns = ['cat_ШМР_ПодгРаб', 'cat_ШМР_Back', 'cat_ШМР_МонтБлок'];
        foreach ($patterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                echo "✅ Found pattern: $pattern\n";
            } else {
                echo "❌ Missing pattern: $pattern\n";
            }
        }
    } else {
        echo "❌ MessageService missing createSupervisionWorksKeyboard method\n";
    }
} else {
    echo "❌ MessageService.php not found\n";
}

echo "\n=== END DEBUG ===\n";
?>