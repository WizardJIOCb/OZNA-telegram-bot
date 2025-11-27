<?php
// Comprehensive Warranty Equipment Diagnostic

echo "=== COMPREHENSIVE WARRANTY EQUIPMENT DIAGNOSTIC ===\n";

// Test 1: Check TabelHandler canHandle method for warranty callbacks
echo "\n1. TESTING CANHANDLE METHOD:\n";
$handlerContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

$warrantyCallbacks = [
    'cat_ТабГарантии',
    'category_Табель_РабГарантии_ЭлектрОб',
    'category_Табель_РабГарантии_СрИзм',
    'category_Табель_РабГарантии_НасОб'
];

foreach ($warrantyCallbacks as $callback) {
    if (strpos($handlerContent, $callback) !== false) {
        echo "✅ '{$callback}' found in TabelHandler\n";
    } else {
        echo "❌ '{$callback}' NOT found in TabelHandler\n";
    }
}

// Test 2: Check if warranty equipment keyboard method exists and has correct callbacks
echo "\n2. TESTING WARRANTY KEYBOARD:\n";
$messageServiceContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');

if (strpos($messageServiceContent, 'createWarrantyEquipmentKeyboard') !== false) {
    echo "✅ createWarrantyEquipmentKeyboard method exists\n";
    
    // Check keyboard callbacks
    $keyboardCallbacks = [
        'category_Табель_РабГарантии_ЭлектрОб',
        'category_Табель_РабГарантии_СрИзм',
        'category_Табель_РабГарантии_НасОб'
    ];
    
    foreach ($keyboardCallbacks as $callback) {
        if (strpos($messageServiceContent, $callback) !== false) {
            echo "✅ Keyboard contains '{$callback}'\n";
        } else {
            echo "❌ Keyboard missing '{$callback}'\n";
        }
    }
} else {
    echo "❌ createWarrantyEquipmentKeyboard method NOT found\n";
}

// Test 3: Check warranty handler method
echo "\n3. TESTING WARRANTY HANDLER:\n";
if (strpos($handlerContent, 'handleWarrantyEquipmentCallback') !== false) {
    echo "✅ handleWarrantyEquipmentCallback method exists\n";
    
    // Check if it handles individual equipment callbacks
    if (strpos($handlerContent, 'category_Табель_РабГарантии_') !== false) {
        echo "✅ Handler processes individual equipment callbacks\n";
    } else {
        echo "❌ Handler does NOT process individual equipment callbacks\n";
    }
} else {
    echo "❌ handleWarrantyEquipmentCallback method NOT found\n";
}

// Test 4: Check isActivityConfirmation method
echo "\n4. TESTING ACTIVITY CONFIRMATION:\n";
if (strpos($handlerContent, 'isActivityConfirmation') !== false) {
    echo "✅ isActivityConfirmation method exists\n";
    
    // Check if it handles warranty equipment
    if (strpos($handlerContent, 'WARRANTY CONFIRMATION') !== false) {
        echo "✅ isActivityConfirmation handles warranty equipment\n";
    } else {
        echo "❌ isActivityConfirmation does NOT handle warranty equipment\n";
    }
} else {
    echo "❌ isActivityConfirmation method NOT found\n";
}

// Test 5: Check handleActivityConfirmation method
echo "\n5. TESTING ACTIVITY CONFIRMATION HANDLER:\n";
if (strpos($handlerContent, 'handleActivityConfirmation') !== false) {
    echo "✅ handleActivityConfirmation method exists\n";
    
    // Check if it handles warranty equipment
    if (strpos($handlerContent, 'РабГарантии_') !== false) {
        echo "✅ handleActivityConfirmation processes warranty equipment\n";
    } else {
        echo "❌ handleActivityConfirmation does NOT process warranty equipment\n";
    }
} else {
    echo "❌ handleActivityConfirmation method NOT found\n";
}

// Test 6: Check isActivityExecution method
echo "\n6. TESTING ACTIVITY EXECUTION:\n";
if (strpos($handlerContent, 'isActivityExecution') !== false) {
    echo "✅ isActivityExecution method exists\n";
    
    // Check if it handles warranty confirmations
    if (strpos($handlerContent, 'РабГарантии_(.+)_yes') !== false) {
        echo "✅ isActivityExecution handles warranty confirmations\n";
    } else {
        echo "❌ isActivityExecution does NOT handle warranty confirmations\n";
    }
} else {
    echo "❌ isActivityExecution method NOT found\n";
}

// Test 7: Check TabelEntry mappings
echo "\n7. TESTING TABEL ENTRY MAPPINGS:\n";
$tabelEntryContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\TabelEntry.php');

$warrantyMappings = [
    'РабГарантии_ЭлектрОб',
    'РабГарантии_СрИзм',
    'РабГарантии_НасОб',
    'РабГарантии_ТрубАрм',
    'РабГарантии_ИспМех',
    'РабГарантии_КонтрОб',
    'РабГарантии_Прочее'
];

foreach ($warrantyMappings as $mapping) {
    if (strpos($tabelEntryContent, "'$mapping'") !== false) {
        echo "✅ '{$mapping}' mapping exists\n";
    } else {
        echo "❌ '{$mapping}' mapping missing\n";
    }
}

// Test 8: Check routing in routeTabelCallback
echo "\n8. TESTING CALLBACK ROUTING:\n";
if (strpos($handlerContent, 'handleWarrantyEquipmentCallback') !== false && 
    strpos($handlerContent, 'cat_ТабГарантии') !== false) {
    echo "✅ Warranty routing exists in routeTabelCallback\n";
} else {
    echo "❌ Warranty routing missing in routeTabelCallback\n";
}

// Test 9: Extract exact warranty keyboard content
echo "\n9. EXTRACTING EXACT KEYBOARD CONTENT:\n";
$keyboardStart = strpos($messageServiceContent, 'public function createWarrantyEquipmentKeyboard');
if ($keyboardStart !== false) {
    $keyboardEnd = strpos($messageServiceContent, '}', $keyboardStart + 100);
    $keyboardMethod = substr($messageServiceContent, $keyboardStart, $keyboardEnd - $keyboardStart + 1);
    
    // Count callback_data entries
    $callbackCount = substr_count($keyboardMethod, 'callback_data');
    echo "✅ Warranty keyboard has {$callbackCount} buttons\n";
    
    if (strpos($keyboardMethod, 'category_Табель_РабГарантии_ЭлектрОб') !== false) {
        echo "✅ Электрооборудование callback correctly configured\n";
    } else {
        echo "❌ Электрооборудование callback incorrectly configured\n";
    }
} else {
    echo "❌ Cannot extract warranty keyboard content\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "\nIf any items show ❌, those are the areas causing the issue.\n";
echo "The warranty equipment system needs all items to show ✅ to work properly.\n";
?>