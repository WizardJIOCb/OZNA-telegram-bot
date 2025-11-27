<?php
// Test the complete idle flow from callback to final message

// Simulate the getIdleReasonFromCallback method
function getIdleReasonFromCallback($category) {
    // Parse the callback to build a readable reason
    $parts = explode('_', str_replace('category_Табель_', '', $category));
    
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
        // Категория 1: Ожидание допоставки
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

// Simulate the createTabelMessage method for idle activities
function createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason) {
    // For idle activities, activityType will be 'Простой' or 'ШМ_Простой'
    $activityText = $activityType;
    
    // Для простоя добавляем детальную причину
    if (($activityType === 'Простой' || $activityType === 'ШМ_Простой') && !empty($idleReason)) {
        $activityText .= " ({$idleReason})";
    }
    
    // Idle activities ("Простой") - use new commissioning format
    return sprintf(
        "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
        $fio,
        $date,
        $projectCode,
        $activityText
    );
}

// Test with the user's example
$category = 'category_Табель_Простой_1_def'; // Ожидание допоставки (Дефицит по спецификации)
$idleReason = getIdleReasonFromCallback($category);
echo "Generated idle reason: " . $idleReason . "\n";

$fio = 'Иванов Иван Иванович';
$date = '29.09.2025';
$projectCode = 'ОИ726';
$activityType = 'Простой';

$message = createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
echo "\nGenerated message:\n" . $message . "\n";

// Expected format from user's example
$expected = "<b>Иванов Иван Иванович</b>\r\n😨 ⚠️ 29.09.2025, Проект <b>ОИ726</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ Простой (Ожидание допоставки (Дефицит по спецификации))</b>";
echo "\nExpected message:\n" . $expected . "\n";

// Clean up line endings for comparison
$messageClean = str_replace("\r\n", "\n", $message);
$expectedClean = str_replace("\r\n", "\n", $expected);

echo "\nMatch: " . ($messageClean === $expectedClean ? 'YES' : 'NO') . "\n";