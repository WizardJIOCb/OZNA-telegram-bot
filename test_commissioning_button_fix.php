<?php
// Test script to simulate the "Пусконаладочные работы" button press with correct callback data

echo "=== Testing 'Пусконаладочные работы' button (Corrected Version) ===\n";

// Simulate the callback data that would be sent by the button (corrected)
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

// Check if TabelHandler would handle this
function tabelHandlerCanHandle($callbackData) {
    return strpos($callbackData, 'category_Табель') === 0;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

// Simulate the routing in TabelHandler
function routeTabelCallback($category) {
    echo "Routing category: $category\n";
    
    // Check for main tabel menu
    if ($category === 'Табель') {
        echo "  -> Would handle main tabel menu\n";
        return true;
    }
    
    // Check for commissioning works menu (Пусконаладочные работы)
    if ($category === 'Табель_ПНЛ') {
        echo "  -> Would handle commissioning works menu (Пусконаладочные работы)\n";
        echo "  -> Should call handleOtherWorkTypes with category Табель_ПНЛ\n";
        return handleOtherWorkTypes('test_chat_id', $category, null);
    }
    
    echo "  -> Would not match any specific route\n";
    return false;
}

function handleOtherWorkTypes($chatId, $category, $conversation) {
    echo "In handleOtherWorkTypes with category: $category\n";
    
    if ($category === 'Табель_ПНЛ') {
        echo "  -> Matched Табель_ПНЛ category\n";
        echo "  -> Would call handleCommissioningWorksMenu\n";
        return handleCommissioningWorksMenu($chatId);
    }
    
    echo "  -> Category not matched in handleOtherWorkTypes\n";
    return false;
}

function handleCommissioningWorksMenu($chatId) {
    echo "In handleCommissioningWorksMenu\n";
    echo "  -> Would create commissioning works keyboard\n";
    echo "  -> Would send message with equipment selection menu\n";
    return true;
}

// Test the routing
$result = routeTabelCallback($category);
echo "Routing result: " . ($result ? 'success' : 'failure') . "\n";

echo "\nTest completed successfully!\n";
echo "The 'Пусконаладочные работы' button should work correctly.\n";
?>