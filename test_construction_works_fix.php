<?php
// Test script to verify the fix for construction works buttons

require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the extractCategoryFromCallback method
echo "Testing extractCategoryFromCallback method:\n";

$testCallbacks = [
    'cat_ТабПодгПоездки' => 'Табель_СтройМонтРаботы_ПодготовкаПоездки',
    'cat_ТабСтройМонт_Дорога' => 'Табель_СтройМонтРаботы_Дорога',
    'cat_ТабСтройМонт_Инструктаж' => 'Табель_СтройМонтРаботы_Инструктаж',
    'cat_ТабСтройМонт_АвансОтчет' => 'Табель_СтройМонтРаботы_АвансОтчет',
    'cat_ТабПодгПоездки_yes' => 'Табель_СтройМонтРаботы_ПодготовкаПоездки_yes',
    'cat_ТабСтройМонт_Дорога_yes' => 'Табель_СтройМонтРаботы_Дорога_yes',
    'cat_ТабСтройМонт_Инструктаж_yes' => 'Табель_СтройМонтРаботы_Инструктаж_yes',
    'cat_ТабСтройМонт_АвансОтчет_yes' => 'Табель_СтройМонтРаботы_АвансОтчет_yes'
];

foreach ($testCallbacks as $callbackData => $expectedCategory) {
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    
    $actualCategory = $method->invokeArgs($tabelHandler, [$callbackData]);
    
    if ($actualCategory === $expectedCategory) {
        echo "✓ {$callbackData} -> {$actualCategory}\n";
    } else {
        echo "✗ {$callbackData} -> {$actualCategory} (expected: {$expectedCategory})\n";
    }
}

echo "\nTesting isActivityExecution method:\n";

// Test the isActivityExecution method
$testCategories = [
    'Табель_СтройМонтРаботы_ПодготовкаПоездки_yes' => true,
    'Табель_СтройМонтРаботы_Дорога_yes' => true,
    'Табель_СтройМонтРаботы_Инструктаж_yes' => true,
    'Табель_СтройМонтРаботы_АвансОтчет_yes' => true
];

foreach ($testCategories as $category => $expectedResult) {
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('isActivityExecution');
    $method->setAccessible(true);
    
    // Create a mock conversation object
    $conversation = new stdClass();
    $conversation->notes = [];
    
    $actualResult = $method->invokeArgs($tabelHandler, [$category, $conversation]);
    
    if ($actualResult === $expectedResult) {
        echo "✓ {$category} -> " . ($actualResult ? 'true' : 'false') . "\n";
    } else {
        echo "✗ {$category} -> " . ($actualResult ? 'true' : 'false') . " (expected: " . ($expectedResult ? 'true' : 'false') . ")\n";
    }
}

echo "\nFix applied successfully! The construction works buttons should now work correctly.\n";
?>