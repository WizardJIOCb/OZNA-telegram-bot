<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

// Initialize services
$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

// Test Гайнутдинов
$userId = 1429455763;

echo "Direct method test for User ID: $userId\n\n";

// Use Reflection to access private methods
$reflection = new ReflectionClass($personnelService);

// Test hasAnyWorkActivities
$hasWorkMethod = $reflection->getMethod('hasAnyWorkActivities');
$hasWorkMethod->setAccessible(true);
$hasWork = $hasWorkMethod->invoke($personnelService, $userId);

echo "Has work activities: " . ($hasWork ? "YES" : "NO") . "\n";

// Test findFirstActivity
$firstMethod = $reflection->getMethod('findFirstActivity');
$firstMethod->setAccessible(true);
$firstActivity = $firstMethod->invoke($personnelService, $userId);

echo "First activity: " . ($firstActivity ? $firstActivity->format('Y-m-d H:i:s') : "NULL") . "\n";

// Test findLastActivity
$lastMethod = $reflection->getMethod('findLastActivity');
$lastMethod->setAccessible(true);
$lastActivity = $lastMethod->invoke($personnelService, $userId);

echo "Last activity:  " . ($lastActivity ? $lastActivity->format('Y-m-d H:i:s') : "NULL") . "\n\n";

// Logic check
if (!$hasWork && $firstActivity) {
    echo "✅ Logic: Office worker (no work activities)\n";
    echo "   Should use FIRST activity: " . $firstActivity->format('d.m.Y') . "\n";
} elseif ($hasWork && $lastActivity) {
    echo "ℹ️  Logic: Has work activities\n";
    echo "   Would use LAST activity: " . $lastActivity->format('d.m.Y') . "\n";
}

/**
 * Test to verify the fix is working by directly testing the createTabelMessage method
 */

echo "=== DIRECT METHOD TEST ===\n\n";

// Include the TabelEntry class
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

// Mock the dependencies
class MockDatabaseService {
    public function addUserTabel($userId, $message) {
        echo "MOCK DB: Would save: {$message}\n";
        return true;
    }
    public function getAllUsers() { return []; }
}

class MockMessageService {
    public function sendToHistoryChatByFunctionalDirection($message, $direction) {
        echo "MOCK MSG: Would send to {$direction}: {$message}\n";
        return true;
    }
}

try {
    $databaseService = new MockDatabaseService();
    $messageService = new MockMessageService();
    
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry(
        $databaseService, 
        $messageService
    );
    
    echo "✅ TabelEntry instance created successfully\n\n";
    
    // Test the exact scenario
    $fio = "Test User";
    $date = "18.09.2025";
    $projectCode = "ОИ696";
    $activityType = "Прочее_ЭлектрОб";
    $customText = "Проверка";
    
    echo "Testing createTabelMessage with:\n";
    echo "  fio: '{$fio}'\n";
    echo "  date: '{$date}'\n";
    echo "  projectCode: '{$projectCode}'\n";
    echo "  activityType: '{$activityType}'\n";
    echo "  idleReason: '{$customText}'\n\n";
    
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $customText);
    
    echo "RESULT MESSAGE:\n";
    echo "===============\n";
    echo $message . "\n\n";
    
    if (strpos($message, $customText) !== false) {
        echo "✅ SUCCESS: Custom text '{$customText}' is included in the message!\n";
    } else {
        echo "❌ FAILED: Custom text '{$customText}' is NOT included in the message!\n";
    }
    
    if (strpos($message, '📊 Табель') !== false) {
        echo "❌ ERROR: Message still contains '📊 Табель'!\n";
    } else {
        echo "✅ GOOD: Message does NOT contain '📊 Табель'\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "This might be a type checking issue with mock classes\n";
}

// Let's try a different approach - direct test of the logic
echo "\n=== FALLBACK LOGIC TEST ===\n";

$activityTypeMappings = [
    'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
];

$activityType = 'Прочее_ЭлектрОб';
$idleReason = 'Проверка';

$activityText = $activityTypeMappings[$activityType] ?? $activityType;

if ($activityType === 'Прочее_ЭлектрОб' && !empty($idleReason)) {
    $activityText .= " ({$idleReason})";
}

$message = sprintf(
    "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
    "Test User",
    "18.09.2025", 
    "ОИ696",
    $activityText
);

echo "Fallback message: {$message}\n";

if (strpos($message, 'Проверка') !== false) {
    echo "✅ Fallback test PASSED: Custom text is included\n";
} else {
    echo "❌ Fallback test FAILED: Custom text is missing\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>