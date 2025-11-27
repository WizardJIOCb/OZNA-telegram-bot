<?php
// Тестовые сообщения
$testMessages = [
    // Сообщения ШМР с дефисом
    '<b>Тестовый пользователь</b>
✅26.10.2025, Проект <b>ОИ779</b>: Добавлена запись в табель: 
 <b>🔧 Шеф-монтажные работы (Монтаж оборудования - Электрооборудование)</b>',
    
    '<b>Тестовый пользователь</b>
✅26.10.2025, Проект <b>ОИ779</b>: Добавлена запись в табель: 
 <b>🔧 Шеф-монтажные работы (Прокладка кабеля - Автоматизация)</b>',
    
    // Сообщения ШМР без скобок (старый формат)
    '<b>Тестовый пользователь</b>
✅ Запись в табель от <b>04.10.2025</b>, в проект <b>ОИ846</b>
> о <b>🔧 Шеф-монтажные работы добавлена</b>'
];

echo "=== ТЕСТ ПАРСИНГА СООБЩЕНИЙ ===\n\n";

foreach ($testMessages as $i => $message) {
    echo "Сообщение " . ($i + 1) . ":\n";
    echo $message . "\n\n";
    
    // Извлекаем описание из <b> тегов
    if (preg_match('/<b>(.*?)<\/b>\s*$/s', $message, $matches)) {
        $description = $matches[1];
        echo "Описание: $description\n";
        
        // Парсим описание
        $parsed = parseDescriptionMock($description);
        echo "Результат парсинга:\n";
        echo "  main: " . ($parsed['main'] ?? '') . "\n";
        echo "  subs: " . print_r($parsed['subs'] ?? [], true) . "\n";
        
        // Применяем логику из ProjectHandler
        $result = applyProjectHandlerLogic($parsed);
        echo "Результат обработки:\n";
        echo "  Основной вид работ: " . $result['osnovnaya'] . "\n";
        echo "  Подтип работ: " . $result['tip1'] . "\n";
        echo "  Детали: " . $result['tip2'] . "\n";
        echo "  Примечание: " . $result['tip3'] . "\n";
    }
    
    echo str_repeat("-", 50) . "\n\n";
}

function parseDescriptionMock($description) {
    // Убираем эмодзи
    $description = preg_replace('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $description);
    $description = trim($description);
    
    // Извлекаем основную часть и подкатегории
    if (preg_match('/^([^(]+)(?:\((.*)\))?$/', $description, $matches)) {
        $main = trim($matches[1]);
        $subsString = $matches[2] ?? '';
        
        $subs = [];
        if ($subsString) {
            // Разбираем вложенные скобки
            $subs = parseNestedParentheses($subsString);
        }
        
        return ['main' => $main, 'subs' => $subs];
    }
    
    return ['main' => $description, 'subs' => []];
}

function parseNestedParentheses($string) {
    $result = [];
    $current = '';
    $depth = 0;
    
    for ($i = 0; $i < strlen($string); $i++) {
        $char = $string[$i];
        
        if ($char === '(') {
            if ($depth > 0) {
                $current .= $char;
            }
            $depth++;
        } elseif ($char === ')') {
            $depth--;
            if ($depth > 0) {
                $current .= $char;
            } elseif ($depth === 0) {
                $result[] = trim($current);
                $current = '';
            }
        } elseif ($char === ',' && $depth === 1) {
            $result[] = trim($current);
            $current = '';
        } else {
            if ($depth > 0) {
                $current .= $char;
            }
        }
    }
    
    if ($current !== '') {
        $result[] = trim($current);
    }
    
    return $result;
}

function applyProjectHandlerLogic($parsed) {
    $main = $parsed['main'] ?? '';
    $subs = $parsed['subs'] ?? [];
    
    $result = [
        'osnovnaya' => '',
        'tip1' => '',
        'tip2' => '',
        'tip3' => ''
    ];
    
    // Имитируем логику из ProjectHandler
    if ($main === 'Работы по графику' || $main === 'Подписание акта ШМР' || $main === 'Шеф-монтажные работы') {
        $result['osnovnaya'] = $main;
        
        // Handle combined format: "Work - Category"
        if (isset($subs[0]) && strpos($subs[0], ' - ') !== false) {
            // Split by " - " separator
            $parts = explode(' - ', $subs[0], 2);
            $result['tip1'] = trim($parts[0]); // Work type
            $result['tip2'] = trim($parts[1]); // Category/Stage
            // Any additional subs go to tip3
            $result['tip3'] = $subs[1] ?? '';
        } else {
            // Normal format: separate subcategories
            $result['tip1'] = $subs[0] ?? '';
            $result['tip2'] = $subs[1] ?? '';
            $result['tip3'] = $subs[2] ?? '';
        }
    } elseif (empty($subs)) {
        // No subcategories - main is the activity itself
        $result['osnovnaya'] = $main;
    } else {
        // Default logic
        $result['osnovnaya'] = $main;
        $result['tip1'] = $subs[0] ?? '';
        $result['tip2'] = $subs[1] ?? '';
        $result['tip3'] = $subs[2] ?? '';
    }
    
    return $result;
}
