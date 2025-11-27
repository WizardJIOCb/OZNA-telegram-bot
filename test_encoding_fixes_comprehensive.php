<?php

// Comprehensive test for encoding fixes

echo "=== Comprehensive Encoding Fix Test ===\n";

// Function to fix double-encoded UTF-8 strings
function fixDoubleEncodedUtf8($str) {
    // Try multiple approaches to fix double-encoded UTF-8
    
    // Method 1: Direct replacement of known patterns (most reliable)
    $replacements = [
        // Cyrillic letters that get corrupted
        'Рђ' => 'А', 'Р‘' => 'Б', 'Р’' => 'В', 'Р“' => 'Г', 'Р”' => 'Д', 'Р•' => 'Е', 'Р–' => 'Ж', 'Р—' => 'З',
        'Р˜' => 'И', 'Р™' => 'Й', 'Рљ' => 'К', 'Р›' => 'Л', 'Рњ' => 'М', 'Рќ' => 'Н', 'Рћ' => 'О', 'Рџ' => 'П',
        'Р ' => 'Р', 'РЎ' => 'С', 'Рў' => 'Т', 'РЈ' => 'У', 'Р¤' => 'Ф', 'РҐ' => 'Х', 'Р¦' => 'Ц', 'Р§' => 'Ч',
        'РЁ' => 'Ш', 'Р©' => 'Щ', 'РЄ' => 'Ъ', 'Р«' => 'Ы', 'Р¬' => 'Ь', 'Р­' => 'Э', 'Р®' => 'Ю', 'РЇ' => 'Я',
        'Р°' => 'а', 'Р±' => 'б', 'РІ' => 'в', 'Рі' => 'г', 'Рґ' => 'д', 'Рµ' => 'е', 'С‘' => 'ё', 'Р¶' => 'ж',
        'Р·' => 'з', 'Рё' => 'и', 'Р№' => 'й', 'Рє' => 'к', 'Р»' => 'л', 'Рј' => 'м', 'РЅ' => 'н', 'Рѕ' => 'о',
        'Рї' => 'п', 'СЂ' => 'р', 'СЃ' => 'с', 'С‚' => 'т', 'Сѓ' => 'у', 'С„' => 'ф', 'С…' => 'х', 'С†' => 'ц',
        'С‡' => 'ч', 'С€' => 'ш', 'С‰' => 'щ', 'СЉ' => 'ъ', 'С‹' => 'ы', 'СЊ' => 'ь', 'СЌ' => 'э', 'СЋ' => 'ю', 'СЏ' => 'я',
        
        // Common corrupted words
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
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
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎСѓРїРµСЂРІР°Р№Р·РµСЂ' => 'Супервайзер',
        'РћС‚С‡РµС‚С‹' => 'Отчеты',
        'Р’СЃРµРћС‚С‡РµС‚С‹' => 'ВсеОтчеты',
        'РђРєС‚РёРІРЅРѕСЃС‚СЊ' => 'Активность',
        'РќР°Р·Р°Рґ' => 'Назад',
        'Р“Р»Р°РІРЅРѕРµ' => 'Главное',
        'РџРѕР»СЊР·РѕРІР°С‚РµР»Рё' => 'Пользователи',
        'РџСЂРѕС„РёР»СЊ' => 'Профиль',
        'РџСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤РРћ' => 'ФИО',
        'Р¤Рќ' => 'ФН',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        $str = str_replace($corrupted, $fixed, $str);
    }
    
    return $str;
}

// Function to fix double-encoded UTF-8 using conversion methods
function fixDoubleEncodedUtf8Conversion($str) {
    // Method 1: Convert from UTF-8 to Windows-1251 and back to UTF-8
    $fixed1 = @mb_convert_encoding($str, 'Windows-1251', 'UTF-8');
    if ($fixed1 !== false) {
        $fixed1 = @mb_convert_encoding($fixed1, 'UTF-8', 'Windows-1251');
        // Check if this looks like a reasonable fix
        if (strlen($fixed1) < strlen($str)) {
            return $fixed1;
        }
    }
    
    // Method 2: Try to decode as UTF-8 and then re-encode
    $fixed2 = @mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    if ($fixed2 !== false && strlen($fixed2) <= strlen($str)) {
        return $fixed2;
    }
    
    // Method 3: Try ISO-8859-1 to UTF-8 conversion
    $fixed3 = @mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    if ($fixed3 !== false) {
        return $fixed3;
    }
    
    // If none of the above worked, return original
    return $str;
}

// Test cases
$testCases = [
    "РџСЂРѕС„РёР»СЊ" => "Профиль",
    "РўР°Р±РµР»СЊ" => "Табель",
    "РђРґРјРёРЅРєР°" => "Админка",
    "РЎСѓРїРµСЂРІР°Р№Р·РµСЂ" => "Супервайзер",
    "РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°" => "Пусконаладка",
    "РЁРµС„РњРѕРЅС‚Р°Р¶" => "ШефМонтаж",
    "РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°" => "МобильнаяБригада"
];

echo "Testing direct replacement method:\n";
foreach ($testCases as $corrupted => $expected) {
    $fixed = fixDoubleEncodedUtf8($corrupted);
    $status = ($fixed === $expected) ? "PASS" : "FAIL";
    echo "  $corrupted -> $fixed (expected: $expected) [$status]\n";
}

echo "\nTesting conversion method:\n";
foreach ($testCases as $corrupted => $expected) {
    $fixed = fixDoubleEncodedUtf8Conversion($corrupted);
    $status = ($fixed === $expected) ? "PASS" : "FAIL";
    echo "  $corrupted -> $fixed (expected: $expected) [$status]\n";
}

// Test with actual callback data patterns
echo "\nTesting with callback data patterns:\n";
$callbackPatterns = [
    "category_РџСЂРѕС„РёР»СЊ" => "category_Профиль",
    "category_РўР°Р±РµР»СЊ" => "category_Табель",
    "category_РђРґРјРёРЅРєР°_Р—Р°СЏРІРєРё" => "category_Админка_Заявки"
];

echo "Testing direct replacement method with callback patterns:\n";
foreach ($callbackPatterns as $corrupted => $expected) {
    $fixed = fixDoubleEncodedUtf8($corrupted);
    $status = ($fixed === $expected) ? "PASS" : "FAIL";
    echo "  $corrupted -> $fixed (expected: $expected) [$status]\n";
}

?>