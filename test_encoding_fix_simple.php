<?php
// Simple test script to verify encoding fixes

require_once __DIR__ . '/encoding_helper.php';

// Test case for double-encoded UTF-8 text
$testCases = [
    'РўР°Р±РµР»СЊ' => 'Табель',
    'РџСЂРѕС„РёР»СЊ' => 'Профиль',
    'РђРґРјРёРЅРєР°' => 'Админка',
    'РџРѕРјРѕС‰СЊ' => 'Помощь'
];

echo "Testing encoding fixes...\n\n";

foreach ($testCases as $corrupted => $expected) {
    $fixed = fixEncoding($corrupted);
    $status = ($fixed === $expected) ? "PASS" : "FAIL";
    echo "Input:    $corrupted\n";
    echo "Expected: $expected\n";
    echo "Fixed:    $fixed\n";
    echo "Status:   $status\n\n";
}

echo "Testing completed.\n";
?>