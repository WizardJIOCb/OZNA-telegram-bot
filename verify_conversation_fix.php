<?php
// Simple verification that our conversation fix is in place

echo "Verifying conversation fix...\n\n";

// Read the CallbackqueryCommand file
$callbackContent = file_get_contents(__DIR__ . '/CustomCommands/CallbackqueryCommand.php');

// Check for the correct conversation creation
if (strpos($callbackContent, '$callback_query->getFrom()->getId()') !== false) {
    echo "✓ Conversation created with user ID (correct)\n";
} else {
    echo "✗ Conversation not created with user ID\n";
}

if (strpos($callbackContent, '$callback_query->getMessage()->getChat()->getId()') !== false) {
    echo "✓ Conversation created with chat ID (correct)\n";
} else {
    echo "✗ Conversation not created with chat ID\n";
}

// Check that both parameters are present
$pattern = '/new Conversation\([^,]+,[^,]+,[^)]+\)/';
if (preg_match($pattern, $callbackContent)) {
    echo "✓ Conversation creation syntax is correct\n";
} else {
    echo "✗ Conversation creation syntax is incorrect\n";
}

// Check the GenericmessageCommand for consistency
$genericContent = file_get_contents(__DIR__ . '/CustomCommands/GenericmessageCommand.php');

if (strpos($genericContent, 'new Conversation($userId, $chatId, \'/callback\')') !== false) {
    echo "✓ GenericmessageCommand conversation creation is consistent\n";
} else {
    echo "✗ GenericmessageCommand conversation creation is inconsistent\n";
}

echo "\nVerification complete.\n";