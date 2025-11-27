<?php
/**
 * Debug script to identify why activity text is missing from reports
 */

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Debugging Missing Activity Text ===\n\n";

// Create required dependencies
$databaseService = new DatabaseService();
$messageService = new MessageService();

// Create test instance
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test data based on user's report
$fio = "Test User";
$date = "05.10.2025";
$projectCode = "ХХХ";
$activityType = "ПроверкаМонтажнойПлощадки";
$idleReason = "";

echo "Testing with the exact parameters from user report:\n";
echo "  FIO: {$fio}\n";
echo "  Date: {$date}\n";
echo "  Project: {$projectCode}\n";
echo "  Activity Type: {$activityType}\n";
echo "  Idle Reason: {$idleReason}\n\n";

// Check what the activity mappings return
$reflection = new ReflectionClass($tabelEntry);
$mappingsProperty = $reflection->getProperty('activityTypeMappings');
$mappingsProperty->setAccessible(true);
$mappings = $mappingsProperty->getValue($tabelEntry);

echo "Checking activity type mappings:\n";
echo "====================================\n";
if (isset($mappings[$activityType])) {
    echo "✅ Found mapping for '{$activityType}': '{$mappings[$activityType]}'\n";
} else {
    echo "❌ NO mapping found for '{$activityType}'\n";
    echo "Available mappings containing 'Проверка':\n";
    foreach ($mappings as $key => $value) {
        if (strpos($key, 'Проверка') !== false || strpos($value, 'Проверка') !== false) {
            echo "  '{$key}' => '{$value}'\n";
        }
    }
}

echo "\nTesting message generation:\n";
echo "==========================\n";

// Use reflection to access the private createTabelMessage method
$method = $reflection->getMethod('createTabelMessage');
$method->setAccessible(true);

echo "Calling createTabelMessage method...\n";
$result = $method->invoke($tabelEntry, $fio, $date, $projectCode, $activityType, $idleReason);

echo "\nGenerated message:\n";
echo "==================\n";
echo $result;

echo "\n\nHTML stripped version:\n";
echo "=====================\n";
echo strip_tags($result);

echo "\n\nAnalysis:\n";
echo "=========\n";

// Check if activity text is present after the colon
$colonPos = strpos($result, 'Добавлена запись в табель:');
if ($colonPos !== false) {
    $afterColon = substr($result, $colonPos + strlen('Добавлена запись в табель:'));
    $afterColon = trim(strip_tags($afterColon));
    
    if (empty($afterColon)) {
        echo "❌ PROBLEM: Activity text is EMPTY after 'Добавлена запись в табель:'\n";
    } else {
        echo "✅ Activity text found: '{$afterColon}'\n";
    }
} else {
    echo "❌ PROBLEM: Could not find 'Добавлена запись в табель:' in the message\n";
}

echo "\n=== Debug Complete ===\n";
?>