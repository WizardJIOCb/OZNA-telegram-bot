<?php
/**
 * Debug script to check project moderation state flow
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Conversation;

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

try {
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
    
    echo "=== DEBUG: Project Add State Check ===\n\n";
    
    // Test user ID (Борис)
    $testUserId = 5261465966;
    $testChatId = $testUserId;
    
    echo "1. Checking current conversation state for user {$testUserId}:\n";
    
    // Create conversation with same key as in code
    $conversation = new Conversation($testUserId, $testChatId, '/callback');
    
    echo "   Current conversation notes:\n";
    print_r($conversation->notes);
    echo "\n";
    
    $projectModAction = $conversation->notes['project_mod_action'] ?? null;
    echo "   project_mod_action value: " . ($projectModAction ?? 'null') . "\n";
    echo "   Is in project moderation state: " . (in_array($projectModAction, ['add_new', 'rename', 'reject']) ? 'YES' : 'NO') . "\n\n";
    
    echo "2. Simulating button press (setting state to 'add_new'):\n";
    
    // Set the state as ProjectModerationHandler would
    $conversation->notes['project_mod_action'] = 'add_new';
    $conversation->update();
    
    echo "   State set successfully\n";
    echo "   Updated conversation notes:\n";
    print_r($conversation->notes);
    echo "\n";
    
    echo "3. Re-reading conversation to verify persistence:\n";
    
    // Create new conversation instance to verify
    $verifyConversation = new Conversation($testUserId, $testChatId, '/callback');
    echo "   Verification conversation notes:\n";
    print_r($verifyConversation->notes);
    echo "\n";
    
    $verifyAction = $verifyConversation->notes['project_mod_action'] ?? null;
    echo "   project_mod_action value after reload: " . ($verifyAction ?? 'null') . "\n";
    echo "   Is in project moderation state after reload: " . (in_array($verifyAction, ['add_new', 'rename', 'reject']) ? 'YES' : 'NO') . "\n\n";
    
    echo "4. Testing state detection logic (as GenericmessageCommand would):\n";
    
    $isInProjectModerationState = in_array($verifyAction, ['add_new', 'rename', 'reject']);
    echo "   Detection result: " . ($isInProjectModerationState ? 'WILL HANDLE' : 'WILL NOT HANDLE') . "\n\n";
    
    if ($isInProjectModerationState) {
        echo "   ✅ State is correctly set and will be handled\n";
        echo "   ✅ When user sends 'ТЕСТ', it should be processed by ProjectModerationHandler\n";
    } else {
        echo "   ❌ State is NOT correctly set\n";
        echo "   ❌ User input will NOT be processed\n";
    }
    
    echo "\n5. Clearing state for cleanup:\n";
    $conversation->notes['project_mod_action'] = null;
    $conversation->update();
    echo "   State cleared\n\n";
    
    echo "=== Test Instructions ===\n";
    echo "1. Ask user to click button: Проекты → Добавить проект\n";
    echo "2. Check logs/telegram_bot_error.log for these messages:\n";
    echo "   - 'ProjectModerationHandler: Handling callback'\n";
    echo "   - 'GenericmessageCommand: Current project_mod_action: add_new'\n";
    echo "3. User should type project name (e.g., ТЕСТ)\n";
    echo "4. Check logs for:\n";
    echo "   - 'GenericmessageCommand handling text: ТЕСТ'\n";
    echo "   - 'Handling project moderation input for action: add_new'\n\n";
    
    echo "=== IMPORTANT ===\n";
    echo "The conversation state is SHARED between:\n";
    echo "  - CallbackqueryCommand (for buttons)\n";
    echo "  - GenericmessageCommand (for text input)\n";
    echo "Both use '/callback' as conversation name\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
