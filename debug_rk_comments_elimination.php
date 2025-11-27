<?php
// Comprehensive debug test for RK Comments Elimination button issue

echo "=== RK Comments Elimination Button Debug Test ===\n\n";

// Test 1: Check callback data generation
echo "1. CALLBACK DATA GENERATION TEST:\n";
echo "================================\n";

// Simulate MessageService createCommissioningWorksKeyboard
$commissioningKeyboard = [
    [
        ['text' => '⚡ Электрооборудование', 'callback_data' => 'cat_ТабЭлОб']
    ],
    [
        ['text' => '🔧 Устранения замечаний РК', 'callback_data' => 'cat_ТабУстрЗамРК']
    ],
    [
        ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
    ]
];

$rkButton = null;
foreach ($commissioningKeyboard as $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Устранения замечаний РК') !== false) {
            $rkButton = $button;
            break 2;
        }
    }
}

if ($rkButton) {
    echo "✅ RK Button found:\n";
    echo "   Text: {$rkButton['text']}\n";
    echo "   Callback: {$rkButton['callback_data']}\n";
} else {
    echo "❌ RK Button NOT found in keyboard!\n";
}

echo "\n";

// Test 2: Check TabelHandler canHandle
echo "2. TABELHANDLER CANHANDLE TEST:\n";
echo "===============================\n";

$callbackData = 'cat_ТабУстрЗамРК';
echo "Testing callback: {$callbackData}\n";

// Simulate TabelHandler canHandle logic
$canHandleChecks = [
    'starts_with_category_Табель' => str_starts_with($callbackData, 'category_Табель'),
    'starts_with_cat_ТабПНРаботы' => str_starts_with($callbackData, 'cat_ТабПНРаботы'),
    'starts_with_cat_Таб' => str_starts_with($callbackData, 'cat_Таб'),
    'equals_новое_прочее_yes' => $callbackData === 'новое_прочее_yes',
    'equals_cat_ТабЭлОб_Проч_yes' => $callbackData === 'cat_ТабЭлОб_Проч_yes',
    'equals_cat_ТабВентОтопл' => $callbackData === 'cat_ТабВентОтопл',
    'equals_cat_ТабОтоплВент' => $callbackData === 'cat_ТабОтоплВент',
    'starts_with_cat_ТабВентОтопл_' => str_starts_with($callbackData, 'cat_ТабВентОтопл_'),
    'starts_with_cat_ТабОтоплВент_' => str_starts_with($callbackData, 'cat_ТабОтоплВент_')
];

$canHandle = false;
foreach ($canHandleChecks as $check => $result) {
    echo "   {$check}: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    if ($result) {
        $canHandle = true;
    }
}

echo "Overall canHandle result: " . ($canHandle ? '✅ TRUE' : '❌ FALSE') . "\n";

echo "\n";

// Test 3: Check extractCategoryFromCallback
echo "3. EXTRACTCATEGORYFROMCALLBACK TEST:\n";
echo "===================================\n";

function simulateExtractCategoryFromCallback($callbackData) {
    echo "Input callback: {$callbackData}\n";
    
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $result = substr($callbackData, strlen('category_'));
        echo "Matched category_ format, result: {$result}\n";
        return $result;
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        echo "Matched cat_Таб format\n";
        
        // Check if it's in shortFormats mapping
        $shortFormats = [
            'cat_ТабУстрЗамРК' => 'ТабУстрЗамРК', // Our mapping
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабМетрол' => 'Табель_Метрология'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            $result = $shortFormats[$callbackData];
            echo "Found in shortFormats mapping, result: {$result}\n";
            return $result;
        }
        
        // Handle other short formats as needed
        $result = substr($callbackData, strlen('cat_'));
        echo "Not in mapping, using default substr, result: {$result}\n";
        return $result;
    }
    
    echo "No match found, returning empty string\n";
    return '';
}

$extractedCategory = simulateExtractCategoryFromCallback($callbackData);
echo "Final extracted category: '{$extractedCategory}'\n";

echo "\n";

// Test 4: Check handleOtherWorkTypes logic
echo "4. HANDLEOTHERWORKTYPES ROUTING TEST:\n";
echo "====================================\n";

