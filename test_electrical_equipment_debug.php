<?php

// Debug test script to verify the electrical equipment button fix

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Mock classes for testing
class MockCallbackQuery {
    private $data;
    private $message;
    
    public function __construct($data) {
        $this->data = $data;
        $this->message = new MockMessage();
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getFrom() {
        return new MockUser();
    }
}

class MockMessage {
    public function getChat() {
        return new MockChat();
    }
}

class MockChat {
    public function getId() {
        return 123456789;
    }
}

class MockUser {
    public function getId() {
        return 987654321;
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

echo "Testing electrical equipment button functionality...\n\n";

// Test 1: Check if TabelHandler can handle the electrical equipment callback
echo "Test 1: TabelHandler canHandle method\n";
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
$callbackData = 'category_Табель_Электрооборудование';
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler canHandle '{$callbackData}': " . ($canHandle ? 'true' : 'false') . "\n";

// Test 2: Check if AdminHandler correctly rejects electrical equipment callback
echo "\nTest 2: AdminHandler canHandle method\n";
$adminHandler = new AdminHandler($adminService, $messageService);
$canHandleAdmin = $adminHandler->canHandle($callbackData);
echo "AdminHandler canHandle '{$callbackData}': " . ($canHandleAdmin ? 'true' : 'false') . "\n";

// Test 3: Check CallbackHandlerFactory category extraction
echo "\nTest 3: CallbackHandlerFactory category extraction\n";
$handlerFactory = new CallbackHandlerFactory();

// Use reflection to access private method
$reflection = new ReflectionClass($handlerFactory);
$method = $reflection->getMethod('extractCategory');
$method->setAccessible(true);
$category = $method->invokeArgs($handlerFactory, [$callbackData]);

echo "CallbackHandlerFactory extracted category from '{$callbackData}': {$category}\n";
echo "Expected category: Табель_Электрооборудование\n";
echo "Category extraction " . ($category === 'Табель_Электрооборудование' ? 'SUCCESS' : 'FAILED') . "\n";

// Test 4: Check MessageService keyboard creation
echo "\nTest 4: MessageService keyboard creation\n";
$keyboard = $messageService->createElectricalEquipmentKeyboard();
echo "Electrical equipment keyboard created with " . count($keyboard) . " rows\n";

// Check if the keyboard has the expected structure
$hasBackButton = false;
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            if ($button['text'] === '⬅️ Назад' && $button['callback_data'] === 'category_Табель_ПусконаладочныеРаботы') {
                $hasBackButton = true;
            }
        }
    }
}

echo "Keyboard has proper back button: " . ($hasBackButton ? 'true' : 'false') . "\n";

echo "\nTest completed.\n";