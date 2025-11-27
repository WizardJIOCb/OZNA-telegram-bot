<?php
// Comprehensive real-time test for ШМР technological equipment button

echo "=== REAL-TIME ШМР TECHNOLOGICAL EQUIPMENT TEST ===\n";
echo "==================================================\n\n";

// Simulate the exact Telegram callback that would be sent
$simulatedWebhookData = [
    'update_id' => 999999999,
    'callback_query' => [
        'id' => '999999999999999999',
        'from' => [
            'id' => 230379668,
            'is_bot' => false,
            'first_name' => 'Test',
            'username' => 'testuser',
            'language_code' => 'ru'
        ],
        'message' => [
            'message_id' => 99999,
            'from' => [
                'id' => 7902640773,
                'is_bot' => true,
                'first_name' => 'OznaWork',
                'username' => 'OznaWork_bot'
            ],
            'chat' => [
                'id' => 230379668,
                'first_name' => 'Test',
                'username' => 'testuser',
                'type' => 'private'
            ],
            'date' => time(),
            'text' => 'Some menu text with buttons'
        ],
        'data' => 'cat_ШМР_ТехОб'  // This is the critical callback data
    ]
];

echo "1. Simulated webhook data:\n";
echo "   Callback data: " . $simulatedWebhookData['callback_query']['data'] . "\n";
echo "   User ID: " . $simulatedWebhookData['callback_query']['from']['id'] . "\n";
echo "   Chat ID: " . $simulatedWebhookData['callback_query']['message']['chat']['id'] . "\n\n";

// Create a temporary test file to process this webhook
$testWebhookFile = __DIR__ . '/test_webhook_process.php';

$testCode = '<?php
// Test webhook processing for ШМР technological equipment

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/test_webhook_error.log");

// Clear any existing test logs
file_put_contents(__DIR__ . "/test_webhook_error.log", "=== ШМР TECH EQUIPMENT TEST START ===\n");

try {
    // Load required files
    require __DIR__ . "/vendor/autoload.php";
    require_once __DIR__ . "/custom_autoload.php";
    require_once __DIR__ . "/Custome/DB.php";

    $bot_api_key = "7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ";
    $bot_username = "OznaWork_bot";

    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Enable MySQL if available
    if (extension_loaded("pdo_mysql")) {
        $mysql_credentials = [
            "host" => "82.146.42.213",
            "port" => 3306,
            "user" => "WizardJIOCb",
            "password" => "7qQ#28051961",
            "database" => "tg_bot",
        ];
        $telegram->enableMySql($mysql_credentials);
    }

    // Add commands path
    $telegram->addCommandsPaths(array(__DIR__ . "/CustomCommands"));

    // Enable admins
    $telegram->enableAdmins([230379668, 5261465966]);

    echo "Telegram bot initialized successfully\n";
    error_log("Telegram bot initialized for ШМР test");

    // Process the callback
    $input = json_encode(' . var_export($simulatedWebhookData, true) . ');
    file_put_contents("php://input", $input);
    
    error_log("Processing ШМР technological equipment callback: cat_ШМР_ТехОб");
    $result = $telegram->handle();
    
    if ($result->isOk()) {
        echo "✅ Webhook processed successfully!\n";
        error_log("✅ ШМР test webhook processed successfully");
    } else {
        echo "❌ Webhook processing failed: " . $result->getDescription() . "\n";
        error_log("❌ ШМР test webhook failed: " . $result->getDescription());
    }

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    error_log("❌ ШМР test exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
} catch (Error $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    error_log("❌ ШМР test fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
}

error_log("=== ШМР TECH EQUIPMENT TEST END ===");
?>';

file_put_contents($testWebhookFile, $testCode);

echo "2. Created test webhook processor: test_webhook_process.php\n\n";

echo "3. Running test webhook...\n";
echo "   This will simulate exactly what happens when you click the button.\n\n";

// Run the test
exec("php test_webhook_process.php 2>&1", $output, $returnCode);

echo "4. Test Results:\n";
echo "   Return code: $returnCode\n";
echo "   Output:\n";
foreach ($output as $line) {
    echo "   " . $line . "\n";
}

echo "\n5. Checking test logs...\n";
if (file_exists(__DIR__ . '/test_webhook_error.log')) {
    $logContent = file_get_contents(__DIR__ . '/test_webhook_error.log');
    echo "   Log content:\n";
    $logLines = explode("\n", $logContent);
    foreach ($logLines as $line) {
        if (!empty(trim($line))) {
            echo "   " . $line . "\n";
        }
    }
} else {
    echo "   No log file generated\n";
}

// Clean up
unlink($testWebhookFile);
if (file_exists(__DIR__ . '/test_webhook_error.log')) {
    unlink(__DIR__ . '/test_webhook_error.log');
}

echo "\n6. Test completed!\n";
echo "   If you see '🔧🔧🔧 CRITICAL DEBUG:' messages in the logs,\n";
echo "   the handler is being reached and working correctly.\n";
echo "   If not, there's still an issue with the routing.\n";
?>