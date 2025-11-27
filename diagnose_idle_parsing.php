<?php
/**
 * Диагностический тест для проверки парсинга структуры "Простой"
 */

// Тестовое сообщение с эмодзи из INCIDENT формата
$testMessage = '<b>Нигматзянов Азат Закиевич</b>\r\n😨 ⚠️ 25.10.2025, Проект <b>ОИ819</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⏸️ Простой (Шеф-монтаж) (Прочее (Отсутствует готовность ГИ заказчиком))</b>';

echo "====================================\n";
echo "ДИАГНОСТИЧЕСКИЙ ТЕСТ ПАРСИНГА ПРОСТОЯ\n";
echo "====================================\n\n";

echo "Тестовое сообщение:\n";
echo $testMessage . "\n\n";

// Имитируем парсинг INCIDENT формата
if (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>(.*?)<\/b>/', $testMessage, $matches)) {
    $date = $matches[1];
    $project = $matches[2];
    $description = $matches[3];
    
    echo "Извлеченные данные:\n";
    echo "  Дата: " . $date . "\n";
    echo "  Проект: " . $project . "\n";
    echo "  Описание: " . $description . "\n\n";
    
    // Улучшенная функция удаления эмодзи
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
    
    // Применяем улучшенное удаление эмодзи
    $cleanDescription = removeEmojis($description);
    echo "Описание после удаления эмодзи: " . $cleanDescription . "\n\n";
    
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
    echo "  Основная часть: '" . $mainPart . "'\n";
    echo "  Строка подкатегорий: '" . $subsString . "'\n\n";
    
    $subs = extractSubs($subsString);
    
    echo "Подкатегории (первый уровень):\n";
    foreach ($subs as $index => $sub) {
        echo "  [" . $index . "]: '" . $sub . "'\n";
    }
    echo "\n";
    
    // Теперь имитируем логику из ProjectHandler для обработки простоя
    echo "ЛОГИКА ОБРАБОТКИ ПРОСТОЯ (как в ProjectHandler):\n";
    
    if ($mainPart === 'Простой') {
        echo "  ✅ Основная активность определена как 'Простой'\n";
        
        // tip1 - первая причина
        if (count($subs) >= 1) {
            $tip1 = $subs[0];
            echo "  ✅ tip1 (первая причина): '" . $tip1 . "'\n";
        }
        
        // tip2 и tip3 - из второй подкатегории
        if (count($subs) >= 2) {
            echo "  ✅ Вторая подкатегория: '" . $subs[1] . "'\n";
            
            // Здесь должна быть рекурсивная обработка второй подкатегории
            // Как в ProjectHandler - вызываем parseDescription для второй подкатегории
            $secondSub = $subs[1];
            
            // Имитируем parseDescription для второй подкатегории
            echo "  🔍 Рекурсивный парсинг второй подкатегории: '" . $secondSub . "'\n";
            
            // Убираем эмодзи (хотя их быть не должно)
            $secondSubClean = removeEmojis($secondSub);
            echo "    После очистки: '" . $secondSubClean . "'\n";
            
            // Ищем первую скобку
            $secondFirstParen = strpos($secondSubClean, '(');
            if ($secondFirstParen !== false) {
                $secondMainPart = trim(substr($secondSubClean, 0, $secondFirstParen));
                $secondSubsString = trim(substr($secondSubClean, $secondFirstParen));
            } else {
                $secondMainPart = $secondSubClean;
                $secondSubsString = '';
            }
            
            echo "    Основная часть второй подкатегории: '" . $secondMainPart . "'\n";
            echo "    Подстрока скобок второй подкатегории: '" . $secondSubsString . "'\n";
            
            // Извлекаем подкатегории второй подкатегории
            $secondSubs = extractSubs($secondSubsString);
            
            echo "    Подкатегории второй подкатегории:\n";
            foreach ($secondSubs as $index => $sub) {
                echo "      [" . $index . "]: '" . $sub . "'\n";
            }
            
            // tip2 - основная часть второй подкатегории
            $tip2 = $secondMainPart;
            echo "  ✅ tip2: '" . $tip2 . "'\n";
            
            // tip3 - первая подкатегория из второй подкатегории
            if (count($secondSubs) >= 1) {
                $tip3 = $secondSubs[0];
                echo "  ✅ tip3: '" . $tip3 . "'\n";
            } else {
                $tip3 = '';
                echo "  ⚠️ tip3: пусто\n";
            }
        }
        
        echo "\n  ИТОГОВЫЙ РЕЗУЛЬТАТ:\n";
        echo "    osnovnaya: 'Простой'\n";
        echo "    vid: '' (пусто для простоя)\n";
        echo "    tip1: '" . (isset($tip1) ? $tip1 : '') . "'\n";
        echo "    tip2: '" . (isset($tip2) ? $tip2 : '') . "'\n";
        echo "    tip3: '" . (isset($tip3) ? $tip3 : '') . "'\n";
    }
} else {
    echo "❌ ОШИБКА: Не удалось распарсить INCIDENT формат!\n";
}

echo "\n====================================\n";
echo "ДИАГНОСТИКА ЗАВЕРШЕНА\n";
echo "====================================\n";