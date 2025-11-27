<?php
/**
 * Diagnostic script for НеучВОР "Да" button
 * Shows what should happen when user clicks "Да"
 */

echo "=== Диагностика кнопки 'Да' для НеучВОР ===\n\n";

echo "ОЖИДАЕМЫЙ ПОТОК ВЫПОЛНЕНИЯ:\n";
echo "═══════════════════════════════════════════\n\n";

echo "1️⃣ Пользователь нажимает 'Да'\n";
echo "   Callback: cat_ШМР_МонтТруб_НеучВОР_yes\n\n";

echo "2️⃣ TabelHandler::handle() получает callback\n";
echo "   → Проверяет isActivityExecution()\n";
echo "   → Должен вернуть TRUE\n\n";

echo "3️⃣ TabelHandler::handleActivityExecution() обрабатывает\n";
echo "   → Pattern: /^cat_ШМР_(.+)_НеучВОР_yes$/u\n";
echo "   → Извлекает: 'МонтТруб'\n";
echo "   → Маппинг: 'МонтТруб' → 'ШМР_МонтажТрубопроводов_НеучВОР'\n\n";

echo "4️⃣ Создается запись в табель\n";
echo "   → TabelEntry::addEntry()\n";
echo "   → Activity type: 'ШМР_МонтажТрубопроводов_НеучВОР'\n\n";

echo "═══════════════════════════════════════════\n\n";

// Test the pattern matching
echo "ТЕСТ ПАТТЕРНОВ:\n\n";

$testCallbacks = [
    'cat_ШМР_МонтТруб_НеучВОР_yes',
    'cat_ШМР_ГидрИсп_НеучВОР_yes',
    'cat_ШМР_МонтТехОб_НеучВОР_yes'
];

foreach ($testCallbacks as $callback) {
    echo "Тестирую: {$callback}\n";
    
    // Test isActivityExecution pattern
    if (preg_match('/^cat_ШМР_(.+)_НеучВОР_yes$/u', $callback, $matches)) {
        echo "  ✅ Паттерн isActivityExecution: СОВПАДАЕТ\n";
        echo "  Извлечено: '{$matches[1]}'\n";
        
        // Test handleActivityExecution mapping
        $shortToFullMap = [
            'МонтТруб' => 'ШМР_МонтажТрубопроводов_НеучВОР',
            'ГидрИсп' => 'ШМР_ГидроПневмоИспытания_НеучВОР',
            'МонтТехОб' => 'ШМР_МонтажТехнологическогоОборудования_НеучВОР',
            'МонтЗРА' => 'ШМР_МонтажЗРА_НеучВОР',
            'ПротСоед' => 'ШМР_ПротяжкаСоединений_НеучВОР',
            'МонтТеплИзол' => 'ШМР_МонтажТеплоизоляции_НеучВОР'
        ];
        
        $shortKey = $matches[1];
        if (isset($shortToFullMap[$shortKey])) {
            $activityType = $shortToFullMap[$shortKey];
            echo "  ✅ Маппинг найден: '{$activityType}'\n";
        } else {
            echo "  ❌ ОШИБКА: Маппинг НЕ найден для ключа '{$shortKey}'\n";
        }
    } else {
        echo "  ❌ ОШИБКА: Паттерн НЕ совпадает\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════\n\n";

echo "ЧТО ПРОВЕРИТЬ В ЛОГАХ TELEGRAM:\n";
echo "1. Ищи строку: '🔥 UNACCOUNTED ВОР EXECUTION:'\n";
echo "2. Проверь, что activityType = 'ШМР_МонтажТрубопроводов_НеучВОР'\n";
echo "3. Если нет этой строки - значит callback не доходит до handleActivityExecution\n";
echo "4. Если есть 'Неизвестная команда табеля' - проверь что activityType правильный\n\n";

echo "ВОЗМОЖНЫЕ ПРОБЛЕМЫ:\n";
echo "❌ Callback не распознается в isActivityExecution()\n";
echo "❌ Callback не маппится в handleActivityExecution()\n";
echo "❌ Activity type не найден в списке разрешенных\n";
echo "❌ Encoding проблема с кириллицей\n\n";
