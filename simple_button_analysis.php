<?php

echo "=== SIMPLE BUTTON ANALYSIS ===\n\n";

// Check the main callback handler
echo "🔍 CHECKING CALLBACK ROUTING\n";
echo "============================\n";

$callbackquery_file = 'c:\ozna_bot\CustomCommands\CallbackqueryCommand.php';
if (file_exists($callbackquery_file)) {
    echo "✅ CallbackqueryCommand.php exists\n";
    
    // Check last 10 lines of log for recent errors
    $log_lines = [];
    $log_file = 'c:\ozna_bot\telegram_bot_error.log';
    if (file_exists($log_file)) {
        $handle = fopen($log_file, 'r');
        if ($handle) {
            // Get file size and read last 2KB
            fseek($handle, -2048, SEEK_END);
            $content = fread($handle, 2048);
            fclose($handle);
            
            $lines = explode("\n", $content);
            $recent_lines = array_slice($lines, -10);
            
            echo "\nRecent errors:\n";
            foreach ($recent_lines as $line) {
                if (strpos($line, 'callback') !== false || strpos($line, 'category') !== false) {
                    echo "  " . substr($line, 0, 100) . "...\n";
                }
            }
        }
    }
} else {
    echo "❌ CallbackqueryCommand.php not found\n";
}

echo "\n🔧 MAIN THEORY\n";
echo "==============\n";
echo "Main menu buttons work because:\n";
echo "1. They use 'category_' prefix\n";
echo "2. No Cyrillic characters in callback data\n";
echo "3. Direct routing without encoding issues\n\n";

echo "Submenu buttons fail because:\n";
echo "1. They use 'cat_' prefix with Cyrillic\n";
echo "2. Telegram corrupts Cyrillic encoding\n";
echo "3. No handler found for corrupted text\n\n";

echo "📋 NEXT STEPS\n";
echo "=============\n";
echo "1. Check if CallbackqueryCommand has encoding fixes\n";
echo "2. Add encoding pattern to TabelHandler\n";
echo "3. Test the fix\n";

?>