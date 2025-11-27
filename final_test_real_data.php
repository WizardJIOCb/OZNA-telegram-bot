<?php
/**
 * Финальный тест с реальными данными из базы
 */

echo "====================================\n";
echo "ФИНАЛЬНЫЙ ТЕСТ С РЕАЛЬНЫМИ ДАННЫМИ\n";
echo "====================================\n\n";

// Реальные данные из базы
$realEntries = array(
    array(
        'user_id' => 6095261415,
        'fio' => 'Нигматзянов Азат Закиевич',
        'fn' => 'Шеф-монтаж',
        'dt' => '2025-10-25 14:43:43',
        'message' => '<b>Нигматзянов Азат Закиевич</b>\r\n😨 ⚠️ 25.10.2025, Проект <b>ОИ819</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⏸️ Простой (Шеф-монтаж) (Прочее (Отсутствует готовность ГИ заказчиком))</b>'
    ),
    array(
        'user_id' => 965123843,
        'fio' => 'Шарипов Ильнур Флюсьевич',
        'fn' => 'Шеф-монтаж',
        'dt' => '2025-10-25 14:18:43',
        'message' => '<b>Шарипов Ильнур Флюсьевич</b>\r\n😨 ⚠️ 25.10.2025, Проект <b>ОИ873</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⏸️ Простой (Шеф-монтаж) (Готовность Заказчика (Отсутствие строительной готовности))</b>'
    )
);

// Функции парсинга (копии из ProjectHandler)
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

function extractSubs($s) {
    $subs = array();
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

function parseDescription($desc) {
    $desc = trim($desc);
    $desc = removeEmojis($desc);
    $desc = trim($desc);
    
    // Find first parenthesis
    $firstParen = strpos($desc, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($desc, 0, $firstParen));
        $subsString = trim(substr($desc, $firstParen));
    } else {
        $mainPart = $desc;
        $subsString = '';
    }
    
    $subs = extractSubs($subsString);
    
    // If we have one group without nested parentheses, split by commas
    if (count($subs) === 1 && strpos($subs[0], '(') === false && strpos($subs[0], ',') !== false) {
        $subs = array_map('trim', explode(',', $subs[0]));
    }
    
    return array('main' => $mainPart, 'subs' => $subs);
}

function getProcessActivity($process) {
    if ($process === 'Шеф-монтаж') {
        return 'Шеф-монтажные работы';
    } elseif ($process === 'Пусконаладка') {
        return 'Пусконаладочные работы';
    }
    return '';
}

