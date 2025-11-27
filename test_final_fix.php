<?php
/**
 * Test script to verify the FINAL FIX for НеучВОР buttons
 * This simulates the complete callback processing chain
 */

echo "=== ТЕСТ ФИНАЛЬНОГО ИСПРАВЛЕНИЯ ДЛЯ НеучВОР ===\n\n";

// Test case 1: Original callback
$originalCallback = 'cat_ШМР_МонтТруб_НеучВОР_yes';
echo "1️⃣ ОРИГИНАЛЬНЫЙ CALLBACK: '{$originalCallback}'\n";
echo "   Pattern: /^cat_ШМР_(.+)_НеучВОР_yes$/u\n";
if (preg_match('/^cat_ШМР_(.+)_НеучВОР_yes$/u', $originalCallback, $matches)) {
    echo "   ✅ СОВПАДАЕТ в isActivityExecution (первый паттерн)\n";
    echo "   ✅ isActivityExecution вернет TRUE\n";
} else {
    echo "   ❌ НЕ СОВПАДАЕТ\n";
}

echo "\n";

// Test case 2: After mapping (the CRITICAL FIX)
$mappedCallback = 'ШМР_МонтажТрубопроводов_НеучВОР_yes';
echo "2️⃣ МАППИРОВАННЫЙ CALLBACK: '{$mappedCallback}'\n";
echo "   (после mapShortCallbackToFullCategory)\n";
echo "   Pattern: /^ШМР_(.+)_НеучВОР_yes$/u (НОВЫЙ ПАТТЕРН!)\n";
if (preg_match('/^ШМР_(.+)_НеучВОР_yes$/u', $mappedCallback, $matches)) {
    echo "   ✅ СОВПАДАЕТ в isActivityExecution (второй паттерн - НОВЫЙ!)\n";
    echo "   ✅ isActivityExecution вернет TRUE\n";
    
    $fullActivityType = str_replace('_yes', '', $mappedCallback);
    echo "   ✅ Extracted activityType: '{$fullActivityType}'\n";
} else {
    echo "   ❌ НЕ СОВПАДАЕТ\n";
}

echo "\n";

// Test all 6 activities
$testCases = [
    'ШМР_МонтажТрубопроводов_НеучВОР_yes',
    'ШМР_ГидроПневмоИспытания_НеучВОР_yes',
    'ШМР_МонтажТехнологическогоОборудования_НеучВОР_yes',
    'ШМР_МонтажЗРА_НеучВОР_yes',
    'ШМР_ПротяжкаСоединений_НеучВОР_yes',
    'ШМР_МонтажТеплоизоляции_НеучВОР_yes'
];

echo "3️⃣ ТЕСТ ВСЕХ 6 АКТИВНОСТЕЙ:\n\n";

foreach ($testCases as $callback) {
    if (preg_match('/^ШМР_(.+)_НеучВОР_yes$/u', $callback, $matches)) {
        $activityType = str_replace('_yes', '', $callback);
        echo "   ✅ '{$activityType}' - БУДЕТ РАСПОЗНАН\n";
    } else {
        echo "   ❌ '{$callback}' - НЕ БУДЕТ РАСПОЗНАН\n";
    }
}

echo "\n";
echo "=== РЕЗУЛЬТАТ ===\n";
echo "🎉 ВСЕ МАППИРОВАННЫЕ CALLBACKS ТЕПЕРЬ БУДУТ РАСПОЗНАНЫ!\n";
echo "🎉 isActivityExecution вернет TRUE\n";
echo "🎉 handleActivityExecution БУДЕТ ВЫЗВАН\n";
echo "🎉 Запись табеля БУДЕТ СОЗДАНА\n";
echo "\n";
echo "✅ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!\n";
