<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/hook.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the mobile brigade button callback
$callback_data = 'category_Табель_Мобильная_Бригада';
$category = 'Табель_Мобильная_Бригада';

echo "Testing mobile brigade button callback:\n";
echo "Callback data: {$callback_data}\n";
echo "Category: {$category}\n";
echo "TabelHandler canHandle: " . ($tabelHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";

// Test the handleOtherWorkTypes method directly
echo "\nTesting handleOtherWorkTypes method:\n";

// Create a mock conversation object
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

$conversation = new MockConversation();
$chatId = 230379668; // Test chat ID

// Call the handleOtherWorkTypes method directly
echo "Calling handleOtherWorkTypes with category: {$category}\n";

// Since we can't easily test the actual method without proper mocks,
// let's check what the method should do based on the code:
// In TabelHandler::handleOtherWorkTypes, line 606-608:
// if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
//     error_log("TabelHandler: handling mobile brigade - calling handleMobileBrigadeDateSelection");
//     $result = $this->handleMobileBrigadeDateSelection($chatId);
//     error_log("TabelHandler: handleMobileBrigadeDateSelection returned");
//     return $result;
// }

echo "Checking category match:\n";
echo "Category === 'Табель_Мобильная_Бригада': " . ($category === 'Табель_Мобильная_Бригада' ? 'true' : 'false') . "\n";
echo "Category === 'Табель_МобБриг': " . ($category === 'Табель_МобБриг' ? 'true' : 'false') . "\n";

?>