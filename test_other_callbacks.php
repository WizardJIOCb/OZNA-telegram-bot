<?php
// Test script to verify that other callbacks still work

require_once 'vendor/autoload.php';

// Simulate the CallbackHandlerFactory extractCategory method
function extractCategory(string $callbackData): string
{
    error_log("CallbackHandlerFactory: extractCategory called with callbackData: '{$callbackData}'");
    
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        error_log("CallbackHandlerFactory extracted category: '{$category}' from callback data: '{$callbackData}'");
        return $category;
    }
    
    // Handle short category format for electrical equipment
    if (strpos($callbackData, 'cat_') === 0) {
        // For short format, we need to let the handler extract the actual category
        // since the mapping is handler-specific
        error_log("CallbackHandlerFactory: short format callback detected: '{$callbackData}'");
        return ''; // Return empty string, let handler extract actual category
    }
    
    // Handle date selection callbacks
    if (strpos($callbackData, 'date_') === 0) {
        $category = substr($callbackData, strlen('date_'));
        error_log("CallbackHandlerFactory extracted date category: '{$category}' from callback data: '{$callbackData}'");
        return $category;
    }
    
    // Handle FIO selection callbacks
    if (strpos($callbackData, 'fio_') === 0) {
        $category = substr($callbackData, strlen('fio_'));
        error_log("CallbackHandlerFactory extracted FIO category: '{$category}' from callback data: '{$callbackData}'");
        return $category;
    }
    
    error_log("CallbackHandlerFactory: No category found in callback data: '{$callbackData}'");
    return '';
}

// Simulate the TabelHandler canHandle method
function tabelHandlerCanHandle(string $callbackData): bool
{
    // Add detailed logging
    error_log("TabelHandler: canHandle called with callbackData: '{$callbackData}'");
    
    // Special handling for electrical equipment callback with encoding issues
    // Check for both correctly encoded and corrupted versions
    if (strpos($callbackData, 'Электрооборудование') !== false || 
        strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
        strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
        strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
        error_log("TabelHandler: canHandle detected electrical equipment callback");
        return true;
    }
    
    // Check for both original and short callback formats
    $result = strpos($callbackData, 'category_Табель') === 0 || strpos($callbackData, 'cat_Таб') === 0;
    error_log("TabelHandler: canHandle result for '{$callbackData}': " . ($result ? 'true' : 'false'));
    return $result;
}

// Test other callbacks
echo "=== Testing Other Callbacks ===\n\n";

$otherCallbacks = [
    'category_Табель' => 'Табель',
    'category_Табель_Мобильная_Бригада' => 'Табель_Мобильная_Бригада',
    'category_Табель_Шеф_монтаж' => 'Табель_Шеф_монтаж',
    'category_Табель_Пусконаладка' => 'Табель_Пусконаладка',
    'category_Профиль' => 'Профиль',
    'category_Админка' => 'Админка'
];

$allTestsPassed = true;

foreach ($otherCallbacks as $callbackData => $expectedCategory) {
    echo "Testing callback: {$callbackData}\n";
    
    // Test canHandle
    $canHandle = tabelHandlerCanHandle($callbackData);
    echo "  canHandle: " . ($canHandle ? 'PASS' : 'FAIL') . "\n";
    if (!$canHandle) {
        $allTestsPassed = false;
    }
    
    // Test extractCategory (CallbackHandlerFactory)
    $extractedCategory = extractCategory($callbackData);
    echo "  extractCategory (CallbackHandlerFactory): '{$extractedCategory}'\n";
    
    // Check if the extracted category matches expected
    if ($extractedCategory === $expectedCategory) {
        echo "  Category extraction: PASS\n";
    } else {
        echo "  Category extraction: FAIL (expected: '{$expectedCategory}', got: '{$extractedCategory}')\n";
        $allTestsPassed = false;
    }
    
    echo "\n";
}

echo "=== Test Results ===\n";
if ($allTestsPassed) {
    echo "All tests PASSED! Other callbacks are still working correctly.\n";
} else {
    echo "Some tests FAILED! There are issues with other callback handling.\n";
}
?>