function simulateHandleOtherWorkTypes($category) {
    echo "Processing category: '{$category}'\n";
    
    // Test RK comments elimination menu (HIGHEST PRIORITY)
    if ($category === 'cat_ТабУстрЗамРК' || $category === 'ТабУстрЗамРК') {
        echo "✅ MATCH: RK comments elimination menu handler\n";
        echo "   Would call: handleRKCommentsEliminationMenu()\n";
        return "handleRKCommentsEliminationMenu";
    }
    
    // Test commissioning
    if ($category === 'Табель_Пусконаладка') {
        echo "   MATCH: Commissioning date selection\n";
        return "handleCommissioningDateSelection";
    }
    
    // Test supervision
    if ($category === 'Табель_Шеф_монтаж') {
        echo "   MATCH: Supervision date selection\n";
        return "handleSupervisionDateSelection";
    }
    
    // Test activity types checking
    $activityTypes = [
        'УЗ_РК', 'ПНР', 'ШМР', 'Электромонтажные', 'Сварочные'
    ];
    
    $strippedCategory = str_replace('Табель_', '', $category);
    echo "   Stripped category: '{$strippedCategory}'\n";
    
    if (in_array($strippedCategory, $activityTypes)) {
        echo "❌ MATCH: Activity type confirmation (this is the problem!)\n";
        echo "   Activity type: {$strippedCategory}\n";
        echo "   Would show confirmation dialog instead of sub-menu\n";
        return "showConfirmationDialog";
    }
    
    echo "   No match found\n";
    return "unknown";
}

$routingResult = simulateHandleOtherWorkTypes($extractedCategory);
echo "Routing result: {$routingResult}\n";

echo "\n";

// Test 5: Check if УЗ_РК is in activityTypes
echo "5. ACTIVITY TYPES CHECK:\n";
echo "========================\n";

$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет'
];

echo "Checking if problematic activity types exist:\n";
$problemTypes = ['УЗ_РК', 'УстрЗам', 'ТабУстрЗамРК'];

foreach ($problemTypes as $type) {
    $exists = in_array($type, $activityTypes);
    echo "   '{$type}': " . ($exists ? '❌ EXISTS (problem!)' : '✅ NOT EXISTS') . "\n";
}

echo "\n";

// Test 6: Full simulation
echo "6. FULL FLOW SIMULATION:\n";
echo "========================\n";

echo "User clicks: 🔧 Устранения замечаний РК\n";
echo "Step 1: Button generates callback: {$callbackData}\n";
echo "Step 2: TabelHandler canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
echo "Step 3: extractCategoryFromCallback: '{$extractedCategory}'\n";
echo "Step 4: handleOtherWorkTypes routing: {$routingResult}\n";

if ($routingResult === "handleRKCommentsEliminationMenu") {
    echo "✅ SUCCESS: Should show RK sub-menu with 6 sections\n";
} elseif ($routingResult === "showConfirmationDialog") {
    echo "❌ PROBLEM: Shows confirmation dialog instead of sub-menu\n";
    echo "   This happens because '{$extractedCategory}' matches an activity type\n";
} else {
    echo "❌ PROBLEM: Unknown routing result\n";
}

echo "\n";

// Test 7: Proposed fix verification
echo "7. PROPOSED FIX VERIFICATION:\n";
echo "=============================\n";

echo "Problem analysis:\n";
if ($extractedCategory === 'УЗ_РК' || $extractedCategory === 'ТабУстрЗамРК') {
    echo "✅ The extracted category '{$extractedCategory}' might be conflicting\n";
    echo "   with existing activity types or handling logic.\n";
    
    if (in_array($extractedCategory, $activityTypes)) {
        echo "❌ CONFIRMED: '{$extractedCategory}' exists in activityTypes array!\n";
        echo "   This causes it to be treated as a direct activity confirmation.\n";
    }
    
    echo "\nRecommended fixes:\n";
    echo "1. Ensure RK handler is at the TOP of handleOtherWorkTypes\n";
    echo "2. Remove any conflicting entries from activityTypes\n";
    echo "3. Verify extractCategoryFromCallback mapping is correct\n";
    echo "4. Add debug logging to see exact execution path\n";
}

echo "\n=== DEBUG TEST COMPLETE ===\n";
?>