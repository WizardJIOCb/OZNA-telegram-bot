<?php
// Test script to verify the fix for electrical equipment reporting issue

require_once 'vendor/autoload.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

// Mock services
class MockDatabaseService extends DatabaseService {
    public function __construct() {}
    
    public function getUserData(int $userId) {
        return [
            'fio' => 'Test User',
            'functional_direction' => 'Мобильная бригада'
        ];
    }
}

class MockMessageService extends MessageService {
    public function __construct() {}
    
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML') {
        echo "Message sent to chat $chatId: $text\n";
        if ($keyboard) {
            echo "Keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        // Return a mock ServerResponse
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true], 'test');
    }
    
    public function createConfirmationKeyboard(string $confirmCallbackData): array {
        return [
            [
                ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
                ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
            ]
        ];
    }
}

class MockProjectSelector extends ProjectSelector {
    public function __construct() {}
    
    public function isDateSelection(string $category): bool {
        return false;
    }
    
    public function isProjectSelection(string $category): bool {
        return false;
    }
}

class MockTabelEntry extends TabelEntry {
    public function __construct() {
        // Skip parent constructor
    }
    
    public function getConfirmationMessage(string $activityType, string $projectCode): string {
        return "Подтвердите запись в табель для активности: $activityType";
    }
    
    public function addEntry(int $userId, string $fio, string $date, string $projectCode, string $activityType, string $chatId, string $idleReason = ''): bool {
        echo "Entry added: User $userId, FIO $fio, Date $date, Project $projectCode, Activity $activityType, Chat $chatId, Idle Reason $idleReason\n";
        return true;
    }
    
    public function createTabelMessage(string $fio, string $date, string $projectCode, string $activityType, string $idleReason = ''): string {
        return "Запись в табель добавлена для пользователя $fio";
    }
}

class MockAdminService extends AdminService {
    public function __construct() {}
}

class TestCallbackQuery extends CallbackQuery {
    private $data;
    private $message;
    
    public function __construct(string $callbackData) {
        parent::__construct([]);
        $this->data = $callbackData;
        // Mock message with chat
        $this->message = new \stdClass();
        $this->message->chat = new \stdClass();
        $this->message->chat->id = 'test_chat_id';
        // Mock from with user
        $this->from = new \stdClass();
        $this->from->id = 12345;
        $this->from->first_name = 'Test';
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getFrom() {
        return $this->from;
    }
}

// Test the fix
echo "Testing electrical equipment reporting fix...\n\n";

// Create mock services
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Create mock conversation
$conversation = new Conversation(12345, 'test_chat_id');

// Test cases
$testCases = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'Работы по графику (Электрооборудование)',
    'cat_ТабЭлОб_ФормИД_yes' => 'Формирование ИД (Электрооборудование)'
];

foreach ($testCases as $callbackData => $expectedActivity) {
    echo "Testing callback: $callbackData\n";
    
    // Create callback query
    $callbackQuery = new TestCallbackQuery($callbackData);
    
    // Test if handler can handle this callback
    $canHandle = $tabelHandler->canHandle($callbackData);
    echo "Can handle: " . ($canHandle ? 'Yes' : 'No') . "\n";
    
    // Test handling the callback
    try {
        $response = $tabelHandler->handle($callbackQuery, '', $conversation);
        echo "Handler executed successfully\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Test completed.\n";