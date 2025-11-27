<?php
// Test script to simulate the "Пусконаладочные работы" button press with detailed logging
echo "=== Testing 'Пусконаладочные работы' button with detailed logging ===\n";

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/test_pnl_debug.log');

// Simulate the callback data that would be sent when pressing the button
$callbackData = 'category_Табель_ПНЛ';
echo "Original callback data: $callbackData\n";
error_log("Test PNL: Original callback data: $callbackData");

// Simulate the CallbackHandlerFactory logic
function extractCategory($callbackData) {
    error_log("Test PNL: extractCategory called with: $callbackData");
    
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    error_log("Test PNL: After encoding fix: $callbackData");
    
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        // Fix encoding for category
        $category = mb_convert_encoding($category, 'UTF-8', 'auto');
        error_log("Test PNL: Extracted category: $category");
        return $category;
    }
    
    error_log("Test PNL: No category prefix found, returning original: $callbackData");
    return $callbackData;
}

$category = extractCategory($callbackData);
echo "Extracted category: $category\n";

// Simulate the TabelHandler canHandle method
function tabelHandlerCanHandle($callbackData) {
    error_log("Test PNL: tabelHandlerCanHandle called with: $callbackData");
    
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    $canHandle = strpos($callbackData, 'category_Табель') === 0;
    
    error_log("Test PNL: tabelHandlerCanHandle result: " . ($canHandle ? 'true' : 'false'));
    return $canHandle;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "TabelHandler can handle: " . ($canHandle ? 'Yes' : 'No') . "\n";

// Simulate the TabelHandler routing logic
function routeTabelCallback($category) {
    error_log("Test PNL: routeTabelCallback called with category: $category");
    echo "Routing category: $category\n";
    
    // Special handling for commissioning works menu (Пусконаладочные работы)
    // Using a unique callback to avoid conflicts with activity types
    if ($category === 'Табель_ПНЛ') {
        error_log("Test PNL: Matched Табель_ПНЛ, would call handleCommissioningWorksMenu");
        echo "  -> Matched Табель_ПНЛ\n";
        echo "  -> Would call handleCommissioningWorksMenu\n";
        return true;
    }
    
    error_log("Test PNL: No match found for category: $category");
    echo "  -> No match found\n";
    return false;
}

// Test the routing
$result = routeTabelCallback($category);
echo "Routing result: " . ($result ? 'Success' : 'Failure') . "\n";

error_log("Test PNL: Test completed");
echo "\nTest completed! Check test_pnl_debug.log for detailed logs.\n";
?>