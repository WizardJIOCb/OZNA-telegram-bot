<?php

// Test script to verify electrical equipment menu creation and sending

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Mock classes for testing
class MockChat {
    public function getId() {
        return 123456789;
    }
}

class MockMessage {
    public function getChat() {
        return new MockChat();
    }
}

class MockCallbackQuery {
    public function getMessage() {
        return new MockMessage();
    }
    
    public function getData() {
        return 'category_Табель_Электрооборудование';
    }
    
    public function getFrom() {
        return new MockUser();
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

echo "Testing electrical equipment menu creation and sending...\n\n";

// Test 1: Create MessageService and generate keyboard
echo "Test 1: Creating electrical equipment keyboard\n";
$messageService = new MessageService();
$keyboard = $messageService->createElectricalEquipmentKeyboard();

echo "Keyboard created with " . count($keyboard) . " rows\n";
foreach ($keyboard as $index => $row) {
    echo "Row $index: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}

// Test 2: Check if keyboard has the expected structure
echo "\nTest 2: Verifying keyboard structure\n";
$hasElectricalButton = false;
$hasBackButton = false;

foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            if ($button['text'] === '⚡ Электрооборудование' && $button['callback_data'] === 'category_Табель_Электрооборудование') {
                $hasElectricalButton = true;
            }
            if ($button['text'] === '⬅️ Назад' && $button['callback_data'] === 'category_Табель_ПусконаладочныеРаботы') {
                $hasBackButton = true;
            }
        }
    }
}

echo "Has electrical equipment button in commissioning works menu: " . ($hasElectricalButton ? 'true' : 'false') . "\n";
echo "Has proper back button: " . ($hasBackButton ? 'true' : 'false') . "\n";

// Test 3: Check commissioning works keyboard
echo "\nTest 3: Checking commissioning works keyboard\n";
$commissioningKeyboard = $messageService->createCommissioningWorksKeyboard();
echo "Commissioning works keyboard created with " . count($commissioningKeyboard) . " rows\n";

$hasElectricalInCommissioning = false;
foreach ($commissioningKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            if ($button['text'] === '⚡ Электрооборудование' && $button['callback_data'] === 'category_Табель_Электрооборудование') {
                $hasElectricalInCommissioning = true;
            }
        }
    }
}

echo "Has electrical equipment button in commissioning works menu: " . ($hasElectricalInCommissioning ? 'true' : 'false') . "\n";

echo "\nTest completed.\n";