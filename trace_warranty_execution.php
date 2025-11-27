<?php
/**
 * Trace warranty equipment execution flow
 * Simulates the callback handling to identify where it fails
 */

echo "🔧 WARRANTY EQUIPMENT EXECUTION FLOW TRACE\n";
echo "=".str_repeat("=", 60)."\n\n";

// Simulate the callback data that would be sent for warranty equipment confirmation
$testCallbackData = 'cat_Гарантии_ЭлектрОб_yes';
echo "Testing callback data: '{$testCallbackData}'\n\n";

// Load required classes
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

echo "1. CREATING MOCK SERVICES\n";
echo "-".str_repeat("-", 30)."\n";

// Create mock services (simplified for testing)
class MockDatabaseService {
    public function getUserData($userId) {
        return ['fio' => 'Test User', 'functional_direction' => 'Пусконаладка'];
    }
    
    public function getUserAccessLevel($userId) {
        return 2; // Normal user level
    }
}

class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "   📤 Sending message to chat {$chatId}:\n";
        echo "      {$text}\n";
        if ($keyboard) {
            echo "      With keyboard: " . json_encode($keyboard) . "\n";
        }
        return new stdClass(); // Mock ServerResponse
    }
}

class MockProjectSelector {
    public function extractProjectCode($category) {
        return 'TEST-PROJECT';
    }
}

class MockTabelEntry {
    public function getConfirmationMessage($activityType, $projectCode) {
        return "Подтвердите выполнение: {$activityType} для проекта {$projectCode}";
    }
    
    public function addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason = '') {
        echo "   📊 Adding entry to tabel:\n";
        echo "      User: {$fio} (ID: {$userId})\n";
        echo "      Date: {$date}\n";
        echo "      Project: {$projectCode}\n";
        echo "      Activity: {$activityType}\n";
        if (!empty($idleReason)) {
            echo "      Reason: {$idleReason}\n";
        }
        return true;
    }
}

class MockAdminService {
    public function getUserAccessLevel($userId) {
        return 2; // Normal user
    }
    
    public function isSuperAdmin($userId) {
        return false;
    }
}

echo "✅ Mock services created\n\n";

echo "2. CREATING TABEL HANDLER\n";
echo "-".str_repeat("-", 30)."\n";

// Create TabelHandler with mock services
$databaseService = new MockDatabaseService();
$messageService = new MockMessageService();
$projectSelector = new MockProjectSelector();
$tabelEntry = new MockTabelEntry();
$adminService = new MockAdminService();

$tabelHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

echo "✅ TabelHandler created\n\n";

echo "3. TESTING IS ACTIVITY EXECUTION\n";
echo "-".str_repeat("-", 30)."\n";

// Test if the callback is recognized as an activity execution
$isActivityExecution = $tabelHandler->isActivityExecution($testCallbackData);
echo "isActivityExecution('{$testCallbackData}') = " . ($isActivityExecution ? '✅ TRUE' : '❌ FALSE') . "\n\n";

if ($isActivityExecution) {
    echo "✅ Callback correctly identified as activity execution\n";
    echo "   This means the warranty equipment confirmation should work!\n\n";
} else {
    echo "❌ Callback NOT recognized as activity execution\n";
    echo "   This is the root cause of the issue!\n\n";
    
    // Let's check what category is extracted
    echo "4. DEBUGGING CATEGORY EXTRACTION\n";
    echo "-".str_repeat("-", 30)."\n";
    
    // Try to extract category manually
    if (preg_match('/^cat_Гарантии_(.+)_yes$/', $testCallbackData, $matches)) {
        $equipmentType = $matches[1];
        echo "✅ Manual regex match successful\n";
        echo "   Equipment type: {$equipmentType}\n";
        echo "   Full activity type: РабГарантии_{$equipmentType}\n\n";
    } else {
        echo "❌ Manual regex match failed\n\n";
    }
}

echo "=".str_repeat("=", 60)."\n";
echo "TRACE COMPLETE\n";
echo "=".str_repeat("=", 60)."\n";
?>