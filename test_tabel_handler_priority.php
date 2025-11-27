<?php
// Test script to verify TabelHandler priority

require_once 'vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Get priority
$priority = $tabelHandler->getPriority();

echo "TabelHandler priority: " . $priority . "\n";

if ($priority >= 30) {
    echo "SUCCESS: TabelHandler has high enough priority to handle submenu buttons correctly.\n";
} else {
    echo "ERROR: TabelHandler priority is too low. It should be at least 30.\n";
}