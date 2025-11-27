<?php
// Test script to debug only the activity execution part for electrical equipment

require_once 'vendor/autoload.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

echo "DEBUGGING ACTIVITY EXECUTION FOR ELECTRICAL EQUIPMENT\n";
echo "==================================================\n\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Create mock callback query
class MockCallbackQuery extends CallbackQuery {
    private $data;
    private $message;
    private $from;
    
    public function __construct($data) {
        $this->data = $data;
        $this->message = new class extends Message {
            public function __construct() {
                parent::__construct([]);
            }
            
            public function getChat() {
                return new class extends Chat {
                    public function __construct() {
                        parent::__construct([]);
                    }
                    
                    public function getId() {
                        return 123456789;
                    }
                };
            }
        };
        
        $this->from = new class extends User {
            public function __construct() {
                parent::__construct([]);
            }
            
            public function getId() {
                return 987654321;
            }
            
            public function getFirstName() {
                return 'TestUser';
            }
        };
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

// Test the activity execution directly
echo "Testing handleActivityExecution method directly:\n\n";

$testCallbacks = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'Работы по графику',
    'cat_ТабЭлОб_ФормИД_yes' => 'Формирование ИД',
    'cat_ТабЭлОб_ИзмСхПд_yes' => 'Изменение схемы подключения',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'Прокладка кабеля',
    'cat_ТабЭлОб_РаскКбЛн_yes' => 'Подключение кабеля'
];

foreach ($testCallbacks as $callback => $description) {
    echo "Testing: {$description} ({$callback})\n";
    
    $callbackQuery = new MockCallbackQuery($callback);
    $conversation = new Conversation(987654321, 123456789);
    
    // Set up conversation notes
    $conversation->notes['project_code'] = 'TEST-001';
    $conversation->notes['tabel_data'] = '01.01.2025';
    $conversation->update();
    
    echo "Conversation notes:\n";
    print_r($conversation->notes);
    echo "\n";
    
    try {
        echo "Calling handleActivityExecution method...\n";
        
        // Use reflection to call the private method
        $reflection = new ReflectionClass($tabelHandler);
        $method = $reflection->getMethod('handleActivityExecution');
        $method->setAccessible(true);
        $result = $method->invoke($tabelHandler, $callbackQuery, $callback, $conversation);
        
        echo "Result: Method executed successfully\n\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

echo "Test completed.\n";
?>