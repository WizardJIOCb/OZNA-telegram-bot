<?php

// Test script to compare electrical equipment vs automation equipment report formatting

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "Testing comparison between electrical equipment and automation equipment report formatting...\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$projectCode = 'ОИ834';

echo "\n=== ELECTRICAL EQUIPMENT ===\n";
$electricalActivities = [
    'РаботыПоГрафику_Электрооборудование' => 'Работы по графику (Электрооборудование)',
    'ФормированиеИД_Электрооборудование' => 'Формирование ИД (Электрооборудование)',
    'Прочее_Электрооборудование' => 'Прочее (Электрооборудование, Дополнительные работы)'
];

foreach ($electricalActivities as $activityType => $expectedName) {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    echo "Activity: {$activityType}\n";
    echo "Message: {$confirmationMessage}\n\n";
}

echo "\n=== AUTOMATION EQUIPMENT ===\n";
$automationActivities = [
    'РабПоГр_Автом' => 'Работы по графику (Автоматизация)',
    'ФормИД_Автом' => 'Формирование ИД (Автоматизация)',
    'Прочее_Автом' => 'Прочее (Автоматизация, Дополнительные работы)'
];

foreach ($automationActivities as $activityType => $expectedName) {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    echo "Activity: {$activityType}\n";
    echo "Message: {$confirmationMessage}\n\n";
}

echo "Comparison test completed.\n";