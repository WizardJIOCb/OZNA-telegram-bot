<?php
/**
 * Тестирование реального ProjectHandler для проверки парсинга "Простой"
 */

// Создаем тестовое сообщение
$testMessage = '<b>Нигматзянов Азат Закиевич</b>\r\n😨 ⚠️ 25.10.2025, Проект <b>ОИ819</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⏸️ Простой (Шеф-монтаж) (Прочее (Отсутствует готовность ГИ заказчиком))</b>';
$functionalDirection = 'Шеф-монтаж';

echo "====================================\n";
echo "ТЕСТ РЕАЛЬНОГО PROJECTHANDLER\n";
echo "====================================\n\n";

echo "Тестовое сообщение:\n";
echo $testMessage . "\n\n";

// Функция удаления эмодзи (копия из ProjectHandler)
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

// Имитируем парсинг INCIDENT формата
if (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>(.*?)<\/b>/', $testMessage, $matches)) {
    $date = $matches[1];
    $project = $matches[2];
    $description = $matches[3];
    
    echo "Извлеченные данные:\n";
    echo "  Дата: {$date}\n";
    echo "  Проект: {$project}\n";
    echo "  Описание: {$description}\n\n";
    
    // Применяем улучшенное удаление эмодзи
    $cleanDescription = removeEmojis($description);
    echo "Описание после удаления эмодзи: {$cleanDescription}\n\n";
    
    // Извлекаем основную часть и подкатегории
    $firstParen = strpos($cleanDescription, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($cleanDescription, 0, $firstParen));
        $subsString = trim(substr($cleanDescription, $firstParen));
    } else {
        $mainPart = $cleanDescription;
        $subsString = '';
    }
    
    echo "Разбор описания:\n";
    echo "  Основная часть: '{$mainPart}'\n";
    echo "  Строка подкатегорий: '{$subsString}'\n\n";
    
    $subs = extractSubs($subsString);
    
    echo "Подкатегории (первый уровень):\n";
    foreach ($subs as $index => $sub) {
        echo "  [{$index}]: '{$sub}'\n";
    }
    echo "\n";
    
    // Логика обработки простоя (как в ProjectHandler)
    if ($mainPart === 'Простой') {
        echo "ЛОГИКА ОБРАБОТКИ ПРОСТОЯ (как в ProjectHandler):\n";
        
        $vid = '';
        $osnovnaya = $mainPart;
        $tip1 = '';
        $tip2 = '';
        $tip3 = '';
        
        // tip1 - первая причина
        if (count($subs) >= 1) {
            $tip1 = $subs[0];
            echo "  ✅ tip1 (первая причина): '{$tip1}'\n";
        }
        
        // tip2 и tip3 - из второй подкатегории
        if (count($subs) >= 2) {
            echo "  ✅ Вторая подкатегория: '{$subs[1]}'\n";
            
            // Рекурсивный парсинг второй подкатегории (как в ProjectHandler)
            $secondSub = $subs[1];
            $secondSubClean = removeEmojis($secondSub);
            
            $secondFirstParen = strpos($secondSubClean, '(');
            if ($secondFirstParen !== false) {
                $secondMainPart = trim(substr($secondSubClean, 0, $secondFirstParen));
                $secondSubsString = trim(substr($secondSubClean, $secondFirstParen));
            } else {
                $secondMainPart = $secondSubClean;
                $secondSubsString = '';
            }
            
            $secondSubs = extractSubs($secondSubsString);
            
            // tip2 - основная часть второй подкатегории
            $tip2 = $secondMainPart;
            echo "  ✅ tip2: '{$tip2}'\n";
            
            // tip3 - первая подкатегория из второй подкатегории
            if (count($secondSubs) >= 1) {
                $tip3 = $secondSubs[0];
                echo "  ✅ tip3: '{$tip3}'\n";
            } else {
                echo "  ⚠️ tip3: пусто\n";
            }
        }
        
        echo "\n  ИТОГОВЫЙ РЕЗУЛЬТАТ:\n";
        echo "    project: '{$project}'\n";
        echo "    date: '{$date}'\n";
        echo "    process: '{$functionalDirection}'\n";
        echo "    vid: '{$vid}'\n";
        echo "    osnovnaya: '{$osnovnaya}'\n";
        echo "    tip1: '{$tip1}'\n";
        echo "    tip2: '{$tip2}'\n";
        echo "    tip3: '{$tip3}'\n";
        
        // Проверяем ожидаемый результат
        $expected = array(
            'project' => 'ОИ819',
            'date' => '25.10.2025',
            'process' => 'Шеф-монтаж',
            'vid' => '',
            'osnovnaya' => 'Простой',
            'tip1' => 'Шеф-монтаж',
            'tip2' => 'Прочее',
            'tip3' => 'Отсутствует готовность ГИ заказчиком'
        );
        
        $result = array(
            'project' => $project,
            'date' => $date,
            'process' => $functionalDirection,
            'vid' => $vid,
            'osnovnaya' => $osnovnaya,
            'tip1' => $tip1,
            'tip2' => $tip2,
            'tip3' => $tip3
        );
        
        echo "\nПроверка корректности:\n";
        $allCorrect = true;
        foreach ($expected as $key => $expectedValue) {
            if ($result[$key] !== $expectedValue) {
                echo "  ❌ Несоответствие в {$key}: '{$result[$key]}' (ожидалось: '{$expectedValue}')\n";
                $allCorrect = false;
            } else {
                echo "  ✅ {$key}: '{$result[$key]}'\n";
            }
        }
        
        if ($allCorrect) {
            echo "\n🎉 ВСЕ ЗНАЧЕНИЯ КОРРЕКТНЫ!\n";
        } else {
            echo "\n❌ Обнаружены ошибки в парсинге\n";
        }
    }
} else {
    echo "❌ Не удалось распарсить INCIDENT формат\n";
}

echo "\n====================================\n";
echo "ТЕСТ ЗАВЕРШЕН\n";
echo "====================================\n";