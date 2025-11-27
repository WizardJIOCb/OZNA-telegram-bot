<?php
/**
 * Тест парсинга сообщений для отчёта
 */

// Тестовые данные - примеры реальных сообщений
$testMessages = [
    [
        'message' => '✅24.10.2025, Проект <b>ОИ743-03</b>: Добавлена запись в табель: <b>📋 Подписание акта ШМР</b>',
        'fn' => 'Шеф-монтаж',
        'expected' => [
            'project' => 'ОИ743-03',
            'date' => '24.10.2025',
            'process' => 'Шеф-монтаж',
            'vid' => 'Подписание акта ШМР',
            'osnovnaya' => 'Шеф-монтажные работы'
        ]
    ],
    [
        'message' => '✅24.10.2025, Проект <b>ОИ696</b>: Добавлена запись в табель: <b>📅 Работы по графику (Сборка кровли)</b>',
        'fn' => 'Шеф-монтаж',
        'expected' => [
            'project' => 'ОИ696',
            'date' => '24.10.2025',
            'process' => 'Шеф-монтаж',
            'vid' => 'Работы по графику',
            'osnovnaya' => 'Шеф-монтажные работы',
            'tip1' => 'Сборка кровли'
        ]
    ],
    [
        'message' => '✅24.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>🔀 Изменение схемы подключения (Электрооборудование, Дополнительные работы)</b>',
        'fn' => 'Пусконаладка',
        'expected' => [
            'project' => 'БКНС120',
            'date' => '24.10.2025',
            'process' => 'Пусконаладка',
            'vid' => 'Изменение схемы подключения',
            'osnovnaya' => 'Дополнительные работы',
            'tip1' => 'Электрооборудование'
        ]
    ],
    [
        'message' => '✅23.10.2025, Проект <b>ОИ846</b>: Добавлена запись в табель: <b>⚡️Электромонтажные работы (Досборка)</b>',
        'fn' => 'Мобильная бригада',
        'expected' => [
            'project' => 'ОИ846',
            'date' => '23.10.2025',
            'process' => 'Мобильная бригада',
            'vid' => 'Электромонтажные работы',
            'osnovнaya' => 'Досборка'
        ]
    ],
    [
        'message' => '✅Запись в табель от <b>01.10.2025</b>: Текущая деятельность (<b>Медицинский осмотр</b>) добавлена',
        'fn' => 'Пусконаладка',
        'expected' => [
            'project' => '',
            'date' => '01.10.2025',
            'process' => 'Пусконаладка',
            'vid' => 'Медицинский осмотр',
            'osnovнaya' => ''
        ]
    ],
    [
        'message' => '<b>Нигматзянов Азат Закиевич</b>\r\nunden ⚠️ 25.10.2025, Проект <b>ОИ819</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⏸️ Простой (Шеф-монтаж) (Прочее (Отсутствует готовность ГИ заказчиком))</b>',
        'fn' => 'Шеф-монтаж',
        'expected' => [
            'project' => 'ОИ819',
            'date' => '25.10.2025',
            'process' => 'Шеф-монтаж',
            'vid' => '',
            'osnovнaya' => 'Простой',
            'tip1' => 'Шеф-монтаж',
            'tip2' => 'Прочее',
            'tip3' => 'Отсутствует готовность ГИ заказчиком'
        ]
    ]
];

// Функция для удаления эмодзи (копия из ProjectHandler с улучшениями)
function removeEmojis($text) {
    // Remove emojis with comprehensive regex
    $text = preg_replace('/[\x{1F600}-\x{1FFFF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}]+/u', '', $text);
    $text = trim($text);
    
    // Special handling for INCIDENT format - remove pause emoji from main
    if (strpos($text, 'Простой') !== false) {
        // Remove pause emoji (both with and without variation selector)
        $text = preg_replace('/⏸[\x{FE0F}]?/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
        $text = trim($text);
    }
    
    return $text;
}

// Функция парсинга описания
function parseDescription($desc) {
    $desc = trim($desc);
    $desc = removeEmojis($desc);
    $desc = trim($desc);
    
    // Находим первую скобку
    $firstParen = strpos($desc, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($desc, 0, $firstParen));
        $subsString = trim(substr($desc, $firstParen));
    } else {
        $mainPart = $desc;
        $subsString = '';
    }
    
    $subs = extractSubs($subsString);
    
    
    // Если у нас одна группа скобок без вложенных скобок, разделяем по запятым
    if (count($subs) === 1 && strpos($subs[0], '(') === false && strpos($subs[0], ',') !== false) {
        $subs = array_map('trim', explode(',', $subs[0]));
    }
    
    return [
        'main' => $mainPart,
        'subs' => $subs
    ];
}

