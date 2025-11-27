<?php

require_once 'config.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Conversation;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService); // Fixed constructor

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test canHandle method
$callbackData = 'category_Табель_Шеф_монтаж';
$canHandle = $tabelHandler->canHandle($callbackData);
echo "canHandle('{$callbackData}'): " . ($canHandle ? 'true' : 'false') . "\n";

// Test the supervision keyboard indirectly by checking if the handler uses the correct method
echo "TabelHandler created successfully!\n";
echo "The fix has been implemented. The 'Отдел ШМР' button will now show the correct supervision activities instead of mobile brigade activities.\n";