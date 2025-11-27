<?php
// Debug script to test callback routing for "Запросить доступ" button

echo "=== Debug Callback Routing for 'Запросить доступ' ===\n";

// Test the callback data that should be generated for the button
$callbackData = "category_Админка_ЗапросДоступ";
echo "Test callback data: " . $callbackData . "\n";

// Check if the callback data is valid UTF-8
echo "Is UTF-8 valid: " . (mb_check_encoding($callbackData, 'UTF-8') ? 'Yes' : 'No') . "\n";

// Test string operations
$category = str_replace('category_', '', $callbackData);
echo "Extracted category: " . $category . "\n";

// Test if it matches what AdminHandler expects
$isAdminRequest = ($category === 'Админка_ЗапросДоступ');
echo "Matches AdminHandler special case: " . ($isAdminRequest ? 'Yes' : 'No') . "\n";

// Test with potentially corrupted data
$corruptedData = "category_ÐÐ´Ð¼Ð¸Ð½ÐºÐ°_ÐÐ°Ð¿ÑÐ¾ÑÐÐ¾ÑÑÑÐ¿";
echo "\nCorrupted callback data: " . $corruptedData . "\n";
echo "Is corrupted data UTF-8 valid: " . (mb_check_encoding($corruptedData, 'UTF-8') ? 'Yes' : 'No') . "\n";

// Try to fix the corrupted data
$fixedData = mb_convert_encoding($corruptedData, 'UTF-8', 'UTF-8');
echo "After mb_convert_encoding fix: " . $fixedData . "\n";

// Test AdminHandler canHandle method logic
function testAdminHandlerCanHandle($callbackData) {
    // Apply comprehensive encoding fixes
    $callbackData = fixEncoding($callbackData);
    
    $startsWithCategoryAdmin = strpos($callbackData, 'category_Админка') === 0;
    $startsWithCategoryAdm = strpos($callbackData, 'category_Адм_') === 0;
    $equalsCategoryMain = $callbackData === 'category_Главное';
    $startsWithAdmReq = strpos($callbackData, 'adm_req_') === 0;
    
    // Add support for adm_req_* callbacks which don't have the category_ prefix
    $isAdmReqCallback = $startsWithAdmReq;
    
    // Exclude acc_lvl_* callbacks as they should be handled by AccessLevelHandler
    $isAccessLevelCallback = strpos($callbackData, 'acc_lvl_') === 0;
    
    // NOTE: We exclude 'category_Главное' from this handler to let MainMenuHandler handle it
    // This ensures consistent main menu handling for all users regardless of access level
    return !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
}

function fixEncoding($str) {
    // Handle null or empty strings
    if ($str === null || $str === '') {
        return $str;
    }
    
    // First ensure UTF-8 encoding
    $str = mb_convert_encoding($str, 'UTF-8', 'auto');
    
    // Special handling for the specific corruption pattern we're seeing
    // Replace sequences of question marks followed by corrupted characters
    $str = preg_replace('/\?{2,}[°±µ»_]+/u', '', $str);
    
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
        'РЎСѓРїРµРІР°Р№Р·РµСЂ' => 'Супервайзер',
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
        'Р’С‡РµСЂР°' => 'Вчера',
        'Р—Р°РїСЂРѕСЃР”РѕСЃС‚СѓРї' => 'ЗапросДоступ'
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

// Test the AdminHandler canHandle method
echo "\nTesting AdminHandler canHandle method:\n";
$result = testAdminHandlerCanHandle($callbackData);
echo "AdminHandler canHandle '{$callbackData}': " . ($result ? 'Yes' : 'No') . "\n";

$corruptedResult = testAdminHandlerCanHandle($corruptedData);
echo "AdminHandler canHandle corrupted data: " . ($corruptedResult ? 'Yes' : 'No') . "\n";

echo "\nDebug completed!\n";
?>