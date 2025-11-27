<?php
// Test the transformations applied in CallbackHandlerFactory

echo "=== Test CallbackHandlerFactory Transformations ===\n";

// The callback data that would be sent by the "Запросить доступ" button
$callbackData = "category_Админка_ЗапросДоступ";
echo "Original callback data: " . $callbackData . "\n";

// Simulate what happens in CallbackHandlerFactory handleCallback method
echo "\n1. Fix encoding issues with callback data\n";
$callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
echo "   After mb_convert_encoding: " . $callbackData . "\n";

echo "\n2. Fix known Cyrillic pattern corruptions\n";
$callbackData = fixKnownCyrillicPatterns($callbackData);
echo "   After fixKnownCyrillicPatterns: " . $callbackData . "\n";

// Simulate extractCategory
echo "\n3. Extract category from callback data\n";
$category = $callbackData;
if (strpos($category, 'category_') === 0) {
    $category = substr($category, strlen('category_'));
}
echo "   Extracted category: " . $category . "\n";

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
        'РџРѕРЅРёР·РёС‚СЊ' => 'Понизить'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        $str = str_replace($corrupted, $fixed, $str);
    }
    
    return $str;
}

echo "\nTest completed!\n";
?>