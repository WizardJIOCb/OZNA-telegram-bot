<?php
// Debug warranty equipment callback processing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Warranty Equipment Callback Debug ===\n";

// List all expected warranty equipment callbacks from MessageService
$expectedCallbacks = [
    'category_Табель_РабГарантии_ЭлектрОб',
    'category_Табель_РабГарантии_СрИзм', 
    'category_Табель_РабГарантии_НасОб',
    'category_Табель_РабГарантии_ТрубАрм',
    'category_Табель_РабГарантии_ИспМех',
    'category_Табель_РабГарантии_КонтрОб',
    'category_Табель_РабГарантии_Прочее'
];

echo "Expected warranty equipment callbacks:\n";
foreach ($expectedCallbacks as $callback) {
    echo "- {$callback}\n";
    echo "  Encoded bytes: " . bin2hex($callback) . "\n";
    echo "  Length: " . strlen($callback) . "\n";
}

echo "\n=== Test handleOtherWorkTypes patterns ===\n";

// Test the patterns used in TabelHandler::handleOtherWorkTypes
$testCategories = [
    'Табель_РабГарантии_ЭлектрОб',
    'Табель_РабГарантии_СрИзм',
    'Табель_РабГарантии_НасОб',
    'Табель_РабГарантии_ТрубАрм',
    'Табель_РабГарантии_ИспМех',
    'Табель_РабГарантии_КонтрОб',
    'Табель_РабГарантии_Прочее'
];

foreach ($testCategories as $category) {
    echo "\nTesting category: '{$category}'\n";
    echo "Bytes: " . bin2hex($category) . "\n";
    echo "Length: " . strlen($category) . "\n";
    
    // Test exact match conditions from TabelHandler
    if ($category === 'Табель_РабГарантии_ЭлектрОб') {
        echo "✅ MATCH: Warranty work for electrical equipment\n";
    } elseif ($category === 'Табель_РабГарантии_СрИзм') {
        echo "✅ MATCH: Warranty work for measuring instruments\n";
    } elseif ($category === 'Табель_РабГарантии_НасОб') {
        echo "✅ MATCH: Warranty work for pump equipment\n";
    } elseif ($category === 'Табель_РабГарантии_ТрубАрм') {
        echo "✅ MATCH: Warranty work for pipeline fittings\n";
    } elseif ($category === 'Табель_РабГарантии_ИспМех') {
        echo "✅ MATCH: Warranty work for actuators\n";
    } elseif ($category === 'Табель_РабГарантии_КонтрОб') {
        echo "✅ MATCH: Warranty work for controller equipment\n";
    } elseif ($category === 'Табель_РабГарантии_Прочее') {
        echo "✅ MATCH: Warranty work for other equipment\n";
    } else {
        echo "❌ NO MATCH: Category not handled\n";
    }
}

echo "\n=== Debug Output ===\n";
echo "The warranty equipment handlers ARE implemented in TabelHandler::handleOtherWorkTypes().\n";
echo "If the buttons show 'неизвестная команда табеля', it means:\n";
echo "1. The callback is not reaching TabelHandler at all, OR\n";
echo "2. There's an encoding issue preventing pattern matching, OR\n";
echo "3. The callback is being processed by a different handler first\n";
echo "\nNext steps:\n";
echo "1. Check webhook logs for the exact callback data received\n";
echo "2. Add debug logging to CallbackqueryCommand to see routing\n";
echo "3. Verify TabelHandler priority in CallbackHandlerFactory\n";
?>