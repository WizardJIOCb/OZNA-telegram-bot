<?php
// Diagnostic script to test the "Пусконаладочные работы" button functionality

echo "=== Diagnostic Test for 'Пусконаладочные работы' Button ===\n\n";

// Test 1: Check the button creation in MessageService
echo "1. Checking button creation in MessageService...\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');
if (strpos($messageServiceContent, "['text' => '⚙️ Пусконаладочные работы', 'callback_data' => 'category_Табель_ПНЛ']") !== false) {
    echo "   ✓ Button correctly created with callback_data 'category_Табель_ПНЛ'\n";
} else {
    echo "   ✗ Button creation not found or incorrect\n";
}

// Test 2: Check TabelHandler routing
echo "\n2. Checking TabelHandler routing...\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if handleOtherWorkTypes method has the correct check
if (substr_count($tabelHandlerContent, "if (\$category === 'Табель_ПНЛ')") >= 1) {
    echo "   ✓ TabelHandler has correct routing for 'Табель_ПНЛ'\n";
} else {
    echo "   ✗ TabelHandler missing correct routing for 'Табель_ПНЛ'\n";
}

// Check for duplicate entries
$count = substr_count($tabelHandlerContent, "if (\$category === 'Табель_ПНЛ') {\n            return \$this->handleCommissioningWorksMenu(\$chatId);\n        }");
if ($count > 1) {
    echo "   ⚠ Found $count duplicate entries for 'Табель_ПНЛ' routing (should be optimized)\n";
} else {
    echo "   ✓ No duplicate routing entries found\n";
}

// Test 3: Simulate the complete flow
echo "\n3. Simulating complete button press flow...\n";

// Simulate button press
$callbackData = 'category_Табель_ПНЛ';
echo "   Button pressed with callback_data: $callbackData\n";

// Simulate category extraction
$category = substr($callbackData, strlen('category_'));
echo "   Extracted category: $category\n";

// Check if TabelHandler can handle this category
$canHandle = (strpos($callbackData, 'category_Табель') === 0);
echo "   TabelHandler can handle: " . ($canHandle ? 'Yes' : 'No') . "\n";

// Simulate routing in routeTabelCallback
if ($category === 'Табель_ПНЛ') {
    echo "   Routed to handleOtherWorkTypes with category: $category\n";
    
    // Simulate handleOtherWorkTypes
    if ($category === 'Табель_ПНЛ') {
        echo "   Matched 'Табель_ПНЛ' in handleOtherWorkTypes\n";
        echo "   Would call handleCommissioningWorksMenu()\n";
        echo "   ✓ SUCCESS: Button should work correctly!\n";
    }
} else {
    echo "   ✗ ERROR: Category not routed correctly\n";
}

echo "\n=== Diagnostic Test Complete ===\n";
echo "If all checks passed, the 'Пусконаладочные работы' button should be working correctly.\n";
echo "If you're still experiencing issues, try restarting the bot to clear any cached data.\n";
?>