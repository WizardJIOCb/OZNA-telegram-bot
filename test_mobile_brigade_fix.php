<?php
// Test script to verify mobile brigade callback fix

require_once 'hook.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Mobile Brigade Callback Fix Test ===\n\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test cases
$testCases = [
    'category_Табель_Сварочные_yes' => 'Full format сварочные activity execution',
    'Табель_Сварочные' => 'Full format сварочные activity confirmation',
    'cat_ТабЭлОб_РабПоГр' => 'Short format electrical schedule activity confirmation',
    'cat_ТабЭлОб_РабПоГр_yes' => 'Short format electrical schedule activity execution'
];

echo "Testing isActivityExecution method:\n";
foreach ($testCases as $callbackData => $description) {
    // Use reflection to access the private method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('isActivityExecution');
    $method->setAccessible(true);
    $result = $method->invoke($tabelHandler, $callbackData);
    echo "  {$description}: " . ($result ? 'PASS' : 'FAIL') . "\n";
}

echo "\nTesting isActivityConfirmation method:\n";
foreach ($testCases as $callbackData => $description) {
    // Use reflection to access the private method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('isActivityConfirmation');
    $method->setAccessible(true);
    $result = $method->invoke($tabelHandler, $callbackData);
    echo "  {$description}: " . ($result ? 'PASS' : 'FAIL') . "\n";
}

echo "\nTest completed!\n";
?>