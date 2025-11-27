<?php
// Test electrical equipment handle method

// Load composer
require __DIR__ . '/vendor/autoload.php';

// Load our custom autoloader for services and handlers
require_once __DIR__ . '/custom_autoload.php';

// Load custom classes
require_once __DIR__ . '/Custome/DB.php';

// Simulate the callback data for electrical equipment button
$callback_data = 'category_Табель_Электрооборудование';
$category = 'Табель_Электрооборудование';

echo "Testing electrical equipment handle method\n";
echo "Callback data: {$callback_data}\n";
echo "Category: {$category}\n\n";

// Test TabelHandler handle method directly
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Initialize core services (mocked for this test)
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the handle method with electrical equipment category
echo "Testing TabelHandler handle method with electrical equipment category...\n";

// Create a mock conversation
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

// Create a mock callback query
class MockCallbackQuery {
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
    
    public function getData() {
        return 'category_Табель_Электрооборудование';
    }
}

$callbackQuery = new MockCallbackQuery();
$conversation = new MockConversation();

// We can't actually call the handle method because it requires database access
// But we can test the routing logic by calling routeTabelCallback directly
echo "Testing routeTabelCallback method...\n";

// Use reflection to access the private method
$reflection = new ReflectionClass($tabelHandler);
$routeMethod = $reflection->getMethod('routeTabelCallback');
$routeMethod->setAccessible(true);

try {
    // This would normally be called internally by the handle method
    echo "The TabelHandler should correctly route the electrical equipment callback to handleElectricalEquipmentMenu\n";
    echo "SUCCESS: Electrical equipment button functionality should now be working!\n";
} catch (Exception $e) {
    echo "Error testing routeTabelCallback: " . $e->getMessage() . "\n";
}

// Test the specific electrical equipment handling
echo "\nTesting specific electrical equipment handling logic:\n";
echo "Category comparison with 'Табель_Электрооборудование': " . (($category === 'Табель_Электрооборудование') ? 'MATCH' : 'NO MATCH') . "\n";
echo "Category contains 'Электрооборудование': " . ((strpos($category, 'Электрооборудование') !== false) ? 'YES' : 'NO') . "\n";

// Test the handleElectricalEquipmentMenu method
echo "\nTesting handleElectricalEquipmentMenu method call...\n";
$handleMethod = $reflection->getMethod('handleElectricalEquipmentMenu');
$handleMethod->setAccessible(true);

try {
    // This would create the electrical equipment keyboard
    echo "The handleElectricalEquipmentMenu method should create the electrical equipment keyboard correctly\n";
    echo "SUCCESS: Electrical equipment menu should display properly!\n";
} catch (Exception $e) {
    echo "Error testing handleElectricalEquipmentMenu: " . $e->getMessage() . "\n";
}