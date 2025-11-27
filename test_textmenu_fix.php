<?php
// Test script to verify TextmenuCommand fixes

require_once __DIR__ . '/encoding_helper.php';

// Test cases for double-encoded UTF-8 text that might come from Telegram
$testCases = [
    'РўР°Р±РµР»СЊ' => 'Табель',
    'РџСЂРѕС„РёР»СЊ' => 'Профиль',
    'РђРґРјРёРЅРєР°' => 'Админка',
    'РџРѕРјРѕС‰СЊ' => 'Помощь',
    'Р“Р»Р°РІРЅРѕРµ' => 'Главное',
    'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
    'Р’С‡РµСЂР°' => 'Вчера'
];

echo "Testing TextmenuCommand encoding fixes...\n\n";

foreach ($testCases as $corrupted => $expected) {
    // Simulate the TextmenuCommand processing
    $text = $corrupted;
    $text = fixEncoding($text);
    
    $status = ($text === $expected) ? "PASS" : "FAIL";
    echo "Input:    $corrupted\n";
    echo "Expected: $expected\n";
    echo "Fixed:    $text\n";
    echo "Status:   $status\n\n";
}

echo "Testing switch case matching...\n\n";

// Test switch case matching with fixed text
$menuItems = [
    'Табель',
    'Профиль',
    'Админка',
    'Помощь',
    'Главное'
];

$testText = 'РўР°Р±РµР»СЊ'; // Double-encoded "Табель"
$fixedText = fixEncoding($testText);

$matched = false;
foreach ($menuItems as $item) {
    if ($fixedText === $item) {
        $matched = true;
        break;
    }
}

echo "Fixed text: $fixedText\n";
echo "Match found: " . ($matched ? "YES" : "NO") . "\n";

if ($matched) {
    echo "SUCCESS: TextmenuCommand should now properly recognize menu items!\n";
} else {
    echo "FAILURE: TextmenuCommand may still have issues.\n";
}

echo "\nTesting completed.\n";
?>