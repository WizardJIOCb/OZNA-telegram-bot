<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== TESTING MOBILE BRIGADE IDLE FIX ===\n\n";

// Mock dependencies
class MockDatabaseService extends DatabaseService {
    public function addUserTabel(int $userId, string $message): bool {
        return true;
    }
    
    public function getUserData(int $userId): array {
        return ['functional_direction' => 'Мобильная бригада'];
    }
    
    public function getAllUsers(): array {
        return [];
    }
}

class MockMessageService extends MessageService {
    public function sendToHistoryChatByFunctionalDirection(string $message, string $direction): \Longman\TelegramBot\Entities\ServerResponse {
        // Mock implementation - return a mock ServerResponse
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true]);
    }
}

// Create TabelEntry instance
$mockDb = new MockDatabaseService();
$mockMsg = new MockMessageService();
$tabelEntry = new TabelEntry($mockDb, $mockMsg);

// Test parameters from user's report
$fio = "Тест тест тест";
$date = "11.10.2025";
$projectCode = "ХХХ";
$activityType = "МобБриг_Простой";
$idleReason = "Дефицит по спецификации";

echo "Testing Mobile Brigade idle message generation:\n";
echo "==============================================\n";
echo "Input parameters:\n";
echo "  FIO: {$fio}\n";
echo "  Date: {$date}\n";
echo "  Project: {$projectCode}\n";
echo "  Activity Type: {$activityType}\n";
echo "  Idle Reason: {$idleReason}\n\n";

// Call the createTabelMessage method
$message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);

echo "Generated message:\n";
echo "==================\n";
echo $message . "\n\n";

// Check if the message uses the incident format (which is correct for idle activities)
if (strpos($message, 'ИНЦИДЕНТ') !== false) {
    echo "✅ SUCCESS: Message uses the correct incident format for idle activities\n";
} else {
    echo "❌ ISSUE: Message does NOT use the incident format\n";
}

// Check if it shows user-friendly activity name instead of technical name
if (strpos($message, 'МобБриг_Простой') !== false) {
    echo "❌ ISSUE: Message still shows technical activity name 'МобБриг_Простой'\n";
} else {
    echo "✅ SUCCESS: Message uses user-friendly activity name\n";
}

// Check if custom reason is included
if (strpos($message, $idleReason) !== false) {
    echo "✅ SUCCESS: Custom idle reason '{$idleReason}' is included\n";
} else {
    echo "❌ ISSUE: Custom idle reason '{$idleReason}' is missing\n";
}

// Show HTML stripped version for readability
echo "\nReadable version (HTML stripped):\n";
echo "=================================\n";
echo strip_tags($message) . "\n";

echo "\n=== TEST COMPLETE ===\n";
echo "The fix should ensure that Mobile Brigade idle activities use the proper incident format\n";
echo "and show user-friendly names instead of technical activity names like 'МобБриг_Простой'.\n";

?>