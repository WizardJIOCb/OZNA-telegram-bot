<?php
/**
 * Debug character encoding for supervision installation works
 */

echo "🔍 SUPERVISION CALLBACK CHARACTER ANALYSIS\n";
echo "==========================================\n\n";

$testCallback = 'cat_ШМР_СдачаОтч_yes';
echo "Test callback: '{$testCallback}'\n";
echo "Length: " . strlen($testCallback) . " bytes\n";
echo "Character encoding: " . mb_detect_encoding($testCallback) . "\n\n";

// Test each character
echo "Character by character analysis:\n";
for ($i = 0; $i < strlen($testCallback); $i++) {
    $char = $testCallback[$i];
    $ord = ord($char);
    echo "Position {$i}: '{$char}' (ASCII: {$ord})\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

// Test pattern matching step by step
echo "PATTERN MATCHING TESTS:\n";

// Test 1: Direct string match
$directMatches = [
    'cat_ШМР_СдачаОтч_yes' => 'ШМ_СдачаАвансовогоОтчета',
    'cat_ШМР_РабПоГр_yes' => 'ШМ_РабПоГрафику',
    'cat_ШМР_Дорога_yes' => 'ШМ_Дорога'
];

echo "\n1. Direct string matching:\n";
foreach ($directMatches as $callback => $expected) {
    $match = ($testCallback === $callback);
    echo "   '{$callback}' === '{$testCallback}': " . ($match ? "✅ YES" : "❌ NO") . "\n";
    if ($match) {
        echo "   → Should map to: '{$expected}'\n";
    }
}

// Test 2: Regex pattern matching
echo "\n2. Regex pattern matching:\n";
$pattern = '/^cat_ШМР_(.+)_yes$/u';
echo "Pattern: {$pattern}\n";

if (preg_match($pattern, $testCallback, $matches)) {
    echo "✅ PATTERN MATCHES!\n";
    echo "Full match: '{$matches[0]}'\n";
    echo "Captured group: '{$matches[1]}'\n";
    
    $shortToFullMap = [
        'РабПоГр' => 'ШМ_РабПоГрафику',
        'ПодгПоезд' => 'ШМ_ПодготовкаКПоездке',
        'Дорога' => 'ШМ_Дорога',
        'Инстр' => 'ШМ_Инструктаж',
        'СдачаОтч' => 'ШМ_СдачаАвансовогоОтчета'
    ];
    
    $shortKey = $matches[1];
    if (isset($shortToFullMap[$shortKey])) {
        echo "✅ MAPPING FOUND: '{$shortKey}' → '{$shortToFullMap[$shortKey]}'\n";
    } else {
        echo "❌ MAPPING NOT FOUND for '{$shortKey}'\n";
        echo "Available mappings:\n";
        foreach ($shortToFullMap as $key => $value) {
            echo "   '{$key}' → '{$value}'\n";
        }
    }
} else {
    echo "❌ PATTERN DOES NOT MATCH\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "DIAGNOSIS:\n";
echo "This test will show exactly what character encoding issues exist\n";
echo "and whether the pattern matching should work.\n";
?>