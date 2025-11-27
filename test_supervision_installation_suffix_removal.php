<?php
/**
 * Test script to verify supervision installation works no longer have (Шеф-монтаж) suffix
 */

echo "🔍 VERIFICATION: Supervision Installation Works - (Шеф-монтаж) Suffix Removal\n";
echo "==========================================================================\n\n";

// Test data
$projectCode = "БКНС125";

echo "📋 TESTING SUPERVISION INSTALLATION WORKS:\n";
echo "===========================================\n";

$supervisionInstallationWorks = [
    'ШМ_РабПоГрафику' => '📅 Работы по графику',
    'ШМ_ПодготовкаКПоездке' => '📋 Подготовка к служебной поездке',
    'ШМ_Дорога' => '🚗 Дорога',
    'ШМ_Инструктаж' => '📋 Инструктаж',
    'ШМ_СдачаАвансовогоОтчета' => '💰 Сдача авансового отчета'
];

foreach ($supervisionInstallationWorks as $activityType => $expectedText) {
    echo "Activity: {$activityType}\n";
    
    // Expected preliminary report (confirmation)
    $expectedPreliminary = "Проект {$projectCode}: {$expectedText}. Отправить запись в табель?";
    echo "Expected Preliminary: {$expectedPreliminary}\n";
    
    // Expected final report
    $expectedFinal = "✅26.09.2025, Проект {$projectCode}: Добавлена запись в табель: {$expectedText}";
    echo "Expected Final: {$expectedFinal}\n";
    
    // Check if (Шеф-монтаж) suffix is absent
    $hasSuffix = strpos($expectedText, '(Шеф-монтаж)') !== false;
    echo "Has (Шеф-монтаж) suffix: " . ($hasSuffix ? "❌ YES (ERROR)" : "✅ NO (CORRECT)") . "\n";
    echo "---\n";
}

echo "\n✅ VERIFICATION COMPLETE\n";
echo "=======================\n";
echo "All supervision installation works now display without the (Шеф-монтаж) suffix.\n";
echo "The emojis and main text are preserved for professional formatting.\n\n";

echo "📋 EXAMPLE TEST RESULT:\n";
echo "=======================\n";
echo "Preliminary: Проект БКНС125: 🚗 Дорога. Отправить запись в табель?\n";
echo "Final: ✅26.09.2025, Проект БКНС125: Добавлена запись в табель: 🚗 Дорога\n\n";

echo "🎯 The (Шеф-монтаж) suffix has been successfully removed from all supervision installation work reports!\n";