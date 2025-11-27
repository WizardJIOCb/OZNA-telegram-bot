<?php

echo "=== ОТЛАДКА ПОСЛЕДОВАТЕЛЬНОСТИ ОБРАБОТКИ cat_ШМР_АналФунд_yes ===\n\n";

// Имитируем последовательность обработки callback'а
$category = "cat_ШМР_АналФунд_yes";

echo "1. CALLBACK ОБРАБОТКА:\n";
echo "   Входящий callback: '{$category}'\n\n";

// Проверяем isActivityExecution
echo "2. ПРОВЕРКА isActivityExecution:\n";
if (preg_match('/^cat_ШМР_(.+)_yes$/u', $category, $matches)) {
    $shortActivityKey = $matches[1];
    echo "   ✅ Соответствует паттерну: cat_ШМР_(.+)_yes\n";
    echo "   Извлеченный ключ: '{$shortActivityKey}'\n";
    
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    if (isset($simpleSupervisionMap[$shortActivityKey])) {
        $activityType = $simpleSupervisionMap[$shortActivityKey];
        echo "   ✅ Найдено в simpleSupervisionMap: '{$activityType}'\n";
        echo "   ✅ isActivityExecution() должен вернуть TRUE\n";
    } else {
        echo "   ❌ НЕ найдено в simpleSupervisionMap!\n";
    }
} else {
    echo "   ❌ НЕ соответствует паттерну\n";
}

echo "\n3. ПРОВЕРКА handleActivityExecution:\n";
if (preg_match('/^cat_ШМР_(.+)_yes$/u', $category, $matches)) {
    $shortActivityKey = $matches[1];
    echo "   Извлеченный ключ: '{$shortActivityKey}'\n";
    
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    if (isset($simpleSupervisionMap[$shortActivityKey])) {
        $activityType = $simpleSupervisionMap[$shortActivityKey];
        echo "   ✅ Сопоставлено с: '{$activityType}'\n";
        
        // Проверяем что activityType не пустой
        if (empty($activityType)) {
            echo "   ❌ ОШИБКА: activityType пустой!\n";
        } else {
            echo "   ✅ activityType корректный\n";
            
            // Проверяем createTabelMessage
            echo "\n4. ПРОВЕРКА createTabelMessage:\n";
            
            $activityTypeMappings = [
                'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки',
            ];
            
            $activityText = $activityTypeMappings[$activityType] ?? $activityType;
            echo "   Activity Type: '{$activityType}'\n";
            echo "   Activity Text: '{$activityText}'\n";
            
            if ($activityText === $activityType) {
                echo "   ⚠️ ПРЕДУПРЕЖДЕНИЕ: Используется fallback (activityType вместо mapped text)\n";
                echo "   Возможная причина: не найдено в activityTypeMappings\n";
            } else {
                echo "   ✅ Использован mapped text\n";
            }
            
            // Проверяем формат сообщения
            echo "\n5. ПРОВЕРКА ФОРМАТА СООБЩЕНИЯ:\n";
            $commissioningActivities = [
                'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
                'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
                'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
                'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
            ];
            
            if (in_array($activityType, $commissioningActivities)) {
                echo "   ✅ Активность в commissioningActivities - должен использовать правильный формат\n";
                
                $fio = "Тестовый Пользователь";
                $date = "06.10.2025";
                $projectCode = "ХХХ";
                
                $expectedMessage = sprintf(
                    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                    $fio,
                    $date,
                    $projectCode,
                    $activityText
                );
                
                echo "   Ожидаемое сообщение:\n";
                echo "   '{$expectedMessage}'\n";
            } else {
                echo "   ❌ ОШИБКА: Активность НЕ в commissioningActivities!\n";
            }
        }
    } else {
        echo "   ❌ НЕ найдено в simpleSupervisionMap!\n";
    }
}

echo "\n=== ЗАКЛЮЧЕНИЕ ===\n";
echo "Если все проверки прошли успешно, то проблема может быть в:\n";
echo "1. Неправильных параметрах, передаваемых в createTabelMessage\n";
echo "2. Отсутствии вызова createTabelMessage\n";
echo "3. Проблемах с логированием или отправкой сообщения\n";
echo "4. Ошибках в коде после createTabelMessage\n";

?>