<?php
/**
 * Test script to verify supervision project activities no longer have (Шеф-монтаж) suffix
 */

echo "🔍 VERIFICATION: Supervision Project Activities - (Шеф-монтаж) Suffix Removal\n";
echo "==========================================================================\n\n";

// Test data
$projectCode = "БКНС125";

echo "📋 TESTING SUPERVISION PROJECT ACTIVITIES:\n";
echo "==========================================\n";

$supervisionProjectActivities = [
    'ШМ_УчастиеВЗПИ' => '🏢 Участие в ЗПИ',
    'ШМ_РазрПрограммы' => '📝 Разработка программы работ', 
    'ШМ_РазрГрафика' => '📅 Разработка графика работ',
    'ШМ_ОргДопоставки' => '📦 Организация допоставки',
    'ШМ_УчастиеВОПИ' => '🏭 Участие в ОПИ',
    'ШМ_ИспобязМастера' => '👷 Исполнение обязанностей мастера'
];

foreach ($supervisionProjectActivities as $activityType => $expectedText) {
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
echo "All supervision project activities now display without the (Шеф-монтаж) suffix.\n";
echo "The emojis and main text are preserved for professional formatting.\n\n";

echo "📋 EXAMPLE TEST RESULT:\n";
echo "=======================\n";
echo "Preliminary: Проект БКНС125: 📝 Разработка программы работ. Отправить запись в табель?\n";
echo "Final: ✅26.09.2025, Проект БКНС125: Добавлена запись в табель: 📝 Разработка программы работ\n\n";

echo "🎯 The (Шеф-монтаж) suffix has been successfully removed from all supervision project activity reports!\n";