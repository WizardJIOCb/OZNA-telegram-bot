<?php

// Test script to verify the preparation activity fix

require_once 'vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the isActivityConfirmation method
$category = 'category_Табель_Подготовка';
$result = $tabelHandler->isActivityConfirmation($category);

echo "Testing isActivityConfirmation with category: {$category}\n";
echo "Result: " . ($result ? 'true' : 'false') . "\n";

// Test the isActivityExecution method
$category = 'category_Табель_Подготовка_yes';
$result = $tabelHandler->isActivityExecution($category);

echo "Testing isActivityExecution with category: {$category}\n";
echo "Result: " . ($result ? 'true' : 'false') . "\n";

?>