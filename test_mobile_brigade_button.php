<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/hook.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create mock objects
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the mobile brigade button callback
$callbackData = 'category_Табель_Мобильная_Бригада';

echo "Testing mobile brigade button callback: {$callbackData}\n";
echo "Can handle result: " . ($tabelHandler->canHandle($callbackData) ? 'true' : 'false') . "\n";

// Test the corrupted version
$corruptedCallbackData = 'category_РўР°Р±РµР»СЊ_РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°';
echo "Testing corrupted mobile brigade button callback: {$corruptedCallbackData}\n";
echo "Can handle result: " . ($tabelHandler->canHandle($corruptedCallbackData) ? 'true' : 'false') . "\n";

?>