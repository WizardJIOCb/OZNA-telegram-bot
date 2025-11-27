<?php
// Test script to verify the fix for electrical equipment button callback handling

require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Mock classes for testing
class MockDatabaseService {
    public function getUserData($userId) {
        return ['fio' => 'Test User', 'functional_direction' => 'Пусконаладка'];
    }
    
    public function hasRecentTabelEntry($userId, $isSuperAdmin) {
        return false;
    }
}

class MockProjectSelector {
    public function isDateSelection($category) {
        return false;
    }
    
    public function isProjectSelection($category) {
        return false;
    }
    
    public function extractDate($category) {
        return '01.01.2025';
    }
    
    public function extractProjectCode($category) {
        return 'TEST-001';
    }
}

class MockTabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Confirm activity: $activityType for project $projectCode";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        return true;
    }
    
    public function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
        return "Entry created for $fio on $date for project $projectCode: $activityType";
    }
}

class MockAdminService {
    public function getUserAccessLevel($userId) {
        return 3; // Supervisor level
    }
    
    public function isSuperAdmin($userId) {
        return false;
    }
}

class MockMessageService extends MessageService {
    public function sendMessage($chatId, $text, $keyboard = null, $parseMode = 'HTML') {
        echo "Sending message to chat $chatId:\n$text\n";
        if ($keyboard) {
            echo "With keyboard:\n";
            print_r($keyboard);
        }
        echo "\n---\n";
        return new class { public function isOk() { return true; } };
    }
    
    public function createElectricalEquipmentKeyboard() {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабЭлОб_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабЭлОб_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабЭлОб_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабЭлОб_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }
}

// Make the isActivityExecution method temporarily public for testing
// This is a hack to test the private method
$tabelHandlerReflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler');
$isActivityExecutionMethod = $tabelHandlerReflection->getMethod('isActivityExecution');
$isActivityExecutionMethod->setAccessible(true);

// Create mock services
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the isActivityExecution method
echo "Testing isActivityExecution method:\n";

// Test cases that should return false (menu callbacks)
$menuCallbacks = [
    'cat_ТабЭлОб',
    'cat_ТабАвтом',
    'cat_ТабПожСигн'
];

foreach ($menuCallbacks as $callback) {
    $result = $isActivityExecutionMethod->invoke($tabelHandler, $callback);
    echo "isActivityExecution('$callback'): " . ($result ? 'true' : 'false') . " (expected: false)\n";
}

// Test cases that should return true (activity execution callbacks)
$activityCallbacks = [
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабЭлОб_ФормИД_yes',
    'cat_ТабПНРаботы_yes'
];

foreach ($activityCallbacks as $callback) {
    $result = $isActivityExecutionMethod->invoke($tabelHandler, $callback);
    echo "isActivityExecution('$callback'): " . ($result ? 'true' : 'false') . " (expected: true)\n";
}

echo "\nTest completed!\n";
?>