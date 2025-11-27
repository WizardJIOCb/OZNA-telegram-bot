<?php
// Detailed test script to debug fire alarm callback processing

// Include the necessary classes
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Mock classes for testing
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

class MockCallbackQuery {
    public function getMessage() {
        return new MockMessage();
    }
    
    public function getFrom() {
        return new MockUser();
    }
    
    public function getData() {
        return 'category_Табель_ПожарнаяСигнализация';
    }
}

class MockMessage {
    public function getChat() {
        return new MockChat();
    }
}

class MockChat {
    public function getId() {
        return '123456789';
    }
}

class MockUser {
    public function getId() {
        return '987654321';
    }
    
    public function getFirstName() {
        return 'TestUser';
    }
}

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test canHandle method
$callbackData = 'category_Табель_ПожарнаяСигнализация';
$canHandle = $tabelHandler->canHandle($callbackData);
echo "canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

// Test handle method
$callbackQuery = new MockCallbackQuery();
$category = 'Табель_ПожарнаяСигнализация';
$conversation = new MockConversation();

echo "Testing handle method with category: {$category}\n";
// We won't actually call the handle method as it would try to send messages
// Instead, let's test the specific logic that should be triggered

?>