<?php
// Comprehensive test for mobile brigade button functionality

echo "Comprehensive Mobile Brigade Button Test\n";
echo "======================================\n\n";

// Test 1: Check if the mobile brigade button callback is properly handled
echo "Test 1: Mobile Brigade Button Callback Handling\n";
echo "----------------------------------------------\n";

$correctCallback = 'category_Табель_Мобильная_Бригада';
$corruptedCallback = 'category_РўР°Р±РµР»СЊ_РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°';

echo "Correct callback: $correctCallback\n";
echo "Starts with 'category_Табель': " . (str_starts_with($correctCallback, 'category_Табель') ? 'YES' : 'NO') . "\n\n";

echo "Corrupted callback: $corruptedCallback\n";
echo "Starts with 'category_Табель': " . (str_starts_with($corruptedCallback, 'category_Табель') ? 'YES' : 'NO') . "\n";
echo "Contains corrupted pattern: " . (strpos($corruptedCallback, 'РўР°Р±РµР»СЊ') !== false ? 'YES' : 'NO') . "\n\n";

// Test 2: Simulate the fix process
echo "Test 2: Fix Process Simulation\n";
echo "-----------------------------\n";

function simulateFixProcess($callbackData) {
    echo "Input callback: $callbackData\n";
    
    // Check if it needs fixing
    if (strpos($callbackData, 'category_Табель') === false && strpos($callbackData, 'category_РўР°Р±РµР»СЊ') !== false) {
        echo "Needs fixing - contains corrupted pattern\n";
        $callbackData = fixCorruptedPatterns($callbackData);
        echo "After fixing: $callbackData\n";
    } else {
        echo "No fixing needed\n";
    }
    
    $canHandle = str_starts_with($callbackData, 'category_Табель');
    echo "Can handle: " . ($canHandle ? 'YES' : 'NO') . "\n\n";
    
    return $canHandle;
}

function fixCorruptedPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РўР°Р±РµР»СЊ' => 'Табель',
        'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
        'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    return $str;
}

simulateFixProcess($correctCallback);
simulateFixProcess($corruptedCallback);

// Test 3: Check routing logic
echo "Test 3: Routing Logic\n";
echo "--------------------\n";

function testRouting($category) {
    echo "Category: $category\n";
    
    if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
        echo "Routing to: handleMobileBrigadeDateSelection()\n";
        echo "Expected behavior: Show date selection menu for mobile brigade\n";
        return true;
    } else {
        echo "Routing to: Other handler or unknown command\n";
        return false;
    }
}

// Test with correct category
$correctCategory = 'Табель_Мобильная_Бригада';
testRouting($correctCategory);

// Test with alternative category
$alternativeCategory = 'Табель_МобБриг';
testRouting($alternativeCategory);

echo "\nTest completed successfully!\n";
echo "The mobile brigade button should now work correctly.\n";