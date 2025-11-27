<?php
// Test script to verify the electrical equipment confirmation fix

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

echo "=== Testing Electrical Equipment Confirmation Fix ===\n\n";

// Initialize services
$messageService = new MessageService();
$databaseService = new DatabaseService();
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler(
    $databaseService,
    $messageService,
    new ProjectSelector(),
    new TabelEntry($databaseService, $messageService),
    $adminService
);

// Test case: Electrical equipment confirmation callback
$callbackData = 'cat_ТабЭлОб_РабПоГр_yes';

echo "Testing callback data: {$callbackData}\n";

// Create mock callback query
class MockCallbackQuery {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return new class {
            public function getChat() {
                return new class {
                    public function getId() {
                        return 123456789;
                    }
                };
            }
        };
    }
    
    public function getFrom() {
        return new class {
            public function getId() {
                return 987654321;
            }
            public function getFirstName() {
                return 'TestUser';
            }
        };
    }
}

// Create mock conversation
class MockConversation {
    public $notes = [
        'project_code' => 'TEST-001',
        'tabel_data' => '01.01.2023'
    ];
    
    public function update() {
        // Mock implementation
    }
}

$callbackQuery = new MockCallbackQuery($callbackData);
$conversation = new MockConversation();

echo "Checking if TabelHandler can handle the callback...\n";
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'YES' : 'NO') . "\n\n";

if ($canHandle) {
    echo "Testing TabelHandler handle method with electrical equipment confirmation callback...\n";
    
    // Enable error logging to see what happens
    error_log("=== STARTING ELECTRICAL EQUIPMENT CONFIRMATION TEST ===");
    
    try {
        // This should now correctly route to handleActivityExecution instead of showing "unknown command"
        $result = $tabelHandler->handle($callbackQuery, '', $conversation);
        
        error_log("=== ELECTRICAL EQUIPMENT CONFIRMATION TEST COMPLETED ===");
        
        if (is_object($result) && method_exists($result, 'isOk')) {
            echo "Handle result: " . ($result->isOk() ? 'SUCCESS' : 'FAILED') . "\n";
            if (!$result->isOk()) {
                echo "Error description: " . $result->getDescription() . "\n";
            }
        } else {
            echo "Handle result: " . (is_string($result) ? $result : gettype($result)) . "\n";
        }
    } catch (Exception $e) {
        echo "Exception occurred: " . $e->getMessage() . "\n";
        echo "Exception trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\nTest completed.\n";
} else {
    echo "TabelHandler cannot handle this callback. The fix may not be working correctly.\n";
}

echo "\n=== Test Finished ===\n";