function parseReportEntry($message, $functionalDirection) {
    // Initialize default values
    $result = array(
        'project' => '',
        'date' => '',
        'process' => $functionalDirection,
        'vid' => '',
        'osnovnaya' => '',
        'tip1' => '',
        'tip2' => '',
        'tip3' => ''
    );
    
    // Try to parse project-based entry format:
    // "<b>ФИО</b>\r\n✅Дата, Проект <b>КОД</b>: Добавлена запись в табель: <b>ОПИСАНИЕ</b>"
    if (preg_match('/✅(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = $matches[2];
        $description = $matches[3];
    } 
    // Try to parse INCIDENT format (Простой entries):
    // "<b>ФИО</b>\r\n😨 ⚠️ Дата, Проект <b>КОД</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>ОПИСАНИЕ</b>"
    elseif (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>(.*?)<\/b>/', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = $matches[2];
        $description = $matches[3];
    } 
    // Try to parse current activity format (Текущая деятельность):
    // "✅Запись в табель от <b>ДД.ММ.ГГГГ</b>: Текущая деятельность (<b>ОПИСАНИЕ</b>) добавлена"
    elseif (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>: Текущая деятельность \(<b>(.*?)<\/b>\) добавлена/', $message, $matches)) {
        $result['date'] = $matches[1];
        $result['project'] = '';
        $description = $matches[2];
    } else {
        // Cannot parse - return defaults
        return $result;
    }
    
    // Parse description
    $parsed = parseDescription($description);
    $main = $parsed['main'];
    $subs = $parsed['subs'];
    
    $vid = $main;
    $osnovnaya = '';
    $tip1 = '';
    $tip2 = '';
    $tip3 = '';
    
    // Logic based on main activity type
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
        // Parse idle time structure: Простой (Reason (Details (SubDetails)))
        if (count($subs) >= 1) {
            $tip1 = $subs[0]; // First reason (e.g., "Ожидание материалов")
        }
        if (count($subs) >= 2) {
            // Check if second sub has nested structure
            $subParsed = parseDescription($subs[1]);
            $tip2 = $subParsed['main'];
            if (!empty($subParsed['subs'])) {
                $tip3 = $subParsed['subs'][0];
            }
        }
    } elseif ($main === 'Работы по графику' || $main === 'Подписание акта ШМР') {
        $vid = $main;
        $osnovnaya = getProcessActivity($result['process']);
        
        // Handle combined format: "Work - Category" (e.g., "Монтаж оборудования - Электрооборудование")
        if (isset($subs[0]) && strpos($subs[0], ' - ') !== false) {
            // Split by " - " separator
            $parts = explode(' - ', $subs[0], 2);
            $tip1 = trim($parts[0]); // Work type
            $tip2 = trim($parts[1]); // Category/Stage
            // Any additional subs go to tip3
            $tip3 = $subs[1] ?? '';
        } else {
            // Normal format: separate subcategories
            $tip1 = $subs[0] ?? '';
            $tip2 = $subs[1] ?? '';
        }
    } elseif ($main === 'Участие в ЗПИ') {
        $vid = $main;
        $osnovnaya = 'Проектная деятельность';
    } else {
        // Default logic based on process
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

// Обрабатываем реальные записи
echo "Обработка реальных записей:\n\n";

foreach ($realEntries as $index => $entry) {
    echo "Запись " . ($index + 1) . ":\n";
    echo "  ФИО: " . $entry['fio'] . "\n";
    echo "  Сообщение: " . $entry['message'] . "\n\n";
    
    $parsed = parseReportEntry($entry['message'], $entry['fn']);
    
    echo "  Результат парсинга:\n";
    foreach ($parsed as $key => $value) {
        echo "    {$key}: '{$value}'\n";
    }
    
    // Проверяем "готовность"
    if (strpos($parsed['tip3'], 'готовность') !== false) {
        echo "  ✅ 'готовность' найдена в tip3: '{$parsed['tip3']}'\n";
    } elseif (strpos($parsed['tip2'], 'готовность') !== false) {
        echo "  ✅ 'готовность' найдена в tip2: '{$parsed['tip2']}'\n";
    } elseif (strpos($parsed['tip1'], 'готовность') !== false) {
        echo "  ✅ 'готовность' найдена в tip1: '{$parsed['tip1']}'\n";
    } else {
        echo "  ❌ 'готовность' не найдена в результатах\n";
    }
    
    echo "\n";
}

// Генерируем CSV
echo "Генерация CSV:\n";
echo "Проект,Дата,Процесс,Вид деятельности,Основная активность,Тип работ,Тип работ 2,Тип работ 3,Фамилия,,\n";

foreach ($realEntries as $entry) {
    $parsed = parseReportEntry($entry['message'], $entry['fn']);
    $csvRow = array(
        $parsed['project'],
        $parsed['date'],
        $parsed['process'],
        $parsed['vid'],
        $parsed['osnovnaya'],
        $parsed['tip1'],
        $parsed['tip2'],
        $parsed['tip3'],
        $entry['fio'],
        '',
        ''
    );
    
    echo implode(',', $csvRow) . "\n";
}

echo "\n====================================\n";
echo "ТЕСТ ЗАВЕРШЕН\n";
echo "====================================\n";

// Проверим, правильно ли обрабатывается конкретная структура
echo "\nДетальная проверка структуры 'готовность':\n";
$testMessage = '<b>Нигматзянов Азат Закиевич</b>\r\n😨 ⚠️ 25.10.2025, Проект <b>ОИ819</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⏸️ Простой (Шеф-монтаж) (Прочее (Отсутствует готовность ГИ заказчиком))</b>';

echo "Тестовое сообщение: " . $testMessage . "\n\n";

// Извлекаем описание
if (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>(.*?)<\/b>/', $testMessage, $matches)) {
    $description = $matches[3];
    echo "Извлеченное описание: " . $description . "\n";
    
    // Удаляем эмодзи
    $cleanDesc = removeEmojis($description);
    echo "После удаления эмодзи: " . $cleanDesc . "\n";
    
    // Парсим описание
    $parsedDesc = parseDescription($cleanDesc);
    echo "Результат parseDescription:\n";
    echo "  main: '" . $parsedDesc['main'] . "'\n";
    echo "  subs: " . json_encode($parsedDesc['subs'], JSON_UNESCAPED_UNICODE) . "\n";
    
    if (count($parsedDesc['subs']) >= 2) {
        $secondSub = $parsedDesc['subs'][1];
        echo "Вторая подкатегория: '" . $secondSub . "'\n";
        
        // Рекурсивный парсинг второй подкатегории
        $parsedSecond = parseDescription($secondSub);
        echo "Результат парсинга второй подкатегории:\n";
        echo "  main: '" . $parsedSecond['main'] . "'\n";
        echo "  subs: " . json_encode($parsedSecond['subs'], JSON_UNESCAPED_UNICODE) . "\n";
        
        if (count($parsedSecond['subs']) >= 1) {
            $tip3 = $parsedSecond['subs'][0];
            echo "tip3 (первая подкатегория второй подкатегории): '" . $tip3 . "'\n";
            
            if (strpos($tip3, 'готовность') !== false) {
                echo "✅ 'готовность' правильно извлечена!\n";
            }
        }
    }
}

echo "\n====================================\n";