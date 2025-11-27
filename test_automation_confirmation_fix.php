<?php

// Test script to verify that automation confirmation messages now use the new format

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "Testing automation confirmation messages after fix...\n\n";

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$projectCode = 'ОИ834';

echo "=== AUTOMATION EQUIPMENT CONFIRMATION MESSAGES ===\n";
$automationActivities = [
    'РабПоГр_Автом' => 'Работы по графику (Автоматизация)',
    'ФормИД_Автом' => 'Формирование ИД (Автоматизация)', 
    'ПодтВех_Автом' => 'Подтвердить достижение вехи (Автоматизация)',
    'ДопРаб_Автом' => 'Дополнительные работы (Автоматизация)',
    'ИзмСх_Автом' => 'Изменение схемы подключения (Автоматизация, Дополнительные работы)',
    'МонтКб_Автом' => 'Прокладка кабеля (Автоматизация, Дополнительные работы)',
    'РаскКб_Автом' => 'Подключение кабеля (Автоматизация, Дополнительные работы)',
    'МонтАСУ_Автом' => 'Монтаж оборудования АСУ ТП (Автоматизация, Дополнительные работы)',
    'УдалПО_Автом' => 'Удаленный ПНР ПО (Автоматизация, Дополнительные работы)',
    'Прочее_Автом' => 'Прочее (Автоматизация, Дополнительные работы)'
];

foreach ($automationActivities as $activityType => $expectedName) {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    
    echo "\nActivity Type: {$activityType}\n";
    echo "Expected Name: {$expectedName}\n";
    echo "Confirmation Message: {$confirmationMessage}\n";
    
    // Check if using new format: "Проект ОИ834: [Activity Name]. Отправить запись в табель?"
    if (strpos($confirmationMessage, "Проект <b>{$projectCode}</b>:") !== false && 
        strpos($confirmationMessage, "Отправить запись в табель?") !== false) {
        echo "✅ USING NEW FORMAT\n";
    } else {
        echo "❌ USING OLD FORMAT\n";
    }
}

echo "\n=== COMPARISON WITH ELECTRICAL EQUIPMENT ===\n";
$electricalActivities = [
    'РабПоГр_ЭлектрОб' => 'Работы по графику (Электрооборудование)',
    'ФормИД_ЭлектрОб' => 'Формирование ИД (Электрооборудование)',
    'Прочее_ЭлектрОб' => 'Прочее (Электрооборудование, Дополнительные работы)'
];

foreach ($electricalActivities as $activityType => $expectedName) {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    
    echo "\nElectrical Activity Type: {$activityType}\n";
    echo "Confirmation Message: {$confirmationMessage}\n";
    
    // Check if using new format
    if (strpos($confirmationMessage, "Проект <b>{$projectCode}</b>:") !== false && 
        strpos($confirmationMessage, "Отправить запись в табель?") !== false) {
        echo "✅ ELECTRICAL USING NEW FORMAT\n";
    } else {
        echo "❌ ELECTRICAL USING OLD FORMAT\n";
    }
}

echo "\nTest completed! Automation confirmation messages should now match electrical equipment format.\n";