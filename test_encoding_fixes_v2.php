<?php

// Test script to verify encoding fixes

echo "Testing encoding fixes...\n";

// Test string with corrupted Cyrillic characters
$corrupted = 'category_РџСЂРѕС„РёР»СЊ_РџСЂРѕСЃРјРѕС‚СЂ';
echo "Corrupted string: $corrupted\n";
echo "Corrupted string length: " . strlen($corrupted) . "\n";
echo "Corrupted string mb_length: " . mb_strlen($corrupted, 'UTF-8') . "\n";

// Function to fix double-encoded UTF-8 strings
function fixDoubleEncodedUtf8($str) {
    // Check if string is already valid UTF-8
    if (mb_check_encoding($str, 'UTF-8')) {
        // Check if it looks like double-encoded UTF-8
        // Double-encoded UTF-8 strings often have a lot of Cyrillic characters that are actually 
        // Latin characters representing UTF-8 bytes
        $looksDoubleEncoded = true;
        for ($i = 0; $i < min(strlen($str), 10); $i++) {
            $char = $str[$i];
            // If we find non-ASCII characters that are mostly in the Cyrillic range
            // but the string is valid UTF-8, it might be double-encoded
            if (ord($char) > 127) {
                // Check if this looks like a double-encoded character
                // In double-encoded UTF-8, we often see sequences like РџСЂ
                // which represent the UTF-8 bytes for Cyrillic characters
                if (strpos('РЎРўРЈРљР›РњРќРћРџР РЎРўРЈРљР›РњРќРћРџ', $char) === false) {
                    $looksDoubleEncoded = false;
                    break;
                }
            }
        }
        
        if ($looksDoubleEncoded) {
            // Try to decode as Windows-1251 to UTF-8
            $attempt1 = @mb_convert_encoding($str, 'UTF-8', 'Windows-1251');
            if ($attempt1 !== false && $attempt1 !== $str) {
                return $attempt1;
            }
        }
    }
    
    // Fallback: try to decode as UTF-8 and then re-encode
    $fixed = @mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    return $fixed ?: $str;
}

// Function to fix known Cyrillic pattern corruptions
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера',
        'РџСЂРѕС„РёР»СЊ' => 'Профиль',
        'РџСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤РРћ' => 'ФИО',
        'Р¤Рќ' => 'ФН',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
        'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
        'РўР°Р±РµР»СЊ' => 'Табель',
        'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
        'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
        'РџРѕРЅРёР·РёС‚СЊ' => 'Понизить',
        'РђРґРјРёРЅРєР°' => 'Админка',
        'Р—Р°СЏРІРєРё' => 'Заявки',
        'РџСЂРѕРµРєС‚С‹' => 'Проекты',
        'РЈРґР°Р»РёС‚СЊ' => 'Удалить',
        'РџРѕРґС‚РІРµСЂРґРёС‚СЊ' => 'Подтвердить',
        'РћС‡РёСЃС‚РёС‚СЊ' => 'Очистить',
        'РЈСЂРѕРІРµРЅСЊ' => 'Уровень',
        'РЎСѓРїРµСЂРІР°Р№Р·РµСЂ' => 'Супервайзер',
        'РћС‚С‡РµС‚С‹' => 'Отчеты',
        'Р’СЃРµРћС‚С‡РµС‚С‹' => 'ВсеОтчеты',
        'РђРєС‚РёРІРЅРѕСЃС‚СЊ' => 'Активность',
        'РќР°Р·Р°Рґ' => 'Назад',
        'Р“Р»Р°РІРЅРѕРµ' => 'Главное',
        'РџРѕР»СЊР·РѕРІР°С‚РµР»Рё' => 'Пользователи'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        $str = str_replace($corrupted, $fixed, $str);
    }
    
    return $str;
}

// Apply fixes
$fixed = fixDoubleEncodedUtf8($corrupted);
echo "After double encoding fix: $fixed\n";

$fixed = fixKnownCyrillicPatterns($fixed);
echo "After pattern fix: $fixed\n";

// Test if it matches expected result
$expected = 'category_Профиль_Просмотр';
if ($fixed === $expected) {
    echo "✅ Test passed! Encoding fixes are working correctly.\n";
} else {
    echo "❌ Test failed! Expected: $expected, Got: $fixed\n";
}

echo "\nTesting other corrupted strings...\n";

$testStrings = [
    'category_РђРґРјРёРЅРєР°' => 'category_Админка',
    'category_РўР°Р±РµР»СЊ' => 'category_Табель',
    'category_РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°_РЈРїСЂР°РІР»РµРЅРёРµ' => 'category_УровеньДоступа_Управление'
];

foreach ($testStrings as $corrupted => $expected) {
    $fixed = fixDoubleEncodedUtf8($corrupted);
    $fixed = fixKnownCyrillicPatterns($fixed);
    
    if ($fixed === $expected) {
        echo "✅ Test passed for '$corrupted'\n";
    } else {
        echo "❌ Test failed for '$corrupted'. Expected: $expected, Got: $fixed\n";
    }
}

echo "\nAll tests completed.\n";