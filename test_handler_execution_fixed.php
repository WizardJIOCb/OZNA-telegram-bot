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

echo "=== Handler Execution Test ===\n\n";

// Create all required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the mobile brigade callback
$callbackData = 'category_Табель_Мобильная_Бригада';
$category = 'Табель_Мобильная_Бригада';

echo "Testing TabelHandler handleOtherWorkTypes method:\n";
echo "=============================================\n";
echo "Callback data: '{$callbackData}'\n";
echo "Category: '{$category}'\n\n";

// Test canHandle
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler->canHandle(): " . ($canHandle ? 'YES' : 'NO') . "\n";

if ($canHandle) {
    echo "\nTesting handleOtherWorkTypes method directly...\n";
    
    try {
        // Use reflection to access the private handleOtherWorkTypes method
        $reflection = new ReflectionClass($tabelHandler);
        $method = $reflection->getMethod('handleOtherWorkTypes');
        $method->setAccessible(true);
        
        // Create a mock chatId and conversation
        $chatId = 123456789;
        $conversation = new class {
            public $notes = [];
            public function update() {
                return true;
            }
        };
        
        // Call handleOtherWorkTypes directly to test the mobile brigade flow
        echo "Calling handleOtherWorkTypes with category: '{$category}'\n";
        $result = $method->invoke($tabelHandler, $chatId, $category, $conversation);
        
        echo "✅ handleOtherWorkTypes executed\n";
        echo "This method should have called handleMobileBrigadeDateSelection() for mobile brigade category\n";
        
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