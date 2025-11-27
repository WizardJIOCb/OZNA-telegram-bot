<?php

// Test script to verify encoding fixes

echo "Testing encoding fixes...\n";

// Function to fix the specific corruption pattern we're seeing
function fixCyrillicCorruption($str) {
    // The corruption pattern we're seeing is when UTF-8 bytes are interpreted as Windows-1251
    // For example: "Профиль" in UTF-8 is 0xD0 0x9F 0xD1 0x80 0xD0 0xBE 0xD1 0x84 0xD0 0xB8 0xD0 0xBB 0xD1 0x8C
    // When these bytes are interpreted as Windows-1251, they become:
    // РџСЂРѕС„РёР»СЊ
    
    // Let's create a mapping for the specific corruption we're seeing
    $corruptionMap = [
        // Individual character mappings for the corruption pattern
        'Рџ' => 'П', 'СЂ' => 'р', 'Рѕ' => 'о', 'С„' => 'ф', 'Рё' => 'и', 'Р»' => 'л', 'СЊ' => 'ь',
        'Рђ' => 'А', 'Рґ' => 'д', 'Рј' => 'м', 'Рё' => 'и', 'РЅ' => 'н', 'Рє' => 'к', 'Р°' => 'а',
        'Рў' => 'Т', 'Р°' => 'а', 'Р±' => 'б', 'Рµ' => 'е', 'Р»' => 'л', 'СЊ' => 'ь',
        'РЈ' => 'У', 'СЂ' => 'р', 'Рѕ' => 'о', 'РІ' => 'в', 'Рµ' => 'е', 'РЅ' => 'н', 'СЊ' => 'ь',
        'Р”' => 'Д', 'Рѕ' => 'о', 'СЃ' => 'с', 'С‚' => 'т', 'Сѓ' => 'у', 'Рї' => 'п', 'Р°' => 'а',
        'Р’' => 'В', 'С‡' => 'ч', 'Рµ' => 'е', 'СЂ' => 'р', 'Р°' => 'а',
        'РЎ' => 'С', 'Рµ' => 'е', 'Рі' => 'г', 'Рѕ' => 'о', 'Рґ' => 'д', 'РЅ' => 'н', 'СЏ' => 'я',
        'Р—' => 'З', 'Р°' => 'а', 'СЏ' => 'я', 'РІ' => 'в', 'Рє' => 'к', 'Рё' => 'и',
        'Рџ' => 'П', 'СЂ' => 'р', 'Рѕ' => 'о', 'Рµ' => 'е', 'Рє' => 'к', 'С‚' => 'т', 'С‹' => 'ы',
        'РЈ' => 'У', 'Рґ' => 'д', 'Р°' => 'а', 'Р»' => 'л', 'Рё' => 'и', 'С‚' => 'т', 'СЊ' => 'ь',
        'Рџ' => 'П', 'Рѕ' => 'о', 'Рґ' => 'д', 'С‚' => 'т', 'РІ' => 'в', 'Рµ' => 'е', 'СЂ' => 'р', 'Рґ' => 'д', 'Рё' => 'и', 'С‚' => 'т', 'СЊ' => 'ь',
        'Рћ' => 'О', 'С‡' => 'ч', 'Рё' => 'и', 'СЃ' => 'с', 'С‚' => 'т', 'Рё' => 'и', 'С‚' => 'т', 'СЊ' => 'ь',
        'РЎ' => 'С', 'РІ' => 'в', 'Р°' => 'а', 'СЂ' => 'р', 'Рѕ' => 'о', 'С‡' => 'ч', 'РЅ' => 'н', 'С‹' => 'ы', 'Рµ' => 'е',
        'РЁ' => 'Ш', 'Рµ' => 'е', 'С„' => 'ф', 'Рњ' => 'М', 'Рѕ' => 'о', 'РЅ' => 'н', 'С‚' => 'т', 'Р°' => 'а', 'Р¶' => 'ж',
        'Рџ' => 'П', 'Сѓ' => 'у', 'СЃ' => 'с', 'Рє' => 'к', 'Рѕ' => 'о', 'РЅ' => 'н', 'Р°' => 'а', 'Р»' => 'л', 'Р°' => 'а', 'Рґ' => 'д', 'Рє' => 'к', 'Р°' => 'а',
        'Рџ' => 'П', 'Рѕ' => 'о', 'РІ' => 'в', 'С‹' => 'ы', 'СЃ' => 'с', 'Рё' => 'и', 'С‚' => 'т', 'СЊ' => 'ь',
        'Рџ' => 'П', 'Рѕ' => 'о', 'РЅ' => 'н', 'Рё' => 'и', 'Р·' => 'з', 'Рё' => 'и', 'С‚' => 'т', 'СЊ' => 'ь',
        'РЎ' => 'С', 'Сѓ' => 'у', 'Рї' => 'п', 'Рµ' => 'е', 'СЂ' => 'р', 'РІ' => 'в', 'Р°' => 'а', 'Р№' => 'й', 'Р·' => 'з', 'Рµ' => 'е', 'СЂ' => 'р',
        'Рћ' => 'О', 'С‚' => 'т', 'С‡' => 'ч', 'Рµ' => 'е', 'С‚' => 'т', 'С‹' => 'ы',
        'Р’' => 'В', 'СЃ' => 'с', 'Рµ' => 'е', 'Рћ' => 'О', 'С‚' => 'т', 'С‡' => 'ч', 'Рµ' => 'е', 'С‚' => 'т', 'С‹' => 'ы',
        'Рђ' => 'А', 'Рє' => 'к', 'С‚' => 'т', 'Рё' => 'и', 'РІ' => 'в', 'РЅ' => 'н', 'Рѕ' => 'о', 'СЃ' => 'с', 'С‚' => 'т', 'СЊ' => 'ь',
        'Рќ' => 'Н', 'Р°' => 'а', 'Р·' => 'з', 'Р°' => 'а', 'Рґ' => 'д',
        'Р“' => 'Г', 'Р»' => 'л', 'Р°' => 'а', 'РІ' => 'в', 'РЅ' => 'н', 'Рѕ' => 'о', 'Рµ' => 'е',
        'Рџ' => 'П', 'Рѕ' => 'о', 'Р»' => 'л', 'СЊ' => 'ь', 'Р·' => 'з', 'Рѕ' => 'о', 'РІ' => 'в', 'Р°' => 'а', 'С‚' => 'т', 'Рµ' => 'е', 'Р»' => 'л', 'Рё' => 'и'
    ];
    
    // Apply character-by-character replacement
    $result = '';
    for ($i = 0; $i < mb_strlen($str, 'UTF-8'); $i++) {
        $char = mb_substr($str, $i, 1, 'UTF-8');
        if (isset($corruptionMap[$char])) {
            $result .= $corruptionMap[$char];
        } else {
            $result .= $char;
        }
    }
    
    return $result;
}

