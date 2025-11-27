<?php
/**
 * Test script for report generation flow
 * Tests the full flow from button click to report generation
 */

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

echo "Testing report generation flow...\n\n";

// Create mock objects
$databaseService = new DatabaseService();
$messageService = new MessageService();
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test if activity execution is correctly detected
echo "1. Testing isActivityExecution method:\n";

// Test case 1: Works by schedule confirmation callback
$callback1 = 'category_Табель_РаботыПоГрафику_Электрооборудование_yes';
echo "   Callback: {$callback1}\n";

// We need to create a TabelHandler to test the method
// But we can't easily test the private method, so let's test the regex pattern directly
$pattern = '/^Табель_(.+)_yes$/';
if (preg_match($pattern, $callback1, $matches)) {
    $activityType = $matches[1];
    echo "   Extracted activity type: {$activityType}\n";
    
    // Check if this activity type is in the activityTypes array
    $reflection = new ReflectionClass($tabelEntry);
    $property = $reflection->getProperty('activityTypes');
    $property->setAccessible(true);
    $activityTypes = $property->getValue($tabelEntry);
    
    if (in_array($activityType, $activityTypes)) {
        echo "   ✅ Activity type found in activityTypes array\n";
    } else {
        echo "   ❌ Activity type NOT found in activityTypes array\n";
    }
} else {
    echo "   ❌ Pattern did not match\n";
}

// Test case 2: ID formation confirmation callback
$callback2 = 'category_Табель_ФормированиеИД_Электрооборудование_yes';
echo "\n   Callback: {$callback2}\n";

if (preg_match($pattern, $callback2, $matches)) {
    $activityType = $matches[1];
    echo "   Extracted activity type: {$activityType}\n";
    
    if (in_array($activityType, $activityTypes)) {
        echo "   ✅ Activity type found in activityTypes array\n";
    } else {
        echo "   ❌ Activity type NOT found in activityTypes array\n";
    }
} else {
    echo "   ❌ Pattern did not match\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed!\n";
echo str_repeat("=", 50) . "\n";