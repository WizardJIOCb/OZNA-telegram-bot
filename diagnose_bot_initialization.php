<?php
/**
 * Diagnostic script to check bot initialization for test notifications
 */

echo "=== Bot Initialization Diagnostic ===\n\n";

// Step 1: Check if Telegram bot framework is loaded
echo "1. Checking Telegram framework...\n";
require_once __DIR__ . '/vendor/autoload.php';

try {
    $telegram = new \Longman\TelegramBot\Telegram('dummy_token', 'test_bot');
    echo "   ✓ Telegram framework loaded successfully\n";
} catch (Exception $e) {
    echo "   ❌ Telegram framework error: " . $e->getMessage() . "\n";
}

// Step 2: Check Request class behavior without bot initialization
echo "\n2. Testing Request class without initialization...\n";
try {
    // This should fail because no bot is initialized
    $result = \Longman\TelegramBot\Request::sendMessage([
        'chat_id' => '123456789',
        'text' => 'Test message'
    ]);
    echo "   ⚠️ Request succeeded (unexpected): " . get_class($result) . "\n";
} catch (Exception $e) {
    echo "   ❌ Request failed as expected: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "   ❌ Request error: " . $e->getMessage() . "\n";
}

// Step 3: Check if we can find bot configuration
echo "\n3. Looking for bot configuration...\n";

$configFiles = [
    __DIR__ . '/config.php',
    __DIR__ . '/bot_config.php',
    __DIR__ . '/telegram_config.php'
];

$foundConfig = false;
foreach ($configFiles as $configFile) {
    if (file_exists($configFile)) {
        echo "   ✓ Found config file: " . basename($configFile) . "\n";
        $foundConfig = true;
        
        // Try to load it safely
        try {
            $config = include $configFile;
            if (is_array($config)) {
                echo "   ✓ Config loaded successfully\n";
                if (isset($config['api_key']) || isset($config['bot_token'])) {
                    echo "   ✓ Bot token found in config\n";
                } else {
                    echo "   ⚠️ No bot token found in config\n";
                }
            }
        } catch (Exception $e) {
            echo "   ⚠️ Error loading config: " . $e->getMessage() . "\n";
        }
    }
}

if (!$foundConfig) {
    echo "   ⚠️ No configuration files found\n";
}

// Step 4: Check for webhook vs standalone execution
echo "\n4. Checking execution context...\n";
if (isset($_POST['php://input']) || !empty($_POST)) {
    echo "   ✓ Running in webhook context\n";
} else {
    echo "   ⚠️ Running in standalone context (no webhook data)\n";
    echo "   This might explain why test notifications fail\n";
}

// Step 5: Test MessageService creation
echo "\n5. Testing MessageService creation...\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "   ✓ MessageService created successfully\n";
    
    // Check if it has sendMessage method
    if (method_exists($messageService, 'sendMessage')) {
        echo "   ✓ sendMessage method exists\n";
    } else {
        echo "   ❌ sendMessage method not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ MessageService error: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Summary ===\n";
echo "The issue is likely that test notifications are sent outside webhook context,\n";
echo "but the Telegram bot instance needs to be properly initialized with API token.\n";
echo "\nSolution: Create a proper bot initialization in MessageService for standalone use.\n";
?>