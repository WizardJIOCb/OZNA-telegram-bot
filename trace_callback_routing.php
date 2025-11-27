<?php
// Trace the exact callback routing flow

echo "=== Trace Callback Routing Flow ===\n";

// The callback data that would be sent by the "Запросить доступ" button
$callbackData = "category_Админка_ЗапросДоступ";
echo "1. Original callback data: " . $callbackData . "\n";

// Simulate what happens in CallbackHandlerFactory handleCallback method
echo "2. Fix encoding issues with callback data\n";
$callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
echo "   After mb_convert_encoding: " . $callbackData . "\n";

echo "3. Fix known Cyrillic pattern corruptions\n";
$callbackData = fixKnownCyrillicPatterns($callbackData);
echo "   After fixKnownCyrillicPatterns: " . $callbackData . "\n";

// Simulate extractCategory
echo "4. Extract category from callback data\n";
$category = $callbackData;
if (strpos($category, 'category_') === 0) {
    $category = substr($category, strlen('category_'));
}
echo "   Extracted category: " . $category . "\n";

// Test each handler in priority order
echo "5. Test handlers in priority order\n";

// MainMenuHandler (priority 50)
echo "   Testing MainMenuHandler (priority 50)\n";
$mainMenuCanHandle = testMainMenuHandlerCanHandle($callbackData);
echo "     canHandle result: " . ($mainMenuCanHandle ? 'true' : 'false') . "\n";

// AdminHandler (priority 30)
echo "   Testing AdminHandler (priority 30)\n";
$adminCanHandle = testAdminHandlerCanHandle($callbackData);
echo "     canHandle result: " . ($adminCanHandle ? 'true' : 'false') . "\n";

// AccessLevelHandler (priority 25)
echo "   Testing AccessLevelHandler (priority 25)\n";
$accessLevelCanHandle = testAccessLevelHandlerCanHandle($callbackData);
echo "     canHandle result: " . ($accessLevelCanHandle ? 'true' : 'false') . "\n";

// ProfileHandler (priority 15)
echo "   Testing ProfileHandler (priority 15)\n";
$profileCanHandle = testProfileHandlerCanHandle($callbackData);
echo "     canHandle result: " . ($profileCanHandle ? 'true' : 'false') . "\n";

// Determine which handler would be selected
echo "6. Determine which handler would be selected\n";
if ($mainMenuCanHandle) {
    echo "   Selected handler: MainMenuHandler\n";
} elseif ($adminCanHandle) {
    echo "   Selected handler: AdminHandler\n";
    echo "   Special case check: " . ($category === 'Админка_ЗапросДоступ' ? 'true' : 'false') . "\n";
} elseif ($accessLevelCanHandle) {
    echo "   Selected handler: AccessLevelHandler\n";
} elseif ($profileCanHandle) {
    echo "   Selected handler: ProfileHandler\n";
} else {
    echo "   No handler found - would return default response\n";
}

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

function testMainMenuHandlerCanHandle($callbackData) {
    // Apply comprehensive encoding fixes
    $callbackData = fixEncoding($callbackData);
    
    // Handle main menu callback
    return $callbackData === 'category_Главное';
}

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

function testAccessLevelHandlerCanHandle($callbackData) {
    // Fix encoding issues with callback data
    $callbackData = fixEncoding($callbackData);
    
    // Check for category-based access level callbacks
    $isCategoryAccessLevel = strpos($callbackData, 'category_УровеньДоступа') === 0;
    
    // Also check for corrupted UTF-8 version and fix if needed
    if (!$isCategoryAccessLevel) {
        $isCategoryAccessLevel = strpos($callbackData, 'category_РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°') === 0;
        // If we found the corrupted version, we need to fix it
        if ($isCategoryAccessLevel) {
            $callbackData = fixKnownCyrillicPatterns($callbackData);
            $isCategoryAccessLevel = strpos($callbackData, 'category_УровеньДоступа') === 0;
        }
    }
    
    // Check for new access level management callbacks
    $isAccessLevelManagement = (
        $callbackData === 'acc_lvl_users' ||
        $callbackData === 'acc_lvl_manage_users' ||
        strpos($callbackData, 'acc_lvl_user_details_') === 0 ||
        strpos($callbackData, 'acc_lvl_set_level_') === 0
    );
    
    return $isCategoryAccessLevel || $isAccessLevelManagement;
}

function testProfileHandlerCanHandle($callbackData) {
    // Apply encoding fixes
    $callbackData = fixEncoding($callbackData);
    
    // Check if it starts with category_Профиль
    return strpos($callbackData, 'category_Профиль') === 0;
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

echo "\nTrace completed!\n";
?>