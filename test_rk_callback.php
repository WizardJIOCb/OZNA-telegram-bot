<?php
// Simple test to check RK callback handling

echo "=== RK CALLBACK DEBUG TEST ===\n\n";

// Test 1: Check what the actual callback should be
echo "1. EXPECTED CALLBACK DATA:\n";
echo "=========================\n";

// From MessageService createCommissioningWorksKeyboard
$expectedCallback = 'cat_ТабУстрЗамРК';
echo "Expected callback: '{$expectedCallback}'\n";
echo "Callback length: " . strlen($expectedCallback) . " bytes\n";
echo "Callback hex: " . bin2hex($expectedCallback) . "\n\n";

// Test 2: Check TabelHandler canHandle logic
echo "2. CANHANDLE LOGIC TEST:\n";
echo "========================\n";

function testCanHandle($callbackData) {
    echo "Testing callback: '{$callbackData}'\n";
    
    // Simulate TabelHandler canHandle logic
    $canHandle = str_starts_with($callbackData, 'category_Табель') || 
                 str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                 str_starts_with($callbackData, 'cat_Таб') ||
                 $callbackData === 'новое_прочее_yes' ||
                 $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
                 $callbackData === 'cat_ТабВентОтопл' ||
                 $callbackData === 'cat_ТабОтоплВент' ||
                 str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
                 str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
                 $callbackData === 'cat_ТабУстрЗамРК';
    
    echo "  canHandle result: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";
    
    // Test specific conditions
    if (str_starts_with($callbackData, 'cat_Таб')) {
        echo "  ✅ Matches 'cat_Таб' prefix\n";
    } else {
        echo "  ❌ Does NOT match 'cat_Таб' prefix\n";
    }
    
    if ($callbackData === 'cat_ТабУстрЗамРК') {
        echo "  ✅ Exact match for RK callback\n";
    } else {
        echo "  ❌ NOT exact match for RK callback\n";
    }
    
    return $canHandle;
}

$result = testCanHandle($expectedCallback);
echo "\nFinal result: " . ($result ? 'SHOULD BE HANDLED' : 'WILL NOT BE HANDLED') . "\n\n";

// Test 3: Check if there might be encoding issues
echo "3. ENCODING ISSUES CHECK:\n";
echo "=========================\n";

$variations = [
    'cat_ТабУстрЗамРК',
    'cat_ТабУстрЗамРК', // Potential UTF-8 variation
    'cat_ТабУстрЗамРК' // Another potential variation
];

foreach ($variations as $i => $variation) {
    echo "Variation " . ($i + 1) . ": '{$variation}'\n";
    echo "  Length: " . strlen($variation) . " bytes\n";
    echo "  Hex: " . bin2hex($variation) . "\n";
    echo "  canHandle: " . (testCanHandle($variation) ? 'TRUE' : 'FALSE') . "\n\n";
}

// Test 4: Check routing logic
echo "4. ROUTING LOGIC TEST:\n";
echo "======================\n";

function testExtractCategory($callbackData) {
    echo "Testing extractCategoryFromCallback for: '{$callbackData}'\n";
    
    // Simulate the mapping logic
    $shortFormats = [
        'cat_ТабУстрЗамРК' => 'ТабУстрЗамРК'
    ];
    
    if (str_starts_with($callbackData, 'cat_')) {
        if (isset($shortFormats[$callbackData])) {
            $result = $shortFormats[$callbackData];
            echo "  Found in mapping: '{$result}'\n";
            return $result;
        }
        
        $result = substr($callbackData, strlen('cat_'));
        echo "  Using substr: '{$result}'\n";
        return $result;
    }
    
    echo "  No extraction needed: '{$callbackData}'\n";
    return $callbackData;
}

$extractedCategory = testExtractCategory($expectedCallback);
echo "Final extracted category: '{$extractedCategory}'\n\n";

// Test 5: Check handleOtherWorkTypes logic
echo "5. HANDLEOTHERWORKTYPES LOGIC:\n";
echo "==============================\n";

function testHandleOtherWorkTypes($category) {
    echo "Testing handleOtherWorkTypes with category: '{$category}'\n";
    
    // Test RK handler condition
    if ($category === 'cat_ТабУстрЗамРК' || $category === 'ТабУстрЗамРК') {
        echo "  ✅ MATCHES RK handler condition!\n";
        echo "  Would call: handleRKCommentsEliminationMenu()\n";
        return 'SUCCESS';
    } else {
        echo "  ❌ Does NOT match RK handler condition\n";
        echo "  Would fall through to 'Unknown command'\n";
        return 'FAIL';
    }
}

$handlingResult = testHandleOtherWorkTypes($extractedCategory);
echo "Handling result: {$handlingResult}\n\n";

echo "=== SUMMARY ===\n";
echo "Expected flow:\n";
echo "1. Button callback: '{$expectedCallback}'\n";
echo "2. canHandle: " . ($result ? 'TRUE' : 'FALSE') . "\n";
echo "3. extractCategory: '{$extractedCategory}'\n";
echo "4. handleOtherWorkTypes: {$handlingResult}\n\n";

if ($result && $handlingResult === 'SUCCESS') {
    echo "✅ SHOULD WORK CORRECTLY\n";
    echo "If you're still getting 'Unknown command', there may be:\n";
    echo "- Another handler intercepting the callback\n";
    echo "- Server logs not showing TabelHandler debug messages\n";
    echo "- Callback data corruption during transmission\n";
} else {
    echo "❌ LOGIC ERROR FOUND\n";
    echo "The issue is in the handler logic itself.\n";
}
?>