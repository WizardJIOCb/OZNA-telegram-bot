<?php
// Debug script to test idle reason parsing

echo "=== DEBUGGING IDLE REASON PARSING ===\n\n";

function getIdleReasonFromCallback($category) {
    echo "Input category: $category\n";
    
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

// Test with the actual callback from your test
$category = 'category_Табель_Простой_1_izm';
$result = getIdleReasonFromCallback($category);

echo "\n=== EXPECTED vs ACTUAL ===\n";
echo "Expected: Ожидание допоставки (Изменение ТР)\n";
echo "Actual:   $result\n";

if ($result === 'Ожидание допоставки (Изменение ТР)') {
    echo "✅ SUCCESS: Result matches expected\n";
} else {
    echo "❌ FAILURE: Result does not match expected\n";
}

echo "\n=== TESTING OTHER CASES ===\n";

// Test another case
$category2 = 'category_Табель_Простой_2_teh';
$result2 = getIdleReasonFromCallback($category2);
echo "Input: $category2\n";
echo "Result: $result2\n";
echo "Expected: Отсутствие обратной связи (Техническое решение)\n\n";

// Test supervision case
$category3 = 'category_Табель_ШМ_Простой_3_ene';
$result3 = getIdleReasonFromCallback($category3);
echo "Input: $category3\n";
echo "Result: $result3\n";
echo "Expected: Готовность Заказчика (Отсутствие энергоресурсов)\n\n";
?>