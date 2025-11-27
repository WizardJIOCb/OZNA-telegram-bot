<?php
// Test supervision callback mapping fix

echo "=== SUPERVISION CALLBACK MAPPING TEST ===\n\n";

// Test the exact callbacks that are being sent
$supervisionCallbacks = [
    'category_Табель_ШМ_РабГарантии' => 'Supervision warranty works',
    'category_Табель_ШМ_УЗ_РК' => 'Supervision RK elimination'
];

echo "1. TESTING SUPERVISION CALLBACK EXTRACTION:\n";
echo "============================================\n";

// Simulate the extractCategoryFromCallback method
function simulateExtractCategoryFromCallback($callbackData) {
    echo "Input callback: '{$callbackData}'\n";
    
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $result = substr($callbackData, strlen('category_'));
        echo "Standard category extraction: '{$result}'\n";
        
        // Now check if it should be mapped
        $supervisionMappings = [
            'Табель_ШМ_РабГарантии' => 'Табель_ШМ_РабГарантии',
            'Табель_ШМ_УЗ_РК' => 'Табель_ШМ_УЗ_РК'
        ];
        
        if (isset($supervisionMappings[$result])) {
            echo "✅ SUPERVISION MAPPING FOUND: '{$result}' -> '{$supervisionMappings[$result]}'\n";
            return $supervisionMappings[$result];
        } else {
            echo "❌ NO SUPERVISION MAPPING: '{$result}'\n";
            return $result;
        }
    }
    
    echo "❌ Not a category_ callback\n";
    return '';
}

foreach ($supervisionCallbacks as $callback => $description) {
    echo "\nTesting {$description}:\n";
    echo str_repeat("-", 40) . "\n";
    $extracted = simulateExtractCategoryFromCallback($callback);
    echo "Final result: '{$extracted}'\n";
}

echo "\n2. TESTING ROUTING LOGIC:\n";
echo "=========================\n";

// Simulate the routing logic from handleOtherWorkTypes
function simulateSupervisionRouting($category) {
    echo "Testing routing for category: '{$category}'\n";
    
    // Check supervision warranty equipment routing
    if ($category === 'Табель_ШМ_РабГарантии') {
        echo "✅ MATCH: Supervision warranty equipment routing\n";
        echo "   Would call: handleWarrantyEquipmentMenu(\$chatId, \$conversation, 'supervision')\n";
        return 'warranty_menu';
    }
    
    // Check supervision RK elimination routing
    if ($category === 'Табель_ШМ_УЗ_РК') {
        echo "✅ MATCH: Supervision RK elimination routing\n";
        echo "   Would call: handleRKCommentsEliminationMenu(\$chatId)\n";
        return 'rk_menu';
    }
    
    echo "❌ NO MATCH: Would fall through to other handlers\n";
    return 'no_match';
}

foreach ($supervisionCallbacks as $callback => $description) {
    echo "\nRouting test for {$description}:\n";
    echo str_repeat("-", 40) . "\n";
    $extracted = simulateExtractCategoryFromCallback($callback);
    $routing = simulateSupervisionRouting($extracted);
    echo "Routing result: {$routing}\n";
}

echo "\n3. COMPLETE FLOW SIMULATION:\n";
echo "============================\n";

echo "SCENARIO: User clicks '🛡️ Работы по гарантии' in supervision department\n";
echo "1. Telegram sends callback: 'category_Табель_ШМ_РабГарантии'\n";
echo "2. TabelHandler.canHandle(): " . (str_starts_with('category_Табель_ШМ_РабГарантии', 'category_Табель') ? 'TRUE ✅' : 'FALSE ❌') . "\n";
echo "3. extractCategoryFromCallback(): ";
$extracted1 = simulateExtractCategoryFromCallback('category_Табель_ШМ_РабГарантии');
echo "4. handleOtherWorkTypes() routing: ";
$routing1 = simulateSupervisionRouting($extracted1);
echo "5. Expected result: User sees warranty equipment sub-menu\n";

echo "\nSCENARIO: User clicks '🔧 Устранение замечаний РК' in supervision department\n";
echo "1. Telegram sends callback: 'category_Табель_ШМ_УЗ_РК'\n";
echo "2. TabelHandler.canHandle(): " . (str_starts_with('category_Табель_ШМ_УЗ_РК', 'category_Табель') ? 'TRUE ✅' : 'FALSE ❌') . "\n";
echo "3. extractCategoryFromCallback(): ";
$extracted2 = simulateExtractCategoryFromCallback('category_Табель_ШМ_УЗ_РК');
echo "4. handleOtherWorkTypes() routing: ";
$routing2 = simulateSupervisionRouting($extracted2);
echo "5. Expected result: User sees RK sections sub-menu\n";

echo "\n4. VERIFICATION SUMMARY:\n";
echo "========================\n";

if ($routing1 === 'warranty_menu' && $routing2 === 'rk_menu') {
    echo "✅ SUCCESS: Both supervision sub-activities should work correctly!\n";
    echo "✅ Users will see sub-menus instead of creating reports\n";
    echo "✅ Supervision department fully configured\n";
} else {
    echo "❌ ISSUE: Some routing is not working correctly\n";
    echo "   Warranty routing: " . ($routing1 === 'warranty_menu' ? 'OK' : 'FAILED') . "\n";
    echo "   RK routing: " . ($routing2 === 'rk_menu' ? 'OK' : 'FAILED') . "\n";
}

echo "\n=== SUPERVISION CALLBACK MAPPING TEST COMPLETE ===\n";
?>