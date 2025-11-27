<?php
/**
 * Test script for the new short callback format
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

echo "=== Testing Short Callback Format ===\n\n";

// Test the new short callback format
$callbackData = 'cat_ТабЭлОб';
echo "Testing new callback data: {$callbackData}\n";
echo "Callback data length: " . strlen($callbackData) . " bytes\n";
echo "Callback data hex: " . bin2hex($callbackData) . "\n\n";

try {
    echo "=== Step 1: Testing TabelHandler canHandle ===\n";
    
    // Create all required services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Test TabelHandler canHandle
    $canHandle = $tabelHandler->canHandle($callbackData);
    echo "TabelHandler canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n\n";
    
    echo "=== Step 2: Testing CallbackHandlerFactory extractCategory ===\n";
    
    // Create CallbackHandlerFactory
    $callbackHandlerFactory = new CallbackHandlerFactory();
    
    // Use reflection to test the extractCategory method
    $reflection = new ReflectionClass($callbackHandlerFactory);
    $method = $reflection->getMethod('extractCategory');
    $method->setAccessible(true);
    $category = $method->invokeArgs($callbackHandlerFactory, [$callbackData]);
    
    echo "Extracted category: {$category}\n\n";
    
    echo "=== Step 3: Testing MessageService Keyboard Creation ===\n";
    
    // Test that the MessageService can create the electrical equipment keyboard
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "MessageService created electrical equipment keyboard with " . count($keyboard) . " rows\n";
    echo "First row: " . json_encode($keyboard[0], JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "=== Final Summary ===\n";
    
    if ($canHandle && $category === 'Табель_Электрооборудование') {
        echo "✅ All tests passed!\n";
        echo "The new short callback format should work correctly.\n";
        echo "Callback data length: " . strlen($callbackData) . " bytes (well within 64-byte limit)\n";
    } else {
        echo "❌ Some tests failed!\n";
        if (!$canHandle) echo "   - TabelHandler cannot handle the callback\n";
        if ($category !== 'Табель_Электрооборудование') echo "   - Category extraction failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";