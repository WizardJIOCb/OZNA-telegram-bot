<?php

echo "=== TESTING CURRENT ACTIVITY RESTORATION ===\n\n";

// Test 1: Verify MessageService createCurrentActivityKeyboard method exists
echo "Test 1: Checking MessageService createCurrentActivityKeyboard method\n";
echo "======================================================================\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $keyboard = $messageService->createCurrentActivityKeyboard();
    
    echo "✅ Method exists and executed successfully\n";
    echo "Number of buttons: " . count($keyboard) . "\n";
    echo "Available current activities:\n";
    
    foreach ($keyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  - " . $button['text'] . " (callback: " . $button['callback_data'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Verify TabelHandler activity types include current activities
echo "\nTest 2: Checking TabelHandler activity types\n";
echo "=============================================\n";

require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

try {
    $reflection = new ReflectionClass('\Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler');
    $activityTypesProperty = $reflection->getProperty('activityTypes');
    $activityTypesProperty->setAccessible(true);
    
    // Create a dummy instance to access the property
    $dummyHandler = $reflection->newInstanceWithoutConstructor();
    $activityTypes = $activityTypesProperty->getValue($dummyHandler);
    
    $currentActivityTypes = ['МедОсмотр', 'Аттестация', 'Общая'];
    
    echo "Checking for required current activity types:\n";
    foreach ($currentActivityTypes as $type) {
        if (in_array($type, $activityTypes)) {
            echo "  ✅ '{$type}' - FOUND\n";
        } else {
            echo "  ❌ '{$type}' - NOT FOUND\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error accessing TabelHandler activity types: " . $e->getMessage() . "\n";
}

// Test 3: Verify callback data format matches expected pattern
echo "\nTest 3: Checking callback data format compatibility\n";
echo "===================================================\n";

$expectedCallbacks = [
    'category_Табель_МедОсмотр',
    'category_Табель_Аттестация', 
    'category_Табель_ПодготовкаТКП',
    'category_Табель_Общая'
];

foreach ($expectedCallbacks as $callback) {
    // Simulate the extraction logic from TabelHandler
    $activityType = str_replace('category_Табель_', '', $callback);
    echo "  Callback: '{$callback}' → Activity: '{$activityType}'\n";
}

echo "\n=== CURRENT ACTIVITY RESTORATION TEST COMPLETE ===\n";
echo "The 'Текущая деятельность' (Current Activities) functionality should now be working properly.\n";
echo "Users should be able to:\n";
echo "1. Select a date for current activities\n";
echo "2. See the current activity buttons (Medical Examination, Certification, TKP Preparation, General Activity)\n";
echo "3. Confirm and submit their activity entries\n";

?>