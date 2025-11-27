<?php

echo "Debugging Electrical Equipment Routing Issue\n";
echo "===========================================\n\n";

// Let's trace the callback routing to see where it's failing

echo "Step 1: Check what callback data is generated for Electrical Equipment\n";
echo "--------------------------------------------------------------------\n";

// Include the MessageService to check the keyboard
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

try {
    $messageService = new \CustomCommands\Services\MessageService();
    
    // Check the commissioning works keyboard
    echo "Checking commissioning works keyboard...\n";
    $commissioningKeyboard = $messageService->createCommissioningWorksKeyboard();
    
    echo "Found " . count($commissioningKeyboard) . " buttons in commissioning works keyboard:\n\n";
    
    $electricalButton = null;
    foreach ($commissioningKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "Button: '" . $button['text'] . "' → Callback: '" . $button['callback_data'] . "'\n";
            
            // Look specifically for electrical equipment
            if (strpos($button['text'], 'Электрооборудование') !== false) {
                $electricalButton = $button;
                echo "  ^^^ FOUND ELECTRICAL EQUIPMENT BUTTON ^^^\n";
            }
        }
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
    
    if ($electricalButton) {
        echo "Step 2: Analyze Electrical Equipment Button\n";
        echo "==========================================\n";
        echo "Text: " . $electricalButton['text'] . "\n";
        echo "Callback Data: " . $electricalButton['callback_data'] . "\n";
        echo "Callback Length: " . strlen($electricalButton['callback_data']) . " bytes\n";
        echo "Expected Category: 'ТабЭлОб'\n\n";
        
        // Check if the callback matches what we expect
        if ($electricalButton['callback_data'] === 'cat_ТабЭлОб') {
            echo "✅ Callback data matches expected format\n";
        } else {
            echo "❌ Callback data does NOT match expected format\n";
            echo "   Expected: 'cat_ТабЭлОб'\n";
            echo "   Actual:   '" . $electricalButton['callback_data'] . "'\n";
        }
    } else {
        echo "❌ ERROR: Electrical equipment button not found in commissioning works keyboard!\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
    
    echo "Step 3: Check TabelHandler Routing Logic\n";
    echo "========================================\n";
    
    // Read the actual routing logic from TabelHandler
    $tabelHandlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
    if (file_exists($tabelHandlerFile)) {
        $content = file_get_contents($tabelHandlerFile);
        
        // Check if our routing logic is present
        if (strpos($content, "if (\$category === 'ТабЭлОб')") !== false) {
            echo "✅ Routing logic for 'ТабЭлОб' found in TabelHandler\n";
        } else {
            echo "❌ Routing logic for 'ТабЭлОб' NOT found in TabelHandler\n";
        }
        
        // Check if handler method exists
        if (strpos($content, "handleElectricalEquipmentMenu") !== false) {
            echo "✅ handleElectricalEquipmentMenu method found in TabelHandler\n";
        } else {
            echo "❌ handleElectricalEquipmentMenu method NOT found in TabelHandler\n";
        }
        
        // Check if the callback handler can handle the callback
        echo "\nChecking callback handling...\n";
        
        // Look for the canHandle method logic
        if (strpos($content, "canHandle") !== false) {
            echo "✅ canHandle method exists\n";
            
            // Check if it handles our specific callback
            if (strpos($content, "cat_ТабЭлОб") !== false || strpos($content, "ТабЭлОб") !== false) {
                echo "✅ TabelHandler appears to handle 'ТабЭлОб' callbacks\n";
            } else {
                echo "❌ TabelHandler may not handle 'ТабЭлОб' callbacks properly\n";
            }
        } else {
            echo "❌ canHandle method not found\n";
        }
        
    } else {
        echo "❌ ERROR: TabelHandler.php file not found!\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "DIAGNOSIS\n";
    echo str_repeat("=", 60) . "\n\n";
    
    if ($electricalButton) {
        echo "The issue is likely one of the following:\n\n";
        echo "1. CALLBACK ROUTING ISSUE:\n";
        echo "   - The callback 'cat_ТабЭлОб' is not being recognized by TabelHandler\n";
        echo "   - The canHandle method doesn't include this callback pattern\n\n";
        
        echo "2. CATEGORY EXTRACTION ISSUE:\n";
        echo "   - The callback is being received but not extracted correctly\n";
        echo "   - The extractCategoryFromCallback method may need updating\n\n";
        
        echo "3. ROUTING LOGIC ISSUE:\n";
        echo "   - The routing condition in handleOtherWorkTypes may not be reached\n";
        echo "   - There may be an earlier condition that intercepts the callback\n\n";
        
        echo "NEXT STEPS:\n";
        echo "1. Check the canHandle method to ensure it accepts 'cat_ТабЭлОб'\n";
        echo "2. Verify extractCategoryFromCallback handles short format callbacks\n";
        echo "3. Add debug logging to trace the exact flow\n";
    } else {
        echo "CRITICAL ISSUE: Electrical equipment button is missing from the keyboard!\n";
        echo "This means the issue is in the MessageService keyboard creation.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR during debugging: " . $e->getMessage() . "\n";
}

echo "\n";

?>