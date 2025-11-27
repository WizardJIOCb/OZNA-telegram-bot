<?php
// Test script to simulate project callback

// Include the necessary files
require_once 'vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create instances of the required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService);
$adminService = new AdminService($messageService);

// Create the TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the new project callback format
$testCallback = "project_ОИ834";

echo "Testing project callback: {$testCallback}\n";
echo "canHandle result: " . ($tabelHandler->canHandle($testCallback) ? 'true' : 'false') . "\n";

// Test the project selector
echo "isProjectSelection result: " . ($projectSelector->isProjectSelection($testCallback) ? 'true' : 'false') . "\n";
echo "extracted project code: " . ($projectSelector->extractProjectCode($testCallback) ?? 'null') . "\n";