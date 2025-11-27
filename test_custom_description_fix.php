<?php
/**
 * Test script to verify the fix for custom description in TabelEntry
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== Test Custom Description Fix ===\n\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test the createTabelMessage method with custom input for "Прочее" activity
echo "1. Testing createTabelMessage with 'Прочее_Электрооборудование' and custom description...\n";

try {
    $fio = "Иванов Иван Иванович";
    $date = "10.09.2025";
    $projectCode = "ОПНВГ-001";
    $activityType = "Прочее_Электрооборудование";
    $customDescription = "Тестовая работа по наладке оборудования";
    
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $customDescription);
    
    echo "   Generated message:\n";
    echo "   " . $message . "\n";
    
    // Check if the custom description is included in the message
    if (strpos($message, $customDescription) !== false) {
        echo "   ✓ Custom description is included in the message\n";
    } else {
        echo "   ❌ Custom description is missing from the message\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing createTabelMessage with 'Простой' activity and idle reason...\n";

try {
    $fio = "Иванов Иван Иванович";
    $date = "10.09.2025";
    $projectCode = "ОПНВГ-001";
    $activityType = "Простой";
    $idleReason = "Отсутствие материалов";
    
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
    
    echo "   Generated message:\n";
    echo "   " . $message . "\n";
    
    // Check if the idle reason is included in the message
    if (strpos($message, $idleReason) !== false) {
        echo "   ✓ Idle reason is included in the message\n";
    } else {
        echo "   ❌ Idle reason is missing from the message\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing createTabelMessage with regular activity (no custom input)...\n";

try {
    $fio = "Иванов Иван Иванович";
    $date = "10.09.2025";
    $projectCode = "ОПНВГ-001";
    $activityType = "Электромонтажные";
    $idleReason = ""; // No custom input
    
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
    
    echo "   Generated message:\n";
    echo "   " . $message . "\n";
    
    // Check that the message is generated correctly
    if (!empty($message)) {
        echo "   ✓ Message generated correctly\n";
    } else {
        echo "   ❌ Message is empty\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "The fix should now include custom descriptions in timesheet entries for 'Прочее' activities.\n";