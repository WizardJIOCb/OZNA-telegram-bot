<?php

// Test script to verify both confirmation messages AND final reports for automation equipment

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "Testing BOTH confirmation and final report messages for automation equipment...\n\n";

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$fio = 'Иванов Иван Иванович';
$date = '20.09.2025';
$projectCode = 'ОИ834';

echo "=== AUTOMATION EQUIPMENT MESSAGES TEST ===\n";
$testActivities = [
    'РабПоГр_Автом' => 'Работы по графику (Автоматизация)',
    'ФормИД_Автом' => 'Формирование ИД (Автоматизация)',
    'ПодтВех_Автом' => 'Подтвердить достижение вехи (Автоматизация)',
    'Прочее_Автом' => 'Прочее (Автоматизация, Дополнительные работы)'
];

foreach ($testActivities as $activityType => $expectedName) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "ACTIVITY: {$activityType}\n";
    echo str_repeat("=", 60) . "\n";
    
    // Test 1: Confirmation message (preliminary)
    echo "1. CONFIRMATION MESSAGE (Preliminary):\n";
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    echo $confirmationMessage . "\n";
    
    // Check format
    if (strpos($confirmationMessage, "Проект <b>{$projectCode}</b>:") !== false && 
        strpos($confirmationMessage, "Отправить запись в табель?") !== false) {
        echo "✅ Confirmation using NEW FORMAT\n";
    } else {
        echo "❌ Confirmation using OLD FORMAT\n";
    }
    
    echo "\n2. FINAL REPORT MESSAGE:\n";
    // Test 2: Final report message
    $finalReportMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    echo $finalReportMessage . "\n";
    
    // Check format for final report
    if (strpos($finalReportMessage, "✅{$date}, Проект <b>{$projectCode}</b>: Добавлена запись в табель:") !== false) {
        echo "✅ Final report using NEW FORMAT\n";
    } else {
        echo "❌ Final report using OLD FORMAT\n";
    }
}

echo "\nTest completed! Both confirmation and final report messages should use the new format.\n";