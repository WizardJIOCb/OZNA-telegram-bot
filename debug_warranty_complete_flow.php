<?php
/**
 * Debug complete warranty button flow to identify why reports aren't generated
 */

echo "=== COMPLETE WARRANTY BUTTON FLOW DEBUG ===\n\n";

// Check each step of the warranty flow
echo "1. CHECKING MAIN WARRANTY BUTTON:\n";
echo "=================================\n";

$handlerFile = 'CustomCommands/Handlers/TabelHandler.php';
$messageServiceFile = 'CustomCommands/Services/MessageService.php';

// Check if warranty button exists in commissioning keyboard
if (file_exists($messageServiceFile)) {
    $messageContent = file_get_contents($messageServiceFile);
    
    if (strpos($messageContent, 'cat_ТабГарантии') !== false) {
        echo "✅ Warranty button exists in commissioning keyboard\n";
        echo "   Callback: cat_ТабГарантии\n";
    } else {
        echo "❌ Warranty button missing in commissioning keyboard\n";
    }
}

echo "\n2. CHECKING WARRANTY MENU HANDLER:\n";
echo "==================================\n";

if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    // Check warranty menu handler
    if (strpos($handlerContent, 'cat_ТабГарантии') !== false && 
        strpos($handlerContent, 'handleWarrantyEquipmentMenu') !== false) {
        echo "✅ Warranty menu handler exists\n";
    } else {
        echo "❌ Warranty menu handler missing\n";
    }
}

echo "\n3. CHECKING ELECTRICAL EQUIPMENT HANDLER:\n";
echo "=========================================\n";

if (file_exists($handlerFile)) {
    // Check electrical equipment handler
    if (strpos($handlerContent, 'Табель_РабГарантии_ЭлектрОб') !== false) {
        echo "✅ Electrical equipment handler exists\n";
    } else {
        echo "❌ Electrical equipment handler missing\n";
    }
}

echo "\n4. CHECKING CONFIRMATION CALLBACK HANDLING:\n";
echo "===========================================\n";

if (file_exists($handlerFile)) {
    // Check confirmation callback handling
    if (strpos($handlerContent, 'cat_Гарантии_ЭлектрОб_yes') !== false) {
        echo "✅ Confirmation callback handling exists\n";
    } else {
        echo "❌ Confirmation callback handling missing\n";
    }
    
    // Check activity execution detection
    if (strpos($handlerContent, 'isActivityExecution') !== false) {
        echo "✅ Activity execution method exists\n";
    } else {
        echo "❌ Activity execution method missing\n";
    }
}

echo "\n5. CHECKING ACTIVITY TYPE DEFINITIONS:\n";
echo "======================================\n";

if (file_exists($handlerFile)) {
    // Check if warranty activity types are in activityTypes array
    if (strpos($handlerContent, 'РабГарантии_ЭлектрОб') !== false) {
        echo "✅ Warranty activity types defined\n";
    } else {
        echo "❌ Warranty activity types missing\n";
    }
}

echo "\n6. SIMULATE COMPLETE FLOW:\n";
echo "==========================\n";

echo "Flow should be:\n";
echo "1. User clicks 'Работы по гарантии' → cat_ТабГарантии\n";
echo "2. System shows warranty equipment menu\n";
echo "3. User clicks 'Электрооборудование' → category_Табель_РабГарантии_ЭлектрОб\n";
echo "4. System shows confirmation dialog\n";
echo "5. User clicks 'Да' → cat_Гарантии_ЭлектрОб_yes\n";
echo "6. System generates and sends report\n\n";

echo "POTENTIAL ISSUES:\n";
echo "- Server needs restart to load new code\n";
echo "- User conversation state is corrupted\n";
echo "- Missing project code or date in conversation\n";
echo "- User access level restrictions\n";
echo "- Database connection issues\n\n";

echo "=== RECOMMENDATION ===\n";
echo "If the flow looks correct but still doesn't work, we should:\n";
echo "1. Remove the warranty button completely\n";
echo "2. Clean up all warranty-related code\n";
echo "3. Recreate it from scratch with simple implementation\n";
echo "4. Test step by step\n";
?>