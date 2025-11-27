<?php

// Test to verify "Дорога" activity now uses the correct template

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== TESTING ДОРОГА ACTIVITY TEMPLATE FIX ===\n\n";

// Create required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test data
$projectCode = 'ОИ834';
$fio = 'Богданов Борис Сергеевич';
$date = '19.09.2025';
$activityType = 'Дорога';

echo "Testing 'Дорога' activity:\n";
echo "Expected confirmation format: \"Проект ОИ834: Дорога. Отправить запись в табель?\"\n";
echo "Expected final report format: \"Богданов Борис Сергеевич ✅19.09.2025, Проект ОИ834: Добавлена запись в табель: 🚗 Дорога\"\n\n";

// Test confirmation message
$confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
echo "CONFIRMATION MESSAGE:\n";
echo $confirmationMessage . "\n\n";

// Check if using new format
if (strpos($confirmationMessage, "Проект <b>{$projectCode}</b>:") !== false && 
    strpos($confirmationMessage, "Отправить запись в табель?") !== false) {
    echo "✅ CONFIRMATION using NEW FORMAT (correct!)\n";
} else {
    echo "❌ CONFIRMATION using OLD FORMAT (incorrect!)\n";
}

echo "\nFINAL REPORT MESSAGE:\n";
// Test final report message
$finalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
echo $finalMessage . "\n\n";

// Check format for final report
if (strpos($finalMessage, "✅{$date}, Проект <b>{$projectCode}</b>: Добавлена запись в табель:") !== false) {
    echo "✅ FINAL REPORT using NEW FORMAT (correct!)\n";
} else {
    echo "❌ FINAL REPORT using OLD FORMAT (incorrect!)\n";
}

echo "\n=== COMPARISON WITH OTHER ACTIVITIES ===\n";

// Test other activities for comparison
$testActivities = [
    'Инструктаж' => 'Инструктаж',
    'УчастиеВЗПИ' => 'Участие в ЗПИ (Project Activity)',
    'РабПоГр_ЭлектрОб' => 'Работы по графику (Электрооборудование)'
];

foreach ($testActivities as $testActivityType => $description) {
    echo "\n{$description}:\n";
    
    $confirmationMessage = $tabelEntry->getConfirmationMessage($testActivityType, $projectCode);
    $finalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $testActivityType);
    
    echo "Confirmation: " . substr($confirmationMessage, 0, 80) . "...\n";
    echo "Final Report: " . substr($finalMessage, 0, 80) . "...\n";
    
    // Check format
    $confirmationCorrect = strpos($confirmationMessage, "Проект <b>{$projectCode}</b>:") !== false;
    $finalCorrect = strpos($finalMessage, "✅{$date}, Проект <b>{$projectCode}</b>:") !== false;
    
    echo "Format: " . ($confirmationCorrect && $finalCorrect ? "✅ NEW FORMAT" : "❌ OLD FORMAT") . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "The 'Дорога' activity should now match the template format used by other commissioning activities!\n";