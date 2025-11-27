<?php
/**
 * Encoding debug test to check for issues with Cyrillic text in callback_data
 */

echo "=== Encoding Debug Test ===\n\n";

// Test the actual callback_data that should be sent by the button
$originalCallbackData = 'category_Админка_Заявки';
echo "Original callback_data: {$originalCallbackData}\n";
echo "Length: " . strlen($originalCallbackData) . " bytes\n";
echo "UTF-8 valid: " . (mb_check_encoding($originalCallbackData, 'UTF-8') ? 'YES' : 'NO') . "\n\n";

// Test encoding conversion
$utf8CallbackData = mb_convert_encoding($originalCallbackData, 'UTF-8', 'auto');
echo "After UTF-8 conversion: {$utf8CallbackData}\n";
echo "Length: " . strlen($utf8CallbackData) . " bytes\n";
echo "UTF-8 valid: " . (mb_check_encoding($utf8CallbackData, 'UTF-8') ? 'YES' : 'NO') . "\n\n";

// Test string functions
echo "str_starts_with test:\n";
echo "  str_starts_with('{$utf8CallbackData}', 'category_Админка'): " . (str_starts_with($utf8CallbackData, 'category_Админка') ? 'YES' : 'NO') . "\n";
echo "  str_starts_with('{$utf8CallbackData}', 'category_Адм_'): " . (str_starts_with($utf8CallbackData, 'category_Адм_') ? 'YES' : 'NO') . "\n";
echo "  str_starts_with('{$utf8CallbackData}', 'adm_req_'): " . (str_starts_with($utf8CallbackData, 'adm_req_') ? 'YES' : 'NO') . "\n\n";

// Test extracting category
function extractCategory($callbackData) {
    echo "Extracting category from: '{$callbackData}'\n";
    
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    
    if (strpos($callbackData, 'adm_req_') === 0) {
        // For admin request callbacks, return the full callbackData as category
        $category = $callbackData;
        echo "  Extracted category (adm_req): '{$category}'\n";
        return $category;
    }
    
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        // Fix encoding for category
        $category = mb_convert_encoding($category, 'UTF-8', 'auto');
        echo "  Extracted category: '{$category}'\n";
        return $category;
    }
    
    echo "  No recognized prefix found\n";
    return $callbackData;
}

$category = extractCategory($utf8CallbackData);
echo "Final category: '{$category}'\n\n";

// Test the canHandle method logic with the extracted category
function canHandle($callbackData) {
    echo "Testing canHandle with callbackData: '{$callbackData}'\n";
    
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    
    $startsWithCategoryAdmin = str_starts_with($callbackData, 'category_Админка');
    $startsWithCategoryAdm = str_starts_with($callbackData, 'category_Адм_');
    $equalsCategoryMain = $callbackData === 'category_Главное';
    $startsWithAdmReq = str_starts_with($callbackData, 'adm_req_');
    
    // Add support for adm_req_* callbacks which don't have the category_ prefix
    $isAdmReqCallback = $startsWithAdmReq;
    
    // Exclude acc_lvl_* callbacks as they should be handled by AccessLevelHandler
    $isAccessLevelCallback = str_starts_with($callbackData, 'acc_lvl_');
    
    // NOTE: We exclude 'category_Главное' from this handler to let MainMenuHandler handle it
    // This ensures consistent main menu handling for all users regardless of access level
    $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
    
    echo "  startsWithCategoryAdmin: " . ($startsWithCategoryAdmin ? 'true' : 'false') . "\n";
    echo "  startsWithCategoryAdm: " . ($startsWithCategoryAdm ? 'true' : 'false') . "\n";
    echo "  equalsCategoryMain: " . ($equalsCategoryMain ? 'true' : 'false') . "\n";
    echo "  startsWithAdmReq: " . ($startsWithAdmReq ? 'true' : 'false') . "\n";
    echo "  isAdmReqCallback: " . ($isAdmReqCallback ? 'true' : 'false') . "\n";
    echo "  isAccessLevelCallback: " . ($isAccessLevelCallback ? 'true' : 'false') . "\n";
    echo "  Result: " . ($canHandle ? 'YES - can handle' : 'NO - cannot handle') . "\n\n";
    
    return $canHandle;
}

$canHandleResult = canHandle($utf8CallbackData);
echo "canHandle result: " . ($canHandleResult ? 'YES' : 'NO') . "\n\n";

// Test with potential encoding issues
echo "Testing with potential encoding issues...\n";

// Simulate corrupted Cyrillic text
$corruptedCallbackData = 'category_РђРґРјРёРЅРєР°_Р—Р°СЏРІРєРё';
echo "Corrupted callback_data: {$corruptedCallbackData}\n";

// Try to fix it
$fixedCallbackData = mb_convert_encoding($corruptedCallbackData, 'UTF-8', 'auto');
echo "After auto conversion: {$fixedCallbackData}\n";

// Manual replacement of corrupted patterns
$replacements = [
    'Рђ' => 'А', 'Р‘' => 'Б', 'Р’' => 'В', 'Р“' => 'Г', 'Р”' => 'Д', 'Р•' => 'Е', 'Р–' => 'Ж', 'Р—' => 'З',
    'Р˜' => 'И', 'Р™' => 'Й', 'Рљ' => 'К', 'Р›' => 'Л', 'Рњ' => 'М', 'Рќ' => 'Н', 'Рћ' => 'О', 'Рџ' => 'П',
    'Р ' => 'Р', 'РЎ' => 'С', 'Рў' => 'Т', 'РЈ' => 'У', 'Р¤' => 'Ф', 'РҐ' => 'Х', 'Р¦' => 'Ц', 'Р§' => 'Ч',
    'РЁ' => 'Ш', 'Р©' => 'Щ', 'РЄ' => 'Ъ', 'Р«' => 'Ы', 'Р¬' => 'Ь', 'Р­' => 'Э', 'Р®' => 'Ю', 'РЇ' => 'Я',
    'Р°' => 'а', 'Р±' => 'б', 'РІ' => 'в', 'Рі' => 'г', 'Рґ' => 'д', 'Рµ' => 'е', 'С‘' => 'ё', 'Р¶' => 'ж',
    'Р·' => 'з', 'Рё' => 'и', 'Р№' => 'й', 'Рє' => 'к', 'Р»' => 'л', 'Рј' => 'м', 'РЅ' => 'н', 'Рѕ' => 'о',
    'Рї' => 'п', 'СЂ' => 'р', 'СЃ' => 'с', 'С‚' => 'т', 'Сѓ' => 'у', 'С„' => 'ф', 'С…' => 'х', 'С†' => 'ц',
    'С‡' => 'ч', 'С€' => 'ш', 'С‰' => 'щ', 'СЉ' => 'ъ', 'С‹' => 'ы', 'СЊ' => 'ь', 'СЌ' => 'э', 'СЋ' => 'ю', 'СЏ' => 'я',
    'РђРґРјРёРЅРєР°' => 'Админка',
    'Р—Р°СЏРІРєРё' => 'Заявки'
];

foreach ($replacements as $corrupted => $fixed) {
    $fixedCallbackData = str_replace($corrupted, $fixed, $fixedCallbackData);
}
echo "After manual replacement: {$fixedCallbackData}\n";

$canHandleCorrupted = canHandle($corruptedCallbackData);
echo "canHandle result for corrupted data: " . ($canHandleCorrupted ? 'YES' : 'NO') . "\n\n";

echo "=== Test Complete ===\n";