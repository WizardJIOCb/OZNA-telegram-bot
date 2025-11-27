<?php

// Test script to verify the confirmation message fix

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "Testing confirmation message fix...\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

echo "✅ Services created successfully\n";

// Test that the TabelEntry getConfirmationMessage method works correctly
$activityType = 'Теплоизоляция';
$projectCode = 'ОИ-2025-001';
$confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);

echo "Generated confirmation message:\n";
echo $confirmationMessage . "\n";

// Check if the message contains the actual project code instead of placeholder
if (strpos($confirmationMessage, $projectCode) !== false) {
    echo "✅ Confirmation message contains actual project code\n";
} else {
    echo "❌ Confirmation message does not contain actual project code\n";
}

// Check if it doesn't contain the placeholder
if (strpos($confirmationMessage, '[будет добавлен]') === false) {
    echo "✅ Confirmation message does not contain placeholder text\n";
} else {
    echo "❌ Confirmation message still contains placeholder text\n";
}

echo "Test completed.\n";