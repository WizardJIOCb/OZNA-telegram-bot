<?php

// Test script to debug the "Мобильная бригада" button issue

// Simulate the callback data that the button sends
$callbackData = 'category_Табель_Мобильная_Бригада';

echo "Original callback data: " . $callbackData . "\n";
echo "Original callback data length: " . strlen($callbackData) . "\n";
echo "Original callback data hex: " . bin2hex($callbackData) . "\n";

// Simulate the extractCategory function
function extractCategory(string $callbackData): string
{
    echo "extractCategory called with callbackData: '{$callbackData}'\n";
    
    // Handle other categories
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "extractCategory extracted category: '{$category}' from callback data: '{$callbackData}'\n";
        return $category;
    }
    
    echo "extractCategory: No category found in callback data: '{$callbackData}'\n";
    return '';
}

// Extract the category
$category = extractCategory($callbackData);

echo "Extracted category: " . $category . "\n";
echo "Extracted category length: " . strlen($category) . "\n";
echo "Extracted category hex: " . bin2hex($category) . "\n";

// Simulate the condition in handleOtherWorkTypes
echo "\nChecking conditions:\n";
echo "Category === 'Табель_Мобильная_Бригада': " . ($category === 'Табель_Мобильная_Бригада' ? 'true' : 'false') . "\n";

// Check for encoding issues
$categoryUtf8 = mb_convert_encoding($category, 'UTF-8', 'auto');
echo "Category after UTF-8 conversion: " . $categoryUtf8 . "\n";
echo "Category after UTF-8 conversion === 'Табель_Мобильная_Бригада': " . ($categoryUtf8 === 'Табель_Мобильная_Бригада' ? 'true' : 'false') . "\n";

?>
<?php

// Debug script to test mobile brigade callback processing
echo "=== Debug Mobile Brigade Callback Processing ===\n\n";

// Include required files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Test TabelHandler directly
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test callback data that should be handled by mobile brigade button
$callbackData = 'category_Табель_Мобильная_Бригада_Дата';

echo "Testing callback data: {$callbackData}\n";

// Check if TabelHandler can handle this callback data
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

if ($canHandle) {
    echo "TabelHandler would handle this callback correctly\n";
} else {
    echo "TabelHandler cannot handle this callback - there may be an issue\n";
}

// Test the category extraction
echo "\n--- Category Extraction Test ---\n";
$factory = new CallbackHandlerFactory();
// Simulate the extractCategory method
$category = substr($callbackData, strlen('category_'));
echo "Extracted category: {$category}\n";

// Test routing in TabelHandler
echo "\n--- TabelHandler Route Test ---\n";
// Check if the category matches the mobile brigade condition
if ($category === 'Табель_Мобильная_Бригада_Дата') {
    echo "✓ Category matches mobile brigade condition in routeTabelCallback\n";
} else {
    echo "✗ Category does not match mobile brigade condition\n";
    echo "Expected: Табель_Мобильная_Бригада_Дата\n";
    echo "Actual: {$category}\n";
}

// Test in handleOtherWorkTypes method
echo "\n--- handleOtherWorkTypes Method Test ---\n";
if ($category === 'Табель_Мобильная_Бригада_Дата') {
    echo "✓ Category matches mobile brigade condition in handleOtherWorkTypes\n";
} else {
    echo "✗ Category does not match mobile brigade condition in handleOtherWorkTypes\n";
}

echo "\n=== Test Completed ===\n";