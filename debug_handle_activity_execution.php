<?php
// Проверяем логику handleActivityExecution для ТабИнстр

echo "=== ДЕБАГ HANDLEACTIVITYEXECUTION ===\n\n";

$callback = 'cat_ТабИнстр_yes';
echo "Тестируем callback: {$callback}\n\n";

echo "=== ИЗВЛЕЧЕНИЕ ACTIVITYTYPE ===\n\n";

$activityType = '';

// Проверяем первый if - электрооборудование
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callback, $matches)) {
    echo "✓ Матчит electrical pattern\n";
    $activityType = "electrical_match";
} 
// Проверяем второй elseif - общий commissioning
elseif (preg_match('/^cat_(.+)_yes$/u', $callback, $matches)) {
    echo "✓ Матчит general pattern\n";
    $shortActivityKey = $matches[1];
    echo "  Short key: '{$shortActivityKey}'\n";
    
    if ($shortActivityKey === 'ТабПНРаботы') {
        echo "  Это ТабПНРаботы\n";
        $activityType = 'ПусконаладочныеРаботы';
    }
    
    // Handle basic commissioning activities (Инструктаж, Дорога, etc.)
    $basicCommissioningActivities = [
        'ТабИнстр' => 'Инструктаж',
        'ТабДорога' => 'Дорога',
        'ТабПодгПоезд' => 'ПодготовкаКПоездке',
        'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
    ];
    
    if (isset($basicCommissioningActivities[$shortActivityKey])) {
        echo "  ✓ Найден в basicCommissioningActivities\n";
        $activityType = $basicCommissioningActivities[$shortActivityKey];
        echo "  Activity type: '{$activityType}'\n";
    } else {
        echo "  ✗ НЕ найден в basicCommissioningActivities\n";
    }
}
// Проверяем третий elseif - полный формат
elseif (preg_match('/^(category_)?Табель_(.+)_yes$/u', $callback, $matches)) {
    echo "✓ Матчит full format pattern\n";
    $activityType = $matches[2];
} else {
    echo "✗ НЕ матчит никакой pattern\n";
}

echo "\nФинальный activityType: '{$activityType}'\n\n";

echo "=== ПРОБЛЕМА НАЙДЕНА! ===\n\n";

if (empty($activityType)) {
    echo "❌ ПРОБЛЕМА: activityType пустой!\n";
    echo "Это означает что в handleActivityExecution НЕ извлекается activityType\n";
    echo "Поэтому создание записи табеля падает с ошибкой\n\n";
    
    echo "ПРИЧИНА: После извлечения activityType метод продолжает выполнение\n";
    echo "но с пустым activityType, что приводит к ошибке в TabelEntry->addEntry()\n\n";
    
    echo "РЕШЕНИЕ: Нужно добавить проверку на пустой activityType\n";
    echo "и вернуть ошибку если он не извлечен\n";
} else {
    echo "✓ activityType извлечен правильно: '{$activityType}'\n";
    echo "Проблема может быть дальше в коде\n";
}

echo "\n=== ДОПОЛНИТЕЛЬНАЯ ПРОВЕРКА ===\n\n";

// Проверим что происходит если activityType пустой
echo "Что происходит если activityType пустой?\n";
echo "TabelEntry->addEntry(userId, fio, date, projectCode, '', chatId, idleReason)\n";
echo "Скорее всего это вызывает ошибку в базе данных или валидации\n";
echo "И метод возвращает false, что приводит к сообщению об ошибке\n\n";

echo "НО! Если isActivityExecution возвращает true,\n";
echo "то handleActivityExecution ДОЛЖЕН вызываться\n";
echo "Значит проблема именно в извлечении activityType!\n";
?>