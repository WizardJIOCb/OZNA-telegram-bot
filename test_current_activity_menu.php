<?php

require_once 'vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create MessageService instance
$messageService = new MessageService();

// Test current activity keyboard
$keyboard = $messageService->createCurrentActivityKeyboard();

echo "Current Activity Keyboard:\n";
print_r($keyboard);

// Test TKP subtypes keyboard
$tkpKeyboard = $messageService->createTKPSubtypesKeyboard();

echo "\nTKP Subtypes Keyboard:\n";
print_r($tkpKeyboard);