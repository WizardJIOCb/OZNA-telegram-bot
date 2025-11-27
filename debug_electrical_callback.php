<?php
// Comprehensive debug script for electrical equipment callback

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_electrical_callback.log');

// Set UTF-8 encoding
mb_internal_encoding('UTF-8');

echo "=== Electrical Equipment Callback Debug Script ===\n\n";

// Load composer
require __DIR__ . '/vendor/autoload.php';

// Load our custom autoloader for services and handlers
require_once __DIR__ . '/custom_autoload.php';

// Load custom classes
require_once __DIR__ . '/Custome/DB.php';

// Test callback data
$callback_data = 'category_Табель_Электрооборудование';

echo "Testing callback data: {$callback_data}\n";
echo "Length: " . strlen($callback_data) . " bytes\n";
echo "Hex: " . bin2hex($callback_data) . "\n\n";

// Test all handlers to see which one claims it can handle this callback
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;

// Initialize services
try {
    echo "Initializing services...\n";
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    echo "Services initialized successfully!\n\n";
} catch (Exception $e) {
    echo "ERROR initializing services: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Create all handlers
echo "Creating handlers...\n";
$handlers = [
    'AdminHandler' => new AdminHandler($adminService, $messageService),
    'AccessLevelHandler' => new AccessLevelHandler($adminService, $messageService),
    'SupervisorHandler' => new SupervisorHandler($messageService, $adminService),
    'TabelHandler' => new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService),
    'ProfileHandler' => new ProfileHandler($messageService),
    'FunctionalDirectionHandler' => new FunctionalDirectionHandler($messageService),
    'HelpHandler' => new HelpHandler($messageService),
    'MainMenuHandler' => new MainMenuHandler($messageService)
];
echo "Handlers created successfully!\n\n";

// Test each handler's canHandle method
echo "=== Testing canHandle methods ===\n";
foreach ($handlers as $name => $handler) {
    echo "Testing {$name}...\n";
    try {
        $canHandle = $handler->canHandle($callback_data);
        echo "  {$name} canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
        
        // Get priority
        $priority = $handler->getPriority();
        echo "  {$name} priority: {$priority}\n";
    } catch (Exception $e) {
        echo "  ERROR testing {$name}: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test CallbackHandlerFactory processing
echo "=== Testing CallbackHandlerFactory processing ===\n";
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

try {
    $factory = new CallbackHandlerFactory();
    
    // Register all handlers
    foreach ($handlers as $handler) {
        $factory->registerHandler($handler);
    }
    
    echo "Factory created and handlers registered!\n\n";
    
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
            };
        }
    }
    
    class MockConversation {
        public $notes = [];
        
        public function update() {
            // Mock implementation
        }
    }
    
    $callbackQuery = new MockCallbackQuery($callback_data);
    $conversation = new MockConversation();
    
    echo "Testing factory handleCallback method...\n";
    // We can't actually call this because of type constraints, but we can test the logic
    
} catch (Exception $e) {
    echo "ERROR testing CallbackHandlerFactory: " . $e->getMessage() . "\n\n";
}

// Test specific TabelHandler logic
echo "=== Testing TabelHandler specific logic ===\n";
$tabelHandler = $handlers['TabelHandler'];

// Test the specific checks in TabelHandler
echo "Testing TabelHandler internal logic:\n";

// Check 1: Special handling for electrical equipment - check this first
$isSpecialCase = str_starts_with($callback_data, 'category_Табель_Электрооборудование');
echo "str_starts_with 'category_Табель_Электрооборудование': " . ($isSpecialCase ? 'YES' : 'NO') . "\n";

// Check 2: Additional check for corrupted electrical equipment callback data
$containsElectro = strpos($callback_data, 'Электрооборудование') !== false;
echo "Contains 'Электрооборудование': " . ($containsElectro ? 'YES' : 'NO') . "\n";

// Check 3: Check for double-encoded electrical equipment callback data
$containsDoubleEncoded = strpos($callback_data, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false;
echo "Contains double-encoded 'Электрооборудование': " . ($containsDoubleEncoded ? 'YES' : 'NO') . "\n";

// Test the base check
$baseCheck = str_starts_with($callback_data, 'category_Табель');
echo "str_starts_with 'category_Табель': " . ($baseCheck ? 'YES' : 'NO') . "\n";

echo "\n";

// Test MessageService keyboard creation
echo "=== Testing MessageService keyboard creation ===\n";
try {
    $electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "Electrical equipment keyboard created successfully!\n";
    echo "Number of rows: " . count($electricalKeyboard) . "\n";
    
    foreach ($electricalKeyboard as $index => $row) {
        echo "Row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "ERROR creating electrical equipment keyboard: " . $e->getMessage() . "\n\n";
}

// Test database connection
echo "=== Testing database connection ===\n";
try {
    // Test a simple database query
    $testQuery = $databaseService->getUserAccessLevel(123456789);
    echo "Database query test successful!\n";
    echo "Test user access level: {$testQuery}\n\n";
} catch (Exception $e) {
    echo "ERROR with database connection: " . $e->getMessage() . "\n\n";
}

echo "=== Debug script completed ===\n";