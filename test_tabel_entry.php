<?php

// Test script to verify the TabelEntry getConfirmationMessage method

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "Testing TabelEntry getConfirmationMessage method...\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

echo "✅ TabelEntry created successfully\n";

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