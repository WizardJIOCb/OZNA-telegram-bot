<?php
// Test script to debug the complete flow for electrical equipment report submission

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

echo "DEBUGGING COMPLETE ELECTRICAL EQUIPMENT REPORT SUBMISSION FLOW\n";
echo "=============================================================\n\n";

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

// Test the complete flow for a specific electrical equipment activity
echo "Testing complete flow for 'Работы по графику' electrical equipment activity:\n\n";

// Step 1: User clicks 'Работы по графику' button (cat_ТабЭлОб_РабПоГр)
echo "1. User clicks 'Работы по графику' button (cat_ТабЭлОб_РабПоГр)\n";
$callbackQuery1 = new MockCallbackQuery('cat_ТабЭлОб_РабПоГр');
$conversation1 = new Conversation(987654321, 123456789);

// Set up conversation notes to simulate having selected a project and date
$conversation1->notes['project_code'] = 'TEST-001';
$conversation1->notes['tabel_data'] = '01.01.2025';
$conversation1->update();

echo "Conversation notes:\n";
print_r($conversation1->notes);
echo "\n";

try {
    echo "Calling handle method...\n";
    $result1 = $tabelHandler->handle($callbackQuery1, 'cat_ТабЭлОб_РабПоГр', $conversation1);
    echo "Result: Handler processed the callback\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Step 2: User clicks 'Да' to confirm (cat_ТабЭлОб_РабПоГр_yes)
echo "2. User clicks 'Да' to confirm (cat_ТабЭлОб_РабПоГр_yes)\n";
$callbackQuery2 = new MockCallbackQuery('cat_ТабЭлОб_РабПоГр_yes');
$conversation2 = new Conversation(987654321, 123456789);

// Set up conversation notes to simulate having selected a project and date
$conversation2->notes['project_code'] = 'TEST-001';
$conversation2->notes['tabel_data'] = '01.01.2025';
$conversation2->update();

echo "Conversation notes:\n";
print_r($conversation2->notes);
echo "\n";

try {
    echo "Calling handle method...\n";
    $result2 = $tabelHandler->handle($callbackQuery2, 'cat_ТабЭлОб_РабПоГр_yes', $conversation2);
    echo "Result: Handler processed the confirmation callback\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Let's also test the isActivityExecution method directly
echo "3. Testing isActivityExecution method directly:\n";
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('isActivityExecution');
$method->setAccessible(true);

$testCallbacks = [
    'cat_ТабЭлОб_РабПоГр',
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабЭлОб_ФормИД',
    'cat_ТабЭлОб_ФормИД_yes'
];

foreach ($testCallbacks as $callback) {
    $result = $method->invoke($tabelHandler, $callback);
    echo "isActivityExecution('{$callback}') = " . ($result ? 'true' : 'false') . "\n";
}

echo "\nTest completed.\n";
?>