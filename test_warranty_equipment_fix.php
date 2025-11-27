<?php
// Test to verify the warranty equipment fix
require_once 'vendor/autoload.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

// Mock services
class MockDatabaseService extends DatabaseService {
    public function __construct() {}
    
    public function getUserData($userId) {
        return [
            'fio' => 'Test User',
            'functional_direction' => 'Пусконаладка'
        ];
    }
    
    public function hasRecentTabelEntry($userId, $isSuperAdmin = false) {
        return false;
    }
}

class MockMessageService extends MessageService {
    public function __construct() {}
    
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "sendMessage called:\n";
        echo "  Chat ID: $chatId\n";
        echo "  Text: $text\n";
        if ($keyboard) {
            echo "  Keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "\n";
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true], 'test');
    }
    
    public function createConfirmationKeyboard($callbackData) {
        return [['text' => 'Confirm', 'callback_data' => $callbackData]];
    }
}

class MockTabelEntry extends TabelEntry {
    public function __construct() {}
    
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Confirm $activityType for project $projectCode?";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        echo "addEntry called with:\n";
        echo "  userId: $userId\n";
        echo "  fio: $fio\n";
        echo "  date: $date\n";
        echo "  projectCode: $projectCode\n";
        echo "  activityType: $activityType\n";
        echo "  chatId: $chatId\n";
        echo "  idleReason: $idleReason\n";
        return true;
    }
}

class MockAdminService extends AdminService {
    public function __construct() {}
    
    public function getUserAccessLevel($userId) {
        return 3; // Admin level
    }
    
    public function isSuperAdmin($userId) {
        return false;
    }
}

class MockProjectSelector extends ProjectSelector {
    public function __construct() {}
}

// Create mock conversation
$conversation = new Conversation(123, 456, '/callback');
$conversation->notes['project_code'] = 'TestProject';
$conversation->notes['tabel_data'] = '01.01.2023';

// Create mock callback query for cat_Гарантии_ЭлектрОб
$mockChat = $this->createMock(Chat::class);
$mockChat->method('getId')->willReturn(456);

$mockMessage = $this->createMock(Message::class);
$mockMessage->method('getChat')->willReturn($mockChat);

$mockUser = $this->createMock(User::class);
$mockUser->method('getId')->willReturn(123);

$callbackQuery = new CallbackQuery([
    'id' => '1',
    'from' => ['id' => 123, 'is_bot' => false, 'first_name' => 'Test'],
    'message' => [
        'message_id' => 1,
        'date' => time(),
        'chat' => ['id' => 456, 'type' => 'private']
    ],
    'data' => 'cat_Гарантии_ЭлектрОб'
]);

// Create handler with mock services
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

$handler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "Testing warranty equipment callback handling...\n";
echo "Callback data: cat_Гарантии_ЭлектрОб\n";
echo "Expected: Should show confirmation for warranty work on electrical equipment\n\n";

// Test the handler
try {
    $result = $handler->handle($callbackQuery, 'cat_Гарантии_ЭлектрОб', $conversation);
    echo "Handler executed successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>