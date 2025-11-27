<?php
// Test script to verify the idle reason fix

echo "=== TESTING IDLE REASON FIX ===\n\n";

// Simulate the actual Telegram bot flow with separate requests
class MockConversation {
    public $notes = [];
    public $user_id = 123456;
    
    public function update() {
        // Simulate conversation update
        echo "   Conversation updated\n";
        // In real implementation, this would save to database
    }
    
    public function stop() {
        // Simulate conversation stop
        echo "   Conversation stopped\n";
        $this->notes = [];
    }
}

class TabelHandler {
    public function __construct() {
        // Mock constructor
    }
    
    public function getIdleReasonFromCallback($category) {
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
        
        return $reason ?: 'Простой';
    }
    
    public function handleIdleFinalSelection($category, $conversation) {
        echo "2. handleIdleFinalSelection method:\n";
        
        // Convert the detailed idle selection to a simple "Простой" activity type
        $activityType = strpos($category, 'ШМ_') !== false ? 'ШМ_Простой' : 'Простой';
        echo "   Activity type: $activityType\n";
        
        // Store the detailed idle reason in conversation notes for confirmation
        $conversation->notes['idle_reason'] = $this->getIdleReasonFromCallback($category);
        // CRITICAL FIX: Also store the pending idle category in case of conversation state loss
        $conversation->notes['pending_idle_category'] = $category;
        $conversation->update();
        
        echo "   Stored idle_reason in conversation: " . $conversation->notes['idle_reason'] . "\n";
        echo "   Stored pending_idle_category in conversation: " . $conversation->notes['pending_idle_category'] . "\n";
        
        // Create confirmation callback
        $confirmationCallback = "category_Табель_{$activityType}_yes";
        echo "   Created confirmation callback: $confirmationCallback\n";
        
        return $confirmationCallback;
    }
    
    public function handleActivityExecution($confirmationCallback, $conversation) {
        echo "\n4. handleActivityExecution method:\n";
        
        // Extract activity type using regex
        if (preg_match('/^(category_)?Табель_(Простой|ШМ_Простой)_yes$/', $confirmationCallback, $matches)) {
            $extractedActivityType = $matches[2];
            echo "   Extracted activity type: $extractedActivityType\n";
            
            // Get idle reason from conversation notes
            $idleReason = $conversation->notes['idle_reason'] ?? '';
            echo "   Retrieved idle reason from conversation: '$idleReason'\n";
            
            // CRITICAL FIX: Check if we have a pending idle category that needs processing
            if (isset($conversation->notes['pending_idle_category'])) {
                echo "   Found pending idle category, regenerating idle reason\n";
                $pendingCategory = $conversation->notes['pending_idle_category'];
                $regeneratedReason = $this->getIdleReasonFromCallback($pendingCategory);
                echo "   Regenerated idle reason: '$regeneratedReason'\n";
                
                // Update conversation with regenerated idle reason
                $conversation->notes['idle_reason'] = $regeneratedReason;
                $idleReason = $regeneratedReason;
                
                // Clear the pending state
                unset($conversation->notes['pending_idle_category']);
                $conversation->update();
            }
            
            // Check if it's an idle activity
            if ($extractedActivityType === 'Простой' || $extractedActivityType === 'ШМ_Простой') {
                echo "   Processing idle activity, using idle_reason from conversation: '$idleReason'\n";
            }
            
            // Simulate creating the final message
            $fio = 'Богданов Борис Сергеевич';
            $date = '01.10.2025';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            
            $activityText = $extractedActivityType;
            if (($extractedActivityType === 'Простой' || $extractedActivityType === 'ШМ_Простой') && !empty($idleReason)) {
                $activityText .= " ($idleReason)";
            }
            
            $message = sprintf(
                "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
            
            echo "\n5. Final message:\n";
            echo $message . "\n\n";
            
            echo "6. Expected result:\n";
            echo "   ⚠️ Простой (Ожидание допоставки (Изменение ТР))\n\n";
            
            echo "7. Actual result in message:\n";
            echo "   " . $activityText . "\n\n";
            
            if (strpos($activityText, 'Ожидание допоставки (Изменение ТР)') !== false) {
                echo "✅ SUCCESS: Nested parentheses format is correct!\n";
                return true;
            } else {
                echo "❌ FAILURE: Nested parentheses format is incorrect!\n";
                return false;
            }
        } else {
            echo "   Pattern did not match!\n";
            return false;
        }
    }
}

// Test 1: Normal flow (conversation state preserved)
echo "=== TEST 1: Normal Flow (Conversation State Preserved) ===\n";
echo "1. User selects idle reason (Request 1):\n";
$category = 'category_Табель_Простой_1_izm';
echo "   Category: $category\n";

// Create conversation for first request
$conversation1 = new MockConversation();
$conversation1->notes['project_code'] = 'ОИ839';

// Process idle selection
$tabelHandler = new TabelHandler();
$confirmationCallback = $tabelHandler->handleIdleFinalSelection($category, $conversation1);

// Simulate the conversation being saved to database and loaded in second request
echo "\n3. User confirms with callback (Request 2): $confirmationCallback\n";

// Create conversation for second request (simulating fresh load from database)
$conversation2 = new MockConversation();
$conversation2->notes = $conversation1->notes; // Simulate loading from database

// Process confirmation
$success = $tabelHandler->handleActivityExecution($confirmationCallback, $conversation2);

echo "\n=== TEST 1 COMPLETE ===\n\n";

// Test 2: Conversation state loss scenario (our fix should handle this)
echo "=== TEST 2: Conversation State Loss (Our Fix Should Handle This) ===\n";
echo "8. Simulating conversation state loss scenario:\n";
$conversation3 = new MockConversation();
$conversation3->notes['project_code'] = 'ОИ839';
// Note: idle_reason is missing due to state loss, but pending_idle_category should be there
$conversation3->notes['pending_idle_category'] = 'category_Табель_Простой_1_izm';

$confirmationCallback3 = 'category_Табель_Простой_yes';
echo "   Processing confirmation with callback: $confirmationCallback3\n";
echo "   Conversation state: " . json_encode($conversation3->notes) . "\n";

$success3 = $tabelHandler->handleActivityExecution($confirmationCallback3, $conversation3);

if ($success3) {
    echo "   ✅ SUCCESS: Our fix correctly regenerated the idle reason!\n";
} else {
    echo "   ❌ FAILURE: Our fix did not work!\n";
}

echo "\n=== TEST 2 COMPLETE ===\n";

echo "\n=== ALL TESTS COMPLETE ===\n";
?>