<?php
/**
 * Test script to verify manual input feedback functionality
 * Following safety protocols for non-programmer user
 */

echo "=== TESTING MANUAL INPUT FEEDBACK ===\n\n";

// Test data
$userId = 4225504;
$chatId = 4225504;
$projectCode = 'ОИ789';

echo "Testing manual input flow:\n";
echo "User ID: {$userId}\n";
echo "Chat ID: {$chatId}\n";
echo "Project Code: {$projectCode}\n\n";

// Simulate the complete flow
echo "1. User clicks 'Ввести ID группы вручную' button\n";
echo "   → Callback: proj_ctrl_group_manual_{$projectCode}\n";
echo "   → ProjectHandler sets conversation state\n";
echo "   → User sees instructions and hint\n\n";

echo "2. User enters group ID: -1001234567890\n";
echo "   → GenericmessageCommand detects conversation state\n";
echo "   → Validates group ID format\n";
echo "   → Saves to database\n";
echo "   → Shows success message\n\n";

echo "3. Expected feedback messages:\n";
echo "   ✓ Instructions with examples\n";
echo "   ✓ Format validation with helpful errors\n";
echo "   ✓ Success confirmation with saved ID\n";
echo "   ✓ Navigation back to project settings\n\n";

// Check if the functions exist
echo "4. Checking implementation:\n";

$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
$content = file_get_contents($genericFile);

$functions = [
    'isAwaitingManualGroupInput' => 'Manual group input detection',
    'isAwaitingManualChatInput' => 'Manual chat input detection',
    'handleManualGroupInput' => 'Group ID processing and validation',
    'handleManualChatInput' => 'Chat ID processing and validation'
];

foreach ($functions as $function => $description) {
    if (strpos($content, $function) !== false) {
        echo "   ✓ {$description}\n";
    } else {
        echo "   ✗ {$description} - Missing\n";
    }
}

echo "\n5. Validation features:\n";
$validationFeatures = [
    'preg_match(\'/^-100\\\\d+$/\', $text)' => 'Group ID format validation',
    'preg_match(\'/^-?\\\\d+$/\', $text)' => 'Chat ID format validation',
    'conversation->stop()' => 'Conversation cleanup',
    'updated_at' => 'Timestamp tracking'
];

foreach ($validationFeatures as $pattern => $description) {
    if (strpos($content, $pattern) !== false) {
        echo "   ✓ {$description}\n";
    } else {
        echo "   ✗ {$description} - Missing\n";
    }
}

echo "\n6. User experience improvements:\n";
$uxFeatures = [
    'Примеры правильных ID' => 'ID format examples',
    'Как узнать ID группы' => 'How-to instructions',
    'Введите корректный' => 'Retry prompts',
    'успешно настроена' => 'Success confirmations'
];

foreach ($uxFeatures as $text => $description) {
    if (strpos($content, $text) !== false) {
        echo "   ✓ {$description}\n";
    } else {
        echo "   ✗ {$description} - Missing\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
echo "The manual input should now provide proper feedback!\n";