// Test with a known corrupted string
$corrupted = 'category_РџСЂРѕС„РёР»СЊ_РџСЂРѕСЃРјРѕС‚СЂ';
echo "Corrupted string: $corrupted\n";

// Apply fixes
$fixed = fixCyrillicCorruption($corrupted);
echo "After corruption fix: $fixed\n";

// Test if it matches expected result
$expected = 'category_Профиль_Просмотр';
if ($fixed === $expected) {
    echo "✅ Test passed! Encoding fixes are working correctly.\n";
} else {
    echo "❌ Test failed! Expected: $expected, Got: $fixed\n";
}

echo "\nTesting other corrupted strings...\n";

$testCases = [
    'category_РђРґРјРёРЅРєР°' => 'category_Админка',
    'category_РўР°Р±РµР»СЊ' => 'category_Табель',
    'category_РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°_РЈРїСЂР°РІР»РµРЅРёРµ' => 'category_УровеньДоступа_Управление',
    'category_РџСЂРѕС„РёР»СЊ_Р¤Рќ' => 'category_Профиль_ФН',
    'category_РџСЂРѕС„РёР»СЊ_Р¤Рќ_РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'category_Профиль_ФН_МобильнаяБригада'
];

foreach ($testCases as $corrupted => $expected) {
    $fixed = fixCyrillicCorruption($corrupted);
    
    if ($fixed === $expected) {
        echo "✅ Test passed for '$corrupted'\n";
    } else {
        echo "❌ Test failed for '$corrupted'. Expected: $expected, Got: $fixed\n";
    }
}

echo "\nAll tests completed.\n";