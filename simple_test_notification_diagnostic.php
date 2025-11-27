<?php
/**
 * Simplified diagnostic for test notification issues
 */

echo "=== SIMPLIFIED TEST NOTIFICATION DIAGNOSTIC ===\n\n";

echo "1. Testing basic autoload and database connection...\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✓ Vendor autoload successful\n";
    
    require_once __DIR__ . '/custom_autoload.php';
    echo "   ✓ Custom autoload successful\n";
    
} catch (Exception $e) {
    echo "   ❌ Autoload error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Testing DatabaseService...\n";

try {
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    echo "   ✓ DatabaseService created\n";
    
    $userId = 4225504;
    $userData = $databaseService->getUserData($userId);
    echo "   ✓ User data retrieved\n";
    
    $controlProjects = $userData['control_projects'] ?? [];
    echo "   User has " . count($controlProjects) . " control projects\n";
    
    if (!empty($controlProjects)) {
        $firstProject = array_key_first($controlProjects);
        $firstSettings = $controlProjects[$firstProject];
        
        echo "   First project: {$firstProject}\n";
        echo "   Group ID: " . ($firstSettings['group_id'] ?? 'NOT SET') . "\n";
        echo "   Chat ID: " . ($firstSettings['chat_id'] ?? 'NOT SET') . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ DatabaseService error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. Testing MessageService basic functionality...\n";

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "   ✓ MessageService created\n";
    
    // Try to create a simple test message to user's personal chat
    echo "   Attempting to send test message to user {$userId}...\n";
    
    $testMessage = "🔧 Простой тест: " . date('H:i:s');
    $result = $messageService->sendMessage($userId, $testMessage);
    
    if ($result && method_exists($result, 'isOk')) {
        if ($result->isOk()) {
            echo "   ✅ Test message sent successfully to user\n";
        } else {
            echo "   ❌ Test message failed: " . $result->getDescription() . "\n";
        }
    } else {
        echo "   ⚠️ Unexpected result type: " . gettype($result) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ MessageService error: " . $e->getMessage() . "\n";
}

echo "\n4. Testing specific group/chat delivery...\n";

if (!empty($controlProjects)) {
    foreach ($controlProjects as $projectCode => $settings) {
        echo "   Testing delivery for project: {$projectCode}\n";
        
        // Test group delivery if configured
        if (!empty($settings['group_id'])) {
            echo "   Trying group: {$settings['group_id']}\n";
            
            try {
                $groupTestMessage = "🧪 Тест группы для {$projectCode} - " . date('H:i:s');
                $groupResult = $messageService->sendMessage($settings['group_id'], $groupTestMessage);
                
                if ($groupResult && method_exists($groupResult, 'isOk')) {
                    if ($groupResult->isOk()) {
                        echo "   ✅ Group message delivered\n";
                    } else {
                        echo "   ❌ Group delivery failed: " . $groupResult->getDescription() . "\n";
                        
                        // Common error analysis
                        $error = $groupResult->getDescription();
                        if (strpos($error, 'chat not found') !== false) {
                            echo "      → Bot not added to group or wrong Group ID\n";
                        } elseif (strpos($error, 'not enough rights') !== false) {
                            echo "      → Bot doesn't have permission to send messages\n";
                        } elseif (strpos($error, 'kicked') !== false) {
                            echo "      → Bot was removed from the group\n";
                        }
                    }
                } else {
                    echo "   ⚠️ Unexpected group result\n";
                }
                
            } catch (Exception $e) {
                echo "   ❌ Group delivery exception: " . $e->getMessage() . "\n";
            }
        }
        
        // Test chat delivery if configured
        if (!empty($settings['chat_id'])) {
            $targetChatId = ($settings['chat_id'] === 'personal') ? $userId : $settings['chat_id'];
            echo "   Trying chat: {$targetChatId}\n";
            
            try {
                $chatTestMessage = "🧪 Тест чата для {$projectCode} - " . date('H:i:s');
                $chatResult = $messageService->sendMessage($targetChatId, $chatTestMessage);
                
                if ($chatResult && method_exists($chatResult, 'isOk')) {
                    if ($chatResult->isOk()) {
                        echo "   ✅ Chat message delivered\n";
                    } else {
                        echo "   ❌ Chat delivery failed: " . $chatResult->getDescription() . "\n";
                    }
                } else {
                    echo "   ⚠️ Unexpected chat result\n";
                }
                
            } catch (Exception $e) {
                echo "   ❌ Chat delivery exception: " . $e->getMessage() . "\n";
            }
        }
        
        break; // Only test first project to avoid spam
    }
} else {
    echo "   No control projects to test\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "Check the results above to identify the delivery issue.\n";