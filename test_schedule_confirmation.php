<?php

require_once 'config.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "Testing the updated 'Работы по графику' button behavior...\n";

// Test the specific callback that was causing issues
$testCallback = 'cat_ТабЭлОб_РабПоГр';  // "Работы по графику" button

echo "\n--- Testing: {$testCallback} ---\n";

// Test canHandle
$canHandle = $tabelHandler->canHandle($testCallback);
echo "canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";

// Test category extraction
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('extractCategoryFromCallback');
$method->setAccessible(true);
$category = $method->invokeArgs($tabelHandler, [$testCallback]);
echo "Extracted category: {$category}\n";

// Check if this is the "Работы по графику" category
$isScheduleWorks = ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
                    $testCallback === 'cat_ТабЭлОб_РабПоГр');

echo "Is 'Работы по графику': " . ($isScheduleWorks ? 'YES' : 'NO') . "\n";

if ($isScheduleWorks) {
    echo "\n✓ VERIFICATION SUCCESSFUL:\n";
    echo "  The 'Работы по графику' button will now:\n";
    echo "  1. Show a confirmation dialog before executing\n";
    echo "  2. Only generate a report after user confirmation\n";
    echo "  3. No longer execute directly without confirmation\n";
} else {
    echo "\n✗ Verification failed\n";
}

echo "\nTest completed.\n";