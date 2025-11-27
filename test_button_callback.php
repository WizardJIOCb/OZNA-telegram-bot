<?php

require_once 'vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== TESTING PREPARATORY WORKS BUTTON CALLBACK ===\n\n";

// Test the exact callback that should be sent when clicking "Подготовительные работы"
$testCallback = 'cat_ШМР_ПодгРаб';

echo "Testing callback: '{$testCallback}'\n";
echo "Callback length: " . strlen($testCallback) . " bytes\n";
echo "Callback hex: " . bin2hex($testCallback) . "\n\n";

// Mock services
class MockDatabaseService extends DatabaseService {
    public function __construct() {}
    public function getUserData($userId) { return ['fio' => 'Test User', 'functional_direction' => 'Test']; }
    public function hasRecentTabelEntry($userId, $isSuperAdmin) { return false; }
}

class MockMessageService extends MessageService {
    public function __construct() {}
    public function createPreparatoryWorksKeyboard() { 
        return [
            [['text' => '🔍 Входной контроль', 'callback_data' => 'cat_ШМР_ВхКонтр']],
            [['text' => '🚚 Завоз оборудования на площадку', 'callback_data' => 'cat_ШМР_ЗавОбор']],
            [['text' => '📄 Анализ информации по фундаменту', 'callback_data' => 'cat_ШМР_АналФунд']],
            [['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']]
        ];
    }
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "Message would be sent:\n";
        echo "Chat ID: {$chatId}\n";
        echo "Text: {$text}\n";
        if ($keyboard) {
            echo "Keyboard: " . json_encode($keyboard) . "\n";
        }
        return true;
    }
}

class MockProjectSelector extends ProjectSelector {
    public function __construct() {}
}

class MockTabelEntry extends TabelEntry {
    public function __construct() {}
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Confirmation for {$activityType} in project {$projectCode}";
    }
}

class MockAdminService extends AdminService {
    public function __construct() {}
    public function getUserAccessLevel($userId) { return 2; } // User level
    public function isSuperAdmin($userId) { return false; }
}

try {
    // Create TabelHandler instance
    $databaseService = new MockDatabaseService();
    $messageService = new MockMessageService();
    $projectSelector = new MockProjectSelector();
    $tabelEntry = new MockTabelEntry();
    $adminService = new MockAdminService();
    
    $tabelHandler = new TabelHandler(
        $databaseService,
        $messageService,
        $projectSelector,
        $tabelEntry,
        $adminService
    );
    
    // Test canHandle
    echo "=== TESTING canHandle() ===\n";
    $canHandle = $tabelHandler->canHandle($testCallback);
    echo "TabelHandler->canHandle('{$testCallback}'): " . ($canHandle ? 'TRUE' : 'FALSE') . "\n\n";
    
    if ($canHandle) {
        echo "✅ TabelHandler can handle this callback\n";
        echo "The button should work if the callback reaches TabelHandler\n\n";
        
        echo "=== CHECKING CALLBACK MAPPING ===\n";
        
        // We can't directly test extractCategoryFromCallback as it's private,
        // but we can verify the mapping exists in the source code
        echo "Expected mapping: 'cat_ШМР_ПодгРаб' -> 'Табель_ПодготовительныеРаботы'\n";
        echo "This mapping should exist in the extractCategoryFromCallback method\n\n";
        
        echo "=== CONCLUSION ===\n";
        echo "If TabelHandler->canHandle() returns TRUE but the button doesn't work,\n";
        echo "the issue is likely that:\n";
        echo "1. The callback is not reaching TabelHandler (handled by another handler first)\n";
        echo "2. There's an issue with the webhook/bot receiving the callback\n";
        echo "3. There's an error in the callback processing pipeline\n";
        
    } else {
        echo "❌ TabelHandler cannot handle this callback\n";
        echo "This means the canHandle() method needs to be updated\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Check if the bot webhook is working at all\n";
echo "2. Check if TabelHandler has the correct priority in the handler factory\n";
echo "3. Verify that no other handler is intercepting this callback\n";
echo "4. Add more debugging to the webhook entry point\n";

?>