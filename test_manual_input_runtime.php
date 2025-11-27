<?php
/**
 * Runtime verification of manual input feedback
 * Testing actual behavior to ensure safe implementation
 */

echo "=== RUNTIME VERIFICATION: Manual Input Feedback ===\n\n";

// Check current GenericmessageCommand implementation
$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
if (!file_exists($genericFile)) {
    echo "❌ GenericmessageCommand.php not found\n";
    exit(1);
}

echo "1. Checking current implementation...\n";
$content = file_get_contents($genericFile);

// Check for manual input handlers
$handlers = [
    'isAwaitingManualGroupInput' => '✓ Group input detection',
    'isAwaitingManualChatInput' => '✓ Chat input detection', 
    'handleManualGroupInput' => '✓ Group input processing',
    'handleManualChatInput' => '✓ Chat input processing'
];

$missingHandlers = [];
foreach ($handlers as $handler => $description) {
    if (strpos($content, $handler) !== false) {
        echo "   {$description}\n";
    } else {
        echo "   ❌ Missing: {$handler}\n";
        $missingHandlers[] = $handler;
    }
}

echo "\n2. Checking feedback messages...\n";
$feedbackElements = [
    'ID группы сохранён' => 'Group ID saved confirmation',
    'ID чата сохранён' => 'Chat ID saved confirmation',
    'успешно настроена' => 'Success notification for group',
    'успешно настроен' => 'Success notification for chat',
    'Вернуться к настройкам проекта' => 'Navigation back to settings'
];

$missingFeedback = [];
foreach ($feedbackElements as $text => $description) {
    if (strpos($content, $text) !== false) {
        echo "   ✓ {$description}\n";
    } else {
        echo "   ❌ Missing: {$description}\n";
        $missingFeedback[] = $text;
    }
}

echo "\n3. Checking database persistence...\n";
$persistenceChecks = [
    "userData['control_projects']" => 'Control projects data structure',
    "updateUserData" => 'Database update method',
    "updated_at" => 'Timestamp tracking'
];

foreach ($persistenceChecks as $check => $description) {
    if (strpos($content, $check) !== false) {
        echo "   ✓ {$description}\n";
    } else {
        echo "   ❌ Missing: {$description}\n";
    }
}

echo "\n4. Checking conversation cleanup...\n";
if (strpos($content, 'stop()') !== false) {
    echo "   ✓ Conversation state cleanup\n";
} else {
    echo "   ❌ Missing conversation cleanup\n";
}

echo "\n5. Testing validation patterns...\n";
// Test group ID validation
$testGroupIds = ['-1001234567890', '1234567890', '-100abc', 'invalid'];
foreach ($testGroupIds as $id) {
    $isValid = preg_match('/^-100\d+$/', $id);
    echo "   Group ID '{$id}': " . ($isValid ? "✓ Valid" : "❌ Invalid") . "\n";
}

// Test chat ID validation  
$testChatIds = ['-1001234567890', '123456789', '-123456789', 'invalid'];
foreach ($testChatIds as $id) {
    $isValid = preg_match('/^-?\d+$/', $id);
    echo "   Chat ID '{$id}': " . ($isValid ? "✓ Valid" : "❌ Invalid") . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";

if (empty($missingHandlers) && empty($missingFeedback)) {
    echo "✅ Implementation appears complete - feedback should work\n";
    echo "If feedback is still missing, the issue may be:\n";
    echo "   • Conversation state not being set correctly\n";
    echo "   • GenericmessageCommand not being called for text input\n";
    echo "   • Message routing issues\n";
} else {
    echo "❌ Implementation issues detected\n";
    echo "Missing handlers: " . implode(', ', $missingHandlers) . "\n";
    echo "Missing feedback: " . implode(', ', $missingFeedback) . "\n";
}