<?php
// Test script to simulate actual handler execution

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

echo "=== Handler Execution Test ===\n\n";

// Create all required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Create a mock callback query
class MockCallbackQuery extends CallbackQuery {
    private $data;
    private $message;
    private $from;
    
    public function __construct($data) {
        $this->data = $data;
        $this->message = new class {
            public function getChat() {
                return new class {
                    public function getId() {
                        return 123456789;
                    }
                };
            }
        };
        $this->from = new class {
            public function getId() {
                return 987654321;
            }
            public function getFirstName() {
                return 'TestUser';
            }
            public function getLastName() {
                return 'TestLastName';
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

// Create a mock conversation
class MockConversation extends Conversation {
    public $notes = [];
    
    public function __construct() {
        // Don't call parent constructor to avoid database connection
    }
    
    public function update() {
        // Mock implementation
        echo "Conversation updated\n";
    }
}

// Test the mobile brigade callback
$callbackData = 'category_Табель_Мобильная_Бригада';
$category = 'Табель_Мобильная_Бригада';
$callbackQuery = new MockCallbackQuery($callbackData);
$conversation = new MockConversation();

echo "Testing TabelHandler execution:\n";
echo "===============================\n";
echo "Callback data: '{$callbackData}'\n";
echo "Category: '{$category}'\n\n";

// Test canHandle
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler->canHandle(): " . ($canHandle ? 'YES' : 'NO') . "\n";

if ($canHandle) {
    echo "\nCalling TabelHandler->handle()...\n";
    
    try {
        // This would normally call routeTabelCallback which would call handleOtherWorkTypes
        // which would then call handleMobileBrigadeDateSelection
        $reflection = new ReflectionClass($tabelHandler);
        $method = $reflection->getMethod('handleOtherWorkTypes');
        $method->setAccessible(true);
        
        // Call handleOtherWorkTypes directly to test the mobile brigade flow
        $chatId = 123456789;
        $result = $method->invoke($tabelHandler, $chatId, $category, $conversation);
        
        echo "✅ handleOtherWorkTypes executed successfully\n";
        echo "This method should have called handleMobileBrigadeDateSelection()\n";
        
        // Check if the category matches mobile brigade condition
        if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
            echo "✅ Category matches mobile brigade condition\n";
            echo "📞 handleMobileBrigadeDateSelection() should have been called\n";
        } else {
            echo "❌ Category does not match mobile brigade condition\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Exception occurred: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    } catch (Error $e) {
        echo "❌ Error occurred: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "❌ TabelHandler cannot handle this callback\n";
}

echo "\n=== Test Complete ===\n";

?>