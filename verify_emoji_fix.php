<?php
/**
 * Простой тест для проверки исправления проблемы с эмодзи
 */

// Подключаем только нужные функции из ProjectHandler
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

// Создаем тестовое сообщение
$testMessage = '<b>Нигматзянов Азат Закиевич</b>\r\n😨 ⚠️ 25.10.2025, Проект <b>ОИ819</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⏸️ Простой (Шеф-монтаж) (Прочее (Отсутствует готовность ГИ заказчиком))</b>';

echo "====================================\n";
echo "ПРОВЕРКА ИСПРАВЛЕНИЯ ПРОБЛЕМЫ С ЭМОДЗИ\n";
echo "====================================\n\n";

echo "Тестовое сообщение:\n";
echo $testMessage . "\n\n";

// Имитируем парсинг INCIDENT формата
if (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>(.*?)<\/b>/', $testMessage, $matches)) {
    $date = $matches[1];
    $project = $matches[2];
    $description = $matches[3];
    
    echo "Извлеченные данные:\n";
    echo "  Дата: {$date}\n";
    echo "  Проект: {$project}\n";
    echo "  Описание: {$description}\n\n";
    
    // Теперь проверим, как работает наша улучшенная функция parseDescription
    // Создадим её копию здесь для тестирования
    
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
    
    // Применяем улучшенное удаление эмодзи
    $cleanDescription = removeEmojis($description);
    echo "Описание после удаления эмодзи: {$cleanDescription}\n\n";
    
    // Извлекаем подкатегории
    $firstParen = strpos($cleanDescription, '(');
    if ($firstParen !== false) {
        $mainPart = trim(substr($cleanDescription, 0, $firstParen));
        $subsString = trim(substr($cleanDescription, $firstParen));
    } else {
        $mainPart = $cleanDescription;
        $subsString = '';
    }
    
    $subs = extractSubs($subsString);
    
    echo "Разбор описания:\n";
    echo "  Основная часть: '{$mainPart}'\n";
    echo "  Подкатегории: " . json_encode($subs, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Проверяем, что эмодзи больше не присутствуют
    if (strpos($mainPart, '⏸') === false && strpos($mainPart, '⏸️') === false) {
        echo "✅ УСПЕХ: Эмодзи паузы успешно удалены из основной части!\n";
    } else {
        echo "❌ ОШИБКА: Эмодзи паузы все еще присутствуют в основной части!\n";
    }
    
    // Проверяем структуру подкатегорий
    if (count($subs) >= 2) {
        echo "✅ УСПЕХ: Найдены две подкатегории:\n";
        echo "  1. '{$subs[0]}'\n";
        echo "  2. '{$subs[1]}'\n";
        
        // Проверяем вложенную структуру второй подкатегории
        $subParsed = extractSubs($subs[1]);
        if (count($subParsed) > 0) {
            echo "  Вложенная структура второй подкатегории: " . json_encode($subParsed, JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "❌ ОШИБКА: Не найдены необходимые подкатегории!\n";
    }
} else {
    echo "❌ ОШИБКА: Не удалось распарсить INCIDENT формат!\n";
}

echo "\n====================================\n";
echo "ПРОВЕРКА ЗАВЕРШЕНА\n";
echo "====================================\n";