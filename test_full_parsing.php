<?php
/**
 * Full test of parsing logic for Lebedev's case
 */

echo "=== Full Parsing Test for Lebedev's Entry ===\n\n";

// Test message formats
$testMessages = [
    [
        'name' => 'Прочее БЕЗ комментария',
        'message' => "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>📝 Прочее (Электрооборудование, Дополнительные работы)</b>",
        'fn' => 'Пусконаладка'
    ],
    [
        'name' => 'Прочее С комментарием',
        'message' => "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>📝 Прочее (Электрооборудование, Дополнительные работы) (Настройка реле)</b>",
        'fn' => 'Пусконаладка'
    ],
    [
        'name' => 'Монтаж оборудования',
        'message' => "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>⚙️ Монтаж оборудования (Электрооборудование, Дополнительные работы)</b>",
        'fn' => 'Пусконаладка'
    ],
    [
        'name' => 'Пусконаладочные работы (generic)',
        'message' => "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>⚙️ Пусконаладочные работы (Электрооборудование, Дополнительные работы)</b>",
        'fn' => 'Пусконаладка'
    ],
    [
        'name' => 'Неизвестная активность (default case)',
        'message' => "<b>Лебедев Андрей Валериевич</b>\r\n✅09.10.2025, Проект <b>БКНС120</b>: Добавлена запись в табель: <b>Какая-то работа (Электрооборудование, Дополнительные работы)</b>",
        'fn' => 'Пусконаладка'
    ]
];

// Simulate parseDescription
function parseDescription(string $desc): array {
    $desc = trim($desc);
    
    // Remove emojis
    $desc = preg_replace('/[\x{1F600}-\x{1FFFF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}]+/u', '', $desc);
    $desc = trim($desc);
    
    $firstParen = strpos($desc, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($desc, 0, $firstParen));
        $subsString = trim(substr($desc, $firstParen));
    } else {
        $mainPart = $desc;
        $subsString = '';
    }
    
    $subs = extractSubs($subsString);
    
    return ['main' => $mainPart, 'subs' => $subs];
}

function extractSubs(string $s): array {
    $subs = [];
    $s = trim($s);
    
    if (empty($s)) {
        return $subs;
    }
    
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

function simulateParsing($message, $fn) {
    // Extract description
    if (preg_match('/: <b>(.*?)<\/b>/', $message, $matches)) {
        $description = $matches[1];
    } else {
        return null;
    }
    
    $parsed = parseDescription($description);
    $main = $parsed['main'];
    $subs = $parsed['subs'];
    
    $result = [
        'osnovnaya' => '',
        'tip1' => '',
        'tip2' => '',
        'tip3' => '',
        'tip4' => '',
        'condition' => 'NONE'
    ];
    
    // Check conditions in order
    if (strpos($main, 'Прочее') !== false && isset($subs[0])) {
        $result['condition'] = "Прочее with isset(\$subs[0])";
        $result['osnovnaya'] = 'Пусконаладочные работы';
        
        $firstSub = $subs[0];
        if (strpos($firstSub, ',') !== false) {
            $parts = explode(',', $firstSub, 2);
            $result['tip1'] = trim($parts[0]);
            $result['tip2'] = trim($parts[1]);
        } else {
            $result['tip1'] = $firstSub;
            $result['tip2'] = 'Дополнительные работы';
        }
        
        $result['tip3'] = 'Прочее';
        $result['tip4'] = $subs[1] ?? '';
        
    } elseif (in_array($main, ['Изменение схемы подключения', 'Прокладка кабеля', 'Подключение кабеля',
                      'Монтаж оборудования', 'Монтаж оборудования АСУ ТП', 'Удаленный ПНР ПО',
                      'Изменение конфигурации СИ', 'Калибровка/поверка оборудования', 'КМХ СИ', 'Коррекция характеристик СИ',
                      'Демонтаж/ревизия арматуры', 'Демонтаж/ревизия техн. оборудования']) && isset($subs[0])) {
        
        $result['condition'] = "ПНР Additional Works";
        $result['osnovnaya'] = 'Пусконаладочные работы';
        
        $firstSub = $subs[0];
        if (strpos($firstSub, ',') !== false) {
            $parts = explode(',', $firstSub, 2);
            $result['tip1'] = trim($parts[0]);
            $result['tip2'] = trim($parts[1]);
            $result['tip3'] = $main;
            $result['tip4'] = '';
        } else {
            $result['tip1'] = $firstSub;
            $result['tip2'] = 'Дополнительные работы';
            $result['tip3'] = $main;
            $result['tip4'] = '';
        }
        
    } elseif ($main === 'Пусконаладочные работы' && isset($subs[0])) {
        $result['condition'] = "Generic Пусконаладочные работы";
        $result['osnovnaya'] = $main;
        
        $firstSub = $subs[0];
        if (strpos($firstSub, ',') !== false) {
            $parts = explode(',', $firstSub, 2);
            $result['tip1'] = trim($parts[0]);
            $result['tip2'] = trim($parts[1]);
            $result['tip3'] = '';
            $result['tip4'] = '';
        } else {
            $result['tip1'] = $firstSub;
            $result['tip2'] = '';
            $result['tip3'] = '';
            $result['tip4'] = '';
        }
        
    } else {
        // Default
        $result['condition'] = "DEFAULT handler";
        $result['osnovnaya'] = $main;
        
        if (isset($subs[0]) && strpos($subs[0], ',') !== false) {
            $parts = explode(',', $subs[0], 2);
            $result['tip1'] = trim($parts[0]);
            $result['tip2'] = trim($parts[1]);
            $result['tip3'] = $subs[1] ?? '';
            $result['tip4'] = $subs[2] ?? '';
        } else {
            $result['tip1'] = $subs[0] ?? '';
            $result['tip2'] = $subs[1] ?? '';
            $result['tip3'] = $subs[2] ?? '';
            $result['tip4'] = $subs[3] ?? '';
        }
    }
    
    return $result;
}

foreach ($testMessages as $index => $test) {
    echo "Test " . ($index + 1) . ": {$test['name']}\n";
    echo str_repeat("-", 80) . "\n";
    
    $result = simulateParsing($test['message'], $test['fn']);
    
    if ($result) {
        echo "Matched condition: {$result['condition']}\n\n";
        echo "CSV Output:\n";
        echo "  Level 1 (Основная): {$result['osnovnaya']}\n";
        echo "  Level 2 (Тип1):     {$result['tip1']}\n";
        echo "  Level 3 (Тип2):     {$result['tip2']}\n";
        echo "  Level 4 (Тип3):     {$result['tip3']}\n";
        echo "  Level 5 (Тип4):     " . ($result['tip4'] ?: '(empty)') . "\n\n";
        
        // Check if Level 2 contains comma (error)
        if (strpos($result['tip1'], ',') !== false) {
            echo "❌ ERROR: Level 2 contains comma! Not properly split!\n";
        } else {
            echo "✅ Level 2 is clean (no comma)\n";
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
}

echo "\nExpected results:\n";
echo "- Level 2 should NEVER contain comma\n";
echo "- Equipment and Category should be split into separate levels\n";
echo "- All conditions should handle comma-separated values properly\n";
