<?php
// Detailed test to debug the extractCategoryFromCallback method

// Simulate the exact callback data from the logs
$callbackData = 'Табель_ШМ_Back';

echo "Testing extractCategoryFromCallback with callbackData: '$callbackData'\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Multibyte length: " . mb_strlen($callbackData, 'UTF-8') . " characters\n";

// Test each condition in the method
echo "\n--- Testing conditions ---\n";

// Test 1: category_ prefix
$startsWithCategory = strpos($callbackData, 'category_') === 0;
echo "Starts with 'category_': " . ($startsWithCategory ? 'true' : 'false') . "\n";

// Test 2: direct callbacks
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

$inDirectCallbacks = in_array($callbackData, $directCallbacks);
echo "In directCallbacks array: " . ($inDirectCallbacks ? 'true' : 'false') . "\n";

// Test 3: cat_ prefixes
$startsWithCatTab = strpos($callbackData, 'cat_Таб') === 0;
$startsWithCatGarantii = strpos($callbackData, 'cat_Гарантии_') === 0;
$startsWithCatSHMR = strpos($callbackData, 'cat_ШМР_') === 0;
echo "Starts with 'cat_Таб': " . ($startsWithCatTab ? 'true' : 'false') . "\n";
echo "Starts with 'cat_Гарантии_': " . ($startsWithCatGarantii ? 'true' : 'false') . "\n";
echo "Starts with 'cat_ШМР_': " . ($startsWithCatSHMR ? 'true' : 'false') . "\n";

// Simulate the actual method logic
echo "\n--- Simulating method logic ---\n";

// Handle original category format
if (strpos($callbackData, 'category_') === 0) {
    $extracted = substr($callbackData, strlen('category_'));
    echo "Matched category_ prefix, extracted: '$extracted'\n";
} else {
    echo "Did not match category_ prefix\n";
}

// Handle direct callback names (like back buttons)
if (in_array($callbackData, $directCallbacks)) {
    echo "Matched direct callback, returning: '$callbackData'\n";
} else {
    echo "Did not match direct callback\n";
}

// Handle short category format for commissioning equipment
if (strpos($callbackData, 'cat_Таб') === 0 || strpos($callbackData, 'cat_Гарантии_') === 0 || strpos($callbackData, 'cat_ШМР_') === 0) {
    echo "Matched short format prefix\n";
} else {
    echo "Did not match short format prefix\n";
}

echo "\n--- Expected result ---\n";
echo "The method should return: '$callbackData'\n";

// Now let's test the actual method if we can
echo "\n--- Testing actual method (if possible) ---\n";
try {
    // We can't directly call the method since it's private, but we can simulate the logic
    echo "Simulation complete. The method should work correctly.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>