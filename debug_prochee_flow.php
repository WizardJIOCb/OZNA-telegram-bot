<?php
// Debug script to trace exactly why "Прочее" button creates a report

echo "=== DEBUG: ПРОЧЕЕ BUTTON FLOW ===\n\n";

$callbackData = 'cat_ТабЭлОб_Проч';
echo "1. Callback received: {$callbackData}\n\n";

// Simulate extractCategoryFromCallback
echo "2. extractCategoryFromCallback():\n";
$convertedCategory = 'Табель_Прочее_ЭлектрОб'; // From mapping line 526
echo "   '{$callbackData}' → '{$convertedCategory}'\n\n";

// Simulate isActivityConfirmation method
echo "3. isActivityConfirmation('{$convertedCategory}'):\n";

// Check if it's in standard activity types
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'Прочее_ЭлектрОб', // This might be the issue!
];

$isInActivityTypes = false;
foreach ($activityTypes as $activity) {
    // Skip "Прочее" activities - they should trigger custom input, not confirmation
    if (str_contains($activity, 'Прочее')) {
        echo "   🚫 SKIPPING: '{$activity}' contains 'Прочее' - should be custom input\n";
        continue;
    }
    
    if ($convertedCategory === "category_Табель_{$activity}" || $convertedCategory === "Табель_{$activity}") {
        echo "   ✅ FOUND MATCH: '{$convertedCategory}' matches 'Табель_{$activity}'\n";
        $isInActivityTypes = true;
        break;
    }
}

if (!$isInActivityTypes) {
    echo "   ❌ No match in standard activity types\n";
}

// Check short format mapping
echo "\n   Checking short format mappings:\n";
$shortToFullMap = [
    'РабПоГр' => 'РабПоГр_ЭлектрОб',
    'ФормИД' => 'ФормИД_ЭлектрОб',
    'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
    'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
    'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
    'ДопРаб' => 'ДопРаб_ЭлектрОб',
    'ПодтВех' => 'ПодтВех_ЭлектрОб',
    // Note: 'Проч' should be removed according to comments
];

$isShortFormat = false;
foreach ($shortToFullMap as $shortKey => $fullActivity) {
    if ($convertedCategory === "cat_ТабЭлОб_{$shortKey}") {
        echo "   ✅ MATCH IN SHORT FORMAT: '{$convertedCategory}' matches 'cat_ТабЭлОб_{$shortKey}'\n";
        $isShortFormat = true;
        break;
    }
}

if (!$isShortFormat) {
    echo "   ❌ No match in short format mappings\n";
}

$isActivityConfirmationResult = $isInActivityTypes || $isShortFormat;
echo "\n   isActivityConfirmation result: " . ($isActivityConfirmationResult ? 'TRUE' : 'FALSE') . "\n\n";

if ($isActivityConfirmationResult) {
    echo "4. ROUTING: Will call handleActivityConfirmation()\n";
    echo "   → This shows confirmation dialog instead of custom input prompt!\n\n";
    
    echo "🐛 ROOT CAUSE: The callback is being treated as activity confirmation!\n";
    echo "🔧 SOLUTION: Need to prevent 'Прочее' from being recognized as activity confirmation\n";
} else {
    echo "4. ROUTING: Will call handleOtherWorkTypes()\n";
    echo "   → This should prompt for custom input\n\n";
    
    echo "✅ FLOW CORRECT: Should prompt for custom input\n";
}

echo "\n=== ANALYSIS ===\n";
echo "The issue is likely that 'Прочее_ЭлектрОб' is in the activityTypes array,\n";
echo "making isActivityConfirmation() return TRUE for 'Табель_Прочее_ЭлектрОб'\n";
echo "This causes the system to show confirmation instead of custom input prompt.\n";
?>