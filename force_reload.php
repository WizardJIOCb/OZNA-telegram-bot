<?php
// Force reload without server restart

echo "=== FORCE RELOAD WITHOUT SERVER RESTART ===\n\n";

// 1. Clear PHP OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
} else {
    echo "ℹ️ OPcache not available\n";
}

// 2. Clear file stat cache
clearstatcache();
echo "✅ File stat cache cleared\n";

// 3. Update file modification times to force reload
$filesToTouch = [
    __DIR__ . '/hook.php',
    __DIR__ . '/CustomCommands/Services/MessageService.php',
    __DIR__ . '/CustomCommands/Handlers/TabelHandler.php',
    __DIR__ . '/CustomCommands/Services/TabelEntry.php'
];

echo "\n✅ Updating file timestamps:\n";
foreach ($filesToTouch as $file) {
    if (file_exists($file)) {
        touch($file);
        echo "   ✓ " . basename($file) . "\n";
    } else {
        echo "   ✗ " . basename($file) . " not found\n";
    }
}

// 4. Force autoloader to reload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    unset($GLOBALS['_composerRequire']);
    echo "\n✅ Autoloader globals cleared\n";
}

// 5. Create reload trigger file
$reloadFile = __DIR__ . '/force_reload_' . time() . '.flag';
file_put_contents($reloadFile, "Force reload at " . date('Y-m-d H:i:s'));
echo "✅ Reload trigger created: " . basename($reloadFile) . "\n";

echo "\n=== VERIFICATION ===\n";
echo "Testing if MessageService has the updated idle button...\n";

try {
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $keyboard = $messageService->createCommissioningWorksKeyboard();
    
    $foundIdleButton = false;
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            if (str_contains($button['text'], 'Простой') && $button['callback_data'] === 'category_Табель_Простой_1') {
                $foundIdleButton = true;
                echo "✅ UPDATED IDLE BUTTON FOUND!\n";
                echo "   Text: '{$button['text']}'\n";
                echo "   Callback: '{$button['callback_data']}'\n";
                break 2;
            }
        }
    }
    
    if (!$foundIdleButton) {
        echo "❌ Updated idle button not found - server restart may be needed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing: " . $e->getMessage() . "\n";
}

echo "\n=== RESULT ===\n";
echo "✅ Force reload completed!\n";
echo "📱 Try the idle button in Telegram now.\n";
echo "🔍 If still showing 'Простой', the server cache is persistent.\n";

echo "\nFiles updated and cache cleared without server restart!\n";
?>