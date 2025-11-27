<?php

// Test script to verify the automation report formatting fix

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "Testing automation report formatting fix...\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

echo "✅ TabelEntry created successfully\n";

// Test automation activity types
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

$projectCode = 'ОИ834';

echo "\nTesting confirmation messages for automation activities:\n";

foreach ($automationActivities as $activityType => $expectedName) {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    
    echo "\nActivity Type: {$activityType}\n";
    echo "Expected Name: {$expectedName}\n";
    echo "Confirmation Message: {$confirmationMessage}\n";
    
    // Check if the message contains the expected name instead of the abbreviated one
    if (strpos($confirmationMessage, $expectedName) !== false) {
        echo "✅ SUCCESS: Message contains the full name\n";
    } else {
        echo "❌ FAILURE: Message does not contain the full name\n";
    }
    
    // Check if the message contains the project code
    if (strpos($confirmationMessage, $projectCode) !== false) {
        echo "✅ SUCCESS: Message contains the project code\n";
    } else {
        echo "❌ FAILURE: Message does not contain the project code\n";
    }
}

echo "\nTest completed.\n";