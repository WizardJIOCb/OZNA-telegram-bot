<?php
echo "🔍 MASTER DUTIES ROUTING TRACE\n";
echo "==============================\n\n";

// Simulate the exact flow in TabelHandler

$callbackData = 'category_Табель_ИсполнениеОбязМастера';
echo "1. Raw callback data: '{$callbackData}'\n";

// Step 1: extractCategoryFromCallback() mapping
$mapping = [
    'category_Табель_ИсполнениеОбязМастера' => 'Табель_ИсполнениеОбязМастера',
    'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
    'cat_ТабИсполнОбяз_yes' => 'Табель_ИсполнениеОбязМастера_yes'
];

$extractedCategory = $mapping[$callbackData] ?? $callbackData;
echo "2. Extracted category: '{$extractedCategory}'\n";

// Step 2: Check where this category is handled in routeTabelCallback
echo "\n3. Routing logic check:\n";

// Check if it goes to handleOtherWorkTypes (around line 545)
$goesToOtherWorkTypes = !in_array($extractedCategory, [
    'Табель_Дата', 'Табель_Проект', 'Табель_Мобильная_Бригада_Дата', 'Табель_Пусконаладка_Back',
    'Табель_ШМ_Back', 'Табель_Текущая_Назад'
]);
echo "   → Goes to handleOtherWorkTypes: " . ($goesToOtherWorkTypes ? 'YES' : 'NO') . "\n";

// Step 3: Check handleOtherWorkTypes logic (line 2833)
if ($goesToOtherWorkTypes) {
    echo "\n4. handleOtherWorkTypes check:\n";
    $masterDutiesHandler = ($extractedCategory === 'Табель_ИсполнениеОбязМастера');
    echo "   category === 'Табель_ИсполнениеОбязМастера': " . ($masterDutiesHandler ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    if ($masterDutiesHandler) {
        echo "\n✅ SUCCESS: Should be handled by line 2833 handler!\n";
        echo "   Handler code: if (\$category === 'Табель_ИсполнениеОбязМастера') {\n";
        echo "      // Generate confirmation message and keyboard\n";
        echo "   }\n";
    } else {
        echo "\n❌ FAILURE: Not handled by master duties handler\n";
    }
}

echo "\n🔍 DEBUGGING QUESTIONS:\n";
echo "1. Is the mapping in extractCategoryFromCallback correct? ✅ YES\n";
echo "2. Is the handler condition correct? ✅ YES\n";
echo "3. Are there any other handlers intercepting this category first? 🤔 NEED TO CHECK\n";

echo "\n💡 NEXT STEPS:\n";
echo "Check if any earlier handler in handleOtherWorkTypes() is intercepting this category\n";
echo "Add debug logging to confirm the flow reaches line 2833\n";