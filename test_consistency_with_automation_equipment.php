<?php

// Test script to verify consistency between project activities and automation equipment custom descriptions

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "Testing consistency between project activities and automation equipment custom descriptions...\n\n";

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$fio = 'Тестовый Пользователь';
$date = '22.09.2025';
$projectCode = 'ОИ836';
$customDescription = 'Тест на соответствие поведения';

echo "=== CONSISTENCY TEST: PROJECT ACTIVITIES vs AUTOMATION EQUIPMENT ===\n";

// Test 1: Project Activity "Прочее" with custom description
echo "1. PROJECT ACTIVITY 'Прочее' WITH CUSTOM DESCRIPTION:\n";
echo "   Confirmation message:\n";
$projectConfirmation = $tabelEntry->getConfirmationMessage('Прочее_ПроектнаяДеятельность', $projectCode);
echo "     {$projectConfirmation}\n";

echo "   Final report message:\n";
$projectFinal = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'Прочее_ПроектнаяДеятельность', $customDescription);
echo "     {$projectFinal}\n";

// Test 2: Automation Equipment "Прочее" with custom description
echo "\n2. AUTOMATION EQUIPMENT 'Прочее' WITH CUSTOM DESCRIPTION:\n";
echo "   Confirmation message:\n";
$automationConfirmation = $tabelEntry->getConfirmationMessage('Прочее_Автом', $projectCode);
echo "     {$automationConfirmation}\n";

echo "   Final report message:\n";
$automationFinal = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'Прочее_Автом', $customDescription);
echo "     {$automationFinal}\n";

// Test 3: Verify both include custom descriptions in final reports
echo "\n3. CONSISTENCY VERIFICATION:\n";
$projectHasCustom = strpos($projectFinal, $customDescription) !== false;
$automationHasCustom = strpos($automationFinal, $customDescription) !== false;

if ($projectHasCustom && $automationHasCustom) {
    echo "   ✅ SUCCESS: Both project activities and automation equipment include custom descriptions\n";
    echo "   ✅ Project activities: Custom description found\n";
    echo "   ✅ Automation equipment: Custom description found\n";
} else {
    echo "   ❌ INCONSISTENCY DETECTED:\n";
    echo "   ❌ Project activities custom description: " . ($projectHasCustom ? "Found" : "Missing") . "\n";
    echo "   ❌ Automation equipment custom description: " . ($automationHasCustom ? "Found" : "Missing") . "\n";
}

echo "\n=== CONSISTENCY TEST COMPLETED ===\n";
echo "Both project activities and automation equipment should now handle custom descriptions consistently.\n";