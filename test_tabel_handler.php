<?php

// Test script to verify the TabelHandler functionality

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test various callback data scenarios
$testCases = [
    'category_Табель' => 'Main menu',
    'category_Табель_Электрооборудование' => 'Electrical equipment menu',
    'cat_ТабЭлОб' => 'Short format electrical equipment menu',
    'category_Табель_Дорога' => 'Road activity',
    'category_Табель_Подготовка' => 'Preparation activity'
];

echo "Testing TabelHandler canHandle method:\n";
foreach ($testCases as $callbackData => $description) {
    $result = $tabelHandler->canHandle($callbackData);
    echo "  {$description} ({$callbackData}): " . ($result ? 'YES' : 'NO') . "\n";
}

echo "\nAll tests completed.\n";