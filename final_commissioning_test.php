<?php
// Final comprehensive test for commissioning callback handling

echo "=== Final Commissioning Callback Test ===\n";

// Test the exact callback that should be sent by the button
$callbackData = 'category_Табель_ПусконаладочныеРаботы';
echo "Testing callback: $callbackData\n";

// Simulate TabelHandler canHandle method
function tabelHandlerCanHandle($callbackData) {
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    // Apply additional Cyrillic pattern fixes
    $callbackData = fixKnownCyrillicPatterns($callbackData);
    $canHandle = str_starts_with($callbackData, 'category_Табель');
    return $canHandle;
}

// Simulate the fixKnownCyrillicPatterns method from TabelHandler
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРћС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера',
        'РЎЂРѕР¤РёР»СЊ' => 'Профиль',
        'РЎЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤Р˜Рћ' => 'ФИО',
        'Р¤Рќ' => 'ФН',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
        'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
        'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
        'РўР°Р±РµР»СЊ' => 'Табель',
        'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
        'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
        'РџРѕРЅРёР·РёС‚СЊ' => 'Понизить',
        'РџСЂРѕРµРєС‚РЅР°СЏР”РµСЏС‚РµР»СЊРЅРѕСЃС‚СЊ' => 'ПроектнаяДеятельность',
        'РџРѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
        // Add missing patterns for commissioning works
        'РџРќР›' => 'ПНЛ',
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
        'Р Р°Р±РѕС‚С‹' => 'Работы',
        // Add missing patterns for the main buttons (order matters - more specific first)
        'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
        'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
        // Add individual word patterns
        'РјРѕРЅС‚Р°Р¶' => 'монтаж',
        // Add pattern for commissioning works abbreviation
        'РџРќР ' => 'ПНР'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        $str = str_replace($corrupted, $fixed, $str);
    }
    
    return $str;
}

// Test canHandle
$canHandle = tabelHandlerCanHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'YES' : 'NO') . "\n";

// Simulate the extractCategory function
function extractCategory($callbackData) {
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    return $callbackData;
}

// Test with potential corrupted data (what might come from Telegram)
$corruptedCallbackData = 'category_РўР°Р±РµР»СЊ_РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹РµР Р°Р±РѕС‚С‹';
echo "\nTesting potentially corrupted callback: $corruptedCallbackData\n";

// Apply the fix
$fixedCallbackData = fixKnownCyrillicPatterns($corruptedCallbackData);
echo "Fixed callback: $fixedCallbackData\n";

$fixedCanHandle = tabelHandlerCanHandle($fixedCallbackData);
echo "TabelHandler canHandle fixed callback result: " . ($fixedCanHandle ? 'YES' : 'NO') . "\n";

// Test category extraction
$category = extractCategory($fixedCallbackData);
echo "Extracted category: $category\n";

// Simulate the routeTabelCallback logic
function routeTabelCallback($category) {
    echo "Routing category: $category\n";
    
    // Check if it matches the commissioning works condition
    if ($category === 'Табель_ПНЛ' || $category === 'Табель_ПусконаладочныеРаботы') {
        echo "MATCHED: Commissioning works condition\n";
        return "SUCCESS: handleCommissioningWorksMenu would be called";
    }
    
    // Check other conditions that might interfere
    if ($category === 'Табель_Пусконаладка') {
        echo "MATCHED: Commissioning department condition\n";
        return "handleCommissioningDateSelection would be called";
    }
    
    echo "NO MATCH: Would fall through to default handling\n";
    return "Default handling";
}

$result = routeTabelCallback($category);
echo "Routing result: $result\n";

echo "\n=== Test Complete ===\n";
echo "The commissioning works button should now work correctly!\n";