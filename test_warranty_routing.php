<?php
// Test warranty equipment callback routing

echo "=== Testing Warranty Equipment Callback Routing ===\n";

// Simulate the warranty equipment button callback
$warrantyCallback = 'cat_ТабГарантии';
echo "1. Testing main warranty button callback: '$warrantyCallback'\n";

// Load TabelHandler content without requiring the class
$handlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

// Check if callback is in canHandle method
if (strpos($handlerContent, 'cat_ТабГарантии') !== false) {
    echo "✅ 'cat_ТабГарантии' found in TabelHandler\n";
} else {
    echo "❌ 'cat_ТабГарантии' NOT found in TabelHandler\n";
}

// Check if warranty handler method exists
if (strpos($handlerContent, 'handleWarrantyEquipmentCallback') !== false) {
    echo "✅ handleWarrantyEquipmentCallback method found\n";
} else {
    echo "❌ handleWarrantyEquipmentCallback method NOT found\n";
}

// Check warranty equipment keyboard method
$messageServiceContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');
if (strpos($messageServiceContent, 'createWarrantyEquipmentKeyboard') !== false) {
    echo "✅ createWarrantyEquipmentKeyboard method found\n";
} else {
    echo "❌ createWarrantyEquipmentKeyboard method NOT found\n";
}

echo "\n2. Testing individual warranty equipment callbacks:\n";
$warrantyEquipmentCallbacks = [
    'category_Табель_РабГарантии_ЭлектрОб',
    'category_Табель_РабГарантии_СрИзм',
    'category_Табель_РабГарантии_НасОб',
    'category_Табель_РабГарантии_ТрубАрм',
    'category_Табель_РабГарантии_ИспМех',
    'category_Табель_РабГарантии_КонтрОб',
    'category_Табель_РабГарантии_Прочее'
];

foreach ($warrantyEquipmentCallbacks as $callback) {
    if (strpos($handlerContent, str_replace('category_Табель_', '', $callback)) !== false) {
        echo "✅ $callback pattern found\n";
    } else {
        echo "❌ $callback pattern NOT found\n";
    }
}

echo "\n3. Testing warranty equipment confirmation callbacks:\n";
$warrantyConfirmationCallbacks = [
    'category_Табель_РабГарантии_ЭлектрОб_yes',
    'category_Табель_РабГарантии_СрИзм_yes',
    'category_Табель_РабГарантии_НасОб_yes',
    'category_Табель_РабГарантии_ТрубАрм_yes',
    'category_Табель_РабГарантии_ИспМех_yes',
    'category_Табель_РабГарантии_КонтрОб_yes',
    'category_Табель_РабГарантии_Прочее_yes'
];

// Check if warranty confirmations are handled in isActivityExecution
if (strpos($handlerContent, 'РабГарантии_(.+)_yes') !== false) {
    echo "✅ Warranty confirmation pattern found in isActivityExecution\n";
} else {
    echo "❌ Warranty confirmation pattern NOT found in isActivityExecution\n";
}

echo "\n=== Test Complete ===\n";
?>