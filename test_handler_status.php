<?php
// Test current handler priorities and callback processing
require_once __DIR__ . '/custom_autoload.php';

echo "=== HANDLER PRIORITY AND CALLBACK PROCESSING TEST ===\n\n";

// Test callback that should work
$testCallbacks = [
    'category_Табель_Электрооборудование',
    'cat_ТабЭлОб',
    'category_Табель_Мобильная_Бригада',
    'category_Табель_Пусконаладка'
];

try {
    // Initialize services
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);

    // Create handlers
    $tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
        $databaseService, $messageService, $projectSelector, $tabelEntry, $adminService
    );
    
    $adminHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler(
        $adminService, $messageService
    );

    echo "1. HANDLER PRIORITIES:\n";
    echo "   TabelHandler priority: " . $tabelHandler->getPriority() . "\n";
    echo "   AdminHandler priority: " . $adminHandler->getPriority() . "\n\n";

    echo "2. CALLBACK TESTING:\n";
    foreach ($testCallbacks as $callback) {
        echo "   Testing: $callback\n";
        $tabelCanHandle = $tabelHandler->canHandle($callback);
        $adminCanHandle = $adminHandler->canHandle($callback);
        
        echo "     TabelHandler canHandle: " . ($tabelCanHandle ? 'YES' : 'NO') . "\n";
        echo "     AdminHandler canHandle: " . ($adminCanHandle ? 'YES' : 'NO') . "\n";
        
        if ($tabelCanHandle && !$adminCanHandle) {
            echo "     ✅ CORRECT: Only TabelHandler will handle this\n";
        } elseif ($tabelCanHandle && $adminCanHandle) {
            echo "     ⚠️  CONFLICT: Both handlers will try to handle this (priority matters)\n";
        } elseif (!$tabelCanHandle && !$adminCanHandle) {
            echo "     ❌ ERROR: No handler will handle this callback\n";
        } else {
            echo "     ❌ ERROR: Only AdminHandler will handle this (wrong handler)\n";
        }
        echo "\n";
    }

    echo "3. SYSTEM STATUS:\n";
    echo "   TabelHandler class loaded from: " . (new ReflectionClass($tabelHandler))->getFileName() . "\n";
    echo "   Current time: " . date('Y-m-d H:i:s') . "\n";
    echo "   PHP version: " . PHP_VERSION . "\n\n";

    echo "4. RECOMMENDATION:\n";
    if ($tabelHandler->getPriority() >= 30) {
        echo "   ✅ TabelHandler has high enough priority\n";
        echo "   📝 If buttons still don't work, RESTART THE SERVER:\n";
        echo "      - Stop web server and PHP-FPM processes\n";
        echo "      - Clear any opcode cache (if enabled)\n";
        echo "      - Restart web server and PHP-FPM\n";
    } else {
        echo "   ❌ TabelHandler priority is too low (should be 30+)\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>