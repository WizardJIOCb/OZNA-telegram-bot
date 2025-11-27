<?php

// Test emoji handling
require_once __DIR__ . '/encoding_helper.php';

// Test cases
$testCases = [
    '? Табель',
    'Табель',
    '? Помощь',
    'Помощь',
    '? Профиль',
    'Профиль',
    '? Админка',
    'Админка',
    '📊 Табель',
    '❓ Помощь',
    '👤 Профиль',
    '⚙️ Админка'
];

echo "Testing emoji handling:\n";
echo "=====================\n";
foreach ($testCases as $test) {
    $result = handleEmojiText($test);
    echo "Input:  '$test'\n";
    echo "Output: '$result'\n";
    echo "Equal:  " . ($test === $result ? "YES" : "NO") . "\n";
    echo "---\n";
}

// Test with some corrupted text
echo "\nTesting with corrupted text:\n";
echo "============================\n";
$corruptedTests = [
    'РўР°Р±РµР»СЊ',
    'РџСЂРѕС„РёР»СЊ',
    'РџРѕРјРѕС‰СЊ',
    'РђРґРјРёРЅРєР°'
];

foreach ($corruptedTests as $test) {
    $result = handleEmojiText($test);
    echo "Input:  '$test'\n";
    echo "Output: '$result'\n";
    echo "---\n";
}