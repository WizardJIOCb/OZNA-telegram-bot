<?php
// Test script to verify the fix for "Проектная деятельность" button

echo "TESTING FIX FOR 'ПРОЕКТНАЯ ДЕЯТЕЛЬНОСТЬ' BUTTON\n";
echo "===============================================\n\n";

// Include required files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

// Create mock services
class MockDatabaseService extends DatabaseService {
    public function __construct() {}
    
    public function getUserData($userId) {
        return ['fio' => 'Test User'];
    }
    
    public function getUserAccessLevel($userId) {
        return DatabaseService::ACCESS_LEVEL_USER;
    }
}

class MockMessageService extends MessageService {
    public function __construct() {}
    
    public function sendMessage($chatId, $text, $keyboard = null, $parseMode = 'HTML') {
        echo "SENDING MESSAGE:\n";
        echo "Chat ID: $chatId\n";
        echo "Text: $text\n";
        if ($keyboard) {
            echo "Keyboard:\n";
            foreach ($keyboard as $rowIndex => $row) {
                echo "  Row " . ($rowIndex + 1) . ":\n";
                foreach ($row as $buttonIndex => $button) {
                    echo "    Button " . ($buttonIndex + 1) . ": " . $button['text'] . " -> " . $button['callback_data'] . "\n";
                }
            }
        }
        echo "\n";
        // Return a mock ServerResponse
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true]);
    }
    
    public function createProjectSelectionKeyboard() {
        return [
            [
                ['text' => 'Проект 1', 'callback_data' => 'category_Табель_ШифрПроекта_Проект1']
            ]
        ];
    }
    
    public function createCommissioningWorksKeyboardWithMobileActivities() {
        return [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'category_Табель_Электрооборудование']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
            ]
        ];
    }
}

class MockProjectSelector extends ProjectSelector {
    public function extractDate($category) {
        // Return today's date for testing
        return date('d.m.Y');
    }
    
    public function extractProjectCode($category) {
        if (strpos($category, 'ШифрПроекта_') !== false) {
            return substr($category, strpos($category, 'ШифрПроекта_') + strlen('ШифрПроекта_'));
        }
        return 'TEST-PROJECT';
    }
}

class MockTabelEntry extends TabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        return "<b>$activityType</b>, отправить запись в табель в проект <b>$projectCode</b>?";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        echo "ADDING TABEL ENTRY:\n";
        echo "  User ID: $userId\n";
        echo "  FIO: $fio\n";
        echo "  Date: $date\n";
        echo "  Project: $projectCode\n";
        echo "  Activity: $activityType\n";
        echo "  Idle Reason: $idleReason\n\n";
        return true;
    }
    
    public function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
        return "✅ Запись в табель от <b>$fio</b>, в проект <b>$projectCode</b> о <b>$activityType</b> добавлена";
    }
}

class MockAdminService extends AdminService {
    public function __construct() {}
    
    public function getUserAccessLevel($userId) {
        return DatabaseService::ACCESS_LEVEL_USER;
    }
}

// Create mock objects for CallbackQuery
class MockCallbackQuery extends CallbackQuery {
    private $data;
    private $message;
    
    public function __construct($data) {
        $this->data = $data;
        // Create mock message with chat and user
        $mockChat = new Chat(['id' => 123456789]);
        $mockUser = new User(['id' => 987654321, 'first_name' => 'Test', 'username' => 'testuser']);
        $this->message = new Message([
            'message_id' => 1,
            'from' => $mockUser,
            'chat' => $mockChat,
            'date' => time(),
            'text' => 'test'
        ]);
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getFrom() {
        return $this->message->getFrom();
    }
}

// Test the fix
echo "1. Testing TabelHandler canHandle method:\n";
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test if TabelHandler can handle the project activity callback
$canHandle = $tabelHandler->canHandle('category_Табель_ПроектнаяДеятельность');
echo "Can handle 'category_Табель_ПроектнаяДеятельность': " . ($canHandle ? 'YES' : 'NO') . "\n\n";

echo "2. Testing handle method with project activity category:\n";
$callbackQuery = new MockCallbackQuery('category_Табель_ПроектнаяДеятельность');
$conversation = new Conversation(987654321, 123456789);

// This should trigger the handleProjectActivityDateSelection method
$result = $tabelHandler->handle($callbackQuery, 'Табель_ПроектнаяДеятельность', $conversation);
echo "Handle result: " . ($result->isOk() ? 'SUCCESS' : 'FAILURE') . "\n\n";

echo "3. Testing handleDateSelection with project activity:\n";
// Simulate date selection for project activity
$callbackQuery2 = new MockCallbackQuery('category_Табель_ПроектнаяДеятельность_Сегодня');
$result2 = $tabelHandler->handle($callbackQuery2, 'Табель_ПроектнаяДеятельность_Сегодня', $conversation);
echo "Handle date selection result: " . ($result2->isOk() ? 'SUCCESS' : 'FAILURE') . "\n\n";

echo "4. Testing handleProjectSelection with project activity flag:\n";
// Set the project activity flag in conversation
$conversation->notes['is_project_activity'] = true;
$callbackQuery3 = new MockCallbackQuery('category_Табель_ШифрПроекта_Проект1');
$result3 = $tabelHandler->handle($callbackQuery3, 'Табель_ШифрПроекта_Проект1', $conversation);
echo "Handle project selection result: " . ($result3->isOk() ? 'SUCCESS' : 'FAILURE') . "\n\n";

echo "5. Testing back button functionality:\n";
// Test the back button with project activity flag
$callbackQuery4 = new MockCallbackQuery('category_Табель_Пусконаладка_Back');
$result4 = $tabelHandler->handle($callbackQuery4, 'Табель_Пусконаладка_Back', $conversation);
echo "Handle back button result: " . ($result4->isOk() ? 'SUCCESS' : 'FAILURE') . "\n\n";

echo "✅ TEST COMPLETE\n";
echo "The 'Проектная деятельность' button should now work correctly!\n";
?>