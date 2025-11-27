<?php
/**
 * BOT STATUS DIAGNOSTIC
 * Checks if the bot is properly configured and receiving updates
 */

echo "🤖 TELEGRAM BOT STATUS DIAGNOSTIC\n";
echo "=================================\n\n";

// Check if bot files exist
echo "1. BOT FILES CHECK:\n";
echo "===================\n";

$criticalFiles = [
    'bot.php' => __DIR__ . '/bot.php',
    'webhook.php' => __DIR__ . '/webhook.php',
    'TabelHandler.php' => __DIR__ . '/CustomCommands/Handlers/TabelHandler.php',
    'CallbackHandlerFactory.php' => __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php',
    'TestCommand.php' => __DIR__ . '/CustomCommands/UserCommands/TestCommand.php'
];

foreach ($criticalFiles as $name => $path) {
    if (file_exists($path)) {
        echo "✅ {$name}: EXISTS\n";
    } else {
        echo "❌ {$name}: MISSING - {$path}\n";
    }
}

echo "\n2. COMMAND REGISTRATION CHECK:\n";
echo "==============================\n";

$commandDir = __DIR__ . '/CustomCommands/UserCommands/';
if (is_dir($commandDir)) {
    $commands = scandir($commandDir);
    $phpCommands = array_filter($commands, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'php';
    });
    
    echo "Found " . count($phpCommands) . " command files:\n";
    foreach ($phpCommands as $command) {
        echo "• {$command}\n";
    }
} else {
    echo "❌ Commands directory not found\n";
}

echo "\n3. BOT CONFIGURATION CHECK:\n";
echo "===========================\n";

// Check if config file exists
$configFiles = [
    __DIR__ . '/config.php',
    __DIR__ . '/config/config.php',
    __DIR__ . '/bot_config.php'
];

$configFound = false;
foreach ($configFiles as $configFile) {
    if (file_exists($configFile)) {
        echo "✅ Config file found: " . basename($configFile) . "\n";
        $configFound = true;
        break;
    }
}

if (!$configFound) {
    echo "❌ No config file found\n";
}

echo "\n4. CALLBACK ISSUE ANALYSIS:\n";
echo "===========================\n";
echo "Since /testmasterduties and /cancel don't work:\n\n";

echo "POSSIBLE CAUSES:\n";
echo "• Bot token invalid or expired\n";
echo "• Webhook not set up properly\n";
echo "• Server not receiving updates from Telegram\n";
echo "• PHP errors preventing command execution\n";
echo "• Commands not registered correctly\n";
echo "• Bot not started/running\n\n";

echo "IMMEDIATE TESTS:\n";
echo "1. Try a simple command: /test\n";
echo "2. Try built-in commands: /help or /start\n";
echo "3. Check if other users can use the bot\n";
echo "4. Check server error logs\n\n";

echo "MASTER DUTIES SPECIFIC:\n";
echo "If simple commands work but buttons don't:\n";
echo "• Callback handling is broken\n";
echo "• CallbackHandlerFactory not receiving callbacks\n";
echo "• TabelHandler registration issue\n";
echo "• Silent PHP error in callback processing\n\n";

echo "5. NEXT STEPS:\n";
echo "==============\n";
echo "1. Test: Send '/test' to the bot\n";
echo "2. If /test works: Callback handling issue\n";
echo "3. If /test doesn't work: Bot configuration issue\n";
echo "4. Check if ANY command works (/help, /start)\n";
echo "5. Try from different Telegram account\n\n";

echo "Diagnostic completed: " . date('Y-m-d H:i:s') . "\n";
?>