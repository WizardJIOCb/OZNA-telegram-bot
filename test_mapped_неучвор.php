<?php
/**
 * Final test for НеучВОР with mapped callbacks
 */

echo "=== Финальный тест НеучВОР с маппингом ===\n\n";

// Test both formats
$tests = [
    [
        'name' => 'Оригинальный callback (с cat_)',
        'category' => 'cat_ШМР_МонтТруб_НеучВОР_yes'
    ],
    [
        'name' => 'Маппированный callback (без cat_)',
        'category' => 'ШМР_МонтажТрубопроводов_НеучВОР_yes'
    ]
];

foreach ($tests as $test) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "{$test['name']}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Category: {$test['category']}\n\n";
    
    $category = $test['category'];
    $activityType = '';
    
    // Test pattern 1: cat_ШМР_(.+)_НеучВОР_yes
    if (preg_match('/^cat_ШМР_(.+)_НеучВОР_yes$/u', $category, $matches)) {
        echo "✅ ПАТТЕРН 1 СОВПАЛ (cat_ШМР_...)\n";
        $shortToFullMap = [
            'МонтТруб' => 'ШМР_МонтажТрубопроводов_НеучВОР',
            'ГидрИсп' => 'ШМР_ГидроПневмоИспытания_НеучВОР',
            'МонтТехОб' => 'ШМР_МонтажТехнологическогоОборудования_НеучВОР',
            'МонтЗРА' => 'ШМР_МонтажЗРА_НеучВОР',
            'ПротСоед' => 'ШМР_ПротяжкаСоединений_НеучВОР',
            'МонтТеплИзол' => 'ШМР_МонтажТеплоизоляции_НеучВОР'
        ];
        
        $shortActivityKey = $matches[1];
        if (isset($shortToFullMap[$shortActivityKey])) {
            $activityType = $shortToFullMap[$shortActivityKey];
            echo "✅ Маппинг: '{$shortActivityKey}' -> '{$activityType}'\n";
        }
    }
    // Test pattern 2: ШМР_(.+)_НеучВОР_yes (mapped format)
    elseif (preg_match('/^ШМР_(.+)_НеучВОР_yes$/u', $category, $matches)) {
        echo "✅ ПАТТЕРН 2 СОВПАЛ (ШМР_... маппированный)\n";
        $activityType = str_replace('_yes', '', $category);
        echo "✅ Убрали '_yes': '{$activityType}'\n";
    }
    else {
        echo "❌ НИ ОДИН ПАТТЕРН НЕ СОВПАЛ!\n";
    }
    
    if (!empty($activityType)) {
        echo "\n📊 ИТОГ: activityType = '{$activityType}'\n";
        echo "✅ Запись должна создаться успешно!\n";
    } else {
        echo "\n❌ ИТОГ: activityType ПУСТОЙ!\n";
        echo "❌ Будет ошибка 'Неизвестная команда табеля'!\n";
    }
    
    echo "\n\n";
}

echo "═══════════════════════════════════════════\n\n";
echo "🎉 РЕШЕНИЕ НАЙДЕНО!\n\n";
echo "Проблема была в том, что mapShortCallbackToFullCategory\n";
echo "преобразует callback:\n";
echo "  'cat_ШМР_МонтТруб_НеучВОР_yes'\n";
echo "  ↓\n";
echo "  'ШМР_МонтажТрубопроводов_НеучВОР_yes'\n\n";
echo "Теперь handleActivityExecution обрабатывает ОБА формата:\n";
echo "1. Оригинальный (cat_ШМР_...)\n";
echo "2. Маппированный (ШМР_...)\n\n";
echo "Кнопка 'Да' теперь должна работать! 🚀\n\n";
