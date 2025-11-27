<?php
// Test script to check encoding issues with mobile brigade callback

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Encoding Issue Test ===\n\n";

// The callback data as it appears in the test (possibly double-encoded)
$callbackData = 'category_Табель_Мобильная_Бригада';

echo "Original callback data: '{$callbackData}'\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Hex: " . bin2hex($callbackData) . "\n\n";

// Check if it's valid UTF-8
if (mb_check_encoding($callbackData, 'UTF-8')) {
    echo "✅ Valid UTF-8 encoding\n";
} else {
    echo "❌ Invalid UTF-8 encoding\n";
}

// Try to decode as UTF-8
$decoded = mb_convert_encoding($callbackData, 'UTF-8', 'UTF-8');
echo "After UTF-8 conversion: '{$decoded}'\n";
echo "Length: " . strlen($decoded) . " bytes\n";
echo "Hex: " . bin2hex($decoded) . "\n\n";

// Try to decode as Windows-1251
$decodedWin = mb_convert_encoding($callbackData, 'UTF-8', 'Windows-1251');
echo "After Windows-1251 conversion: '{$decodedWin}'\n";
echo "Length: " . strlen($decodedWin) . " bytes\n";
echo "Hex: " . bin2hex($decodedWin) . "\n\n";

// Test what happens when we extract the category
if (strpos($callbackData, 'category_') === 0) {
    $category = substr($callbackData, strlen('category_'));
    echo "Extracted category: '{$category}'\n";
    echo "Category length: " . strlen($category) . " bytes\n";
    echo "Category hex: " . bin2hex($category) . "\n\n";
    
    // Check if this matches the expected mobile brigade category
    if ($category === 'Табель_Мобильная_Бригада') {
        echo "✅ Category matches expected value\n";
    } else {
        echo "❌ Category does not match expected value\n";
        echo "Expected: 'Табель_Мобильная_Бригада'\n";
        echo "Got: '{$category}'\n\n";
        
        // Check if it's a corrupted version
        if (strpos($category, 'РўР°Р±РµР»СЊ') !== false) {
            echo "⚠️ Detected corrupted Cyrillic encoding\n";
        }
    }
}

// Test the TabelHandler condition
$category = 'Табель_Мобильная_Бригада'; // This is what we expect after extraction
if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
    echo "✅ TabelHandler condition would match\n";
} else {
    echo "❌ TabelHandler condition would not match\n";
}

echo "\n=== Test Complete ===\n";

?>