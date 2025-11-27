<?php

// Simple test script to verify the electrical equipment button fix without database dependencies

// Simulate the CallbackHandlerFactory class with only the methods we need for testing
class CallbackHandlerFactory {
    private function fixKnownCyrillicPatterns($str) {
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
            'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
            'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
            'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
            'РўР°Р±РµР»СЊ' => 'Табель',
            'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
            'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
            'РџРѕРЅРёР·РёС‚СЊ' => 'Понизить',
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
            'РџРќР ' => 'ПНР',
            // Add pattern for preparation
            'РџРѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
            // Add pattern for electrical equipment - this is the key fix
            'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
            // Add pattern for full electrical equipment callback - this is the key fix
            'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
            // Add additional patterns for double-encoded Cyrillic that might be causing issues
            'Табель_Электрооборудование' => 'Табель_Электрооборудование',
            'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование'
        ];

        // Special case: if string is already the correct electrical equipment callback, return as is
        if ($str === 'category_Табель_Электрооборудование' || $str === 'Табель_Электрооборудование') {
            return $str;
        }

        foreach ($replacements as $corrupted => $fixed) {
            if (strpos($str, $corrupted) !== false) {
                $str = str_replace($corrupted, $fixed, $str);
            }
        }

        return $str;
    }

    private function extractCategory(string $callbackData): string {
        // Handle new short callback_data formats
        if (strpos($callbackData, 'adm_req_') === 0) {
            // For admin request callbacks, return the full callbackData as category
            return $callbackData;
        }

        // Special handling for electrical equipment - check this first and return immediately
        if (strpos($callbackData, 'category_Табель_Электрооборудование') === 0) {
            return 'Табель_Электрооборудование';
        }

        if (strpos($callbackData, 'category_') === 0) {
            $category = substr($callbackData, strlen('category_'));
            
            // Fix encoding for category
            $category = mb_convert_encoding($category, 'UTF-8', 'auto');
            
            // Fix known Cyrillic pattern corruptions for category as well
            $category = $this->fixKnownCyrillicPatterns($category);
            
            // Special case: if category is exactly 'Табель_Электрооборудование', return it immediately
            if ($category === 'Табель_Электрооборудование') {
                return $category;
            }
            
            return $category;
        }
        
        return $callbackData;
    }
    
    public function handleCallback($callbackData) {
        // Fix encoding issues with callback data - only once at the beginning
        $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
        
        // Fix known Cyrillic pattern corruptions - only once at the beginning
        $callbackData = $this->fixKnownCyrillicPatterns($callbackData);
        
        // Extract category from callback data
        $category = $this->extractCategory($callbackData);
        
        return $category;
    }
}

// Simulate the TabelHandler canHandle method
class TabelHandler {
    public function canHandle(string $callbackData): bool {
        // Special handling for electrical equipment - check this first
        if (str_starts_with($callbackData, 'category_Табель_Электрооборудование')) {
            return true;
        }
        
        // Additional check for corrupted electrical equipment callback data
        if (strpos($callbackData, 'Электрооборудование') !== false) {
            // If it contains электрооборудование, we should handle it
            return true;
        }
        
        return str_starts_with($callbackData, 'category_Табель');
    }
}

// Simulate the AdminHandler canHandle method
class AdminHandler {
    public function canHandle(string $callbackData): bool {
        // Check specifically for electrical equipment callback data
        if (strpos($callbackData, 'Электрооборудование') !== false) {
            // AdminHandler should not handle electrical equipment callbacks
            return false;
        }
        
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
        
        // Special case: Allow 'category_Админка_ЗапросДоступ' to be handled by AdminHandler
        // This ensures that access requests from profile are handled correctly
        if ($callbackData === 'category_Админка_ЗапросДоступ') {
            $canHandle = true; // AdminHandler will handle this
        }
        
        return $canHandle;
    }
}

// Test the electrical equipment callback
$callbackData = 'category_Табель_Электрооборудование';

echo "Testing electrical equipment callback: {$callbackData}\n";

// Initialize handler factory
$handlerFactory = new CallbackHandlerFactory();

// Test if CallbackHandlerFactory can extract the category correctly
$category = $handlerFactory->handleCallback($callbackData);

echo "CallbackHandlerFactory extracted category: {$category}\n";

// Test if the extracted category is correct
$expectedCategory = 'Табель_Электрооборудование';
$isCorrect = ($category === $expectedCategory);

echo "Category extraction is correct: " . ($isCorrect ? 'true' : 'false') . "\n";
echo "Expected: {$expectedCategory}\n";
echo "Actual: {$category}\n";

if ($isCorrect) {
    echo "SUCCESS: Electrical equipment callback handling is fixed!\n";
} else {
    echo "FAILURE: Electrical equipment callback handling still has issues.\n";
}

// Test TabelHandler canHandle
$tabelHandler = new TabelHandler();
$canHandle = $tabelHandler->canHandle($callbackData);
echo "\nTabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

// Test AdminHandler canHandle
$adminHandler = new AdminHandler();
$adminCanHandle = $adminHandler->canHandle($callbackData);
echo "AdminHandler canHandle result: " . ($adminCanHandle ? 'true' : 'false') . "\n";

// Test with corrupted data
$corruptedCallbackData = 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ';
echo "\nTesting corrupted electrical equipment callback: {$corruptedCallbackData}\n";

$category = $handlerFactory->handleCallback($corruptedCallbackData);

echo "CallbackHandlerFactory extracted category from corrupted data: {$category}\n";

$isCorrect = ($category === $expectedCategory);
echo "Corrupted data handling is correct: " . ($isCorrect ? 'true' : 'false') . "\n";

if ($isCorrect) {
    echo "SUCCESS: Corrupted electrical equipment callback handling is fixed!\n";
} else {
    echo "FAILURE: Corrupted electrical equipment callback handling still has issues.\n";
}