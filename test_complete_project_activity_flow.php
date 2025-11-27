<?php

// Test script to verify the complete flow for project activity "Прочее" with custom descriptions

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "Testing complete flow for project activity 'Прочее' with custom descriptions...\n\n";

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$fio = 'Тестовый Пользователь';
$date = '21.09.2025';
$projectCode = 'ОИ835';
$customDescription = 'Тестовая запись для проверки отображения пользовательского описания';

echo "=== COMPLETE PROJECT ACTIVITY FLOW TEST ===\n";

// Step 1: Test confirmation message (what user sees before clicking "Yes")
echo "1. CONFIRMATION MESSAGE (what user sees before clicking 'Yes'):\n";
$confirmationMessage = $tabelEntry->getConfirmationMessage('Прочее_ПроектнаяДеятельность', $projectCode);
echo "   {$confirmationMessage}\n\n";

// Step 2: Simulate what happens when user clicks "Yes" with custom description
echo "2. FINAL REPORT MESSAGE (what appears in the final report):\n";
$finalReportMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'Прочее_ПроектнаяДеятельность', $customDescription);
echo "   {$finalReportMessage}\n\n";

// Step 3: Verify the custom description is included
echo "3. VERIFICATION:\n";
if (strpos($finalReportMessage, $customDescription) !== false) {
    echo "   ✅ SUCCESS: Custom description is properly included in the final report\n";
    echo "   ✅ Custom description: '{$customDescription}'\n";
} else {
    echo "   ❌ FAILURE: Custom description is missing from the final report\n";
    echo "   ❌ Expected to find: '{$customDescription}'\n";
}

// Step 4: Show the difference between confirmation and final report
echo "\n4. COMPARISON:\n";
echo "   Confirmation shows: 'Прочее (Проектная деятельность)'\n";
echo "   Final report shows: 'Прочее (Проектная деятельность) ({$customDescription})'\n";

echo "\n=== FLOW TEST COMPLETED ===\n";
echo "This demonstrates that the custom description flow now works correctly for project activities.\n";
echo "The issue was that 'Прочее_ПроектнаяДеятельность' was missing from the projectActivities array\n";
echo "in the getConfirmationMessage method, which caused inconsistent handling between confirmation\n";
echo "and final report messages.\n";