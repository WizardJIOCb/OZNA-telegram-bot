<?php
/**
 * Test the exact warranty equipment flow based on user description
 */

echo "=== TESTING EXACT WARRANTY FLOW AS DESCRIBED BY USER ===\n\n";

// Load necessary files
$handlerFile = 'CustomCommands/Handlers/TabelHandler.php';
$messageServiceFile = 'CustomCommands/Services/MessageService.php';

echo "1. CHECKING USER'S DESCRIBED FLOW:\n";
echo "==================================\n";
echo "User said: В активностях Отдел ПНР есть кнопка 'Работы по гарантии'\n";
echo "User said: В активностях 'Работы по гарантии' есть активность 'Электрооборудование'\n";
echo "User said: При нажатии кнопки 'Электрооборудование' ничего не происходит\n\n";

// Step 1: Check commissioning keyboard for warranty button
echo "Step 1: Check ПНР department keyboard for warranty button\n";
echo "------------------------------------------------------\n";

if (file_exists($messageServiceFile)) {
    $messageContent = file_get_contents($messageServiceFile);
    
    // Check if commissioning keyboard has warranty button
    if (strpos($messageContent, '🛡️ Работы по гарантии') !== false && 
        strpos($messageContent, 'cat_ТабГарантии') !== false) {
        echo "✅ Found: '🛡️ Работы по гарантии' button in commissioning keyboard\n";
        echo "   Callback: cat_ТабГарантии\n";
    } else {
        echo "❌ Missing: '🛡️ Работы по гарантии' button in commissioning keyboard\n";
    }
} else {
    echo "❌ MessageService file not found\n";
}

// Step 2: Check warranty equipment menu handler
echo "\nStep 2: Check warranty equipment menu handler\n";
echo "---------------------------------------------\n";

if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    // Check for warranty menu handler
    if (strpos($handlerContent, 'cat_ТабГарантии') !== false && 
        strpos($handlerContent, 'handleWarrantyEquipmentMenu') !== false) {
        echo "✅ Found: cat_ТабГарантии routes to handleWarrantyEquipmentMenu\n";
    } else {
        echo "❌ Missing: cat_ТабГарантии routing to handleWarrantyEquipmentMenu\n";
    }
    
    // Check for warranty equipment keyboard creation
    if (strpos($handlerContent, 'createWarrantyEquipmentKeyboard') !== false) {
        echo "✅ Found: Warranty equipment keyboard creation\n";
    } else {
        echo "❌ Missing: Warranty equipment keyboard creation\n";
    }
} else {
    echo "❌ TabelHandler file not found\n";
}

// Step 3: Check warranty equipment keyboard for electrical equipment
echo "\nStep 3: Check warranty equipment keyboard for electrical equipment\n";
echo "----------------------------------------------------------------\n";

if (file_exists($messageServiceFile)) {
    $messageContent = file_get_contents($messageServiceFile);
    
    // Check if warranty equipment keyboard has electrical equipment
    if (strpos($messageContent, '⚡ Электрооборудование') !== false && 
        strpos($messageContent, 'category_Табель_РабГарантии_ЭлектрОб') !== false) {
        echo "✅ Found: '⚡ Электрооборудование' button in warranty equipment keyboard\n";
        echo "   Callback: category_Табель_РабГарантии_ЭлектрОб\n";
    } else {
        echo "❌ Missing: '⚡ Электрооборудование' button in warranty equipment keyboard\n";
    }
}

// Step 4: Check electrical equipment warranty handler
echo "\nStep 4: Check electrical equipment warranty handler\n";
echo "--------------------------------------------------\n";

if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    // Check for electrical equipment warranty handler
    if (strpos($handlerContent, 'Табель_РабГарантии_ЭлектрОб') !== false) {
        echo "✅ Found: Табель_РабГарантии_ЭлектрОб handler\n";
        
        // Check if it creates confirmation
        if (strpos($handlerContent, 'cat_Гарантии_ЭлектрОб_yes') !== false) {
            echo "✅ Found: Confirmation callback cat_Гарантии_ЭлектрОб_yes\n";
        } else {
            echo "❌ Missing: Confirmation callback creation\n";
        }
    } else {
        echo "❌ Missing: Табель_РабГарантии_ЭлектрОб handler\n";
    }
}

// Step 5: Check activity execution for warranty confirmation
echo "\nStep 5: Check activity execution for warranty confirmation\n";
echo "---------------------------------------------------------\n";

if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    // Check for warranty confirmation in isActivityExecution
    if (strpos($handlerContent, 'cat_Гарантии_(.+)_yes') !== false) {
        echo "✅ Found: Warranty confirmation pattern in isActivityExecution\n";
    } else {
        echo "❌ Missing: Warranty confirmation pattern in isActivityExecution\n";
    }
    
    // Check for warranty confirmation in handleActivityExecution
    if (strpos($handlerContent, 'cat_Гарантии_(.+)_yes') !== false && 
        strpos($handlerContent, 'РабГарантии_ЭлектрОб') !== false) {
        echo "✅ Found: Warranty confirmation handling in handleActivityExecution\n";
    } else {
        echo "❌ Missing: Warranty confirmation handling in handleActivityExecution\n";
    }
}

// Summary
echo "\n=== FLOW SUMMARY ===\n";
echo "The complete flow should be:\n";
echo "1. User clicks 'ПНР' from main work types\n";
echo "2. User sees commissioning keyboard with '🛡️ Работы по гарантии' button\n";
echo "3. User clicks '🛡️ Работы по гарантии' → callback: cat_ТабГарантии\n";
echo "4. Bot shows warranty equipment keyboard with '⚡ Электрооборудование'\n";
echo "5. User clicks '⚡ Электрооборудование' → callback: category_Табель_РабГарантии_ЭлектрОб\n";
echo "6. Bot shows confirmation dialog with 'Да' button\n";
echo "7. User clicks 'Да' → callback: cat_Гарантии_ЭлектрОб_yes\n";
echo "8. Bot generates and sends warranty report\n\n";

echo "=== POSSIBLE ISSUES ===\n";
echo "If the user says 'nothing happens' when clicking 'Электрооборудование':\n";
echo "1. The server may need to be restarted to load updated code\n";
echo "2. The user's conversation state may be corrupted\n";
echo "3. The user may not have proper access level\n";
echo "4. The project code may not be set in conversation\n";
echo "5. The user may not have FIO or functional direction filled\n\n";

echo "=== RECOMMENDED SOLUTION ===\n";
echo "1. Have the user type /cancel and /start to reset conversation\n";
echo "2. Follow the complete flow: Select date → Select project → Select ПНР → Warranty\n";
echo "3. Restart the bot server if the issue persists\n";
echo "4. Check user's access level and profile completion\n";
?>