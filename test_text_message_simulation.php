<?php
/**
 * Simulate text message to test GenericmessageCommand
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Telegram;

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

try {
    echo "=== Simulating Text Message to GenericmessageCommand ===\n\n";
    
    // Create Telegram API object
    $telegram = new Telegram($bot_api_key, $bot_username);
    
    // Enable MySQL
    $mysql_credentials = [
        'host'     => '82.146.42.213',
        'port'     => 3306,
        'user'     => 'WizardJIOCb',
        'password' => '7qQ#28051961',
        'database' => 'tg_bot',
    ];
    $telegram->enableMySql($mysql_credentials);
    
    // Add commands path
    $telegram->addCommandsPaths(array(__DIR__ . '/CustomCommands'));
    
    echo "1. Checking if GenericmessageCommand is registered...\n";
    
    // Simulate incoming text message JSON
    $testUserId = 5261465966; // Борис
    $testMessage = "ТЕСТ_СИМУЛЯЦИЯ";
    
    $update_json = json_encode([
        'update_id' => 999999999,
        'message' => [
            'message_id' => 999999,
            'from' => [
                'id' => $testUserId,
                'is_bot' => false,
                'first_name' => 'Борис',
                'username' => 'test_user'
            ],
            'chat' => [
                'id' => $testUserId,
                'first_name' => 'Борис',
                'username' => 'test_user',
                'type' => 'private'
            ],
            'date' => time(),
            'text' => $testMessage
        ]
    ]);
    
    echo "2. Simulated update JSON:\n";
    echo "   " . $update_json . "\n\n";
    
    echo "3. Processing update...\n";
    
    // Process the update using handleGetUpdates (simulating webhook)
    $_POST['php://input'] = $update_json;
    
    // Directly call handle with the JSON
    $result = $telegram->handle(json_decode($update_json, true));
    
    echo "\n4. Result:\n";
    var_dump($result);
    
    echo "\n5. Check error log for GenericmessageCommand logs\n";
    echo "   Look for: 'CUSTOM GenericmessageCommand EXECUTE START'\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
