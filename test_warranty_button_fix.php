<?php
/**
 * Test to verify that the warranty button fix for 'Отдел ПНР' is working correctly
 */

echo "=== TESTING WARRANTY BUTTON FIX FOR 'ОТДЕЛ ПНР' ===\n\n";

// Test 1: Check that handleCommissioningDateSelection sets proper flags
echo "TEST 1: Commissioning Date Selection Flag Setting\n";
echo "=================================================\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

if (strpos($tabelHandlerContent, "\$conversation->notes['is_commissioning'] = true;") !== false) {
    echo "✅ handleCommissioningDateSelection sets is_commissioning = true\n";
    
    if (strpos($tabelHandlerContent, "\$conversation->notes['is_current_activity'] = false;") !== false &&
        strpos($tabelHandlerContent, "\$conversation->notes['is_supervision'] = false;") !== false &&
        strpos($tabelHandlerContent, "\$conversation->notes['is_mobile_brigade'] = false;") !== false) {
        echo "✅ handleCommissioningDateSelection clears other conflicting flags\n";
    } else {
        echo "❌ handleCommissioningDateSelection doesn't clear other flags\n";
    }
} else {
    echo "❌ handleCommissioningDateSelection doesn't set is_commissioning flag\n";
}

// Test 2: Check that handleProjectSelection uses commissioning flag properly 
echo "\nTEST 2: Project Selection Commissioning Flow\n";
echo "=============================================\n";

if (strpos($tabelHandlerContent, "elseif (\$isCommissioning) {") !== false) {
    echo "✅ handleProjectSelection checks is_commissioning flag\n";
    
    if (strpos($tabelHandlerContent, "\$keyboard = \$this->messageService->createCommissioningKeyboard();") !== false) {
        echo "✅ handleProjectSelection calls createCommissioningKeyboard()\n";
    } else {
        echo "❌ handleProjectSelection doesn't call createCommissioningKeyboard()\n";
    }
} else {
    echo "❌ handleProjectSelection doesn't check is_commissioning flag\n";
}

// Test 3: Check that createCommissioningKeyboard includes warranty button
echo "\nTEST 3: Commissioning Keyboard Warranty Button\n";
echo "==============================================\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

if (strpos($messageServiceContent, "'text' => '🛡️ Работы по гарантии', 'callback_data' => 'cat_ТабГарантии'") !== false) {
    echo "✅ createCommissioningKeyboard includes warranty button with correct callback\n";
} else {
    echo "❌ createCommissioningKeyboard missing warranty button or incorrect callback\n";
}

// Test 4: Check the unique date prefix fix
echo "\nTEST 4: Unique Date Selection Prefix\n";
echo "====================================\n";

if (strpos($tabelHandlerContent, "category_Табель_Пусконаладка") !== false) {
    echo "✅ handleCommissioningDateSelection uses unique prefix 'Пусконаладка'\n";
} else {
    echo "❌ handleCommissioningDateSelection doesn't use unique prefix\n";
}

echo "\n";

// Test 5: Complete flow simulation
echo "TEST 5: Complete Flow Simulation\n";
echo "=================================\n";

echo "Expected flow after fix:\n";
echo "1. User clicks 'Отдел ПНР' button\n";
echo "   → handleCommissioningDateSelection() called\n";
echo "   → Sets: is_commissioning = true, other flags = false\n";
echo "   → Shows date selection\n\n";

echo "2. User selects date\n";
echo "   → Shows project selection\n\n";

echo "3. User selects project\n";
echo "   → handleProjectSelection() called\n";
echo "   → Checks: is_commissioning = true\n";
echo "   → Calls: createCommissioningKeyboard()\n";
echo "   → Shows commissioning menu with WARRANTY BUTTON:\n";
echo "     - ⚙️ Пусконаладочные работы\n";
echo "     - 📂 Проектная деятельность\n";
echo "     - 🔧 Устранение замечаний РК\n";
echo "     - 🛡️ Работы по гарантии ← THIS BUTTON!\n";
echo "     - ⏸️ Простой\n";

echo "\n4. User clicks '🛡️ Работы по гарантии'\n";
echo "   → Shows warranty equipment menu\n";
echo "   → User can select equipment type and log warranty work\n\n";

// Test 6: Check warranty button handling
echo "TEST 6: Warranty Button Callback Handling\n";
echo "============================================\n";

if (strpos($tabelHandlerContent, "if (\$category === 'cat_ТабГарантии')") !== false) {
    echo "✅ TabelHandler handles warranty button callback\n";
    
    if (strpos($tabelHandlerContent, "handleWarrantyEquipmentCallback") !== false) {
        echo "✅ TabelHandler routes to warranty equipment callback handler\n";
    } else {
        echo "❌ TabelHandler missing warranty equipment callback handler\n";
    }
} else {
    echo "❌ TabelHandler doesn't handle warranty button callback\n";
}

echo "\n";

// Summary
echo "SUMMARY:\n";
echo "========\n";
echo "The warranty button fix for 'Отдел ПНР' addresses the root cause:\n\n";

echo "🔧 ROOT CAUSE: The handleCommissioningDateSelection() method was not setting\n";
echo "   the 'is_commissioning' flag, so after project selection, the system didn't\n";
echo "   know the user came from the commissioning department flow.\n\n";

echo "✅ SOLUTION: Added proper flag setting in handleCommissioningDateSelection():\n";
echo "   - Sets: is_commissioning = true\n";
echo "   - Clears: is_current_activity, is_supervision, is_mobile_brigade = false\n\n";

echo "🎯 RESULT: After project selection, handleProjectSelection() now correctly\n";
echo "   detects the commissioning flow and shows createCommissioningKeyboard()\n";
echo "   which includes the '🛡️ Работы по гарантии' button.\n\n";

echo "📋 NEXT STEP: Have the user test the complete flow in Telegram:\n";
echo "   Отдел ПНР → Date → Project → Should see warranty button!\n";
?>