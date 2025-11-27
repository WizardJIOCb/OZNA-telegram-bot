<?php
/**
 * Verify that all НеучВОР activity types are in $activityTypes array
 */

echo "=== Проверка НеучВОР в \$activityTypes ===\n\n";

// Read TabelHandler.php
$content = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Extract $activityTypes array
if (preg_match('/private array \$activityTypes = \[(.*?)\];/s', $content, $matches)) {
    $activityTypesContent = $matches[1];
    
    echo "✅ Найден массив \$activityTypes\n\n";
    
    // Check for all 6 НеучВОР types
    $required = [
        'ШМР_МонтажТрубопроводов_НеучВОР',
        'ШМР_ГидроПневмоИспытания_НеучВОР',
        'ШМР_МонтажТехнологическогоОборудования_НеучВОР',
        'ШМР_МонтажЗРА_НеучВОР',
        'ШМР_ПротяжкаСоединений_НеучВОР',
        'ШМР_МонтажТеплоизоляции_НеучВОР'
    ];
    
    $allFound = true;
    
    foreach ($required as $type) {
        if (strpos($activityTypesContent, "'$type'") !== false) {
            echo "✅ НАЙДЕН: $type\n";
        } else {
            echo "❌ НЕ НАЙДЕН: $type\n";
            $allFound = false;
        }
    }
    
    echo "\n";
    
    if ($allFound) {
        echo "🎉 ВСЕ НеучВОР типы добавлены в \$activityTypes!\n";
        echo "\n";
        echo "Теперь кнопка 'Да' должна работать!\n";
        echo "Попробуй еще раз:\n";
        echo "1. Нажми 'Работы неучтенные в ВОР'\n";
        echo "2. Нажми 'Да'\n";
        echo "3. Запись должна создаться успешно!\n";
    } else {
        echo "⚠️ ВНИМАНИЕ: Не все типы найдены!\n";
    }
} else {
    echo "❌ Не удалось найти массив \$activityTypes\n";
}

echo "\n";
