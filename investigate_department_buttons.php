<?php

echo "=== INVESTIGATING DEPARTMENT BUTTONS ===\n\n";

// Let's check what callbacks these specific buttons use
echo "🔍 CHECKING DEPARTMENT BUTTON CALLBACKS\n";
echo "=======================================\n";

// Look for department buttons in MessageService
$message_service_file = 'c:\ozna_bot\CustomCommands\Services\MessageService.php';
if (file_exists($message_service_file)) {
    $content = file_get_contents($message_service_file);
    
    echo "Looking for department button callbacks:\n";
    
    // Search for department-related buttons
    $patterns = [
        'ПНР' => 'Пусконаладка / ПНР',
        'ШМР' => 'Шеф-монтаж / ШМР', 
        'Пусконаладка' => 'Commissioning',
        'Шеф-монтаж' => 'Supervision',
        'МобБриг' => 'Mobile Brigade',
        'Мобильная' => 'Mobile'
    ];
    
    foreach ($patterns as $pattern => $description) {
        if (strpos($content, $pattern) !== false) {
            echo "✅ Found '{$pattern}' ({$description}) in MessageService\n";
            
            // Try to find the callback pattern
            preg_match_all('/callback_data[\'\"]\s*=>\s*[\'\"](.*?' . preg_quote($pattern) . '.*?)[\'\"]/u', $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $callback) {
                    echo "   Callback: '{$callback}'\n";
                }
            }
        } else {
            echo "❌ Pattern '{$pattern}' not found\n";
        }
    }
} else {
    echo "❌ MessageService.php not found\n";
}

echo "\n🔍 CHECKING RECENT CALLBACK LOGS\n";
echo "================================\n";

// Check the last few lines of logs to see what callbacks are being received
$log_file = 'c:\ozna_bot\telegram_bot_error.log';
if (file_exists($log_file)) {
    $handle = fopen($log_file, 'r');
    if ($handle) {
        // Read last 1KB to get recent entries
        fseek($handle, -1024, SEEK_END);
        $content = fread($handle, 1024);
        fclose($handle);
        
        $lines = explode("\n", $content);
        $recent_callbacks = [];
        
        foreach ($lines as $line) {
            if (strpos($line, 'callback_data:') !== false) {
                preg_match("/callback_data: '([^']+)'/", $line, $matches);
                if (!empty($matches[1])) {
                    $recent_callbacks[] = $matches[1];
                }
            }
        }
        
        if (!empty($recent_callbacks)) {
            echo "Recent callbacks received:\n";
            foreach (array_unique($recent_callbacks) as $callback) {
                echo "  - '{$callback}'\n";
            }
        } else {
            echo "No recent callbacks found in logs\n";
        }
    }
}

echo "\n🔧 ANALYZING THE ISSUE\n";
echo "======================\n";

echo "Possible reasons why department buttons don't work:\n";
echo "1. Button callbacks use different format than expected\n";
echo "2. CallbackqueryCommand filter still blocking some patterns\n";
echo "3. No handler registered for these specific callbacks\n";
echo "4. Encoding issues with button text\n";

echo "\n📋 RECOMMENDED CHECKS\n";
echo "=====================\n";
echo "1. Find exact callback_data used by department buttons\n";
echo "2. Check if these callbacks pass through CallbackqueryCommand filter\n";
echo "3. Verify if appropriate handlers are registered\n";
echo "4. Test button functionality step by step\n";

?>