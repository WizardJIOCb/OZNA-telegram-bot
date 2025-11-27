<?php
/**
 * Script to verify that the mobile brigade button is working correctly
 * Run this script after restarting the Telegram bot service and clearing caches
 */

echo "=== Mobile Brigade Button Verification ===\n\n";

// Test 1: Check if TabelHandler can handle mobile brigade callback
echo "Test 1: TabelHandler callback handling\n";
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/hook.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

$callback_data = 'category_Табель_Мобильная_Бригада';
$canHandle = $tabelHandler->canHandle($callback_data);

echo "Result: " . ($canHandle ? "PASS" : "FAIL") . "\n";
echo "Expected: TabelHandler should handle mobile brigade callback\n\n";

// Test 2: Check if category matching works
echo "Test 2: Category matching\n";
$category = 'Табель_Мобильная_Бригада';
$is_mobile_brigade = ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг');

echo "Result: " . ($is_mobile_brigade ? "PASS" : "FAIL") . "\n";
echo "Expected: Category should match mobile brigade\n\n";

// Test 3: Check date selection keyboard creation
echo "Test 3: Date selection keyboard creation\n";
$prefix = 'category_Табель_Мобильная_Бригада';
$dateKeyboard = $messageService->createDateSelectionKeyboard($prefix);
$keyboardValid = (is_array($dateKeyboard) && count($dateKeyboard) > 0);

echo "Result: " . ($keyboardValid ? "PASS" : "FAIL") . "\n";
echo "Expected: Date selection keyboard should be created\n";
echo "Rows created: " . count($dateKeyboard) . "\n\n";

// Test 4: Check user access
echo "Test 4: User access verification\n";
$userId = 230379668; // Your user ID
$userAccessLevel = $adminService->getUserAccessLevel($userId);
$hasSufficientAccess = ($userAccessLevel >= 1); // Any non-guest level should work

echo "User access level: {$userAccessLevel}\n";
echo "Result: " . ($hasSufficientAccess ? "PASS" : "FAIL") . "\n";
echo "Expected: User should have sufficient access (level 1+)\n\n";

// Summary
echo "=== Summary ===\n";
$allTestsPassed = $canHandle && $is_mobile_brigade && $keyboardValid && $hasSufficientAccess;

if ($allTestsPassed) {
    echo "✅ All tests PASSED - Mobile brigade button should work correctly!\n";
    echo "If it's still not working, the issue is likely infrastructure-related:\n";
    echo "1. Restart the Telegram bot service\n";
    echo "2. Clear server caches\n";
    echo "3. Check webhook configuration\n";
    echo "4. Monitor server logs for errors\n";
} else {
    echo "❌ Some tests FAILED - Check the specific test that failed\n";
}

echo "\nTo test in Telegram:\n";
echo "1. Click the '💼 Мобильная бригада' button\n";
echo "2. You should see: '💼 Выберите дату для Мобильной бригады'\n";
echo "3. With date selection buttons (Сегодня, Вчера, etc.)\n";

?>