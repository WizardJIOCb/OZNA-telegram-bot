<?php

// Load the actual class without instantiation - we'll create a reflection approach
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create actual service instances
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

echo "=== TESTING SUPERVISION INSTALLATION WORKS vs PROJECT ACTIVITIES FORMAT ===\n\n";

// Test data
$fio = "Иванов Иван Иванович";
$date = "27.09.2025";
$projectCode = "ОИ834";

// Test Project Activities format (reference standard)
echo "PROJECT ACTIVITIES (reference standard):\n";
$projectActivities = ['УчастиеВЗПИ', 'РазрПрограммы', 'РазрГрафика', 'ОргДопоставки', 'УчастиеВОПИ'];

foreach ($projectActivities as $activity) {
    $confirmation = $tabelEntry->getConfirmationMessage($activity, $projectCode);
    $finalReport = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activity);
    
    echo "Activity: $activity\n";
    echo "Confirmation: $confirmation\n";
    echo "Final Report: $finalReport\n\n";
}

echo "==============================================\n\n";

// Test Supervision Installation Works format (should match exactly)
echo "SUPERVISION INSTALLATION WORKS (should match project activities):\n";
$supervisionActivities = ['ШМ_РабПоГрафику', 'ШМ_ПодготовкаКПоездке', 'ШМ_Дорога', 'ШМ_Инструктаж', 'ШМ_СдачаАвансовогоОтчета'];

foreach ($supervisionActivities as $activity) {
    $confirmation = $tabelEntry->getConfirmationMessage($activity, $projectCode);
    $finalReport = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activity);
    
    echo "Activity: $activity\n";
    echo "Confirmation: $confirmation\n";
    echo "Final Report: $finalReport\n\n";
}

echo "=== COMPARISON SUMMARY ===\n";
echo "Both formats should have:\n";
echo "Confirmation: 'Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?'\n";
echo "Final Report: '<b>%s</b>\\r\\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>'\n";
