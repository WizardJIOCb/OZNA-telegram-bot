<?php
// Test script to verify the electrical equipment confirmation routing fix

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "=== Testing Electrical Equipment Confirmation Routing Fix ===\n\n";

// Initialize services
$messageService = new MessageService();
$databaseService = new DatabaseService();
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler(
    $databaseService,
    $messageService,
    new ProjectSelector(),
    new TabelEntry($databaseService, $messageService),
    $adminService
);

// Test cases for electrical equipment confirmation callbacks
$testCases = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'Works by schedule',
    'cat_ТабЭлОб_ФормИД_yes' => 'ID formation',
    'cat_ТабЭлОб_ИзмСхПд_yes' => 'Scheme changes',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'Cable installation',
    'cat_ТабЭлОб_РаскКбЛн_yes' => 'Cable wiring',
    'cat_ТабЭлОб_ДопРаб_yes' => 'Additional works',
    'cat_ТабЭлОб_ПодтВех_yes' => 'Milestone confirmation',
    'cat_ТабЭлОб_Проч_yes' => 'Other works'
];

echo "Testing if TabelHandler can handle electrical equipment confirmation callbacks...\n\n";

foreach ($testCases as $callbackData => $description) {
    echo "Testing: {$description} ({$callbackData})\n";
    $canHandle = $tabelHandler->canHandle($callbackData);
    echo "Result: " . ($canHandle ? 'CAN HANDLE' : 'CANNOT HANDLE') . "\n\n";
}

// Test the isActivityExecution method directly
echo "Testing isActivityExecution method directly...\n";

$reflection = new ReflectionClass($tabelHandler);
$isActivityExecutionMethod = $reflection->getMethod('isActivityExecution');
$isActivityExecutionMethod->setAccessible(true);

foreach ($testCases as $callbackData => $description) {
    echo "Testing isActivityExecution for: {$description} ({$callbackData})\n";
    try {
        $result = $isActivityExecutionMethod->invoke($tabelHandler, $callbackData);
        echo "isActivityExecution result: " . ($result ? 'TRUE' : 'FALSE') . "\n\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

echo "=== Test Completed ===\n";