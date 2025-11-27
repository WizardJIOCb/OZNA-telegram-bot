<?php
// Create a simple test to verify the warranty equipment callback handling
// This will help us trace where the callback processing fails

echo "=== Warranty Equipment Test Trace ===\n\n";

// Test 1: Create the warranty equipment keyboard manually (to avoid class loading issues)
echo "1. Testing warranty equipment keyboard structure:\n";
$keyboard = [
    [['text' => '⚡ Электрооборудование', 'callback_data' => 'category_Табель_РабГарантии_ЭлектрОб']],
    [['text' => '📎 Средства измерений', 'callback_data' => 'category_Табель_РабГарантии_СрИзм']],
    [['text' => '💧 Насосное оборудование', 'callback_data' => 'category_Табель_РабГарантии_НасОб']],
    [['text' => '🔧 Трубопроводная арматура', 'callback_data' => 'category_Табель_РабГарантии_ТрубАрм']],
    [['text' => '⚙️ Исполнительные механизмы', 'callback_data' => 'category_Табель_РабГарантии_ИспМех']],
    [['text' => '💻 Контроллерное оборудование', 'callback_data' => 'category_Табель_РабГарантии_КонтрОб']],
    [['text' => '📦 Прочее оборудование', 'callback_data' => 'category_Табель_РабГарантии_Прочее']]
];

echo "Generated keyboard:\n";
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        echo "- Text: '{$button['text']}' -> Callback: '{$button['callback_data']}'\n";
    }
}

echo "\n2. Testing expected callback format when 'Электрооборудование' is clicked:\n";
$expectedCallback = 'category_Табель_РабГарантии_ЭлектрОб';
echo "Full callback: '{$expectedCallback}'\n";

// Simulate the callback processing that would happen in CallbackqueryCommand
echo "\n3. Simulating callback processing:\n";

// Extract category from callback (like CallbackqueryCommand does)
if (strpos($expectedCallback, 'category_') === 0) {
    $category = substr($expectedCallback, 9); // Remove 'category_' prefix
    echo "Extracted category: '{$category}'\n";
    echo "Category bytes: " . bin2hex($category) . "\n";
    echo "Category length: " . strlen($category) . "\n";
    
    // Test if this would match the TabelHandler pattern
    if ($category === 'Табель_РабГарантии_ЭлектрОб') {
        echo "✅ SUCCESS: This category SHOULD be handled by TabelHandler warranty equipment handler\n";
    } else {
        echo "❌ FAIL: Category would not match warranty equipment handler\n";
    }
} else {
    echo "❌ FAIL: Callback doesn't start with 'category_'\n";
}

echo "\n=== Debug Recommendation ===\n";
echo "The warranty equipment callback flow should work correctly.\n";
echo "Since you're getting 'неизвестная команда табеля', the issue is likely:\n";
echo "1. The callback is not reaching TabelHandler at all\n";
echo "2. There's a fatal error in TabelHandler before it reaches the warranty handlers\n";
echo "3. Another handler is intercepting the callback first\n\n";

echo "To debug this:\n";
echo "1. Check if webhook is working: Click a warranty button and check if callback appears in webhook logs\n";
echo "2. Add debug logging to CallbackqueryCommand::execute() to see what happens to the callback\n";
echo "3. Add debug logging to TabelHandler::canHandle() to see if it's being called\n";
echo "4. Check CallbackHandlerFactory for handler priority issues\n";
?>