// Функция извлечения подкатегорий
function extractSubs($s) {
    $subs = [];
    $s = trim($s);
    
    if (empty($s)) {
        return $subs;
    }
    
    // Split by top-level parentheses
    $i = 0;
    $len = strlen($s);
    
    while ($i < $len) {
        if ($s[$i] === '(') {
            $start = $i;
            $count = 1;
            $i++;
            
            while ($i < $len && $count > 0) {
                if ($s[$i] === '(') {
                    $count++;
                } elseif ($s[$i] === ')') {
                    $count--;
                }
                $i++;
            }
            
            if ($count === 0) {
                $sub = trim(substr($s, $start + 1, $i - $start - 2));
                $subs[] = $sub;
                // Skip spaces after closing bracket
                while ($i < $len && $s[$i] === ' ') {
                    $i++;
                }
                continue;
            }
        } else {
            $i++;
        }
    }
    
    return $subs;
}

// Функция получения активности по процессу
function getProcessActivity($process) {
    if ($process === 'Шеф-монтаж') {
        return 'Шеф-монтажные работы';
    } elseif ($process === 'Пусконаладка') {
        return 'Пусконаладочные работы';
    }
    return '';
}

// Основная функция парсинга
function parseReportEntry($message, $functionalDirection) {
    $result = [
        'project' => '',
        'date' => '',
        'process' => $functionalDirection,
        'vid' => '',
        'osnovnaya' => '',
        'tip1' => '',
        'tip2' => '',
        'tip3' => ''
    ];
    
    // Парсинг проектных записей
    if (preg_match('/✅(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = $matches[2];
        $description = $matches[3];
    } 
    // Парсинг INCIDENT формата (Простой)
    elseif (preg_match('/unden ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>(.*?)<\/b>/', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = $matches[2];
        $description = $matches[3];
    }
    // Парсинг текущей деятельности
    elseif (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>: Текущая деятельность \(<b>(.*?)<\/b>\) добавлена/', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = '';
        $description = $matches[2];
    } else {
        return $result;
    }
    
    // Парсинг описания
    $parsed = parseDescription($description);
    $main = $parsed['main'];
    $subs = $parsed['subs'];
    
    $vid = $main;
    $osnovnaya = '';
    $tip1 = '';
    $tip2 = '';
    $tip3 = '';
    
    // Логика в зависимости от типа активности
    if ($main === 'Прочее') {
        if (count($subs) >= 2) {
            if ($subs[0] === 'Проектная деятельность') {
                $vid = $subs[1];
                $osnovnaya = $subs[0];
            } else {
                $vid = $subs[1];
                $osnovnaya = getProcessActivity($result['process']);
                $tip1 = $subs[0];
            }
        }
    } elseif ($main === 'Простой') {
        $vid = '';
        $osnovnaya = $main;
        // Парсим структуру простоя: Простой (Причина (Детали (Поддетали)))
        if (count($subs) >= 1) {
            $tip1 = $subs[0]; // Первая причина (например, "Шеф-монтаж")
        }
        if (count($subs) >= 2) {
            // Проверяем, есть ли вложенные структуры
            $subParsed = parseDescription($subs[1]);
            $tip2 = $subParsed['main'];
            if (!empty($subParsed['subs'])) {
                $tip3 = $subParsed['subs'][0];
            }
        }
    } elseif ($main === 'Работы по графику' || $main === 'Подписание акта ШМР') {
        $vid = $main;
        $osnovnaya = getProcessActivity($result['process']);
        $tip1 = $subs[0] ?? '';
        $tip2 = $subs[1] ?? '';
    } elseif ($main === 'Участие в ЗПИ') {
        $vid = $main;
        $osnovnaya = 'Проектная деятельность';
    } else {
        // Логика по умолчанию в зависимости от процесса
        if ($result['process'] === 'Мобильная бригада') {
            $osnovnaya = $subs[0] ?? '';
            $tip1 = $subs[1] ?? '';
            $tip2 = $subs[2] ?? '';
            $tip3 = $subs[3] ?? '';
        } else {
            $osnovnaya = $subs[1] ?? '';
            $tip1 = $subs[0] ?? '';
            $tip2 = $subs[2] ?? '';
            $tip3 = $subs[3] ?? '';
        }
    }
    
    $result['vid'] = $vid;
    $result['osnovnaya'] = $osnovnaya;
    $result['tip1'] = $tip1;
    $result['tip2'] = $tip2;
    $result['tip3'] = $tip3;
    
    return $result;
}

// Запуск тестов
echo "====================================\n";
echo "ТЕСТ ПАРСИНГА ОТЧЁТОВ\n";
echo "====================================\n\n";

$passed = 0;
$failed = 0;

foreach ($testMessages as $index => $test) {
    echo "Тест #" . ($index + 1) . ":\n";
    echo "Сообщение: " . substr($test['message'], 0, 100) . "...\n";
    
    $result = parseReportEntry($test['message'], $test['fn']);
    
    $success = true;
    foreach ($test['expected'] as $key => $expectedValue) {
        if ($result[$key] !== $expectedValue) {
            $success = false;
            echo "  ❌ ОШИБКА: {$key} = '{$result[$key]}' (ожидалось: '{$expectedValue}')\n";
        }
    }
    
    // Дополнительная проверка на отсутствие эмодзи
    $hasEmoji = false;
    foreach ($result as $value) {
        if (preg_match('/[\x{1F600}-\x{1FFFF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}⏸️⏸]+/u', $value)) {
            $hasEmoji = true;
            echo "  ❌ ОШИБКА: Найдены эмодзи в результате: '{$value}'\n";
            break;
        }
    }
    
    if ($success && !$hasEmoji) {
        echo "  ✅ УСПЕХ\n";
        $passed++;
    } else {
        $failed++;
        echo "  Результат парсинга:\n";
        foreach ($result as $key => $value) {
            echo "    {$key}: '{$value}'\n";
        }
    }
    
    echo "\n";
}

echo "====================================\n";
echo "РЕЗУЛЬТАТЫ:\n";
echo "  Успешно: {$passed}\n";
echo "  Провалено: {$failed}\n";
echo "====================================\n";

// Дополнительный тест на эмодзи
echo "\n====================================\n";
echo "ДОПОЛНИТЕЛЬНЫЙ ТЕСТ НА ЭМОДЗИ\n";
echo "====================================\n";

$emojiTestCases = [
    '⏸️ Простой (Шеф-монтаж)' => 'Простой (Шеф-монтаж)',
    '📋 Подписание акта ШМР' => 'Подписание акта ШМР',
    '⚡️Электромонтажные работы (Досборка)' => 'Электромонтажные работы (Досборка)'
];

foreach ($emojiTestCases as $input => $expected) {
    $result = removeEmojis($input);
    if ($result === $expected) {
        echo "✅ УСПЕХ: '{$input}' -> '{$result}'\n";
    } else {
        echo "❌ ОШИБКА: '{$input}' -> '{$result}' (ожидалось: '{$expected}')\n";
    }
}

echo "====================================\n";

// Диагностический тест для проверки парсинга структуры "Простой"

echo "\n====================================\n";
echo "ДИАГНОСТИЧЕСКИЙ ТЕСТ: ГОТОВНОСТЬ ЗАКАЗЧИКА\n";
echo "====================================\n";

// Тестовые случаи для "готовность заказчика"
$readinessTests = [
    'Простой (Шеф-монтаж) (Прочее (Отсутствует готовность ГИ заказчиком))',
    'Простой (Шеф-монтаж) (Готовность Заказчика (Отсутствие строительной готовности))'
];

foreach ($readinessTests as $index => $test) {
    echo "\nТест готовности #" . ($index + 1) . ":\n";
    echo "Вход: {$test}\n";
    
    $parsed = parseDescription($test);
    echo "Основная часть: '{$parsed['main']}'\n";
    echo "Подкатегории: " . json_encode($parsed['subs'], JSON_UNESCAPED_UNICODE) . "\n";
    
    if (count($parsed['subs']) >= 2) {
        $subParsed = parseDescription($parsed['subs'][1]);
        echo "Вложенная структура:\n";
        echo "  Основная: '{$subParsed['main']}'\n";
        echo "  Подкатегории: " . json_encode($subParsed['subs'], JSON_UNESCAPED_UNICODE) . "\n";
        
        if (!empty($subParsed['subs'])) {
            echo "  Готовность (tip3): '{$subParsed['subs'][0]}'\n";
        }
    }
}

echo "\n====================================\n";
echo "ТЕСТ ЗАВЕРШЕН\n";
echo "====================================\n";
