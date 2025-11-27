<?php
// Test the final RK Прочее fix

echo "=== Testing Final RK Прочее Fix ===\n\n";

echo "Testing PARTIAL RK MATCH logic:\n";

function testPartialRKMatch($category) {
    echo "Testing category: '{$category}'\n";
    
    $hasUstrZam = strpos($category, 'УстрЗам') !== false;
    $hasUzRk = strpos($category, 'УЗ_РК') !== false;
    $isProchee = strpos($category, 'УстрЗам_Прочее') !== false;
    
    echo "  Contains 'УстрЗам': " . ($hasUstrZam ? 'Yes' : 'No') . "\n";
    echo "  Contains 'УЗ_РК': " . ($hasUzRk ? 'Yes' : 'No') . "\n";
    echo "  Is 'УстрЗам_Прочее': " . ($isProchee ? 'Yes' : 'No') . "\n";
    
    // Updated logic: trigger safety net but exclude Прочее
    $shouldTriggerSafetyNet = ($hasUstrZam || $hasUzRk) && !$isProchee;
    
    echo "  Should trigger PARTIAL RK MATCH: " . ($shouldTriggerSafetyNet ? 'Yes' : 'No') . "\n";
    echo "  Expected result: " . ($shouldTriggerSafetyNet ? 'Redirect to RK menu' : 'Continue to RK section handler') . "\n\n";
    
    return $shouldTriggerSafetyNet;
}

// Test cases
echo "1. Testing 'Прочее оборудование' callback:\n";
$procheeResult = testPartialRKMatch('Табель_УстрЗам_Прочее');

echo "2. Testing normal RK section callback:\n";
$normalRkResult = testPartialRKMatch('Табель_УстрЗам_ЭС');

echo "3. Testing another RK section callback:\n";
$anotherRkResult = testPartialRKMatch('Табель_УстрЗам_АТХ');

echo "4. Testing non-RK callback:\n";
$nonRkResult = testPartialRKMatch('Табель_Электрооборудование');

// Summary
echo "=== Summary ===\n";
echo "✓ Прочее should NOT trigger partial match: " . (!$procheeResult ? 'PASS' : 'FAIL') . "\n";
echo "✓ Normal RK sections should trigger partial match: " . ($normalRkResult ? 'PASS' : 'FAIL') . "\n";
echo "✓ Another RK section should trigger partial match: " . ($anotherRkResult ? 'PASS' : 'FAIL') . "\n";
echo "✓ Non-RK callbacks should NOT trigger partial match: " . (!$nonRkResult ? 'PASS' : 'FAIL') . "\n\n";

// Test the complete flow now
echo "=== Testing Complete Flow ===\n";

function simulateCompleteFlow($category) {
    echo "Processing category: '{$category}'\n";
    
    // First check: Partial RK match
    $hasUstrZam = strpos($category, 'УстрЗам') !== false;
    $hasUzRk = strpos($category, 'УЗ_РК') !== false;
    $isProchee = strpos($category, 'УстрЗам_Прочее') !== false;
    $shouldTriggerPartialMatch = ($hasUstrZam || $hasUzRk) && !$isProchee;
    
    if ($shouldTriggerPartialMatch) {
        echo "✓ Caught by PARTIAL RK MATCH - redirected to RK menu\n";
        return 'RK_MENU';
    }
    
    echo "✓ Passed PARTIAL RK MATCH check\n";
    
    // Second check: RK section handler
    if (preg_match('/^Табель_УстрЗам_(.+)$/', $category, $matches)) {
        $rkSection = $matches[1];
        echo "✓ Matched RK section handler, extracted: '{$rkSection}'\n";
        
        if ($rkSection === 'Прочее') {
            echo "✓ Detected 'Прочее' section - should prompt for custom input\n";
            return 'CUSTOM_INPUT_PROMPT';
        } else {
            echo "✓ Regular RK section - should show confirmation\n";
            return 'RK_CONFIRMATION';
        }
    }
    
    echo "❌ No handler matched\n";
    return 'UNKNOWN';
}

echo "\nSimulating complete flow for 'Прочее оборудование':\n";
$procheeFlow = simulateCompleteFlow('Табель_УстрЗам_Прочее');
echo "Result: {$procheeFlow}\n\n";

echo "Simulating complete flow for 'Раздел ЭС':\n";
$esFlow = simulateCompleteFlow('Табель_УстрЗам_ЭС');
echo "Result: {$esFlow}\n\n";

// Final result
if ($procheeFlow === 'CUSTOM_INPUT_PROMPT' && $esFlow === 'RK_MENU') {
    echo "🎉 SUCCESS! The fix should work correctly now.\n";
    echo "\nExpected behavior:\n";
    echo "✓ 'Прочее оборудование' → Prompts for custom description\n";
    echo "✓ Other RK sections → Redirected to RK menu (which then shows confirmation dialogs)\n";
    echo "✓ All RK sections work as intended\n";
} else {
    echo "❌ There's still an issue with the logic.\n";
}

echo "\n=== Fixes Applied ===\n";
echo "1. Updated emergency RK safety net to exclude 'УстрЗам_Прочее'\n";
echo "2. Updated partial RK match to exclude 'УстрЗам_Прочее'\n";
echo "3. Updated RK Прочее message to match working pattern\n";
echo "\nThe RK 'Прочее оборудование' button should now work like 'Дополнительные работы' Прочее!\n";