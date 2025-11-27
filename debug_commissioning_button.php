<?php
// Debug script to test the commissioning works button

echo "=== Debug Commissioning Works Button ===\n";

// Simulate the callback data that would be sent by the button
$callbackData = 'category_Табель_ПНЛ';
echo "Original callback data: $callbackData\n";

// Simulate what happens in CallbackHandlerFactory::extractCategory
function extractCategory($callbackData) {
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        return $category;
    }
    return $callbackData;
}

$category = extractCategory($callbackData);
echo "Extracted category: $category\n";

// Test if TabelHandler would handle this
function tabelHandlerCanHandle($callbackData) {
    return strpos($callbackData, 'category_Табель') === 0;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

// Test the routing logic
function routeTabelCallback($category) {
    echo "Routing category: $category\n";
    
    // Check for commissioning works menu
    if ($category === 'Табель_ПНЛ') {
        echo "  -> Matched Табель_ПНЛ\n";
        echo "  -> Would call handleCommissioningWorksMenu\n";
        return true;
    }
    
    echo "  -> No match found\n";
    return false;
}

// Test the routing
$result = routeTabelCallback($category);
echo "Routing result: " . ($result ? 'success' : 'failure') . "\n";

echo "\nTest completed!\n";
?>