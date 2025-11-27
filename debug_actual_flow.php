<?php
// Debug script to trace the actual flow issue

echo "=== ACTUAL FLOW DEBUG ===\n\n";

// Simulate the actual flow with conversation state management
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Simulate conversation update
        echo "   Conversation updated\n";
    }
}

// Simulate the idle flow
echo "1. User selects idle reason:\n";
$category = 'category_Табель_Простой_1_izm';
echo "   Category: $category\n";

// Simulate getIdleReasonFromCallback
function getIdleReasonFromCallback($category) {
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

// Simulate handleIdleFinalSelection
echo "\n2. handleIdleFinalSelection method:\n";
$conversation = new MockConversation();
$conversation->notes['project_code'] = 'ОИ839';

// Convert the detailed idle selection to a simple "Простой" activity type
$activityType = strpos($category, 'ШМ_') !== false ? 'ШМ_Простой' : 'Простой';
echo "   Activity type: $activityType\n";

// Store the detailed idle reason in conversation notes for confirmation
$conversation->notes['idle_reason'] = getIdleReasonFromCallback($category);
$conversation->update();

echo "   Stored idle_reason in conversation: " . $conversation->notes['idle_reason'] . "\n";

// Create confirmation keyboard
$confirmationCallback = "category_Табель_{$activityType}_yes";
echo "   Created confirmation callback: $confirmationCallback\n";

echo "\n3. User confirms with callback: $confirmationCallback\n";

// Simulate handleActivityExecution
// Extract activity type using regex
if (preg_match('/^(category_)?Табель_(Простой|ШМ_Простой)_yes$/', $confirmationCallback, $matches)) {
    $extractedActivityType = $matches[2];
    echo "   Extracted activity type: $extractedActivityType\n";
    
    // Get idle reason from conversation notes
    $idleReason = $conversation->notes['idle_reason'] ?? '';
    echo "   Retrieved idle reason from conversation: $idleReason\n";
    
    // Check if it's an idle activity
    if ($extractedActivityType === 'Простой' || $extractedActivityType === 'ШМ_Простой') {
        echo "   Processing idle activity, using idle_reason from conversation: '$idleReason'\n";
    }
    
    // Simulate creating the final message
    $fio = 'Богданов Борис Сергеевич';
    $date = '01.10.2025';
    $projectCode = $conversation->notes['project_code'];
    
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
    
    echo "\n4. Final message:\n";
    echo $message . "\n\n";
    
    echo "5. Expected result:\n";
    echo "   ⚠️ Простой (Ожидание допоставки (Изменение ТР))\n\n";
    
    echo "6. Actual result in message:\n";
    echo "   " . $activityText . "\n\n";
    
    if (strpos($activityText, 'Ожидание допоставки (Изменение ТР)') !== false) {
        echo "✅ SUCCESS: Nested parentheses format is correct!\n";
    } else {
        echo "❌ FAILURE: Nested parentheses format is incorrect!\n";
    }
} else {
    echo "   Pattern did not match!\n";
}

echo "\n=== DEBUG COMPLETE ===\n";

// Let's also check if there might be an issue with the conversation state being reset
echo "\n=== CHECKING CONVERSATION STATE ISSUES ===\n";

// Simulate a scenario where conversation state might be reset
echo "\n7. Simulating potential conversation state reset:\n";
$conversation2 = new MockConversation();
$conversation2->notes['project_code'] = 'ОИ839';
$conversation2->notes['idle_reason'] = 'Ожидание допоставки (Изменение ТР)';

echo "   Initial conversation state:\n";
echo "   - project_code: " . $conversation2->notes['project_code'] . "\n";
echo "   - idle_reason: " . $conversation2->notes['idle_reason'] . "\n";

// Simulate some other operation that might reset the conversation
echo "\n   Simulating other operation that might affect conversation state...\n";
// This could happen if there's another handler that resets the conversation

// Then process the confirmation
$confirmationCallback2 = 'category_Табель_Простой_yes';
echo "\n   Processing confirmation with callback: $confirmationCallback2\n";

if (preg_match('/^(category_)?Табель_(Простой|ШМ_Простой)_yes$/', $confirmationCallback2, $matches)) {
    $extractedActivityType = $matches[2];
    echo "   Extracted activity type: $extractedActivityType\n";
    
    // Get idle reason from conversation notes
    $idleReason = $conversation2->notes['idle_reason'] ?? '';
    echo "   Retrieved idle reason from conversation: '$idleReason'\n";
    
    if (empty($idleReason)) {
        echo "   ❌ CRITICAL: idle_reason is empty! This could be the source of the issue.\n";
    } else {
        echo "   ✅ idle_reason is present.\n";
    }
}

echo "\n=== CONVERSATION STATE CHECK COMPLETE ===\n";
?>