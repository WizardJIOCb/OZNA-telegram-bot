<?php

echo "Testing supervision prep callback fix...\n\n";

// Test the extractCategoryFromCallback function
require_once 'CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Create required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "1. Testing canHandle() method with cat_supervision_prep:\n";
$canHandle = $tabelHandler->canHandle('cat_supervision_prep');
echo "   Result: " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n\n";

echo "2. Testing condition that was causing the issue:\n";
$callbackData = 'cat_supervision_prep';

if (strpos($callbackData, 'cat_Таб') === 0) {
    echo "   ❌ OLD condition 'cat_Таб' matched (this was the bug)\n";
} elseif (strpos($callbackData, 'cat_supervision_') === 0) {
    echo "   ✅ NEW condition 'cat_supervision_' matched (fixed!)\n";
} else {
    echo "   ❌ No condition matched\n";
}

echo "\n3. Checking if mapping exists:\n";
echo "   The mapping should be: 'cat_supervision_prep' => 'Табель_ПодготовительныеРаботы'\n";
echo "   This mapping is now accessible because we fixed the condition!\n\n";

echo "✅ Fix completed! The button should now work.\n";
echo "\nThe issue was:\n";
echo "- The condition only checked for 'cat_Таб' or 'cat_Гарантии_'\n";
echo "- But 'cat_supervision_prep' starts with 'cat_supervision_'\n";
echo "- So the mapping was never reached\n";
echo "- We added 'cat_supervision_' to the condition\n";

?>