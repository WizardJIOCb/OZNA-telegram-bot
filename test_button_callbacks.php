<?php
// Test script to check button callback processing
require_once __DIR__ . '/encoding_helper.php';

echo "=== Testing Button Callback Processing ===\n";

// Test the callback data that should be working
$callbacks = [
    'category_Табель_Мобильная_Бригада_Дата',
    'category_Табель_Шеф_монтаж',
    'category_Табель_Пусконаладка',
    'category_Табель_Текущая'
];

echo "Testing original callback data:\n";
foreach ($callbacks as $callback) {
    echo "Original: $callback\n";
    
    // Apply the fix
    $fixed = fixKnownCyrillicPatterns($callback);
    echo "Fixed: $fixed\n";
    
    if ($callback === $fixed) {
        echo "SUCCESS: No change needed\n";
    } else {
        echo "WARNING: String was modified!\n";
    }
    echo "\n";
}

// Test with potentially corrupted data
echo "Testing potentially corrupted callback data:\n";
$corruptedCallbacks = [
    'category_РўР°Р±РµР»СЊ_РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°_Р”Р°С‚Р°',
    'category_РўР°Р±РµР»СЊ_РЁРµС„_РјРѕРЅС‚Р°Р¶',
    'category_РўР°Р±РµР»СЊ_РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°',
    'category_РўР°Р±РµР»СЊ_РўРµРєСѓС‰Р°СЏ'
];

foreach ($corruptedCallbacks as $index => $callback) {
    echo "Corrupted: $callback\n";
    $fixed = fixKnownCyrillicPatterns($callback);
    echo "Fixed: $fixed\n";
    echo "Expected: " . $callbacks[$index] . "\n";
    
    if ($fixed === $callbacks[$index]) {
        echo "SUCCESS: Correctly fixed\n";
    } else {
        echo "FAILURE: Incorrect fix\n";
    }
    echo "\n";
}

echo "Test completed!\n";
?>