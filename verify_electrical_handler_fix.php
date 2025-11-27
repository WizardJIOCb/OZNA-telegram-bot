<?php
// Verification script for the electrical equipment handler fix

echo "Verifying electrical equipment handler fix...\n\n";

// Include necessary files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

// Test the canHandle method with various electrical equipment callbacks
$testCallbacks = [
    'category_Табель_Электрооборудование',
    'cat_ТабЭлОб',
    'cat_ТабЭлОб_Наз',
    'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ', // Corrupted version
    'Табель_Электрооборудование',
    'category_Табель_РаботыПоГрафику_Электрооборудование',
    'cat_ТабЭлОб_РабПоГр'
];

echo "Testing TabelHandler::canHandle method:\n";
foreach ($testCallbacks as $callback) {
    $canHandle = $tabelHandler->canHandle($callback);
    echo "  Callback: {$callback}\n";
    echo "  Can Handle: " . ($canHandle ? 'YES' : 'NO') . "\n\n";
}

// Test the extractCategoryFromCallback method
echo "Testing extractCategoryFromCallback method:\n";
foreach ($testCallbacks as $callback) {
    // We need to access the private method through reflection for testing
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    $category = $method->invokeArgs($tabelHandler, [$callback]);
    echo "  Callback: {$callback}\n";
    echo "  Extracted Category: {$category}\n\n";
}

echo "Verification completed!\n";