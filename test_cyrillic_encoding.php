<?php
// Test Cyrillic encoding handling

echo "=== Cyrillic Encoding Test ===\n";

// Test normal callback data
$normalCallback = 'category_Табель_ПусконаладочныеРаботы';
echo "Normal callback: $normalCallback\n";
echo "Starts with 'category_Табель': " . (str_starts_with($normalCallback, 'category_Табель') ? 'YES' : 'NO') . "\n";

// Test what happens with corrupted Cyrillic (simulating what might come from Telegram)
// This simulates the corrupted version that would come through
$corruptedCallback = 'category_РўР°Р±РµР»СЊ_РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹РµР Р°Р±РѕС‚С‹';
echo "\nCorrupted callback: $corruptedCallback\n";

// Apply the fix function to see if it gets corrected
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРћС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера',
        'РџСЂРѕС„РёР»СЊ' => 'Профиль',
        'РџСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤РРћ' => 'ФИО',
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

$fixedCallback = fixKnownCyrillicPatterns($corruptedCallback);
echo "Fixed callback: $fixedCallback\n";
echo "Starts with 'category_Табель': " . (str_starts_with($fixedCallback, 'category_Табель') ? 'YES' : 'NO') . "\n";

// Test extracting the category
$category = substr($fixedCallback, strlen('category_'));
echo "Extracted category: $category\n";

// Test if it matches our expected category
$expectedCategory = 'Табель_ПусконаладочныеРаботы';
echo "Matches expected category: " . ($category === $expectedCategory ? 'YES' : 'NO') . "\n";

echo "\n=== Test Complete ===\n";