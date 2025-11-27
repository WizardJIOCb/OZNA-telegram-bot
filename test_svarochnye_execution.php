<?php
// Specific test for "Сварочные" activity execution

require_once 'hook.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Сварочные Activity Execution Test ===\n\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test the specific case from the logs
$callbackData = 'category_Табель_Сварочные_yes';
$category = 'Табель_Сварочные_yes';

echo "Testing callback data: {$callbackData}\n";
echo "Testing category: {$category}\n\n";

// Test isActivityExecution method
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('isActivityExecution');
$method->setAccessible(true);
$result = $method->invoke($tabelHandler, $category);

echo "isActivityExecution result: " . ($result ? 'PASS' : 'FAIL') . "\n";

if ($result) {
    echo "✅ The 'Сварочные' activity execution is now properly recognized!\n";
    echo "✅ This should fix the mobile brigade callback handling issue.\n\n";
    
    // Test activity type extraction
    if (preg_match('/^(category_)?Табель_(.+)_yes$/', $callbackData, $matches)) {
        $activityType = $matches[2];
        echo "Extracted activity type: {$activityType}\n";
        echo "✅ Activity type correctly extracted from callback data.\n";
    }
} else {
    echo "❌ The 'Сварочные' activity execution is still not recognized.\n";
    echo "❌ There may be additional issues to investigate.\n";
}

echo "\nTest completed!\n";
?>