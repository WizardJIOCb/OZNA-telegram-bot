<?php
/**
 * Clear conversation state for user and test project addition
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
    
    echo "=== Clear User State and Prepare for Project Addition ===\n\n";
    
    // Test user ID (Борис)
    $testUserId = 5261465966;
    $testChatId = $testUserId;
    
    echo "1. Current conversation state for user {$testUserId}:\n";
    
    $conversation = new Conversation($testUserId, $testChatId, '/callback');
    echo "   Before cleanup:\n";
    print_r($conversation->notes);
    echo "\n";
    
    echo "2. Clearing ALL old states:\n";
    
    // Clear all potentially conflicting states
    $conversation->notes['state'] = null;
    $conversation->notes['awaiting_custom_input'] = null;
    $conversation->notes['awaiting_date_input'] = null;
    $conversation->notes['awaiting_fio_input'] = null;
    $conversation->notes['awaiting_custom_idle_reason'] = null;
    $conversation->notes['admin_action'] = null;
    $conversation->notes['project_mod_action'] = null;
    $conversation->notes['custom_input_activity_type'] = null;
    $conversation->notes['custom_input_text'] = null;
    $conversation->notes['idle_reason'] = null;
    $conversation->notes['idle_custom_category'] = null;
    $conversation->notes['custom_idle_text'] = null;
    
    $conversation->update();
    
    echo "   ✅ State cleared\n\n";
    
    echo "3. Verifying cleanup:\n";
    
    $verifyConversation = new Conversation($testUserId, $testChatId, '/callback');
    echo "   After cleanup:\n";
    print_r($verifyConversation->notes);
    echo "\n";
    
    $hasConflictingStates = !empty($verifyConversation->notes['state']) ||
                           !empty($verifyConversation->notes['awaiting_custom_input']) ||
                           !empty($verifyConversation->notes['awaiting_date_input']) ||
                           !empty($verifyConversation->notes['awaiting_fio_input']) ||
                           !empty($verifyConversation->notes['admin_action']);
    
    if ($hasConflictingStates) {
        echo "   ❌ WARNING: Some conflicting states still present!\n\n";
    } else {
        echo "   ✅ All conflicting states cleared successfully\n\n";
    }
    
    echo "=== Next Steps ===\n";
    echo "1. Tell user to try adding project again:\n";
    echo "   - Click: Проекты → Добавить проект\n";
    echo "   - Type project name (e.g., ТЕСТ)\n\n";
    
    echo "2. Watch logs in real-time:\n";
    echo "   PowerShell: Get-Content c:\\ozna_bot\\telegram_bot_error.log -Wait -Tail 20\n\n";
    
    echo "3. Look for these log messages:\n";
    echo "   ✓ 'ProjectModerationHandler: Set state add_new for user 5261465966'\n";
    echo "   ✓ 'GenericmessageCommand handling text: ТЕСТ'\n";
    echo "   ✓ 'GenericmessageCommand: Current project_mod_action: add_new'\n";
    echo "   ✓ 'Handling project moderation input for action: add_new'\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
