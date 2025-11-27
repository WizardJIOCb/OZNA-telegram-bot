<?php

// Test to verify project activity templates match electrical equipment templates

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== PROJECT ACTIVITY TEMPLATE VERIFICATION ===\n\n";

// Create required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test data
$projectCode = 'ОИ834';
$fio = 'Богданов Борис Сергеевич';
$date = '19.09.2025';

echo "1. TESTING PROJECT ACTIVITIES:\n";
echo "Expected confirmation format: \"Проект ОИ834: [Activity Name]. Отправить запись в табель?\"\n";
echo "Expected final report format: \"Богданов Борис Сергеевич ✅19.09.2025, Проект ОИ834: Добавлена запись в табель: [Activity Name]\"\n\n";

$projectActivities = [
    'УчастиеВЗПИ' => 'Участие в ЗПИ',
    'РазрПрограммы' => 'Разработка программы работ',
    'РазрГрафика' => 'Разработка графика работ',
    'ОргДопоставки' => 'Организация допоставки',
    'УчастиеВОПИ' => 'Участие в ОПИ'
];

foreach ($projectActivities as $activityType => $activityName) {
    echo "Activity: {$activityType}\n";
    
    // Test confirmation message
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    echo "Confirmation: {$confirmationMessage}\n";
    
    // Test final report message
    $finalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    echo "Final Report: {$finalMessage}\n\n";
}

echo "2. TESTING ELECTRICAL EQUIPMENT (FOR COMPARISON):\n";
$electricalActivities = [
    'РабПоГр_ЭлектрОб' => 'Работы по графику (Электрооборудование)',
    'ФормИД_ЭлектрОб' => 'Формирование ИД (Электрооборудование)'
];

foreach ($electricalActivities as $activityType => $activityName) {
    echo "Activity: {$activityType}\n";
    
    // Test confirmation message
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    echo "Confirmation: {$confirmationMessage}\n";
    
    // Test final report message
    $finalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    echo "Final Report: {$finalMessage}\n\n";
}

echo "=== VERIFICATION COMPLETE ===\n";
echo "Both project activities and electrical equipment should now use identical templates!\n";