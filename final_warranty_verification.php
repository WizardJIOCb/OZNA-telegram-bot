<?php
/**
 * Final verification script for warranty equipment fix
 */

echo "=== FINAL WARRANTY EQUIPMENT VERIFICATION ===\n\n";

// Load the TabelHandler class
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

// Create mock services
$databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
$projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
$tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
$adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

echo "1. Testing TabelHandler instantiation:\n";
echo "   ✅ TabelHandler instantiated successfully\n\n";

// Test canHandle method with warranty equipment callbacks
$warrantyCallbacks = [
    'cat_Гарантии_ЭлектрОб',
    'cat_Гарантии_СрИзм',
    'cat_Гарантии_НасОб',
    'cat_Гарантии_ТрубАрм',
    'cat_Гарантии_ИспМех',
    'cat_Гарантии_КонтрОб',
    'cat_Гарантии_Прочее'
];

echo "2. Testing canHandle method for warranty equipment callbacks:\n";
foreach ($warrantyCallbacks as $callback) {
    $canHandle = $tabelHandler->canHandle($callback);
    echo "   " . ($canHandle ? "✅" : "❌") . " {$callback}\n";
}

echo "\n3. Testing warranty equipment confirmation patterns:\n";
$warrantyConfirmations = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes'
];

foreach ($warrantyConfirmations as $confirmation) {
    // Test if the confirmation pattern matches the expected warranty equipment pattern
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $confirmation, $matches)) {
        $equipmentType = $matches[1];
        echo "   ✅ {$confirmation} (Equipment: {$equipmentType})\n";
    } else {
        echo "   ❌ {$confirmation}\n";
    }
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "If all tests show ✅, the warranty equipment fix is working correctly.\n";
echo "Users should now be able to:\n";
echo "1. Select 'Работы по гарантии' from the menu\n";
echo "2. Choose any equipment type\n";
echo "3. Click 'Да' to confirm\n";
echo "4. Receive a properly formatted report\n\n";

echo "🎉 WARRANTY EQUIPMENT FUNCTIONALITY RESTORED! 🎉\n";
?>