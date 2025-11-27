<?php
// Simple test for the commissioning works button fix
require_once __DIR__ . '/encoding_helper.php';

echo "=== Simple Commissioning Works Button Fix Test ===\n";

// Test the encoding helper fix
echo "\n1. Testing encoding helper fixKnownCyrillicPatterns:\n";

$corruptedCallback = 'category_РўР°Р±РµР»СЊ_РџРќР›';
echo "Corrupted callback: $corruptedCallback\n";

$fixedCallback = fixKnownCyrillicPatterns($corruptedCallback);
echo "Fixed callback: $fixedCallback\n";

$expectedCallback = 'category_Табель_ПНЛ';
if ($fixedCallback === $expectedCallback) {
    echo "SUCCESS: Encoding helper fix works correctly!\n";
} else {
    echo "FAILURE: Expected '$expectedCallback' but got '$fixedCallback'\n";
}

// Test extracting category
$category = substr($fixedCallback, strlen('category_'));
echo "Extracted category: $category\n";

$expectedCategory = 'Табель_ПНЛ';
if ($category === $expectedCategory) {
    echo "SUCCESS: Category extraction works correctly!\n";
} else {
    echo "FAILURE: Expected '$expectedCategory' but got '$category'\n";
}

// Test routing logic
echo "\n2. Testing routing logic:\n";
if ($category === 'Табель_ПНЛ') {
    echo "SUCCESS: Routing would correctly call handleCommissioningWorksMenu!\n";
} else {
    echo "FAILURE: Routing would not match Табель_ПНЛ\n";
}

// Test with various corrupted patterns
echo "\n3. Testing various corrupted patterns:\n";
$testPatterns = [
    'РўР°Р±РµР»СЊ_РџРќР›' => 'Табель_ПНЛ',
    'РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ_Р Р°Р±РѕС‚С‹' => 'Пусконаладочные_Работы',
    'category_РўР°Р±РµР»СЊ_РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'category_Табель_Пусконаладка'
];

foreach ($testPatterns as $corrupted => $expected) {
    $fixed = fixKnownCyrillicPatterns($corrupted);
    echo "Input: $corrupted\n";
    echo "Fixed: $fixed\n";
    echo "Expected: $expected\n";
    if ($fixed === $expected) {
        echo "SUCCESS!\n";
    } else {
        echo "FAILURE!\n";
    }
    echo "\n";
}

echo "Test completed!\n";
?>