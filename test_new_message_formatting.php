<?php

// Test script for new commissioning message formatting
// Tests both confirmation messages and final reports

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Mock services
$mockDbService = new class extends DatabaseService {
    public function __construct() {}
};

$mockMsgService = new class extends MessageService {
    public function __construct() {}
};

$tabelEntry = new TabelEntry($mockDbService, $mockMsgService);

echo "=== Testing New Message Formatting for Commissioning Activities ===\n\n";

// Test commissioning activities
$commissioningActivities = [
    'КомплексноеОпробование' => 'Комплексное опробование',
    'РежимнаяНаладка' => 'Режимная наладка', 
    'ПодготовкаКПоездке' => 'Подготовка к служебной поездке',
    'СдачаАвансовогоОтчета' => 'Сдача авансового отчета',
    'Инструктаж' => 'Инструктаж'
];

$projectCode = 'ОИ834';
$fio = 'Богданов Борис Сергеевич';
$date = '19.09.2025';

echo "1. TESTING CONFIRMATION MESSAGES:\n";
echo "Expected format: \"Проект ОИ834: Комплексное опробование. Отправить запись в табель?\"\n\n";

foreach ($commissioningActivities as $activityType => $activityName) {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    echo "Activity: {$activityType}\n";
    echo "Confirmation: {$confirmationMessage}\n\n";
}

echo "\n2. TESTING FINAL REPORT MESSAGES:\n";
echo "Expected format: \"Богданов Борис Сергеевич ✅19.092025, Проект ОИ834: Добавлена запись в табель: Комплексное опробование\"\n\n";

foreach ($commissioningActivities as $activityType => $activityName) {
    $finalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
    echo "Activity: {$activityType}\n";
    echo "Final Report: {$finalMessage}\n\n";
}

echo "\n3. TESTING NON-COMMISSIONING ACTIVITY (should use old format):\n";
$regularActivity = 'ПНР';
$confirmationRegular = $tabelEntry->getConfirmationMessage($regularActivity, $projectCode);
$finalRegular = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $regularActivity);

echo "Activity: {$regularActivity}\n";
echo "Confirmation: {$confirmationRegular}\n";
echo "Final Report: {$finalRegular}\n\n";

echo "=== Test completed! ===\n";