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

// Test chat ID
$chatId = 230379668;

// Simulate the exact flow that should happen when mobile brigade button is pressed
echo "Simulating mobile brigade button press...\n";

// 1. The button sends callback_data: 'category_Табель_Мобильная_Бригада'
$callback_data = 'category_Табель_Мобильная_Бригада';

// 2. TabelHandler::canHandle should return true
$canHandle = $tabelHandler->canHandle($callback_data);
echo "TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

// 3. TabelHandler::handle is called with category 'Табель_Мобильная_Бригада'
$category = 'Табель_Мобильная_Бригада';

// 4. In handle method, it calls routeTabelCallback
// 5. In routeTabelCallback, it should call handleOtherWorkTypes
// 6. In handleOtherWorkTypes, it should match the category and call handleMobileBrigadeDateSelection

// Let's test handleOtherWorkTypes directly
echo "\nTesting handleOtherWorkTypes method...\n";

// Create a mock conversation
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

$conversation = new MockConversation();

// Call handleOtherWorkTypes with the mobile brigade category
// This should call handleMobileBrigadeDateSelection
echo "This is where the issue might be - in handleOtherWorkTypes or handleMobileBrigadeDateSelection\n";

// Let's check what handleMobileBrigadeDateSelection does:
// It calls $this->messageService->createDateSelectionKeyboard('category_Табель_Мобильная_Бригада')
// And then sends a message with the text '💼 <b>Выберите дату для Мобильной бригады</b>'

echo "\nTesting createDateSelectionKeyboard...\n";
$prefix = 'category_Табель_Мобильная_Бригада';
$dateKeyboard = $messageService->createDateSelectionKeyboard($prefix);

echo "Date selection keyboard created with " . count($dateKeyboard) . " rows:\n";
foreach ($dateKeyboard as $index => $row) {
    echo "Row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\nTesting message sending with date selection keyboard...\n";
$messageText = '💼 <b>Выберите дату для Мобильной бригады</b>';
echo "Message text: {$messageText}\n";
echo "This should be what the user sees when they press the mobile brigade button.\n";

?>