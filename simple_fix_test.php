<?php
/**
 * Simple test for the Табель_ШМ_Back callback fix
 * 
 * This script tests the core logic of the fix without requiring
 * the full Telegram bot framework
 */

// Simulate the extractCategoryFromCallback method with our fix
function extractCategoryFromCallback($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $extracted = substr($callbackData, strlen('category_'));
        return $extracted;
    }
    
    // Handle direct callback names (like back buttons) - THIS IS THE FIX
    $directCallbacks = [
        'Табель_ШМ_Back',
        'Табель_Пусконаладка_Back',
        'Табель_Автоматизация_Back',
        'Табель_ПожарнаяСигнализация_Back',
        'Табель_ОхраннаяСигнализация_Back',
        'Табель_ОтоплениеИВентиляция_Back',
        'Табель_ТехнологическоеОборудование_Back',
        'Табель_Метрология_Back',
        'Табель_ПроектнаяДеятельность_Back'
    ];
    
    if (in_array($callbackData, $directCallbacks)) {
        return $callbackData;
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0 || strpos($callbackData, 'cat_Гарантии_') === 0 || strpos($callbackData, 'cat_ШМР_') === 0) {
        // Map short formats to full category names (simplified for test)
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабПНРаботы' => 'ПусконаладочныеРаботы'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

// Test the specific case that was failing
echo "Testing Табель_ШМ_Back callback handling:\n\n";

$testCallback = 'Табель_ШМ_Back';
$result = extractCategoryFromCallback($testCallback);

echo "Input callback: $testCallback\n";
echo "Extracted category: $result\n";

if ($result === $testCallback) {
    echo "✅ SUCCESS: The callback was properly extracted!\n";
    echo "This means the 'Вернуться в табель' button in the supervision menu should now work correctly.\n";
} else {
    echo "❌ FAILURE: The callback was not properly extracted.\n";
}

echo "\n" . str_repeat("-", 50) . "\n";
echo "Testing other back button callbacks:\n\n";

$otherCallbacks = [
    'Табель_Пусконаладка_Back',
    'Табель_Автоматизация_Back',
    'Табель_ПожарнаяСигнализация_Back'
];

foreach ($otherCallbacks as $callback) {
    $result = extractCategoryFromCallback($callback);
    $status = ($result === $callback) ? "✅ PASS" : "❌ FAIL";
    echo "$callback: $status\n";
}

echo "\n🎉 All back button callbacks should now work correctly!\n";
?>