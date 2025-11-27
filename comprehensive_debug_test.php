<?php

// More comprehensive test to debug the "Мобильная бригада" button issue

echo "=== Comprehensive Debug Test for Mobile Brigade Button ===\n\n";

// Simulate the actual callback data that the button sends
$callbackData = 'category_Табель_Мобильная_Бригада';

echo "--- Initial Callback Data ---\n";
echo "Callback data: " . $callbackData . "\n";
echo "Callback data length: " . strlen($callbackData) . "\n";
echo "Callback data hex: " . bin2hex($callbackData) . "\n\n";

// Simulate the TabelHandler canHandle method
function tabelHandlerCanHandle(string $callbackData): bool
{
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    $canHandle = str_starts_with($callbackData, 'category_Табель');
    echo "TabelHandler canHandle check:\n";
    echo "  After encoding fix: " . $callbackData . "\n";
    echo "  Starts with 'category_Табель': " . ($canHandle ? 'true' : 'false') . "\n";
    return $canHandle;
}

// Test if TabelHandler can handle this callback
$canHandle = tabelHandlerCanHandle($callbackData);
echo "TabelHandler can handle this callback: " . ($canHandle ? 'YES' : 'NO') . "\n\n";

// Simulate the extractCategory function from CallbackHandlerFactory
function extractCategory(string $callbackData): string
{
    echo "extractCategory called with callbackData: '{$callbackData}'\n";
    
    // Handle other categories
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "extractCategory extracted category: '{$category}' from callback data: '{$callbackData}'\n";
        return $category;
    }
    
    echo "extractCategory: No category found in callback data: '{$callbackData}'\n";
    return '';
}

// Extract the category
$category = extractCategory($callbackData);
echo "Extracted category: " . $category . "\n\n";

// Simulate the routeTabelCallback method
function routeTabelCallback(string $category): string
{
    echo "--- routeTabelCallback method test ---\n";
    echo "Category received: '{$category}'\n";
    
    // Main tabel menu
    if ($category === 'Табель') {
        return "Would show main tabel menu";
    }

    // Handle Mobile Brigade
    if ($category === 'Табель_Мобильная_Бригада') {
        return "Would call handleMobileBrigadeDateSelection()";
    }
    
    // Other work types
    return "Would call handleOtherWorkTypes()";
}

// Test routing
$routingResult = routeTabelCallback($category);
echo "Routing result: " . $routingResult . "\n\n";

// Simulate the handleOtherWorkTypes method
function handleOtherWorkTypes(string $category): string
{
    echo "--- handleOtherWorkTypes method test ---\n";
    echo "Category received: '{$category}'\n";
    
    // Handle Mobile Brigade
    if ($category === 'Табель_Мобильная_Бригада') {
        return "SUCCESS: Would call handleMobileBrigadeDateSelection()";
    }
    
    return "Would show 'Неизвестная команда табеля'";
}

// Test handleOtherWorkTypes
$otherWorkResult = handleOtherWorkTypes($category);
echo "handleOtherWorkTypes result: " . $otherWorkResult . "\n\n";

echo "=== Test Completed ===\n";

?>