<?php
// Debug script to trace the actual execution flow in TabelHandler

require_once 'vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

// Mock classes for testing
class MockChat extends Chat {
    private $id;
    
    public function __construct($id) {
        $this->id = $id;
        parent::__construct(['id' => $id]);
    }
    
    public function getId() {
        return $this->id;
    }
}

class MockUser extends User {
    private $id;
    
    public function __construct($id) {
        $this->id = $id;
        parent::__construct(['id' => $id]);
    }
    
    public function getId() {
        return $this->id;
    }
}

class MockMessage extends Message {
    private $chat;
    
    public function __construct($chatId) {
        $this->chat = new MockChat($chatId);
        parent::__construct(['chat' => $this->chat]);
    }
    
    public function getChat() {
        return $this->chat;
    }
}

class MockCallbackQuery extends CallbackQuery {
    private $message;
    private $from;
    private $data;
    
    public function __construct($chatId, $userId, $callbackData) {
        $this->message = new MockMessage($chatId);
        $this->from = new MockUser($userId);
        $this->data = $callbackData;
        parent::__construct([
            'message' => $this->message,
            'from' => $this->from,
            'data' => $this->data
        ]);
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getFrom() {
        return $this->from;
    }
    
    public function getData() {
        return $this->data;
    }
}

// Mock Conversation class
class MockConversation extends Conversation {
    public $notes = [];
    
    public function __construct($userId, $chatId) {
        // Don't call parent constructor to avoid database connection
        $this->notes['tabel_data'] = '01.01.2023';
        $this->notes['project_code'] = 'TEST001';
    }
    
    public function update() {
        // Mock update method
    }
}

// Mock services
class MockDatabaseService extends DatabaseService {
    public function getUserData($userId) {
        return [
            'fio' => 'Test User',
            'functional_direction' => 'Мобильная бригада'
        ];
    }
    
    public function addUserTabel(int $userId, string $message): bool {
        echo "MockDatabaseService::addUserTabel called with userId: {$userId}\n";
        echo "Message: {$message}\n";
        return true; // Simulate success
    }
    
    protected function getConnection() {
        // Return a mock connection
        return new class {
            public function prepare($query) {
                return new class {
                    public function bind_param(...$params) {}
                    public function execute() { return true; }
                };
            }
        };
    }
}

class MockMessageService extends MessageService {
    public function sendToHistoryChatByFunctionalDirection(string $message, string $functionalDirection): \Longman\TelegramBot\Entities\ServerResponse {
        echo "MockMessageService::sendToHistoryChatByFunctionalDirection called\n";
        echo "Message: {$message}\n";
        echo "Functional Direction: {$functionalDirection}\n";
        
        // Return a mock ServerResponse
        return new \Longman\TelegramBot\Entities\ServerResponse([
            'ok' => true,
            'result' => [
                'message_id' => 12345,
                'chat' => ['id' => 123456789],
                'date' => time(),
                'text' => $message
            ]
        ]);
    }
    
    public function sendMessage($chatId, $text, $keyboard = null): \Longman\TelegramBot\Entities\ServerResponse {
        echo "MockMessageService::sendMessage called\n";
        echo "Chat ID: {$chatId}\n";
        echo "Text: {$text}\n";
        
        // Return a mock ServerResponse
        return new \Longman\TelegramBot\Entities\ServerResponse([
            'ok' => true,
            'result' => [
                'message_id' => 12345,
                'chat' => ['id' => $chatId],
                'date' => time(),
                'text' => $text
            ]
        ]);
    }
}

class MockTabelEntry extends TabelEntry {
    public function addEntry(int $userId, string $fio, string $date, string $projectCode, string $activityType, string $chatId, string $idleReason = ''): bool {
        echo "MockTabelEntry::addEntry called\n";
        echo "userId: {$userId}\n";
        echo "fio: {$fio}\n";
        echo "date: {$date}\n";
        echo "projectCode: {$projectCode}\n";
        echo "activityType: {$activityType}\n";
        echo "chatId: {$chatId}\n";
        echo "idleReason: {$idleReason}\n";
        
        // Call parent method but with mocked services
        return parent::addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason);
    }
    
