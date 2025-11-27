<?php
// Comprehensive test for the main buttons fix
require_once __DIR__ . '/encoding_helper.php';

echo "=== Comprehensive Main Buttons Fix Test ===\n";

// Test the main buttons that were not working
$mainButtons = [
    'category_Табель_Мобильная_Бригада_Дата' => 'Мобильная бригада',
    'category_Табель_Шеф_монтаж' => 'Шеф-монтаж',
    'category_Табель_Пусконаладка' => 'Пусконаладка',
    'category_Табель_Текущая' => 'Текущая деятельность'
];

echo "Testing main button callback processing:\n";
foreach ($mainButtons as $callback => $buttonName) {
    echo "\nButton: $buttonName\n";
    echo "Original callback: $callback\n";
    
    // Apply the fix
    $fixed = fixKnownCyrillicPatterns($callback);
    echo "After fix: $fixed\n";
    
    if ($callback === $fixed) {
        echo "SUCCESS: No change needed (already correct)\n";
    } else {
        echo "WARNING: String was modified!\n";
    }
    
    // Test with potentially corrupted data
    // We'll simulate what might happen with encoding issues
    $corrupted = str_replace(
        ['Табель', 'Мобильная', 'Бригада', 'Дата', 'Шеф', 'монтаж', 'Пусконаладка', 'Текущая'],
        ['РўР°Р±РµР»СЊ', 'РњРѕР±РёР»СЊРЅР°СЏ', 'Р‘СЂРёРіР°РґР°', 'Р”Р°С‚Р°', 'РЁРµС„', 'РјРѕРЅС‚Р°Р¶', 'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°', 'РўРµРєСѓС‰Р°СЏ'],
        $callback
    );
    
    echo "Corrupted version: $corrupted\n";
    $fixedCorrupted = fixKnownCyrillicPatterns($corrupted);
    echo "Fixed corrupted: $fixedCorrupted\n";
    
    if ($fixedCorrupted === $callback) {
        echo "SUCCESS: Corrupted data correctly fixed!\n";
    } else {
        echo "FAILURE: Corrupted data not correctly fixed!\n";
        echo "Expected: $callback\n";
        echo "Got: $fixedCorrupted\n";
    }
}

echo "\n=== Test completed ===\n";
echo "All main buttons should now work correctly!\n";
?>