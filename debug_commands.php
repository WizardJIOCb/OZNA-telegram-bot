<?php
// Debug script to check which commands are being loaded and executed

echo "Debugging command execution...\n";

// Check if both commands exist
$genericMessageCommand = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
$textMenuCommand = __DIR__ . '/CustomCommands/TextmenuCommand.php';

if (file_exists($genericMessageCommand)) {
    echo "✓ GenericmessageCommand.php exists\n";
} else {
    echo "✗ GenericmessageCommand.php does not exist\n";
}

if (file_exists($textMenuCommand)) {
    echo "✓ TextmenuCommand.php exists\n";
} else {
    echo "✗ TextmenuCommand.php does not exist\n";
}

// Check command names in both files
echo "\nChecking command names:\n";

if (file_exists($genericMessageCommand)) {
    $content = file_get_contents($genericMessageCommand);
    if (preg_match('/protected \$name = [\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        echo "GenericmessageCommand name: " . $matches[1] . "\n";
    }
}

if (file_exists($textMenuCommand)) {
    $content = file_get_contents($textMenuCommand);
    if (preg_match('/protected \$name = [\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        echo "TextmenuCommand name: " . $matches[1] . "\n";
    }
}

echo "\nChecking if commands are properly registered...\n";
echo "The Telegram bot framework should automatically load commands from the CustomCommands directory.\n";
echo "Commands are typically selected based on their name and the type of message received.\n";

echo "\nFor text messages that don't match a specific command pattern,\n";
echo "the framework should use a generic message command.\n";
echo "The command with the name 'genericmessage' should handle these messages.\n";

echo "\nFor reply keyboard selections, if TextmenuCommand is properly implemented,\n";
echo "it should intercept these messages before GenericmessageCommand.\n";

echo "\nIf both commands exist and are properly named, the issue might be:\n";
echo "1. Command loading order or priority\n";
echo "2. Message routing logic in the Telegram bot framework\n";
echo "3. Issues with the webhook or message processing\n";
?>