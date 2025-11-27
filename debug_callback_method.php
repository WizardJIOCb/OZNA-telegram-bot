<?php
// Debug callback length and method issues

echo "=== DEBUGGING ИНСТРУКТАЖ CALLBACK LENGTH AND METHOD ===\n\n";

// Test 1: Check callback lengths
echo "1. CALLBACK LENGTH ANALYSIS:\n";
$callbacks = [
    'cat_ТабИнстр' => 'Initial button (should work)',
    'cat_ТабИнстр_yes' => 'Confirmation button (failing)',
];

foreach ($callbacks as $callback => $description) {
    $length = strlen($callback);
    $bytes = mb_strlen($callback, 'UTF-8');
    echo "Callback: '$callback'\n";
    echo "  Description: $description\n";
    echo "  Length (bytes): $length\n";
    echo "  Length (UTF-8): $bytes\n";
    echo "  Status: " . ($length <= 64 ? '✅ OK' : '❌ TOO LONG') . "\n\n";
}

// Test 2: Check pattern matching
echo "2. PATTERN MATCHING ANALYSIS:\n";
$confirmationCallback = 'cat_ТабИнстр_yes';

// Test regex pattern from handleActivityExecution
if (preg_match('/^cat_(.+)_yes$/', $confirmationCallback, $matches)) {
    $shortActivityKey = $matches[1];
    echo "✅ Pattern matched: /^cat_(.+)_yes$/\n";
    echo "Extracted short activity key: '$shortActivityKey'\n";
    
    // Check basic commissioning activities mapping
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
        $activityType = $basicCommissioningActivities[$shortActivityKey];
        echo "✅ Found in basic commissioning activities\n";
        echo "Activity type: '$activityType'\n";
    } else {
        echo "❌ NOT found in basic commissioning activities\n";
        echo "Available keys: " . implode(', ', array_keys($basicCommissioningActivities)) . "\n";
    }
} else {
    echo "❌ Pattern did NOT match: /^cat_(.+)_yes$/\n";
}

// Test 3: Check if activity type is in activityTypes array
echo "\n3. ACTIVITY TYPES ARRAY CHECK:\n";
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
    echo "❌ 'Инструктаж' is NOT in activityTypes array\n";
    echo "This would cause the activity to fail validation!\n";
}

// Test 4: Check exact method flow
echo "\n4. EXACT METHOD FLOW SIMULATION:\n";
$category = 'cat_ТабИнстр_yes';
$activityType = '';

echo "Input category: '$category'\n";

// Step 1: Check if matches electrical equipment pattern
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
    echo "Step 1: Matches electrical equipment pattern - NO (expected)\n";
} else {
    echo "Step 1: Does NOT match electrical equipment pattern - ✅ CORRECT\n";
}

// Step 2: Check if matches commissioning works pattern
if (preg_match('/^cat_(.+)_yes$/', $category, $matches)) {
    echo "Step 2: Matches commissioning works pattern - ✅ YES\n";
    $shortActivityKey = $matches[1];
    echo "        Extracted short activity key: '$shortActivityKey'\n";
    
    // Step 2a: Check for ТабПНРаботы
    if ($shortActivityKey === 'ТабПНРаботы') {
        echo "        Is ТабПНРаботы: NO (expected)\n";
    } else {
        echo "        Is ТабПНРаботы: NO (expected)\n";
    }
    
    // Step 2b: Check basic commissioning activities
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
        $activityType = $basicCommissioningActivities[$shortActivityKey];
        echo "        Found in basic commissioning activities: ✅ YES\n";
        echo "        Activity type set to: '$activityType'\n";
    } else {
        echo "        Found in basic commissioning activities: ❌ NO\n";
        echo "        This is the problem!\n";
    }
} else {
    echo "Step 2: Does NOT match commissioning works pattern - ❌ PROBLEM\n";
}

// Step 3: Check if matches full callback format
if (preg_match('/^(category_)?Табель_(.+)_yes$/', $category, $matches)) {
    echo "Step 3: Matches full callback format - NO (expected)\n";
} else {
    echo "Step 3: Does NOT match full callback format - ✅ EXPECTED\n";
}

echo "\n5. FINAL ACTIVITY TYPE CHECK:\n";
if (!empty($activityType)) {
    echo "Final activity type: '$activityType'\n";
    echo "This should create a tabel entry successfully\n";
} else {
    echo "❌ CRITICAL: Activity type is EMPTY!\n";
    echo "This will cause the method to fail and likely show 'неизвестная команда табеля'\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔍 DIAGNOSIS:\n";
if (!empty($activityType)) {
    echo "✅ The logic should work correctly\n";
    echo "✅ Callback length is OK\n";
    echo "✅ Pattern matching works\n";
    echo "✅ Activity type mapping works\n";
    echo "\nIf still not working, check:\n";
    echo "1. Server restart status\n";
    echo "2. Error logs for specific failure points\n";
    echo "3. User data validation (FIO, functional direction)\n";
} else {
    echo "❌ PROBLEM FOUND: Activity type mapping failed\n";
    echo "❌ This would cause 'неизвестная команда табеля'\n";
}

?>