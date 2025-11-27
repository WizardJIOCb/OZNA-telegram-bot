<?php
// Test script to debug mobile brigade button functionality

require_once 'vendor/autoload.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Handlers/AdminHandler.php';
require_once 'CustomCommands/Services/CallbackHandlerFactory.php';

// Mock classes for testing
class MockDatabaseService {
    public function getUserAccessLevel($userId) {
        return 3; // Regular user with access
    }
}

class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "Message sent to chat $chatId: $text\n";
        if ($keyboard) {
            echo "Keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true]);
    }
    
    public function createWorkTypeKeyboard(): array {
        return [
            [
                ['text' => '💼 Мобильная бригада', 'callback_data' => 'category_Табель_Мобильная_Бригада']
            ]
        ];
    }
    
    public function createDateSelectionKeyboard($prefix = 'category_Табель'): array {
        return [
            [
                ['text' => 'Сегодня', 'callback_data' => $prefix . '_Сегодня']
            ]
        ];
    }
}

class MockProjectSelector {
    public function isDateSelection($category) {
        return strpos($category, 'Сегодня') !== false;
    }
    
    public function extractDate($category) {
        return '01.01.2025';
    }
    
    public function isProjectSelection($category) {
        return false;
    }
}

class MockTabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Confirm activity: $activityType for project: $projectCode";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        return true;
    }
    
    public function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
        return "Entry created for $fio on $date";
    }
    
    public function formatRecentEntryError($recentEntry) {
        return "Recent entry error";
    }
}

class MockAdminService {
    public function getUserAccessLevel($userId) {
        return 3; // Regular user with access
    }
    
    public function isSuperAdmin($userId) {
        return false;
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

// Test the handlers
echo "Testing mobile brigade button functionality...\n\n";

// Create mock services
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

// Create handlers
$tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

$adminHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler(
    $adminService,
    $messageService
);

// Test callback data
$callbackData = 'category_Табель_Мобильная_Бригада';

echo "Testing TabelHandler canHandle with callbackData: $callbackData\n";
$canHandleTabel = $tabelHandler->canHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandleTabel ? 'true' : 'false') . "\n";

echo "\nTesting AdminHandler canHandle with callbackData: $callbackData\n";
$canHandleAdmin = $adminHandler->canHandle($callbackData);
echo "AdminHandler canHandle result: " . ($canHandleAdmin ? 'true' : 'false') . "\n";

echo "\nHandler priorities:\n";
echo "TabelHandler priority: " . $tabelHandler->getPriority() . "\n";
echo "AdminHandler priority: " . $adminHandler->getPriority() . "\n";

// Test the routing
if ($canHandleTabel) {
    echo "\nTesting TabelHandler routing...\n";
    $mockCallbackQuery = new \stdClass();
    $mockCallbackQuery->message = new \stdClass();
    $mockCallbackQuery->message->chat = new \stdClass();
    $mockCallbackQuery->message->chat->id = 12345;
    $mockCallbackQuery->from = new \stdClass();
    $mockCallbackQuery->from->id = 67890;
    
    $conversation = new MockConversation();
    $category = 'Табель_Мобильная_Бригада';
    
    // This would normally call the actual handler method
    echo "Would route to handleOtherWorkTypes for category: $category\n";
}

echo "\nTest completed.\n";