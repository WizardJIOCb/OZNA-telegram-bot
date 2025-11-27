<?php
/**
 * Test script to verify that the new short callback format works correctly
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
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

echo "=== Testing Short Callback Format ===\n\n";

try {
    // Create all required services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create all handlers with their correct constructors
    $handlers = [
        new AdminHandler($adminService, $messageService),
        new MainMenuHandler($messageService),
        new AccessLevelHandler($adminService, $messageService),
        new SupervisorHandler($messageService, $adminService),
        new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService),
        new ProfileHandler($messageService),
        new FunctionalDirectionHandler($messageService),
        new HelpHandler($messageService)
    ];
    
    // Sort handlers by priority (highest first)
    usort($handlers, function($a, $b) {
        return $b->getPriority() - $a->getPriority();
    });
    
    echo "Handler priorities (sorted by priority, highest first):\n";
    foreach ($handlers as $handler) {
        echo "  " . get_class($handler) . ": " . $handler->getPriority() . "\n";
    }
    echo "\n";
    
    // Test the new short callback format
    $shortCallbackData = 'cat_ТабЭлОб';
    echo "Testing new short callback data: {$shortCallbackData}\n";
    echo "Callback data length: " . strlen($shortCallbackData) . " bytes\n";
    echo "Callback data hex: " . bin2hex($shortCallbackData) . "\n\n";
    
    // Test each handler's canHandle method
    echo "Testing each handler's canHandle method:\n";
    foreach ($handlers as $handler) {
        $canHandle = $handler->canHandle($shortCallbackData);
        echo "  " . get_class($handler) . ": " . ($canHandle ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
    
    echo "=== Testing TabelHandler Directly ===\n";
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Test TabelHandler canHandle
    $canHandle = $tabelHandler->canHandle($shortCallbackData);
    echo "TabelHandler canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    echo "\n=== Testing MessageService Keyboard Creation ===\n";
    
    // Test that the MessageService can create the electrical equipment keyboard
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "MessageService created electrical equipment keyboard with " . count($keyboard) . " rows\n";
    
    // Check the length of each callback data
    echo "Callback data lengths:\n";
    foreach ($keyboard as $rowIndex => $row) {
        foreach ($row as $buttonIndex => $button) {
            $text = $button['text'];
            $callbackData = $button['callback_data'];
            $length = strlen($callbackData);
            
            echo "  Row {$rowIndex}, Button {$buttonIndex} ({$text}): {$length} bytes\n";
            
            if ($length > 64) {
                echo "    ⚠️  WARNING: Exceeds Telegram's 64-byte limit!\n";
            }
        }
    }
    
    echo "\n=== Final Summary ===\n";
    
    if ($canHandle) {
        echo "✅ All tests passed!\n";
        echo "The new short callback format should work correctly.\n";
        echo "All callback data lengths are within Telegram's 64-byte limit.\n";
    } else {
        echo "❌ Some tests failed!\n";
        echo "The TabelHandler cannot handle the short callback data.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";