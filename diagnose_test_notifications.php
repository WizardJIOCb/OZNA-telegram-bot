<?php
/**
 * Diagnostic script for test notification delivery issues
 * Following safety protocols for non-programmer user
 */

echo "=== DIAGNOSING TEST NOTIFICATION DELIVERY ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$userId = 4225504;

echo "Checking test notification delivery for user: {$userId}\n\n";

echo "1. Checking user's control projects configuration...\n";

try {
    // Get user data
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $userData = $databaseService->getUserData($userId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    echo "   User has " . count($controlProjects) . " projects on control:\n";
    
    foreach ($controlProjects as $projectCode => $settings) {
        echo "   \n   Project: {$projectCode}\n";
        echo "   ├── Group ID: " . ($settings['group_id'] ?? 'NOT SET') . "\n";
        echo "   ├── Chat ID: " . ($settings['chat_id'] ?? 'NOT SET') . "\n";
        echo "   ├── Created: " . ($settings['created_at'] ?? 'UNKNOWN') . "\n";
        echo "   └── Updated: " . ($settings['updated_at'] ?? 'UNKNOWN') . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error getting user data: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($controlProjects)) {
    echo "\n❌ No control projects found. User needs to add projects first.\n";
    exit(1);
}

echo "\n2. Testing message delivery to configured destinations...\n";

foreach ($controlProjects as $projectCode => $settings) {
    echo "\n   Testing project: {$projectCode}\n";
    
    $testMessage = "🧪 TEST: Проект {$projectCode} - " . date('H:i:s');
    $deliveryResults = [];
    
    // Test group delivery
    if (!empty($settings['group_id'])) {
        echo "   Testing group delivery to: {$settings['group_id']}\n";
        
        try {
            // Create MessageService for testing
            $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
            
            // Attempt to send test message
            $result = $messageService->sendMessage($settings['group_id'], $testMessage);
            
            if ($result->isOk()) {
                echo "   ✅ Group message sent successfully\n";
                $deliveryResults[] = 'Group: SUCCESS';
            } else {
                echo "   ❌ Group message failed: " . $result->getDescription() . "\n";
                $deliveryResults[] = 'Group: FAILED - ' . $result->getDescription();
            }
            
        } catch (Exception $e) {
            echo "   ❌ Group delivery exception: " . $e->getMessage() . "\n";
            $deliveryResults[] = 'Group: EXCEPTION - ' . $e->getMessage();
        }
    } else {
        echo "   ⚠️ No group ID configured\n";
        $deliveryResults[] = 'Group: NOT CONFIGURED';
    }
    
    // Test chat delivery
    if (!empty($settings['chat_id'])) {
        echo "   Testing chat delivery to: {$settings['chat_id']}\n";
        
        $targetChatId = ($settings['chat_id'] === 'personal') ? $userId : $settings['chat_id'];
        echo "   Resolved chat ID: {$targetChatId}\n";
        
        try {
            $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
            $result = $messageService->sendMessage($targetChatId, $testMessage);
            
            if ($result->isOk()) {
                echo "   ✅ Chat message sent successfully\n";
                $deliveryResults[] = 'Chat: SUCCESS';
            } else {
                echo "   ❌ Chat message failed: " . $result->getDescription() . "\n";
                $deliveryResults[] = 'Chat: FAILED - ' . $result->getDescription();
            }
            
        } catch (Exception $e) {
            echo "   ❌ Chat delivery exception: " . $e->getMessage() . "\n";
            $deliveryResults[] = 'Chat: EXCEPTION - ' . $e->getMessage();
        }
    } else {
        echo "   ⚠️ No chat ID configured\n";
        $deliveryResults[] = 'Chat: NOT CONFIGURED';
    }
    
    echo "   \n   Delivery Summary for {$projectCode}:\n";
    foreach ($deliveryResults as $result) {
        echo "   • {$result}\n";
    }
}

echo "\n3. Checking common delivery issues...\n";

echo "   Checking bot token configuration...\n";
try {
    // Try to get bot info
    $telegram = new \Longman\TelegramBot\Telegram('', ''); // Will fail but might give info
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'API KEY') !== false) {
        echo "   ⚠️ Bot API token issue detected\n";
        echo "   This could prevent message delivery\n";
    } else {
        echo "   Bot initialization: " . $e->getMessage() . "\n";
    }
}

echo "\n4. Testing basic message functionality...\n";

// Test if MessageService can send a simple message to the user
try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $testResult = $messageService->sendMessage($userId, "🔧 Диагностический тест: " . date('H:i:s'));
    
    if ($testResult->isOk()) {
        echo "   ✅ Basic message sending works\n";
    } else {
        echo "   ❌ Basic message sending failed: " . $testResult->getDescription() . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ MessageService error: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "\nPossible causes for test message delivery failure:\n";
echo "• Bot not added to the target group/chat\n";
echo "• Bot doesn't have permission to send messages\n";
echo "• Incorrect group/chat ID format\n";
echo "• Bot API token configuration issues\n";
echo "• Group/chat has restricted the bot\n";
echo "• Bot is not an administrator in the group\n";