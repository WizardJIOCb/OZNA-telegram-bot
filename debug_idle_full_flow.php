<?php
// Debug script to test the full idle flow

echo "=== DEBUGGING FULL IDLE FLOW ===\n\n";

// Simulate the TabelHandler class methods
class TabelHandler {
    public function getIdleReasonFromCallback($category) {
        echo "getIdleReasonFromCallback called with: $category\n";
        
        // Parse the callback to build a readable reason
        $parts = explode('_', str_replace('category_Табель_', '', $category));
        
        echo "Parts after parsing: " . json_encode($parts) . "\n";
        
        $categories = [
            '1' => 'Ожидание допоставки',
            '2' => 'Отсутствие обратной связи', 
            '3' => 'Готовность Заказчика',
            '4' => 'Транспорт',
            '5' => 'Погодные условия',
            '6' => 'Прочее'
        ];
        
        // Новые короткие ключи
        $subcategories = [
            // Категория 1: Допоставка
            'def' => 'Дефицит по спецификации',
            'ost' => 'Отсутствие в поставке',
            'izm' => 'Изменение ТР',
            // Категория 2: Отсутствие обратной связи
            'teh' => 'Техническое решение',
            'sog' => 'Согласование плана работ',
            // Категория 3: Готовность Заказчика
            'ene' => 'Отсутствие энергоресурсов',
            'rab' => 'Отсутствие рабочих сред',
            'per' => 'Перенос предъявления работ',
            'str' => 'Отсутствие строительной готовности',
            // Категория 4: Транспорт
            'pol' => 'Поломка',
            'mes' => 'Отсутствие свободных мест',
            // Категория 5: Погодные условия
            'bur' => 'Буран/снегопад',
            'mor' => 'Мороз',
            'ras' => 'Распутица',
            // Категория 6: Прочее
            'po' => 'Разработка ПО',
            'dop' => 'Отсутствие допуска к работам',
            'svy' => 'Отсутствие связи',
            'kom' => 'Свой комментарий',
            // Подподкатегории (разделы)
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
        
        echo "Start index: $startIndex\n";
        
        // Main category
        if (isset($parts[$startIndex]) && isset($categories[$parts[$startIndex]])) {
            $reasonParts[] = $categories[$parts[$startIndex]];
            echo "Added main category: " . $categories[$parts[$startIndex]] . "\n";
        } else {
            echo "Main category not found or not set. parts[startIndex]: " . ($parts[$startIndex] ?? 'NOT SET') . "\n";
        }
        
        // Subcategory
        if (isset($parts[$startIndex + 1]) && isset($subcategories[$parts[$startIndex + 1]])) {
            $reasonParts[] = $subcategories[$parts[$startIndex + 1]];
            echo "Added subcategory: " . $subcategories[$parts[$startIndex + 1]] . "\n";
        } else {
            echo "Subcategory not found or not set. parts[startIndex+1]: " . ($parts[$startIndex + 1] ?? 'NOT SET') . "\n";
        }
        
        // Sub-subcategory
        if (isset($parts[$startIndex + 2]) && isset($subcategories[$parts[$startIndex + 2]])) {
            $reasonParts[] = $subcategories[$parts[$startIndex + 2]];
            echo "Added sub-subcategory: " . $subcategories[$parts[$startIndex + 2]] . "\n";
        } else {
            echo "Sub-subcategory not found or not set. parts[startIndex+2]: " . ($parts[$startIndex + 2] ?? 'NOT SET') . "\n";
        }
        
        echo "Reason parts: " . json_encode($reasonParts) . "\n";
        
        // Format with nested parentheses like: "Ожидание допоставки (Дефицит по спецификации)"
        $reason = '';
        if (count($reasonParts) > 0) {
            $reason = $reasonParts[0];
            if (count($reasonParts) > 1) {
                $reason .= ' (' . implode(' (', array_slice($reasonParts, 1)) . str_repeat(')', count($reasonParts) - 1);
            }
        }
        
        echo "Final reason: '$reason'\n";
        
        return $reason ?: 'Простой';
    }
    
    public function handleIdleFinalSelection($category) {
        echo "handleIdleFinalSelection called with: $category\n";
        
        // Simulate conversation notes
        $conversation_notes = [];
        
        // Convert the detailed idle selection to a simple "Простой" activity type
        $activityType = strpos($category, 'ШМ_') !== false ? 'ШМ_Простой' : 'Простой';
        echo "Activity type: $activityType\n";
        
        // Store the detailed idle reason in conversation notes for confirmation
        $conversation_notes['idle_reason'] = $this->getIdleReasonFromCallback($category);
        // CRITICAL FIX: Also store the pending idle category in case of conversation state loss
        $conversation_notes['pending_idle_category'] = $category;
        
        echo "Stored in conversation notes:\n";
        echo "  idle_reason: " . $conversation_notes['idle_reason'] . "\n";
        echo "  pending_idle_category: " . $conversation_notes['pending_idle_category'] . "\n";
        
        // Create confirmation callback
        $confirmationCallback = "category_Табель_{$activityType}_yes";
        echo "Created confirmation callback: $confirmationCallback\n";
        
        return [$confirmationCallback, $conversation_notes];
    }
    
    public function handleActivityExecution($confirmationCallback, $conversation_notes) {
        echo "handleActivityExecution called with: $confirmationCallback\n";
        echo "Conversation notes: " . json_encode($conversation_notes) . "\n";
        
        // Extract activity type using regex
        if (preg_match('/^(category_)?Табель_(Простой|ШМ_Простой)_yes$/', $confirmationCallback, $matches)) {
            $extractedActivityType = $matches[2];
            echo "Extracted activity type: $extractedActivityType\n";
            
            // Get idle reason from conversation notes
            $idleReason = $conversation_notes['idle_reason'] ?? '';
            echo "Retrieved idle reason from conversation: '$idleReason'\n";
            
            // CRITICAL FIX: Check if we have a pending idle category that needs processing
            if (isset($conversation_notes['pending_idle_category'])) {
                echo "Found pending idle category, regenerating idle reason\n";
                $pendingCategory = $conversation_notes['pending_idle_category'];
                $regeneratedReason = $this->getIdleReasonFromCallback($pendingCategory);
                echo "Regenerated idle reason: '$regeneratedReason'\n";
                
                // Update conversation with regenerated idle reason
                $conversation_notes['idle_reason'] = $regeneratedReason;
                $idleReason = $regeneratedReason;
                
                // Clear the pending state
                unset($conversation_notes['pending_idle_category']);
            }
            
            // Check if it's an idle activity
            if ($extractedActivityType === 'Простой' || $extractedActivityType === 'ШМ_Простой') {
                echo "Processing idle activity, using idle_reason from conversation: '$idleReason'\n";
            }
            
            // Simulate creating the final message using TabelEntry logic
            $fio = 'Богданов Борис Сергеевич';
            $date = '30.09.2025';
            $projectCode = 'ОИ876';
            
            // Simulate activity type mappings (simplified)
            $activityTypeMappings = [
                // Note: 'Простой' and 'ШМ_Простой' are not in the mappings, so they fall back to raw values
            ];
            
            $activityText = $activityTypeMappings[$extractedActivityType] ?? $extractedActivityType;
            echo "Activity text from lookup: '$activityText'\n";
            
            // Для простоя добавляем детальную причину
            if (($extractedActivityType === 'Простой' || $extractedActivityType === 'ШМ_Простой') && !empty($idleReason)) {
                $activityText .= " ({$idleReason})";
                echo "Added idle reason, activityText now: '{$activityText}'\n";
            }
            
            $message = sprintf(
                "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
            
            echo "\nFinal message:\n";
            echo $message . "\n\n";
            
            return $message;
        } else {
            echo "Pattern did not match!\n";
            return false;
        }
    }
}

// Test the full flow
$tabelHandler = new TabelHandler();

// Step 1: User selects idle reason
$category = 'category_Табель_Простой_1_izm';
echo "=== STEP 1: User selects idle reason ===\n";
list($confirmationCallback, $conversation_notes) = $tabelHandler->handleIdleFinalSelection($category);

// Step 2: User confirms
echo "\n=== STEP 2: User confirms ===\n";
$message = $tabelHandler->handleActivityExecution($confirmationCallback, $conversation_notes);

echo "=== TEST COMPLETE ===\n";

// Let's also test what happens if conversation state is lost
echo "\n=== TESTING CONVERSATION STATE LOSS ===\n";
echo "Simulating conversation state loss (idle_reason missing but pending_idle_category present)\n";

$lost_conversation_notes = [
    'pending_idle_category' => 'category_Табель_Простой_1_izm'
];

$message2 = $tabelHandler->handleActivityExecution($confirmationCallback, $lost_conversation_notes);

echo "=== CONVERSATION STATE LOSS TEST COMPLETE ===\n";
?>