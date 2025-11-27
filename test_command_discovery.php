<?php
/**
 * Test if GenericmessageCommand is discovered and loaded
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

try {
    echo "=== Testing GenericmessageCommand Discovery ===\n\n";
    
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    // Add commands path
    $telegram->addCommandsPaths(array(__DIR__ . '/CustomCommands'));
    
    echo "1. Commands path added\n\n";
    
    echo "2. Trying to get GenericmessageCommand object...\n";
    
    // Try to get the command using reflection
    $reflection = new ReflectionClass($telegram);
    $method = $reflection->getMethod('getCommandObject');
    $method->setAccessible(true);
    
    $commandObj = $method->invoke($telegram, 'genericmessage');
    
    if ($commandObj) {
        echo "   ✅ SUCCESS: GenericmessageCommand found!\n";
        echo "   Class: " . get_class($commandObj) . "\n";
        echo "   File: " . (new ReflectionClass($commandObj))->getFileName() . "\n";
        
        // Check if it's our custom version
        $classFile = (new ReflectionClass($commandObj))->getFileName();
        if (strpos($classFile, 'CustomCommands') !== false) {
            echo "   ✅ This is OUR custom version!\n";
        } else {
            echo "   ❌ This is VENDOR version!\n";
        }
    } else {
        echo "   ❌ PROBLEM: GenericmessageCommand NOT found!\n";
        echo "   This means the command won't be executed for text messages!\n";
    }
    
    echo "\n3. Getting all commands list...\n";
    
    $getCommandsList = $reflection->getMethod('getCommandsList');
    $getCommandsList->setAccessible(true);
    $commandsList = $getCommandsList->invoke($telegram);
    
    echo "   Total commands loaded: " . count($commandsList) . "\n";
    
    if (isset($commandsList['genericmessage'])) {
        echo "   ✅ 'genericmessage' is in commands list\n";
        echo "   Class: " . get_class($commandsList['genericmessage']) . "\n";
    } else {
        echo "   ❌ 'genericmessage' is NOT in commands list!\n";
        echo "   Available system commands:\n";
        foreach ($commandsList as $name => $cmd) {
            if ($cmd instanceof \Longman\TelegramBot\Commands\SystemCommand) {
                echo "      - {$name}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
