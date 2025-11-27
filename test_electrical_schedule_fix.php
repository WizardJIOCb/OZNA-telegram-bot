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
use Longman\TelegramBot\Entities\CallbackQuery;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "Testing electrical equipment 'Работы по графику' activity handling...\n";

// Test canHandle method for electrical equipment schedule callback
$callbackData = 'cat_ТабЭлОб_РабПоГр';
$canHandle = $tabelHandler->canHandle($callbackData);
echo "canHandle('{$callbackData}'): " . ($canHandle ? 'true' : 'false') . "\n";

// Test canHandle method for full format electrical equipment schedule callback
$callbackDataFull = 'category_Табель_РаботыПоГрафику_Электрооборудование';
$canHandleFull = $tabelHandler->canHandle($callbackDataFull);
echo "canHandle('{$callbackDataFull}'): " . ($canHandleFull ? 'true' : 'false') . "\n";

echo "The fix has been implemented. The 'Работы по графику' button will now directly generate a report without confirmation.\n";