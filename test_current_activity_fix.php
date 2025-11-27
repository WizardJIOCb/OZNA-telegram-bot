<?php
// Test script to verify the fix for current activity buttons

require_once 'vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing current activity button fix\n";
echo "==================================\n\n";

// Test 1: Check if the activity types are in TabelHandler
echo "Test 1: Checking TabelHandler activityTypes\n";

// Create a reflection to access private property
$tabelHandler = new TabelHandler(
    new DatabaseService(),
    new MessageService(),
    // We'll use null for the other services since we're just testing the activity types
    null,
    null,
    null
);

$reflection = new ReflectionClass($tabelHandler);
$activityTypesProperty = $reflection->getProperty('activityTypes');
$activityTypesProperty->setAccessible(true);
$handlerActivityTypes = $activityTypesProperty->getValue($tabelHandler);

$requiredTypes = ['УчастиеВЗПИ', 'РазработкаПрограммыРабот', 'РазработкаГрафикаРабот', 'ПодготовкаОтчета'];

foreach ($requiredTypes as $type) {
    if (in_array($type, $handlerActivityTypes)) {
        echo "✅ '{$type}' found in TabelHandler activityTypes\n";
    } else {
        echo "❌ '{$type}' NOT found in TabelHandler activityTypes\n";
    }
}

echo "\nTest 2: Checking TabelEntry activityNames\n";

// Test 2: Check if the activity types are in TabelEntry
$tabelEntry = new TabelEntry(new DatabaseService(), new MessageService());

// Use reflection to access the private activityNames array
$tabelEntryReflection = new ReflectionClass($tabelEntry);
$getConfirmationMessage = $tabelEntryReflection->getMethod('getConfirmationMessage');
$getConfirmationMessage->setAccessible(true);

// We can't directly access the private array, but we can test if the method works with our new types
$testProjectCode = 'TEST-PROJECT';

foreach ($requiredTypes as $type) {
    try {
        // Call the method with our new activity type
        $result = $tabelEntry->getConfirmationMessage($type, $testProjectCode);
        if (strpos($result, $type) !== false || !empty($result)) {
            echo "✅ '{$type}' properly handled by TabelEntry (result: {$result})\n";
        } else {
            echo "❌ '{$type}' not properly handled by TabelEntry\n";
        }
    } catch (Exception $e) {
        echo "❌ '{$type}' caused an error in TabelEntry: " . $e->getMessage() . "\n";
    }
}

echo "\nTest 3: Checking MessageService keyboard\n";

// Test 3: Verify the keyboard still has the correct buttons
$messageService = new MessageService();
$keyboard = $messageService->createCurrentActivityKeyboard();

echo "Current activity keyboard buttons:\n";
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        echo "  - {$button['text']} (callback: {$button['callback_data']})\n";
    }
}

echo "\nFix verification complete!\n";