<?php

// Test the callback logic for electrical equipment without database dependencies
echo "Testing callback logic for electrical equipment:\n\n";

// Simulate the callback data that should be handled
$callbackData = 'category_Табель_Электрооборудование';

echo "Input callback data: {$callbackData}\n";
echo "Length: " . strlen($callbackData) . "\n";
echo "Hex representation: " . bin2hex($callbackData) . "\n\n";

// Test the logic that was fixed in TabelHandler
$category = substr($callbackData, strlen('category_'));
echo "Extracted category: {$category}\n";
echo "Length: " . strlen($category) . "\n";
echo "Hex representation: " . bin2hex($category) . "\n\n";

// Test the special handling conditions
$condition1 = ($category === 'Табель_Электрооборудование');
echo "Category strict comparison result: " . ($condition1 ? 'true' : 'false') . "\n";

$condition2 = (strpos($category, 'Табель_') === 0 && substr($category, 8) === 'Электрооборудование');
echo "Substring position check result: " . ($condition2 ? 'true' : 'false') . "\n";

$condition3 = strpos($category, 'Электрооборудование') !== false;
echo "Contains электрооборудование check result: " . ($condition3 ? 'true' : 'false') . "\n";

$shouldHandle = $condition1 || $condition2 || $condition3;
echo "\nShould handle electrical equipment: " . ($shouldHandle ? 'YES' : 'NO') . "\n";

if ($shouldHandle) {
    echo "SUCCESS: The electrical equipment callback should be handled correctly!\n";
} else {
    echo "FAILURE: The electrical equipment callback would not be handled.\n";
}

echo "\n=== Additional Tests ===\n";

// Test different variations
$testCases = [
    'category_Табель_Электрооборудование',
    'category_Табель_Электрооборудование_Extra',
    'Табель_Электрооборудование'
];

foreach ($testCases as $testCase) {
    echo "\nTesting: {$testCase}\n";
    
    // Simulate CallbackHandlerFactory extractCategory logic
    if (strpos($testCase, 'category_') === 0) {
        $extracted = substr($testCase, strlen('category_'));
        echo "  Extracted category: {$extracted}\n";
        
        // Check if it should be handled as electrical equipment
        $isElectrical = ($extracted === 'Табель_Электрооборудование') || 
                       (strpos($extracted, 'Табель_Электрооборудование') === 0) ||
                       (strpos($extracted, 'Табель_') === 0 && substr($extracted, 8) === 'Электрооборудование') ||
                       (strpos($extracted, 'Электрооборудование') !== false);
        
        echo "  Should handle as electrical equipment: " . ($isElectrical ? 'YES' : 'NO') . "\n";
    } else {
        echo "  Not a category callback\n";
    }
}

// Тестовый скрипт для проверки логики обработки callback-ов

echo "=== Тест логики обработки callback-ов ===\n\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/FunctionalDirectionHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/HelpHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;

// Создаем сервисы
$messageService = new MessageService();
$databaseService = new DatabaseService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Создаем обработчики
$adminHandler = new AdminHandler($adminService, $messageService);
$accessLevelHandler = new AccessLevelHandler($adminService, $messageService);
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
$profileHandler = new ProfileHandler($messageService);
$functionalDirectionHandler = new FunctionalDirectionHandler($messageService);
$helpHandler = new HelpHandler($messageService);

// Создаем массив обработчиков в порядке приоритета (как в CallbackqueryCommand)
$handlers = [
    $adminHandler,
    $accessLevelHandler,
    $tabelHandler,
    $profileHandler,
    $functionalDirectionHandler,
    $helpHandler
];

// Тестовые callback-ы
$testCallbacks = [
    'adm_req_clear',
    'adm_req_view_123456789',
    'adm_req_approve_123456789',
    'adm_req_reject_123456789'
];

echo "Тест логики обработки callback-ов:\n";
foreach ($testCallbacks as $callback) {
    echo "\nТест callback: '{$callback}'\n";
    
    $handled = false;
    foreach ($handlers as $handler) {
        $handlerName = (new ReflectionClass($handler))->getShortName();
        
        if ($handler->canHandle($callback)) {
            echo "  Обработчик: {$handlerName} ✓ может обработать\n";
            $handled = true;
            
            // Для AdminHandler проверяем, какой метод будет вызван
            if ($handlerName === 'AdminHandler') {
                // Имитируем extractCategory из CallbackHandlerFactory
                $category = $callback;
                if (strpos($callback, 'adm_req_') === 0) {
                    // Для admin request callbacks, category остается такой же
                } elseif (strpos($callback, 'category_') === 0) {
                    $category = substr($callback, strlen('category_'));
                }
                
                echo "  Категория: '{$category}'\n";
                
                // Проверяем, какой метод routeAdminCallback будет вызван
                if ($category === 'adm_req_clear') {
                    echo "  Будет вызван метод: clearAllAccessRequests\n";
                } elseif (strpos($category, 'adm_req_view_') === 0) {
                    echo "  Будет вызван метод: showAccessRequestDetails\n";
                } elseif (strpos($category, 'adm_req_approve_') === 0) {
                    echo "  Будет вызван метод: approveAccessRequest\n";
                } elseif (strpos($category, 'adm_req_reject_') === 0) {
                    echo "  Будет вызван метод: rejectAccessRequest\n";
                }
            }
            
            break;
        } else {
            echo "  Обработчик: {$handlerName} ✗ не может обработать\n";
        }
    }
    
    if (!$handled) {
        echo "  Нет обработчика для этого callback\n";
    }
}

echo "\n=== Тест завершен ===\n";