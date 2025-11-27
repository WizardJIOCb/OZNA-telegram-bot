<?php

// Test script to verify the fix for electrical equipment activities
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Mock classes for testing
class MockCallbackQuery {
    public function getMessage() {
        return new MockMessage();
    }
    
    public function getData() {
        return 'cat_ТабЭлОб_РабПоГр_yes';
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
    
    public function getFirstName() {
        return 'TestUser';
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

// Create instances of the required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create the TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test 1: Check if the handler can handle the callback data
$callbackData = 'cat_ТабЭлОб_РабПоГр_yes';
$canHandle = $tabelHandler->canHandle($callbackData);
echo "Test 1 - canHandle('{$callbackData}'): " . ($canHandle ? 'PASS' : 'FAIL') . "\n";

// Test 2: Check if isActivityExecution correctly identifies the callback
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('isActivityExecution');
$method->setAccessible(true);
$isActivityExecution = $method->invoke($tabelHandler, $callbackData);
echo "Test 2 - isActivityExecution('{$callbackData}'): " . ($isActivityExecution ? 'PASS' : 'FAIL') . "\n";

// Test 3: Check if the handler correctly processes the callback
$callbackQuery = new MockCallbackQuery();
$conversation = new MockConversation();
$conversation->notes['project_code'] = 'TEST001';
$conversation->notes['tabel_data'] = '01.01.2023';

// This would normally be called internally, but we're testing the flow
try {
    $result = $tabelHandler->handle($callbackQuery, 'cat_ТабЭлОб_РабПоГр_yes', $conversation);
    echo "Test 3 - handle execution: PASS\n";
} catch (Exception $e) {
    echo "Test 3 - handle execution: FAIL - " . $e->getMessage() . "\n";
}

// Test 4: Check if the handler correctly processes the callback in handleActivityExecution
try {
    $method = $reflection->getMethod('handleActivityExecution');
    $method->setAccessible(true);
    $result = $method->invoke($tabelHandler, $callbackQuery, $callbackData, $conversation);
    echo "Test 4 - handleActivityExecution: PASS\n";
} catch (Exception $e) {
    echo "Test 4 - handleActivityExecution: FAIL - " . $e->getMessage() . "\n";
}

echo "\nAll tests completed!\n";
?>