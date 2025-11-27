<?php

// Test script to verify that custom descriptions are properly added to confirmation messages for project activities

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "Testing custom description in confirmation message for project activities...\n\n";

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$fio = 'Тестовый Пользователь';
$date = '20.09.2025';
$projectCode = 'ОИ834';
$customDescription = 'Проверка работ по проектной деятельности';

echo "=== PROJECT ACTIVITY CUSTOM DESCRIPTION TEST ===\n";

// Test 1: Confirmation message for Прочее_ПроектнаяДеятельность
echo "1. CONFIRMATION MESSAGE FOR PROJECT ACTIVITY 'Прочее':\n";
$confirmationMessage = $tabelEntry->getConfirmationMessage('Прочее_ПроектнаяДеятельность', $projectCode);
echo "   Result: {$confirmationMessage}\n";

// Check if custom description would be added (it won't be in confirmation, but should be in final message)
if (strpos($confirmationMessage, 'Прочее (Проектная деятельность)') !== false) {
    echo "   ✅ CORRECT: Confirmation message shows 'Прочее (Проектная деятельность)'\n";
} else {
    echo "   ❌ WRONG: Confirmation message doesn't show expected text\n";
}

echo "\n2. FINAL REPORT MESSAGE FOR PROJECT ACTIVITY 'Прочее':\n";
// Test 2: Final report message with custom description
$finalReportMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'Прочее_ПроектнаяДеятельность', $customDescription);
echo "   Result: {$finalReportMessage}\n";

// Check if custom description is properly added
if (strpos($finalReportMessage, $customDescription) !== false) {
    echo "   ✅ CORRECT: Custom description '{$customDescription}' is included in final report\n";
} else {
    echo "   ❌ WRONG: Custom description '{$customDescription}' is missing from final report\n";
}

echo "\n3. COMPARISON ANALYSIS:\n";
echo "   Confirmation message: Shows activity type without custom description\n";
echo "   Final report message: Should show activity type WITH custom description\n";
echo "   Custom description: '{$customDescription}'\n";

echo "\n=== TEST COMPLETED ===\n";
echo "The fix should ensure that custom descriptions appear in the final report for project activities.\n";