<?php
// Simple test script to verify encoding fix

echo "Testing encoding fix...\n\n";

// Test encoding helper functions
echo "1. Testing encoding helper functions...\n";
require_once __DIR__ . '/encoding_helper.php';

// Test cases from the logs (these are already corrupted in the log file)
// Let's test with properly encoded text to see if our fix works
$testCases = [
    '📊 Табель',
    '👤 Профиль',
    '❓ Помощь',
    '⚙️ Админка',
    'category_Табель',
    'category_Админка',
    'category_Профиль',
    'category_Помощь'
];

echo "Testing with properly encoded text:\n";
foreach ($testCases as $test) {
    echo "   Original: $test\n";
    $fixed = fixEncoding($test);
    echo "   Fixed:    $fixed\n";
    echo "   Match:    " . ($test === $fixed ? "YES" : "NO") . "\n";
    echo "   ------------------------\n";
}

// Test with some known corrupted patterns
echo "\nTesting with known corrupted patterns:\n";
$corruptedCases = [
    'РўР°Р±РµР»СЊ',
    'РџСЂРѕС„РёР»СЊ',
    'РџРѕРјРѕС‰СЊ',
    'РђРґРјРёРЅРєР°'
];

foreach ($corruptedCases as $test) {
    echo "   Corrupted: $test\n";
    $fixed = fixEncoding($test);
    echo "   Fixed:     $fixed\n";
    echo "   ------------------------\n";
}

echo "\nEncoding test completed!\n";
?>