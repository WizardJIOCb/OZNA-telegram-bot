<?php
// Test to reproduce the exact "неизвестная команда табеля" issue

echo "=== REPRODUCING ИНСТРУКТАЖ ISSUE ===\n\n";

// Simulate the exact flow when user clicks "Инструктаж" then "Да"

echo "Step 1: User clicks 'Инструктаж' button\n";
echo "Callback: cat_ТабИнстр\n";

// This should work - show confirmation
$category = 'ТабИнстр';
$shortCallbackMappings = [
    'ТабОхрСигн' => 'ОхраннаяСигнализация',
    'ТабОтоплВент' => 'ОтоплениеИВентиляция', 
    'ТабТехОб' => 'ТехнологическоеОборудование',
    'ТабМетрол' => 'Метрология',
    'ТабКомплОпр' => 'КомплексноеОпробование',
    'ТабРежНал' => 'РежимнаяНаладка',
    'ТабПодгПоезд' => 'ПодготовкаКПоездке',
    'ТабДорога' => 'Дорога',
    'ТабИнстр' => 'Инструктаж',
    'ТабСдачаОтч' => 'СдачаАвансовогоОтчета'
];

if (isset($shortCallbackMappings[$category])) {
    $activityType = $shortCallbackMappings[$category];
    echo "✅ STEP 1 SUCCESS: Found mapping: $category -> $activityType\n";
    echo "Shows confirmation: 'Инструктаж, отправить запись в табель?'\n";
    echo "Confirmation button callback: cat_ТабИнстр_yes\n\n";
} else {
    echo "❌ STEP 1 FAILED: No mapping found\n\n";
}

echo "Step 2: User clicks 'Да' button\n";
echo "Callback: cat_ТабИнстр_yes\n";

// Test isActivityExecution logic
$confirmationCategory = 'cat_ТабИнстр_yes';

// Check if it matches the pattern for basic commissioning activities
if (preg_match('/^cat_(.+)_yes$/', $confirmationCategory, $matches)) {
    $shortActivityKey = $matches[1];
    echo "Extracted short activity key: $shortActivityKey\n";
    
    // Check if it matches the pattern for basic commissioning activities
    $basicCommissioningActivities = [
        'ТабИнстр' => 'Инструктаж',
        'ТабДорога' => 'Дорога',
        'ТабПодгПоезд' => 'ПодготовкаКПоездке',
        'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
        'ТабОхрСигн' => 'ОхраннаяСигнализация',
        'ТабОтоплВент' => 'ОтоплениеИВентиляция',
        'ТабТехОб' => 'ТехнологическоеОборудование',
        'ТабМетрол' => 'Метрология',
        'ТабКомплОпр' => 'КомплексноеОпробование',
        'ТабРежНал' => 'РежимнаяНаладка'
    ];
    
    if (isset($basicCommissioningActivities[$shortActivityKey])) {
        $mappedActivityType = $basicCommissioningActivities[$shortActivityKey];
        echo "✅ STEP 2 SUCCESS: Found in basic commissioning activities\n";
        echo "Activity key: $shortActivityKey -> Activity type: $mappedActivityType\n";
        echo "Should create tabel entry and show success message\n";
    } else {
        echo "❌ STEP 2 FAILED: $shortActivityKey not found in basic commissioning activities\n";
        echo "This would cause 'неизвестная команда табеля'\n";
    }
} else {
    echo "❌ STEP 2 FAILED: Doesn't match expected pattern\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🔍 ISSUE ANALYSIS:\n";

// Check if the issue is in activityTypes array
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет',
    'ПусконаладочныеРаботы'
];

if (in_array('Инструктаж', $activityTypes)) {
    echo "✅ 'Инструктаж' IS in activityTypes array\n";
} else {
    echo "❌ 'Инструктаж' is NOT in activityTypes array - THIS COULD BE THE ISSUE\n";
}

echo "\n🚨 POSSIBLE CAUSES:\n";
echo "1. Server hasn't been restarted after the fix\n";
echo "2. There's a different code path being executed\n";
echo "3. The category extraction is failing somewhere\n";
echo "4. There's an encoding issue with the callback data\n";
echo "5. Another handler is intercepting the callback first\n";

echo "\n🔧 DEBUGGING STEPS:\n";
echo "1. Check server logs when clicking 'Инструктаж' button\n";
echo "2. Check server logs when clicking 'Да' button\n";
echo "3. Verify TabelHandler is handling the callback\n";
echo "4. Check if the category extraction is working correctly\n";
echo "5. Verify the isActivityExecution method is being called\n";

?>