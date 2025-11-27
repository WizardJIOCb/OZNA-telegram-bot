<?php
// Simple test for the encoding fix function

// Copy the fixKnownCyrillicPatterns function here for testing
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера',
        'РЎРѕСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤РРћ' => 'ФИО',
        'Р¤РЊ' => 'ФН',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
        'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
        'РЎСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
        'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
        'РўР°Р±РµР»СЊ' => 'Табель',
        'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
        'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
        'РџРѕРЅРёР·РёССЊ' => 'Понизить',
        // Add missing patterns for commissioning works
        'РџРќР›' => 'ПНЛ',
        'РЎСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
        'Р Р°Р±Р±РѕС‚С‹' => 'Работы',
        // Add missing patterns for the main buttons (order matters - more specific first)
        'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
        'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
        // Add individual word patterns
        'РјРѕРЅС‚Р°Р¶' => 'монтаж',
        // Add pattern for commissioning works abbreviation
        'РџРќР ' => 'ПНР',
        // Add pattern for preparation
        'Р РѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
        // Add pattern for electrical equipment - this is the key fix
        'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
        // Add pattern for full electrical equipment callback - this is the key fix
        'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
        // Add additional patterns for double-encoded Cyrillic that might be causing issues
        'Табель_Электрооборудование' => 'Табель_Электрооборудование',
        'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование',
        // Handle mixed encoding cases
        'РўР°Р±РµР»СЊ_Электрооборудование' => 'Табель_Электрооборудование',
        // Handle the specific partially corrupted case
        'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРинРµ' => 'Электрооборудование',
        // Handle the exact double-encoded pattern we're seeing in the test
        'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
        // Add missing patterns for commissioning works - full words
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
        'Р Р°Р±РѕС‚С‹' => 'Работы',
        // Add missing patterns for commissioning works - partial words
        'РЎСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
        'Р Р°Р±Р±РѕС‚С‹' => 'Работы'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    return $str;
}

// Test with the exact callback that was causing issues
$callbackData = 'Табель_ПусконаладочныеРаботы';
echo "Testing callback data: {$callbackData}\n";

$result = fixKnownCyrillicPatterns($callbackData);
echo "After fixKnownCyrillicPatterns: {$result}\n";

// Check if the result matches what we expect
if ($result === 'Табель_ПусконаладочныеРаботы') {
    echo "SUCCESS: The encoding fix worked correctly!\n";
} else {
    echo "ISSUE: The encoding fix did not work as expected.\n";
}

// Test with a corrupted version that might occur
$corruptedData = 'Табель_РЎСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹РµР Р°Р±Р±РѕС‚С‹';
echo "\nTesting corrupted callback data: {$corruptedData}\n";

$result2 = fixKnownCyrillicPatterns($corruptedData);
echo "After fixKnownCyrillicPatterns: {$result2}\n";

if ($result2 === 'Табель_ПусконаладочныеРаботы') {
    echo "SUCCESS: The corrupted encoding fix worked correctly!\n";
} else {
    echo "ISSUE: The corrupted encoding fix did not work as expected.\n";
}

echo "\nTest completed.\n";
?>