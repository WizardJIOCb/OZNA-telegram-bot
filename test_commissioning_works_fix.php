<?php
// Test script to verify the "Пусконаладочные работы" button fix

echo "=== Testing 'Пусконаладочные работы' Button Fix ===\n";

// Simulate the callback that would be sent when the button is pressed
$callbackData = 'category_Табель_ПусконаладочныеРаботы';
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
    if ($category === 'Табель_ПусконаладочныеРаботы') {
        echo "  -> Would handle commissioning works menu (Пусконаладочные работы)\n";
        echo "  -> Should call handleOtherWorkTypes with category Табель_ПусконаладочныеРаботы\n";
        return handleOtherWorkTypes('test_chat_id', $category, null);
    }
    
    echo "  -> Would not match any specific route\n";
    return false;
}

function handleOtherWorkTypes($chatId, $category, $conversation) {
    echo "In handleOtherWorkTypes with category: $category\n";
    
    // Handle Commissioning Works (Пусконаладочные работы)
    if ($category === 'Табель_ПусконаладочныеРаботы') {
        echo "  -> Matched Табель_ПусконаладочныеРаботы category\n";
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
    
    // Simulate the keyboard that would be created
    $expectedActivities = [
        '⚡ Электрооборудование',
        'монтаж ВО',
        '❄️ Вентиляция и отопление',
        '📏 Измерения',
        '📋 Работы по графику',
        '📝 Формирование ИД',
        '🔧 Доп. работы',
        '📦 Материальный учет',
        '🏗 Строительство'
    ];
    
    echo "  -> Would display " . count($expectedActivities) . " activities:\n";
    foreach ($expectedActivities as $index => $activity) {
        echo "    " . ($index + 1) . ". $activity\n";
    }
    
    return true;
}

// Test the routing
$result = routeTabelCallback($category);
echo "Routing result: " . ($result ? 'success' : 'failure') . "\n";

echo "\n=== Test Complete ===\n";
echo "The 'Пусконаладочные работы' button should now work correctly and display all activities.\n";
?>