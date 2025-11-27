<?php
/**
 * Симуляция нажатия кнопки "Да" для НеучВОР
 * Этот скрипт покажет, что происходит при обработке callback
 */

echo "=== Симуляция обработки НеучВОР 'Да' ===\n\n";

// Симулируем callback
$testCallbacks = [
    'cat_ШМР_МонтТруб_НеучВОР_yes',
    'cat_ШМР_ГидрИсп_НеучВОР_yes',
    'cat_ШМР_МонтТехОб_НеучВОР_yes'
];

echo "Проверяем каждый callback...\n\n";

foreach ($testCallbacks as $category) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Callback: {$category}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    $activityType = '';
    
    // Симулируем логику handleActivityExecution
    echo "1️⃣ Проверка паттерна: /^cat_ШМР_(.+)_НеучВОР_yes\$/u\n";
    
    if (preg_match('/^cat_ШМР_(.+)_НеучВОР_yes$/u', $category, $matches)) {
        echo "   ✅ ПАТТЕРН СОВПАЛ!\n";
        echo "   Извлечено: '{$matches[1]}'\n\n";
        
        $shortToFullMap = [
            'МонтТруб' => 'ШМР_МонтажТрубопроводов_НеучВОР',
            'ГидрИсп' => 'ШМР_ГидроПневмоИспытания_НеучВОР',
            'МонтТехОб' => 'ШМР_МонтажТехнологическогоОборудования_НеучВОР',
            'МонтЗРА' => 'ШМР_МонтажЗРА_НеучВОР',
            'ПротСоед' => 'ШМР_ПротяжкаСоединений_НеучВОР',
            'МонтТеплИзол' => 'ШМР_МонтажТеплоизоляции_НеучВОР'
        ];
        
        $shortActivityKey = $matches[1];
        echo "2️⃣ Поиск в маппинге...\n";
        
        if (isset($shortToFullMap[$shortActivityKey])) {
            $activityType = $shortToFullMap[$shortActivityKey];
            echo "   ✅ НАЙДЕНО В МАППИНГЕ!\n";
            echo "   activityType = '{$activityType}'\n\n";
        } else {
            echo "   ❌ НЕ НАЙДЕНО в маппинге!\n";
            echo "   Доступные ключи: " . implode(', ', array_keys($shortToFullMap)) . "\n\n";
        }
    } else {
        echo "   ❌ ПАТТЕРН НЕ СОВПАЛ!\n\n";
    }
    
    // Проверка в $activityTypes
    echo "3️⃣ Проверка в \$activityTypes массиве...\n";
    
    $content = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');
    if (preg_match('/private array \$activityTypes = \[(.*?)\];/s', $content, $arrayMatches)) {
        $activityTypesContent = $arrayMatches[1];
        
        if (!empty($activityType) && strpos($activityTypesContent, "'$activityType'") !== false) {
            echo "   ✅ НАЙДЕН в \$activityTypes!\n";
            echo "   Запись ДОЛЖНА создаться успешно!\n\n";
        } else {
            echo "   ❌ НЕ НАЙДЕН в \$activityTypes!\n";
            echo "   Это вызовет ошибку 'Неизвестная команда табеля'!\n\n";
        }
    }
    
    echo "📊 ИТОГ:\n";
    if (!empty($activityType)) {
        echo "   ✅ activityType установлен: '{$activityType}'\n";
    } else {
        echo "   ❌ activityType ПУСТОЙ - будет ошибка!\n";
    }
    
    echo "\n\n";
}

echo "═══════════════════════════════════════════\n\n";
echo "💡 РЕКОМЕНДАЦИИ:\n\n";
echo "Если все тесты показывают ✅, но в боте всё равно ошибка,\n";
echo "то проблема может быть в одном из этих мест:\n\n";
echo "1️⃣ Callback преобразуется ПЕРЕД попаданием в handleActivityExecution\n";
echo "   (функция mapShortCallbackToFullCategory может изменять callback)\n\n";
echo "2️⃣ isActivityExecution возвращает FALSE\n";
echo "   (тогда handleActivityExecution вообще не вызывается)\n\n";
echo "3️⃣ Есть другой handler, который перехватывает callback РАНЬШЕ\n";
echo "   (проверь порядок обработки в handle() методе)\n\n";
