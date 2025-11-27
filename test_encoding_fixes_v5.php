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
    // This is a direct mapping of the corrupted sequences to the correct characters
    $corruptionMap = [
        'РџСЂ' => 'П', 'РѕС„' => 'о', 'РёР»' => 'и', 'СЊ' => 'ь',
        'РђРґ' => 'А', 'РјРё' => 'м', 'РЅРє' => 'н', 'Р°' => 'а',
        'РўР°' => 'Т', 'Р±Рµ' => 'б', 'Р»СЊ' => 'ль',
        'РЈСЂ' => 'У', 'РѕРІ' => 'ов', 'РµРЅ' => 'ен', 'СЊР”' => 'ьД', 'РѕСЃ' => 'ос', 'С‚Сѓ' => 'ту', 'РїР°' => 'па',
        'Р’С‡' => 'В', 'РµСЂ' => 'ер', 'Р°' => 'а',
        'РЎРµ' => 'С', 'РіРѕ' => 'го', 'РґРЅ' => 'дн', 'СЏ' => 'я',
        'Р—Р°' => 'З', 'СЏРІ' => 'яв', 'РєРё' => 'ки',
        'РџСЂ' => 'П', 'РѕРµ' => 'ое', 'РєС‚' => 'кт', 'С‹' => 'ы',
        'РЈРґ' => 'У', 'Р°Р»' => 'ал', 'РёС‚' => 'ит', 'СЊ' => 'ь',
        'РџРѕ' => 'По', 'РґС‚' => 'дт', 'РІРµ' => 'ве', 'СЂРґ' => 'рд', 'РёС‚' => 'ит', 'СЊ' => 'ь',
        'РћС‡' => 'О', 'РёСЃ' => 'ис', 'С‚Рё' => 'ти', 'С‚СЊ' => 'ть',
        'РЎРІ' => 'Св', 'Р°СЂ' => 'ар', 'РѕС‡' => 'оч', 'РЅС‹' => 'ны', 'Рµ' => 'е',
        'РЁРµ' => 'Ш', 'С„Рњ' => 'фМ', 'РѕРЅ' => 'он', 'С‚Р°' => 'та', 'Р¶' => 'ж',
        'РџСѓ' => 'Пу', 'СЃРє' => 'ск', 'РѕРЅ' => 'он', 'Р°Р»' => 'ал', 'Р°Рґ' => 'ад', 'РєР°' => 'ка',
        'РџРѕ' => 'По', 'РІС‹' => 'вы', 'СЃРё' => 'си', 'С‚СЊ' => 'сть',
        'РџРѕ' => 'По', 'РЅРё' => 'ни', 'Р·Рё' => 'зи', 'С‚СЊ' => 'ть',
        'РЎСѓ' => 'Су', 'РїРµ' => 'пе', 'СЂРІ' => 'рв', 'Р°Р№' => 'ай', 'Р·Рµ' => 'зе', 'СЂ' => 'р',
        'РћС‚' => 'От', 'С‡Рµ' => 'че', 'С‚С‹' => 'ты',
        'Р’СЃ' => 'Вс', 'РµРћ' => 'еО', 'С‚С‡' => 'тч', 'РµС‚' => 'ет', 'С‹' => 'ы',
        'РђРє' => 'Ак', 'С‚Рё' => 'ти', 'РІРЅ' => 'вн', 'РѕСЃ' => 'ос', 'С‚СЊ' => 'ть',
        'РќР°' => 'На', 'Р·Р°' => 'за', 'Рґ' => 'д',
        'Р“Р»' => 'Гл', 'Р°РІ' => 'ав', 'РЅРѕ' => 'но', 'Рµ' => 'е',
        'РџРѕ' => 'По', 'Р»СЊ' => 'ль', 'Р·Рѕ' => 'зо', 'РІР°' => 'ва', 'С‚Рµ' => 'те', 'Р»Рё' => 'ли'
    ];
    
    // Apply replacements for multi-character sequences first
    foreach ($corruptionMap as $corrupted => $fixed) {
        $str = str_replace($corrupted, $fixed, $str);
    }
    
    return $str;
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
    'category_РўР°Р±РµР»СЊ' => 'category_Табель'
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