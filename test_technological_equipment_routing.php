<?php

echo "=== Testing ШМР Technological Equipment Button Routing ===\n";

// Simulate the callback mapping
$callbackData = 'cat_ШМР_ТехОб';
echo "1. Button clicked: {$callbackData}\n";

// Step 1: Check canHandle
$canHandle = str_starts_with($callbackData, 'cat_ШМР_');
echo "2. TabelHandler canHandle: " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n";

// Step 2: Extract category
$shortFormats = [
    'cat_ШМР_ТехОб' => 'Табель_ШМР_ТехнологическоеОборудование'
];

$extractedCategory = '';
if (isset($shortFormats[$callbackData])) {
    $extractedCategory = $shortFormats[$callbackData];
    echo "3. Category extracted: {$extractedCategory}\n";
} else {
    echo "3. ❌ Category extraction failed!\n";
    exit;
}

// Step 3: Check if early routing will interfere
$categoryForCheck = $extractedCategory;

// Check the problematic early routing condition:
$earlyRoutingCondition = ($categoryForCheck === 'cat_ТабТехОб' || $categoryForCheck === 'Табель_ТехнологическоеОборудование') && $categoryForCheck !== 'cat_ШМР_ТехОб';

echo "4. Early routing condition check:\n";
echo "   - Is 'cat_ТабТехОб'? " . ($categoryForCheck === 'cat_ТабТехОб' ? "YES" : "NO") . "\n";
echo "   - Is 'Табель_ТехнологическоеОборудование'? " . ($categoryForCheck === 'Табель_ТехнологическоеОборудование' ? "YES" : "NO") . "\n";
echo "   - Is NOT 'cat_ШМР_ТехОб'? " . ($categoryForCheck !== 'cat_ШМР_ТехОб' ? "YES" : "NO") . "\n";
echo "   - Will trigger early routing? " . ($earlyRoutingCondition ? "❌ YES (PROBLEM!)" : "✅ NO (GOOD)") . "\n";

if ($earlyRoutingCondition) {
    echo "5. ❌ ISSUE FOUND: Early routing will intercept this callback!\n";
    echo "   The extracted category '{$extractedCategory}' matches the early routing condition.\n";
    echo "   This will call handleTechnologicalEquipmentMenu() instead of showing ШМР submenu.\n";
} else {
    echo "5. ✅ Early routing will NOT interfere.\n";
}

// Step 4: Check if it reaches the correct handler
$correctHandlerCondition = ($extractedCategory === 'Табель_ШМР_ТехнологическоеОборудование');
echo "6. Will reach ШМР technological equipment handler? " . ($correctHandlerCondition ? "✅ YES" : "❌ NO") . "\n";

echo "\n=== CONCLUSION ===\n";
if ($earlyRoutingCondition) {
    echo "❌ PROBLEM: Early routing is intercepting the ШМР technological equipment callback.\n";
    echo "FIX NEEDED: Modify the early routing condition to properly exclude ШМР callbacks.\n";
} else if ($correctHandlerCondition) {
    echo "✅ ROUTING SHOULD WORK: The callback should reach the correct ШМР handler.\n";
} else {
    echo "❌ UNKNOWN ISSUE: Check other routing conditions or handler priorities.\n";
}

echo "\nTest completed.\n";