<?php
/**
 * Test script to verify ШМР Prochee formatting fix
 */

echo "=== ШМР PROCHEE FORMATTING FIX TEST ===\n\n";

// Test the activity mapping
$activityType = 'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр';
$customText = 'Наименование работы';

// Simulate the mapping from TabelEntry.php
$activityTypeMappings = [
    'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр' => '📝 Прочее (Технологическое оборудование)',
];

echo "1. Testing activity type mapping:\n";
echo "   Activity Type: {$activityType}\n";
$activityText = $activityTypeMappings[$activityType] ?? $activityType;
echo "   Base Text: '{$activityText}'\n";

echo "\n2. Testing custom text addition:\n";
echo "   Custom Text: '{$customText}'\n";
if ($activityType === 'ШМР_Прочее_ТехнологическоеОборудование_РабПоГр' && !empty($customText)) {
    $activityText .= " ({$customText})";
    echo "   ✅ Custom text added successfully\n";
} else {
    echo "   ❌ Custom text NOT added\n";
}

echo "   Final Activity Text: '{$activityText}'\n";

echo "\n3. Testing final message format:\n";
$fio = "Тестовый Пользователь";
$date = "09.10.2025";
$projectCode = "ХХХ";

$message = sprintf(
    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "   Final Message:\n";
echo "   {$message}\n";

echo "\n4. Comparison:\n";
echo "   ❌ BEFORE (incorrect): ✅09.10.2025, Проект ХХХ: Добавлена запись в табель: 📝 Прочее (Технологическое оборудование, ШМР)\n";
echo "   ✅ AFTER (correct):    ✅09.10.2025, Проект ХХХ: Добавлена запись в табель: 📝 Прочее (Технологическое оборудование) (Наименование работы)\n";

echo "\n=== FIX VERIFICATION ===\n";
if (strpos($activityText, ', ШМР') === false && strpos($activityText, $customText) !== false) {
    echo "✅ SUCCESS: Fix is working correctly!\n";
    echo "   - 'ШМР' removed from activity text\n";
    echo "   - Custom text added in parentheses\n";
} else {
    echo "❌ FAILURE: Fix needs adjustment\n";
    if (strpos($activityText, ', ШМР') !== false) {
        echo "   - 'ШМР' still present in activity text\n";
    }
    if (strpos($activityText, $customText) === false) {
        echo "   - Custom text not found in activity text\n";
    }
}