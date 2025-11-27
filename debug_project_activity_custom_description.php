<?php

// Debug script to test the complete flow for project activity custom descriptions

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== DEBUGGING PROJECT ACTIVITY CUSTOM DESCRIPTION ISSUE ===\n\n";

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test data
$fio = 'Тестовый Пользователь';
$date = '25.09.2025';
$projectCode = 'ОИ837';
$activityType = 'Прочее_ПроектнаяДеятельность';
$customDescription = 'Проверка работ по проектной деятельности';

echo "Test Parameters:\n";
echo "- FIO: {$fio}\n";
echo "- Date: {$date}\n";
echo "- Project Code: {$projectCode}\n";
echo "- Activity Type: {$activityType}\n";
echo "- Custom Description: {$customDescription}\n\n";

echo "1. Testing activity type mapping...\n";
$reflection = new ReflectionClass($tabelEntry);
$property = $reflection->getProperty('activityTypeMappings');
$property->setAccessible(true);
$mappings = $property->getValue($tabelEntry);

if (isset($mappings[$activityType])) {
    echo "✅ Activity type found in mappings: '{$mappings[$activityType]}'\n";
} else {
    echo "❌ Activity type NOT found in mappings\n";
    echo "Available mappings:\n";
    foreach ($mappings as $key => $value) {
        if (strpos($key, 'Проектная') !== false) {
            echo "  '{$key}' => '{$value}'\n";
        }
    }
}

echo "\n2. Testing createTabelMessage method directly...\n";
try {
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $customDescription);
    echo "Generated message:\n{$message}\n\n";
    
    if (strpos($message, $customDescription) !== false) {
        echo "✅ SUCCESS: Custom description is included in the message\n";
    } else {
        echo "❌ FAILURE: Custom description is missing from the message\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. Testing getConfirmationMessage method...\n";
try {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
    echo "Confirmation message:\n{$confirmationMessage}\n\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Simulating complete flow...\n";
echo "This simulates what happens when a user:\n";
echo "1. Clicks 'Прочее' in project activities\n";
echo "2. Enters custom description: '{$customDescription}'\n";
echo "3. Clicks 'Да' to confirm\n";
echo "4. System creates final report\n\n";

echo "Expected final report format:\n";
echo "<b>{$fio}</b>\n";
echo "✅ {$date}, Проект <b>{$projectCode}</b>: Добавлена запись в табель: <b>📝 Прочее (Проектная деятельность) ({$customDescription})</b>\n\n";

echo "=== DEBUG COMPLETE ===\n";