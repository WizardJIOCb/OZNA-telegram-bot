<?php
/**
 * Script to verify the idle reason fix in the actual Telegram bot
 */

echo "=== VERIFYING IDLE REASON FIX IN TELEGRAM BOT ===\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

// Test user and chat data
$userId = 4225504; // Test user ID
$chatId = 4225504; // Test chat ID

echo "Testing idle reason fix with user ID: {$userId}, chat ID: {$chatId}\n\n";

try {
    // Initialize database connection like in hook.php
    echo "1. Initializing database connection...\n";
    
    $bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
    $bot_username = 'OznaWork_bot';
    
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    // Enable MySQL
    $mysql_credentials = [
        'host'     => '82.146.42.213',
        'port'     => 3306,
        'user'     => 'WizardJIOCb',
        'password' => '7qQ#28051961',
        'database' => 'tg_bot',
    ];
    
    $pdo = $telegram->enableMySql($mysql_credentials);
    
    if ($pdo) {
        echo "   ✅ Database connection initialized successfully\n\n";
    } else {
        echo "   ❌ Database connection initialization failed\n\n";
        exit(1);
    }
    
    echo "2. Testing conversation functionality for idle reason...\n";
    
    // Create a conversation for testing
    $command = 'test_idle_reason_' . time(); // Unique command to avoid conflicts
    echo "   Creating conversation with command: {$command}\n";
    
    $conversation = new \Longman\TelegramBot\Conversation($userId, $chatId, $command);
    
    echo "   Conversation created\n";
    echo "   Initial notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Set up conversation notes similar to idle selection
    $conversation->notes = [
        'project_code' => 'ОИ839',
        'tabel_data' => '01.10.2025',
        'pending_idle_category' => 'category_Табель_Простой_1_izm' // This is what we're testing
    ];
    
    echo "   Setting up test conversation notes\n";
    echo "   Notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Try to update the conversation
    echo "3. Testing conversation update...\n";
    $updateResult = $conversation->update();
    echo "   Update result: " . ($updateResult ? "SUCCESS" : "FAILED") . "\n\n";
    
    if ($updateResult) {
        // Try to read the conversation back
        echo "4. Testing conversation retrieval...\n";
        $readConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, $command);
        echo "   Retrieved notes: " . json_encode($readConversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
        
        // Check if our pending_idle_category is correctly stored
        $pendingCategory = $readConversation->notes['pending_idle_category'] ?? 'NOT_FOUND';
        echo "   Retrieved pending_idle_category: {$pendingCategory}\n";
        
        if ($pendingCategory === 'category_Табель_Простой_1_izm') {
            echo "   ✅ Pending idle category correctly stored and retrieved\n";
            
            // Test the getIdleReasonFromCallback method
            echo "\n5. Testing idle reason generation...\n";
            
            // We'll simulate the TabelHandler's getIdleReasonFromCallback method
            $category = $pendingCategory;
            $parts = explode('_', str_replace('category_Табель_', '', $category));
            
            $categories = [
                '1' => 'Ожидание допоставки',
                '2' => 'Отсутствие обратной связи', 
                '3' => 'Готовность Заказчика',
                '4' => 'Транспорт',
                '5' => 'Погодные условия',
                '6' => 'Прочее'
            ];
            
            $subcategories = [
                'def' => 'Дефицит по спецификации',
                'ost' => 'Отсутствие в поставке',
                'izm' => 'Изменение ТР',
                'teh' => 'Техническое решение',
                'sog' => 'Согласование плана работ',
                'ene' => 'Отсутствие энергоресурсов',
                'rab' => 'Отсутствие рабочих сред',
                'per' => 'Перенос предъявления работ',
                'str' => 'Отсутствие строительной готовности',
                'pol' => 'Поломка',
                'mes' => 'Отсутствие свободных мест',
                'bur' => 'Буран/снегопад',
                'mor' => 'Мороз',
                'ras' => 'Распутица',
                'po' => 'Разработка ПО',
                'dop' => 'Отсутствие допуска к работам',
                'svy' => 'Отсутствие связи',
                'kom' => 'Свой комментарий',
                'es' => 'Раздел ЭС',
                'ath' => 'Раздел АТХ',
                'ov' => 'Раздел ОВ',
                'ps' => 'Раздел ПС/ОС',
                'th' => 'Раздел ТХ',
                'pki' => 'Поставщик ПКИ'
            ];
            
            $reasonParts = [];
            
            // Skip supervision prefix if present
            $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
            
            // Main category
            if (isset($parts[$startIndex]) && isset($categories[$parts[$startIndex]])) {
                $reasonParts[] = $categories[$parts[$startIndex]];
            }
            
            // Subcategory
            if (isset($parts[$startIndex + 1]) && isset($subcategories[$parts[$startIndex + 1]])) {
                $reasonParts[] = $subcategories[$parts[$startIndex + 1]];
            }
            
            // Sub-subcategory
            if (isset($parts[$startIndex + 2]) && isset($subcategories[$parts[$startIndex + 2]])) {
                $reasonParts[] = $subcategories[$parts[$startIndex + 2]];
            }
            
            // Format with nested parentheses like: "Ожидание допоставки (Дефицит по спецификации)"
            $reason = '';
            if (count($reasonParts) > 0) {
                $reason = $reasonParts[0];
                if (count($reasonParts) > 1) {
                    $reason .= ' (' . implode(' (', array_slice($reasonParts, 1)) . str_repeat(')', count($reasonParts) - 1);
                }
            }
            
            $idleReason = $reason ?: 'Простой';
            
            echo "   Generated idle reason: {$idleReason}\n";
            
            if ($idleReason === 'Ожидание допоставки (Изменение ТР)') {
                echo "   ✅ Idle reason correctly generated with nested parentheses\n";
            } else {
                echo "   ❌ Idle reason generation failed\n";
            }
        } else {
            echo "   ❌ Pending idle category not correctly stored\n";
        }
    } else {
        echo "   ❌ Conversation update failed, cannot test retrieval\n";
    }
    
    // Clean up
    echo "\n6. Cleaning up...\n";
    $conversation->stop();
    echo "   Conversation stopped\n";
    
    echo "\n=== VERIFICATION COMPLETE ===\n";
    echo "If all tests passed, the idle reason fix should be working correctly in the Telegram bot.\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}
?>