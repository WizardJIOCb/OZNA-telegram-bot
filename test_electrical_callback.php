<?php
// Test electrical equipment callback processing

// Load composer
require __DIR__ . '/vendor/autoload.php';

// Load our custom autoloader for services and handlers
require_once __DIR__ . '/custom_autoload.php';

// Load custom classes
require_once __DIR__ . '/Custome/DB.php';

// Simulate the callback data for electrical equipment button
$callback_data = 'category_Табель_Электрооборудование';

echo "Testing electrical equipment callback: {$callback_data}\n";
echo "Callback data length: " . strlen($callback_data) . " bytes\n";
echo "Callback data hex: " . bin2hex($callback_data) . "\n\n";

// Test the CallbackHandlerFactory processing
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;

// Initialize core services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Initialize handler factory
$handlerFactory = new CallbackHandlerFactory();

// Register handlers (order matters for priority)
$handlerFactory->registerHandler(
    new AdminHandler($adminService, $messageService)
);
$handlerFactory->registerHandler(
    new AccessLevelHandler($adminService, $messageService)
);
$handlerFactory->registerHandler(
    new SupervisorHandler($messageService, $adminService)
);
$handlerFactory->registerHandler(
    new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService)
);
$handlerFactory->registerHandler(
    new ProfileHandler($messageService)
);
$handlerFactory->registerHandler(
    new FunctionalDirectionHandler($messageService)
);
$handlerFactory->registerHandler(
    new HelpHandler($messageService)
);
$handlerFactory->registerHandler(
    new MainMenuHandler($messageService)
);

// Create a mock callback query
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
        };
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

// Create mock objects
$callbackQuery = new MockCallbackQuery($callback_data);
$conversation = new MockConversation();

echo "Processing callback with factory...\n";
$result = $handlerFactory->handleCallback($callbackQuery, $callback_data, $conversation);

echo "Callback processing result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'SUCCESS' : 'FAILED') : 'UNKNOWN') . "\n";

// Check if TabelHandler would handle this callback
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
$canHandle = $tabelHandler->canHandle($callback_data);
echo "TabelHandler canHandle result: " . ($canHandle ? 'YES' : 'NO') . "\n";

if ($canHandle) {
    echo "TabelHandler would handle this callback correctly!\n";
} else {
    echo "TabelHandler would NOT handle this callback!\n";
}