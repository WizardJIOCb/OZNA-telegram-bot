<?php
// Detailed debug script for electrical equipment button

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

echo "=== Detailed Electrical Equipment Debug ===\n\n";

// Test the specific callback data that's used in the commissioning works menu
$callbackData = 'cat_ТабЭлОб';

echo "Testing callback data: {$callbackData}\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Hex: " . bin2hex($callbackData) . "\n\n";

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

// Test canHandle method
echo "=== Testing canHandle method ===\n";
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler->canHandle('{$callbackData}'): " . ($canHandle ? 'YES' : 'NO') . "\n\n";

// Test extractCategoryFromCallback method
echo "=== Testing extractCategoryFromCallback method ===\n";
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('extractCategoryFromCallback');
$method->setAccessible(true);
$extractedCategory = $method->invoke($tabelHandler, $callbackData);
echo "extractCategoryFromCallback('{$callbackData}'): '{$extractedCategory}'\n\n";

// Test the handle method with a mock callback query
echo "=== Testing handle method ===\n";

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
            public function getFirstName() {
                return 'TestUser';
            }
        };
    }
}

$callbackQuery = new MockCallbackQuery($callbackData);
$conversation = new Conversation(123456789, 987654321);

// We'll manually call the handle method to see what happens
echo "Calling handle method with:\n";
echo "  callbackData: '{$callbackData}'\n";
echo "  category (from extractCategory): '{$extractedCategory}'\n\n";

// Let's see what the handle method would do with these parameters
echo "Expected behavior:\n";
if ($extractedCategory === 'Табель_Электрооборудование' || 
    $extractedCategory === 'cat_ТабЭлОб' || 
    $extractedCategory === 'cat_ТабЭлОб_Наз') {
    echo "  Should call handleElectricalEquipmentMenu()\n";
} else {
    echo "  Would not match electrical equipment condition\n";
}

echo "\n=== Testing MessageService keyboard creation ===\n";
try {
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "Electrical equipment keyboard created successfully!\n";
    echo "Number of rows: " . count($keyboard) . "\n";
    
    foreach ($keyboard as $index => $row) {
        echo "Row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
} catch (Exception $e) {
    echo "Error creating keyboard: " . $e->getMessage() . "\n";
}

echo "\n=== Debug completed ===\n";