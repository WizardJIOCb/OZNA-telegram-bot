<?php
/**
 * Full chain diagnosis for НеучВОР button
 * Checks: mapShortCallbackToFullCategory -> isActivityExecution -> handleActivityExecution
 */

echo "=== ПОЛНАЯ ДИАГНОСТИКА ЦЕПОЧКИ ОБРАБОТКИ ===\n\n";

$originalCallback = 'cat_ШМР_МонтТруб_НеучВОР_yes';

echo "📥 ВХОДЯЩИЙ CALLBACK: {$originalCallback}\n\n";

// Step 1: mapShortCallbackToFullCategory
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ШАГ 1: mapShortCallbackToFullCategory\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$content = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Find the mapping
if (preg_match("/'cat_ШМР_МонтТруб_НеучВОР_yes'\s*=>\s*'([^']+)'/", $content, $matches)) {
    $mappedCallback = $matches[1];
    echo "✅ МАППИНГ НАЙДЕН!\n";
    echo "   {$originalCallback}\n";
    echo "   ↓\n";
    echo "   {$mappedCallback}\n\n";
} else {
    echo "❌ МАППИНГ НЕ НАЙДЕН!\n";
    echo "   Callback НЕ будет преобразован!\n\n";
    $mappedCallback = $originalCallback;
}

$category = $mappedCallback;

// Step 2: isActivityExecution
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ШАГ 2: isActivityExecution\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Проверяем category: '{$category}'\n\n";

$isRecognized = false;

// Check pattern 1: cat_ШМР_(.+)_НеучВОР_yes
if (preg_match('/^cat_ШМР_(.+)_НеучВОР_yes$/u', $category, $matches)) {
    echo "✅ ПАТТЕРН 1 СОВПАЛ (в isActivityExecution)\n";
    echo "   Pattern: /^cat_ШМР_(.+)_НеучВОР_yes$/u\n";
    
    $shortKey = $matches[1];
    $techEquipmentMap = [
        'МонтТруб' => 'ШМР_МонтажТрубопроводов_НеучВОР',
        'ГидрИсп' => 'ШМР_ГидроПневмоИспытания_НеучВОР',
        'МонтТехОб' => 'ШМР_МонтажТехнологическогоОборудования_НеучВОР',
        'МонтЗРА' => 'ШМР_МонтажЗРА_НеучВОР',
        'ПротСоед' => 'ШМР_ПротяжкаСоединений_НеучВОР',
        'МонтТеплИзол' => 'ШМР_МонтажТеплоизоляции_НеучВОР'
    ];
    
    if (isset($techEquipmentMap[$shortKey])) {
        echo "✅ Ключ найден в techEquipmentMap\n";
        echo "✅ isActivityExecution вернет: TRUE\n\n";
        $isRecognized = true;
    } else {
        echo "❌ Ключ НЕ найден в techEquipmentMap\n";
        echo "❌ isActivityExecution вернет: FALSE\n\n";
    }
}

if (!$isRecognized) {
    echo "❌ НИ ОДИН ПАТТЕРН НЕ СОВПАЛ в isActivityExecution!\n";
    echo "❌ isActivityExecution вернет: FALSE\n";
    echo "❌ handleActivityExecution НЕ БУДЕТ ВЫЗВАН!\n\n";
    echo "🚨 ВОТ ПРОБЛЕМА! Нужно добавить паттерн для маппированного формата в isActivityExecution!\n\n";
}

// Step 3: handleActivityExecution (only if isActivityExecution returns TRUE)
if ($isRecognized) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ШАГ 3: handleActivityExecution\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    $activityType = '';
    
    // Pattern 1: cat_ШМР_(.+)_НеучВОР_yes
    if (preg_match('/^cat_ШМР_(.+)_НеучВОР_yes$/u', $category, $matches)) {
        echo "✅ ПАТТЕРН 1 СОВПАЛ\n";
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
            echo "✅ activityType = '{$activityType}'\n\n";
        }
    }
    // Pattern 2: ШМР_(.+)_НеучВОР_yes (mapped)
    elseif (preg_match('/^ШМР_(.+)_НеучВОР_yes$/u', $category, $matches)) {
        echo "✅ ПАТТЕРН 2 СОВПАЛ (маппированный)\n";
        $activityType = str_replace('_yes', '', $category);
        echo "✅ activityType = '{$activityType}'\n\n";
    }
    
    if (!empty($activityType)) {
        echo "✅ Запись будет создана с activityType: '{$activityType}'\n";
    } else {
        echo "❌ activityType ПУСТОЙ - будет ошибка!\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════\n\n";

if (!$isRecognized) {
    echo "🎯 НАЙДЕНА ПРОБЛЕМА!\n\n";
    echo "isActivityExecution НЕ распознает маппированный callback:\n";
    echo "'{$category}'\n\n";
    echo "РЕШЕНИЕ: Добавить паттерн в isActivityExecution для обработки\n";
    echo "маппированного формата (без 'cat_' префикса)\n\n";
}
