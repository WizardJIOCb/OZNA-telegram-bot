<?php
// Debug script to trace the idle flow issue

echo "=== IDLE FLOW DEBUG ===\n\n";

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

$idleReason = getIdleReasonFromCallback($category);
echo "   Generated idle reason: $idleReason\n\n";

// Simulate storing in conversation notes
$conversation_notes = [
    'idle_reason' => $idleReason,
    'project_code' => 'ОИ839',
    'tabel_data' => '01.10.2025'
];

echo "2. Stored in conversation notes:\n";
echo "   idle_reason: " . $conversation_notes['idle_reason'] . "\n";
echo "   project_code: " . $conversation_notes['project_code'] . "\n";
echo "   tabel_data: " . $conversation_notes['tabel_data'] . "\n\n";

// Simulate confirmation callback
$confirmationCallback = 'category_Табель_Простой_yes';
echo "3. User confirms with callback: $confirmationCallback\n";

// Simulate regex pattern matching in handleActivityExecution
if (preg_match('/^(category_)?Табель_(Простой|ШМ_Простой)_yes$/', $confirmationCallback, $matches)) {
    $activityType = $matches[2];
    echo "   Matched pattern, activityType: $activityType\n";
    
    // Simulate retrieving idle reason from conversation notes
    $retrievedIdleReason = $conversation_notes['idle_reason'] ?? '';
    echo "   Retrieved idle reason from conversation: $retrievedIdleReason\n";
    
    // Simulate creating the final message
    $fio = 'Богданов Борис Сергеевич';
    $date = $conversation_notes['tabel_data'];
    $projectCode = $conversation_notes['project_code'];
    
    $activityText = $activityType;
    if (($activityType === 'Простой' || $activityType === 'ШМ_Простой') && !empty($retrievedIdleReason)) {
        $activityText .= " ($retrievedIdleReason)";
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
?>