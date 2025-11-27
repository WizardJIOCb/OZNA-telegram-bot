<?php
// Test script to check command loading and execution

echo "Testing command loading...\n";

// Check if commands exist
$commands = [
    'GenericmessageCommand' => __DIR__ . '/CustomCommands/GenericmessageCommand.php',
    'TextmenuCommand' => __DIR__ . '/CustomCommands/TextmenuCommand.php'
];

foreach ($commands as $name => $path) {
    if (file_exists($path)) {
        echo "✓ $name exists at $path\n";
        
        // Read the file to check the command name
        $content = file_get_contents($path);
        if (preg_match('/protected \$name = [\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            echo "  Command name: " . $matches[1] . "\n";
        }
    } else {
        echo "✗ $name does not exist at $path\n";
    }
}

echo "\nIn the Telegram bot framework:\n";
echo "- Text messages are handled by the command with name 'genericmessage'\n";
echo "- If both GenericmessageCommand and TextmenuCommand have the same name, there might be conflicts\n";
echo "- If TextmenuCommand has a different name, it might not be used for text messages\n";

echo "\nAccording to project specifications:\n";
echo "- TextmenuCommand should handle reply keyboard interactions\n";
echo "- GenericmessageCommand should not intercept reply keyboard messages\n";
echo "- Both commands should coexist without conflicts\n";
?>