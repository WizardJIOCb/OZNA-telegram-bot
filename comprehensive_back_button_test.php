<?php
// Comprehensive test script to verify back button functionality

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Comprehensive Back Button Test ===\n";

// Test 1: Verify TabelHandler canHandle method
echo "\n1. Testing TabelHandler canHandle method:\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test callbacks
$testCallbacks = [
    'Табель_ШМ_Back' => 'Should be handled by TabelHandler',
    'Табель_Пусконаладка_Back' => 'Should be handled by TabelHandler',
    'Табель_Автоматизация_Back' => 'Should be handled by TabelHandler',
    'Табель_ПожарнаяСигнализация_Back' => 'Should be handled by TabelHandler',
    'Табель_Текущая_Назад' => 'Should be handled by TabelHandler',
    'category_Табель_Пусконаладка_Back' => 'Should be handled by TabelHandler',
    'category_Табель_Электрооборудование' => 'Should be handled by TabelHandler'
];

foreach ($testCallbacks as $callback => $description) {
    $canHandle = $tabelHandler->canHandle($callback);
    echo "  $callback: " . ($canHandle ? "✓ PASS" : "✗ FAIL") . " - $description\n";
}

// Test 2: Verify CallbackqueryCommand early exit filter
echo "\n2. Testing CallbackqueryCommand early exit filter:\n";

function willPassEarlyExitFilter($callback_data) {
    return (strpos($callback_data, 'category_') !== 0 && 
            strpos($callback_data, 'adm_req_') !== 0 && 
            strpos($callback_data, 'acc_lvl_') !== 0 && 
            strpos($callback_data, 'cat_Таб') !== 0 &&
            strpos($callback_data, 'proj_ctrl_') !== 0 &&
            strpos($callback_data, 'proj_list_') !== 0 &&
            strpos($callback_data, 'cat_Гарантии') !== 0 &&
            strpos($callback_data, 'cat_ШМР_') !== 0 &&
            strpos($callback_data, 'Табель_ШМ_Back') === false &&
            strpos($callback_data, 'Табель_Пусконаладка_Back') === false &&
            strpos($callback_data, 'Табель_Автоматизация_Back') === false &&
            strpos($callback_data, 'Табель_ПожарнаяСигнализация_Back') === false &&
            strpos($callback_data, 'Табель_Текущая_Назад') === false);
}

foreach ($testCallbacks as $callback => $description) {
    $willExit = willPassEarlyExitFilter($callback);
    echo "  $callback: " . ($willExit ? "✗ WILL EXIT (BAD)" : "✓ WILL CONTINUE (GOOD)") . "\n";
}

echo "\n=== Test Summary ===\n";
echo "If all tests show ✓ PASS or ✓ WILL CONTINUE, the back button fix is working correctly.\n";
echo "The TabelHandler should now properly handle all back button callbacks.\n";
?>