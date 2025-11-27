<?php
/**
 * Test script to verify the site inspection format fix
 */

require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Testing Site Inspection Format Fix ===\n\n";

// Create required dependencies
$databaseService = new DatabaseService();
$messageService = new MessageService();

// Create test instance
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test data
$fio = "Тест Пользователь";
$date = "05.10.2025";
$projectCode = "ХХХ";
$activityType = "ПроверкаМонтажнойПлощадки";
$idleReason = "";

echo "Testing with:\n";
echo "  FIO: {$fio}\n";
echo "  Date: {$date}\n";
echo "  Project: {$projectCode}\n";
echo "  Activity Type: {$activityType}\n";
echo "  Idle Reason: {$idleReason}\n\n";

echo "Generated message:\n";
echo "==================\n";

// Use reflection to access the private createTabelMessage method
$reflection = new ReflectionClass($tabelEntry);
$method = $reflection->getMethod('createTabelMessage');
$method->setAccessible(true);

$result = $method->invoke($tabelEntry, $fio, $date, $projectCode, $activityType, $idleReason);

echo $result;

echo "\n\n=== Expected vs Actual ===\n";
echo "Expected format: '✅05.10.2025, Проект ХХХ: Добавлена запись в табель: 🏗️ Проверка монтажной площадки'\n";
echo "Actual format: " . strip_tags($result) . "\n\n";

// Check if it's using commissioning format (new) vs fallback format (old)
if (strpos($result, 'Добавлена запись в табель:') !== false) {
    echo "✅ SUCCESS: Using new commissioning format\n";
} else if (strpos($result, 'Запись в табель от') !== false) {
    echo "❌ FAIL: Still using old fallback format\n";
} else {
    echo "? UNKNOWN: Format not recognized\n";
}

echo "\n=== Test Complete ===\n";
?>