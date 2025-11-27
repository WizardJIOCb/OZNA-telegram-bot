<?php
/**
 * Final debug script for electrical equipment button issue
 * This script simulates the exact callback flow to identify where the issue occurs
 */

require_once __DIR__ . '/vendor/autoload.php';

// Include necessary classes
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
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

// Create mock objects
function createMockCallbackQuery($callbackData) {
    // Create mock user
    $user = new User([
        'id' => 123456789,
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser'
    ]);
    
    // Create mock chat
    $chat = new Chat([
        'id' => 987654321,
        'type' => 'private',
        'first_name' => 'Test',
        'last_name' => 'User'
    ]);
    
    // Create mock message
    $message = new Message([
        'message_id' => 1,
        'from' => $user->getRawData(),
        'chat' => $chat->getRawData(),
        'date' => time(),
        'text' => 'Test message'
    ]);
    
    // Create mock callback query
    $callbackQuery = new CallbackQuery([
        'id' => 'test_callback_id',
        'from' => $user->getRawData(),
        'message' => $message->getRawData(),
        'chat_instance' => 'test_chat_instance',
        'data' => $callbackData
    ]);
    
    return $callbackQuery;
}

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test cases for electrical equipment callback data
$testCases = [
    'cat_ТабЭлОб', // Short format used in commissioning works menu
    'category_Табель_Электрооборудование', // Full format
    'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ', // Corrupted Cyrillic
    'Электрооборудование', // Just the category name
];

echo "=== ELECTRICAL EQUIPMENT BUTTON DEBUG TEST ===\n\n";

foreach ($testCases as $index => $callbackData) {
    echo "Test Case " . ($index + 1) . ": '{$callbackData}'\n";
    echo "Length: " . strlen($callbackData) . " bytes\n";
    echo "Hex: " . bin2hex($callbackData) . "\n";
    
    // Test TabelHandler canHandle method
    $canHandle = $tabelHandler->canHandle($callbackData);
    echo "TabelHandler->canHandle(): " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";
    
    echo str_repeat("-", 50) . "\n";
}

echo "\n=== TESTING CALLBACK HANDLER FACTORY ===\n\n";

// Create CallbackHandlerFactory and register TabelHandler
$handlerFactory = new CallbackHandlerFactory();
$handlerFactory->registerHandler($tabelHandler);

// Create mock conversation
$conversation = new Conversation(123456789, 987654321);

// Test the specific callback that should trigger the electrical equipment menu
$callbackData = 'cat_ТабЭлОб';
echo "Testing CallbackHandlerFactory with: '{$callbackData}'\n";

$callbackQuery = createMockCallbackQuery($callbackData);

// This would normally be called by the factory, but we'll call it directly to see what happens
$category = '';
if (strpos($callbackData, 'category_') === 0) {
    $category = substr($callbackData, strlen('category_'));
} elseif (strpos($callbackData, 'cat_Таб') === 0) {
    // Handle short category format for electrical equipment
    $shortFormats = [
        'cat_ТабЭлОб' => 'Табель_Электрооборудование',
        'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
        'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
        'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
        'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
        'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
        'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
        'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
        'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
        'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование'
    ];
    
    if (isset($shortFormats[$callbackData])) {
        $category = $shortFormats[$callbackData];
    }
}

echo "Extracted category: '{$category}'\n";

// Test if TabelHandler can handle this category
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler->canHandle('{$callbackData}'): " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";

if ($canHandle) {
    echo "Calling TabelHandler->handle()...\n";
    // In a real scenario, this would send a message with the electrical equipment keyboard
    echo "SUCCESS: TabelHandler would display the electrical equipment menu\n";
} else {
    echo "FAILURE: TabelHandler cannot handle this callback\n";
}

echo "\n=== COMMISSIONING WORKS KEYBOARD TEST ===\n\n";

// Test the commissioning works keyboard creation
$keyboard = $messageService->createCommissioningWorksKeyboard();
echo "Commissioning Works Keyboard:\n";
foreach ($keyboard as $rowIndex => $row) {
    echo "Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $buttonIndex => $button) {
        echo "  Button " . ($buttonIndex + 1) . ": '{$button['text']}' -> '{$button['callback_data']}'\n";
        if ($button['text'] === '⚡ Электрооборудование') {
            echo "  >>> FOUND ELECTRICAL EQUIPMENT BUTTON <<<\n";
            echo "  >>> Callback data: '{$button['callback_data']}'\n";
            echo "  >>> Length: " . strlen($button['callback_data']) . " bytes\n";
        }
    }
}

echo "\n=== TEST COMPLETE ===\n";
?>