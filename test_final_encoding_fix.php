<?php

// Final test to verify encoding fixes are working

echo "=== Final Encoding Fix Verification ===\n";

// Include the comprehensive encoding fix function
function comprehensiveEncodingFix($str) {
    // First ensure UTF-8 encoding
    $str = mb_convert_encoding($str, 'UTF-8', 'auto');
    
    // Then apply direct replacement of known corrupted patterns (most reliable method)
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
    
    // Additional conversion methods as fallback
    // Method 1: Convert from UTF-8 to Windows-1251 and back to UTF-8
    $fixed1 = @mb_convert_encoding($str, 'Windows-1251', 'UTF-8');
    if ($fixed1 !== false) {
        $fixed1 = @mb_convert_encoding($fixed1, 'UTF-8', 'Windows-1251');
        // Check if this looks like a reasonable fix
        if (strlen($fixed1) < strlen($str)) {
            $str = $fixed1;
        }
    }
    
    // Method 2: Try to decode as UTF-8 and then re-encode
    $fixed2 = @mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    if ($fixed2 !== false && strlen($fixed2) <= strlen($str)) {
        $str = $fixed2;
    }
    
    // Method 3: Try ISO-8859-1 to UTF-8 conversion
    $fixed3 = @mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    if ($fixed3 !== false) {
        $str = $fixed3;
    }
    
    return $str;
}

// Test cases for common callback data patterns
$testCases = [
    "category_РџСЂРѕС„РёР»СЊ" => "category_Профиль",
    "category_РўР°Р±РµР»СЊ" => "category_Табель",
    "category_РђРґРјРёРЅРєР°_Р—Р°СЏРІРєРё" => "category_Админка_Заявки",
    "category_РЎСѓРїРµСЂРІР°Р№Р·РµСЂ" => "category_Супервайзер",
    "category_РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°" => "category_Пусконаладка",
    "category_РЁРµС„РњРѕРЅС‚Р°Р¶" => "category_ШефМонтаж",
    "category_РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°" => "category_МобильнаяБригада",
    "category_РџСЂРѕС„РёР»СЊ_Р¤Рќ_РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°" => "category_Профиль_ФН_МобильнаяБригада"
];

echo "Testing comprehensive encoding fix:\n";
$allPassed = true;
foreach ($testCases as $corrupted => $expected) {
    $fixed = comprehensiveEncodingFix($corrupted);
    $status = ($fixed === $expected) ? "PASS" : "FAIL";
    if ($status === "FAIL") {
        $allPassed = false;
    }
    echo "  $corrupted -> $fixed (expected: $expected) [$status]\n";
}

echo "\n" . ($allPassed ? "All tests PASSED!" : "Some tests FAILED!") . "\n";

// Test with actual handler canHandle methods
echo "\nTesting with handler-like patterns:\n";

function testCanHandle($callbackData) {
    $callbackData = comprehensiveEncodingFix($callbackData);
    return strpos($callbackData, 'category_Профиль') === 0;
}

$handlerTestCases = [
    "category_РџСЂРѕС„РёР»СЊ" => true,
    "category_РўР°Р±РµР»СЊ" => false,
    "category_РџСЂРѕС„РёР»СЊ_РџСЂРѕСЃРјРѕС‚СЂ" => true,
    "category_РђРґРјРёРЅРєР°" => false
];

foreach ($handlerTestCases as $testData => $expected) {
    $result = testCanHandle($testData);
    $status = ($result === $expected) ? "PASS" : "FAIL";
    if ($status === "FAIL") {
        $allPassed = false;
    }
    echo "  canHandle('$testData') -> " . ($result ? 'true' : 'false') . " (expected: " . ($expected ? 'true' : 'false') . ") [$status]\n";
}

echo "\nFinal result: " . ($allPassed ? "All tests PASSED!" : "Some tests FAILED!") . "\n";

?>