<?php
// Debug script to test the exact callback routing for "Запросить доступ" button

echo "=== Debug Access Request Routing ===\n";

// The callback data that would be sent by the "Запросить доступ" button
$callbackData = "category_Админка_ЗапросДоступ";
echo "Original callback data: " . $callbackData . "\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Multibyte length: " . mb_strlen($callbackData, 'UTF-8') . " characters\n";
echo "Is valid UTF-8: " . (mb_check_encoding($callbackData, 'UTF-8') ? 'Yes' : 'No') . "\n";

// Test what happens in the CallbackHandlerFactory extractCategory method
$category = $callbackData;
if (strpos($category, 'category_') === 0) {
    $category = substr($category, strlen('category_'));
}
echo "Extracted category: " . $category . "\n";

// Test AdminHandler canHandle method logic
function testAdminHandlerCanHandle($callbackData) {
    // Apply comprehensive encoding fixes (simplified)
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
    // Simplified version of the comprehensive encoding fix
    // First check if the string is already valid UTF-8
    if (mb_check_encoding($str, 'UTF-8')) {
        // If it's already valid UTF-8, check if it contains any corrupted patterns
        // Only apply fixes if we detect corruption
        $hasCorruption = false;
        $corruptedPatterns = [
            'Ð', 'Ð‘', 'Ð’', 'Ð“', 'Ð”', 'Ð•', 'Ð–', 'Ð—',
            'Ð˜', 'Ð™', 'Ðš', 'Ð›', 'Ðœ', 'Ð', 'Ðž', 'ÐŸ',
            'Ð ', 'Ð¡', 'Ð¢', 'Ð£', 'Ð¤', 'Ð¥', 'Ð¦', 'Ð§',
            'Ð¨', 'Ð©', 'Ðª', 'Ð«', 'Ð¬', 'Ð­', 'Ð®', 'Ð¯',
            'Ð°', 'Ð±', 'Ð²', 'Ð³', 'Ð´', 'Ðµ', 'Ñ‘', 'Ð¶',
            'Ð·', 'Ð¸', 'Ð¹', 'Ðº', 'Ð»', 'Ð¼', 'Ð½', 'Ð¾',
            'Ð¿', 'Ñ€', 'Ñ', 'Ñ‚', 'Ñƒ', 'Ñ„', 'Ñ…', 'Ñ†',
            'Ñ‡', 'Ñˆ', 'Ñ‰', 'ÑŠ', 'Ñ‹', 'ÑŒ', 'Ñ', 'ÑŽ', 'Ñ'
        ];
        
        foreach ($corruptedPatterns as $pattern) {
            if (strpos($str, $pattern) !== false) {
                $hasCorruption = true;
                break;
            }
        }
        
        // If no corruption detected, return as is
        if (!$hasCorruption) {
            return $str;
        }
    }
    
    // If we get here, we need to fix encoding issues
    // First ensure UTF-8 encoding
    $str = mb_convert_encoding($str, 'UTF-8', 'auto');
    
    // Then apply direct replacement of known corrupted patterns
    $replacements = [
        'Рђ' => 'А', 'Р‘' => 'Б', 'Р’' => 'В', 'Р“' => 'Г', 'Р”' => 'Д', 'Р•' => 'Е', 'Р–' => 'Ж', 'Р—' => 'З',
        'Р˜' => 'И', 'Р™' => 'Й', 'Рљ' => 'К', 'Р›' => 'Л', 'Рњ' => 'М', 'Рќ' => 'Н', 'Рћ' => 'О', 'Рџ' => 'П',
        'Р ' => 'Р', 'РЎ' => 'С', 'Рў' => 'Т', 'РЈ' => 'У', 'Р¤' => 'Ф', 'РҐ' => 'Х', 'Р¦' => 'Ц', 'Р§' => 'Ч',
        'РЁ' => 'Ш', 'Р©' => 'Щ', 'РЄ' => 'Ъ', 'Р«' => 'Ы', 'Р¬' => 'Ь', 'Р­' => 'Э', 'Р®' => 'Ю', 'РЇ' => 'Я',
        'Р°' => 'а', 'Р±' => 'б', 'РІ' => 'в', 'Рі' => 'г', 'Рґ' => 'д', 'Рµ' => 'е', 'С‘' => 'ё', 'Р¶' => 'ж',
        'Р·' => 'з', 'Рё' => 'и', 'Р№' => 'й', 'Рє' => 'к', 'Р»' => 'л', 'Рј' => 'м', 'РЅ' => 'н', 'Рѕ' => 'о',
        'Рї' => 'п', 'СЂ' => 'р', 'СЃ' => 'с', 'С‚' => 'т', 'Сѓ' => 'у', 'С„' => 'ф', 'С…' => 'х', 'С†' => 'ц',
        'С‡' => 'ч', 'С€' => 'ш', 'С‰' => 'щ', 'СЉ' => 'ъ', 'С‹' => 'ы', 'СЊ' => 'ь', 'СЌ' => 'э', 'СЋ' => 'ю', 'СЏ' => 'я',
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
    
    return $str;
}

// Test AdminHandler canHandle
$adminCanHandle = testAdminHandlerCanHandle($callbackData);
echo "AdminHandler canHandle result: " . ($adminCanHandle ? 'true' : 'false') . "\n";

// Test what happens in AdminHandler handle method
$isSpecialCase = ($category === 'Админка_ЗапросДоступ');
echo "Special case match (Админка_ЗапросДоступ): " . ($isSpecialCase ? 'true' : 'false') . "\n";

// Test ProfileHandler canHandle to make sure it doesn't interfere
function testProfileHandlerCanHandle($callbackData) {
    // Apply encoding fixes
    $callbackData = fixEncoding($callbackData);
    
    // Check if it starts with category_Профиль
    return strpos($callbackData, 'category_Профиль') === 0;
}

$profileCanHandle = testProfileHandlerCanHandle($callbackData);
echo "ProfileHandler canHandle result: " . ($profileCanHandle ? 'true' : 'false') . "\n";

// Test MainMenuHandler canHandle to make sure it doesn't interfere
function testMainMenuHandlerCanHandle($callbackData) {
    // Apply comprehensive encoding fixes
    $callbackData = fixEncoding($callbackData);
    
    // Handle main menu callback
    return $callbackData === 'category_Главное';
}

$mainMenuCanHandle = testMainMenuHandlerCanHandle($callbackData);
echo "MainMenuHandler canHandle result: " . ($mainMenuCanHandle ? 'true' : 'false') . "\n";

echo "\n=== Handler Priority Check ===\n";
echo "AdminHandler priority: 30\n";
echo "ProfileHandler priority: 15\n";
echo "MainMenuHandler priority: 50\n";
echo "AccessLevelHandler priority: 25\n";

echo "\nDebug completed!\n";
?>