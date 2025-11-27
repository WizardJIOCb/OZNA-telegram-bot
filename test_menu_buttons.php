<?php
require_once __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

// Create Telegram API object
$telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

// Commands path
$telegram->addCommandsPaths(array(__DIR__ . '/CustomCommands'));

echo "=== Registered Commands Check ===\n\n";

// Get all command files
$commandFiles = glob(__DIR__ . '/CustomCommands/*.php');
echo "Found " . count($commandFiles) . " PHP files in CustomCommands/\n\n";

// Check for genericmessage command
echo "Looking for 'genericmessage' command handler:\n";
$found = false;
foreach ($commandFiles as $file) {
    $filename = basename($file);
    if (strpos($filename, 'Generic') !== false || strpos($filename, 'Textmenu') !== false) {
        echo "  - File: {$filename}\n";
        
        // Read the file and check command name
        $content = file_get_contents($file);
        if (preg_match('/protected\s+\$name\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            echo "    Command name: '{$matches[1]}'\n";
            if ($matches[1] === 'genericmessage') {
                echo "    ✅ This is the genericmessage handler!\n";
                $found = true;
            }
        }
        echo "\n";
    }
}

if (!$found) {
    echo "❌ ERROR: No command with name 'genericmessage' found!\n";
} else {
    echo "✅ SUCCESS: Found genericmessage command handler\n";
}

echo "\n=== Test Result ===\n";
if ($found) {
    echo "The bot should now handle menu button presses correctly.\n";
    echo "Try pressing a menu button in Telegram.\n";
} else {
    echo "ERROR: Menu buttons won't work without a 'genericmessage' command!\n";
}
