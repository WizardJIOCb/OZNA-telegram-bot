<?php
// Test script to check the automation equipment confirmation functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Testing Automation Equipment Confirmation ===\n";

// Test creating the confirmation keyboard
$messageService = new MessageService();

// Test with "Работы по графику" automation equipment
$category = 'Табель_РабПоГр_Автоматизация';
echo "Testing category: $category\n";

// Extract the activity type
$strippedCategory = 'РабПоГр_Автом';
echo "Stripped category: $strippedCategory\n";

// Create confirmation keyboard
$confirmCallbackData = "category_Табель_{$strippedCategory}_yes";
$keyboard = $messageService->createConfirmationKeyboard($confirmCallbackData);

echo "Confirmation keyboard created:\n";
echo "Number of rows: " . count($keyboard) . "\n";

// Display each row and button
foreach ($keyboard as $rowIndex => $row) {
    echo "Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $buttonIndex => $button) {
        echo "  Button " . ($buttonIndex + 1) . ": " . $button['text'] . "\n";
        if (isset($button['callback_data'])) {
            echo "    Callback: " . $button['callback_data'] . "\n";
        }
    }
}

// Test the confirmation message
$databaseService = new DatabaseService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

$projectCode = 'TEST-001';
$confirmationText = $tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
echo "Confirmation text: $confirmationText\n";

// Test the handleActivityExecution function simulation
function handleActivityExecution($category, $projectCode) {
    global $tabelEntry;
    
    echo "In handleActivityExecution for category: $category\n";
    
    // Extract activity type
    $activityType = '';
    if (preg_match('/^Табель_(.+)_yes$/', $category, $matches)) {
        $activityType = $matches[1];
    }
    
    echo "Extracted activity type: $activityType\n";
    
    // Create and send tabel entry
    $fio = 'Test User';
    $date = date('d.m.Y');
    $chatId = 'test_chat_id';
    $idleReason = '';
    
    // Simulate creating the message
    $message = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
    echo "Created message: $message\n";
    
    // Simulate sending message
    echo "Would send message to chat $chatId\n";
    
    return "Success";
}

// Test with the confirmation callback
$confirmationCallback = "category_Табель_{$strippedCategory}_yes";
$result = handleActivityExecution($confirmationCallback, $projectCode);
echo "Function result: $result\n";

echo "\nTest completed!\n";
?>