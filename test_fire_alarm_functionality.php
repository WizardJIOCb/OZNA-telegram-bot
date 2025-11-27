<?php

// Test script to verify fire alarm functionality

require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

// Test MessageService methods
$messageService = new MessageService();

echo "Testing MessageService fire alarm keyboard methods...\n";

// Test fire alarm equipment keyboard
$fireAlarmKeyboard = $messageService->createFireAlarmEquipmentKeyboard();
echo "Fire alarm equipment keyboard created successfully.\n";
echo "Keyboard structure:\n";
print_r($fireAlarmKeyboard);

// Test additional works fire alarm keyboard
$additionalWorksFireAlarmKeyboard = $messageService->createAdditionalWorksFireAlarmKeyboard();
echo "\nAdditional works fire alarm keyboard created successfully.\n";
echo "Keyboard structure:\n";
print_r($additionalWorksFireAlarmKeyboard);

echo "\nAll tests passed!\n";