    public function createTabelMessage(string $fio, string $date, string $projectCode, string $activityType, string $idleReason = ''): string {
        echo "MockTabelEntry::createTabelMessage called\n";
        $message = parent::createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
        echo "Created message: {$message}\n";
        return $message;
    }
}

// Test the flow
echo "Testing TabelHandler flow for electrical equipment activities\n\n";

// Test data
$callbackData = 'cat_ТабЭлОб_РабПоГр_yes';
$chatId = 123456789;
$userId = 987654321;

echo "Testing with callbackData: {$callbackData}\n";
echo "Chat ID: {$chatId}\n";
echo "User ID: {$userId}\n\n";

// Create mock objects
$callbackQuery = new MockCallbackQuery($chatId, $userId, $callbackData);
$conversation = new MockConversation($userId, $chatId);

// Create mock services
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$tabelEntry = new MockTabelEntry($databaseService, $messageService);

// Test isActivityExecution method
echo "1. Testing isActivityExecution method:\n";
// This is a simplified version of the actual method logic
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $callbackData, $matches)) {
    $shortActivityKey = $matches[1];
    $shortToFullMap = [
        'РабПоГр' => 'РабПоГр_ЭлектрОб',
        'ФормИД' => 'ФормИД_ЭлектрОб',
        'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
        'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
        'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
        'ДопРаб' => 'ДопРаб_ЭлектрОб',
        'ПодтВех' => 'ПодтВех_ЭлектрОб',
        'Проч' => 'Прочее_ЭлектрОб'
    ];
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        $activityTypes = [
            'РабПоГр_ЭлектрОб',
            'ФормИД_ЭлектрОб',
            'ПодтВех_ЭлектрОб',
            'ИзмСхПд_ЭлектрОб',
            'МонтКбЛн_ЭлектрОб',
            'РаскКбЛн_ЭлектрОб',
            'Прочее_ЭлектрОб',
            'РабПоГр_Автом',
            'ФормИД_Автом',
            'ПодтВех_Автом',
            'ДопРаб_Автом',
            'ИзмСх_Автом',
            'МонтКб_Автом',
            'РаскКб_Автом',
            'МонтАСУ_Автом',
            'УдалПО_Автом',
            'Прочее_Автом'
        ];
        
        if (in_array($activityType, $activityTypes)) {
            echo "✅ isActivityExecution should return true\n\n";
            
            // Test handleActivityExecution flow
            echo "2. Testing handleActivityExecution flow:\n";
            
            // Extract activity type
            if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $callbackData, $matches)) {
                $shortActivityKey = $matches[1];
                if (isset($shortToFullMap[$shortActivityKey])) {
                    $activityType = $shortToFullMap[$shortActivityKey];
                    echo "✅ Extracted activityType: {$activityType}\n";
                    
                    // Get user data
                    $userData = $databaseService->getUserData($userId);
                    $fio = $userData['fio'] ?? 'Без ФИО';
                    $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
                    $projectCode = $conversation->notes['projectCode'] ?? 'Unknown';
                    $idleReason = $conversation->notes['idle_reason'] ?? '';
                    
                    echo "User FIO: {$fio}\n";
                    echo "Date: {$date}\n";
                    echo "Project Code: {$projectCode}\n";
                    
                    // Create and send tabel entry
                    echo "\n3. Calling tabelEntry->addEntry:\n";
                    $success = $tabelEntry->addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason);
                    
                    if ($success) {
                        echo "✅ addEntry returned success\n";
                        echo "\n🎉 The flow should work correctly!\n";
                        echo "If the report is not being sent, there might be an issue with the actual DatabaseService or MessageService implementations.\n";
                    } else {
                        echo "❌ addEntry returned failure\n";
                    }
                }
            }
        } else {
            echo "❌ Activity type not found in activityTypes array\n";
        }
    }
}
?>