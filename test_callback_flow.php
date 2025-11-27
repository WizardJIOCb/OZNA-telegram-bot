<?php

// Test to debug the actual callback handling flow

// Include the necessary files
require_once 'custom_autoload.php';
require_once 'CustomCommands/Services/CallbackHandlerInterface.php';
require_once 'CustomCommands/Services/CallbackHandlerFactory.php';

// Create mock classes for testing
class MockDatabaseService {
    public function __construct() {}
}

class MockMessageService {
    public function __construct() {}
    
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "MockMessageService: sendMessage called\n";
        echo "  Chat ID: {$chatId}\n";
        echo "  Text: {$text}\n";
        if ($keyboard) {
            echo "  Keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        // Return a mock response
        return new class {
            public function isOk() { return true; }
        };
    }
}

class MockProjectSelector {
    public function __construct() {}
    
    public function isDateSelection($category) {
        return strpos($category, '_Сегодня') !== false || 
               strpos($category, '_Вчера') !== false || 
               strpos($category, '_Позавчера') !== false ||
               strpos($category, '_ДругаяДата') !== false;
    }
    
    public function isProjectSelection($category) {
        return strpos($category, '_ШифрПроекта_') !== false;
    }
    
    public function extractDate($category) {
        if (strpos($category, '_Сегодня') !== false) return date('d.m.Y');
        if (strpos($category, '_Вчера') !== false) return date('d.m.Y', strtotime('-1 day'));
        if (strpos($category, '_Позавчера') !== false) return date('d.m.Y', strtotime('-2 day'));
        return null;
    }
    
    public function extractProjectCode($category) {
        if (preg_match('/_ШифрПроекта_(.+)$/', $category, $matches)) {
            return $matches[1];
        }
        return null;
    }
}

class MockTabelEntry {
    public function __construct() {}
    
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Вы уверены, что хотите записать активность '{$activityType}' для проекта '{$projectCode}'?";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        echo "MockTabelEntry: addEntry called\n";
        echo "  User ID: {$userId}\n";
        echo "  FIO: {$fio}\n";
        echo "  Date: {$date}\n";
        echo "  Project Code: {$projectCode}\n";
        echo "  Activity Type: {$activityType}\n";
        echo "  Chat ID: {$chatId}\n";
        if ($idleReason) {
            echo "  Idle Reason: {$idleReason}\n";
        }
        return true;
    }
    
    public function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
        return "Запись в табель создана:\nФИО: {$fio}\nДата: {$date}\nПроект: {$projectCode}\nАктивность: {$activityType}";
    }
}

class MockAdminService {
    public function __construct() {}
    
    public function getUserAccessLevel($userId) {
        return 3; // Supervisor level
    }
}

// Test the callback handling flow
echo "=== Testing Callback Handling Flow ===\n\n";

// 1. Create the factory and handlers
$factory = new Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory();

// Create mock services
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

// Create handlers
$adminHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler($databaseService, $messageService, $adminService);
$accessLevelHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler($databaseService, $messageService);
$supervisorHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler($databaseService, $messageService);
$tabelHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "Handler priorities:\n";
echo "  AdminHandler: " . $adminHandler->getPriority() . "\n";
echo "  AccessLevelHandler: " . $accessLevelHandler->getPriority() . "\n";
echo "  SupervisorHandler: " . $supervisorHandler->getPriority() . "\n";
echo "  TabelHandler: " . $tabelHandler->getPriority() . "\n";

// Register handlers
$factory->registerHandler($adminHandler);
$factory->registerHandler($accessLevelHandler);
$factory->registerHandler($supervisorHandler);
$factory->registerHandler($tabelHandler);

// 2. Test the "No" button callback
$callbackData = 'category_Табель';
echo "\nTesting 'No' button callback: '{$callbackData}'\n";

// Simulate what happens in the factory
$category = substr($callbackData, strlen('category_'));
echo "Extracted category: '{$category}'\n";

// Check which handler can handle this callback
echo "\nChecking handler capabilities:\n";
echo "  AdminHandler canHandle: " . ($adminHandler->canHandle($callbackData) ? 'YES' : 'NO') . "\n";
echo "  AccessLevelHandler canHandle: " . ($accessLevelHandler->canHandle($callbackData) ? 'YES' : 'NO') . "\n";
echo "  SupervisorHandler canHandle: " . ($supervisorHandler->canHandle($callbackData) ? 'YES' : 'NO') . "\n";
echo "  TabelHandler canHandle: " . ($tabelHandler->canHandle($callbackData) ? 'YES' : 'NO') . "\n";

echo "\n=== Test Complete ===\n";