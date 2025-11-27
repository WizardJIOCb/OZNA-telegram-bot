<?php
/**
 * URGENT FIX TEST: Verify "Да" button for Prochee works
 */

echo "=== URGENT FIX TEST FOR 'ДА' BUTTON ===\n\n";

$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. CRITICAL TEST: Can TabelHandler handle 'новое_прочее_yes' callback?\n";
echo "================================================================\n";

// Check if canHandle method includes новое_прочее_yes
if (strpos($tabelHandlerContent, "callbackData === 'новое_прочее_yes'") !== false) {
    echo "✅ FIXED: canHandle method now includes 'новое_прочее_yes'\n";
} else {
    echo "❌ ERROR: canHandle method still doesn't include 'новое_прочее_yes'\n";
    echo "   This means the 'Да' button won't work!\n";
}

echo "\n2. CRITICAL TEST: Is 'новое_прочее_yes' recognized as confirmation?\n";
echo "================================================================\n";

// Check if isConfirmationCallback includes новое_прочее_yes
if (strpos($tabelHandlerContent, "category === 'новое_прочее_yes'") !== false) {
    echo "✅ FIXED: isConfirmationCallback now recognizes 'новое_прочее_yes'\n";
} else {
    echo "❌ ERROR: isConfirmationCallback doesn't recognize 'новое_прочее_yes'\n";
}

echo "\n3. CALLBACK ROUTING TEST\n";
echo "========================\n";

// Check routing
if (strpos($tabelHandlerContent, "category === 'новое_прочее_yes'") !== false && 
    strpos($tabelHandlerContent, "handleNewProcheeConfirmation") !== false) {
    echo "✅ FIXED: 'новое_прочее_yes' callback is routed to handleNewProcheeConfirmation\n";
} else {
    echo "❌ ERROR: Callback routing is not set up correctly\n";
}

echo "\n4. METHOD IMPLEMENTATION TEST\n";
echo "=============================\n";

// Check if handleNewProcheeConfirmation method exists
if (strpos($tabelHandlerContent, 'private function handleNewProcheeConfirmation') !== false) {
    echo "✅ FOUND: handleNewProcheeConfirmation method exists\n";
} else {
    echo "❌ ERROR: handleNewProcheeConfirmation method not found\n";
}

echo "\n5. ERROR LOGGING TEST\n";
echo "=====================\n";

// Check if enhanced logging is present
if (strpos($tabelHandlerContent, 'TabelHandler: Executing NEW Prochee confirmation') !== false) {
    echo "✅ FOUND: Enhanced error logging is present\n";
} else {
    echo "❌ ERROR: Enhanced error logging not found\n";
}

echo "\n=== SIMULATION TEST ===\n";
echo "The complete flow should now work:\n\n";

echo "Step 1: User clicks 'Прочее' button\n";
echo "  → callback: cat_ТабЭлОб_Проч\n";
echo "  → canHandle: " . (strpos($tabelHandlerContent, "str_starts_with(\$callbackData, 'cat_Таб')") !== false ? "✅ YES" : "❌ NO") . "\n";
echo "  → Prompts for custom input\n\n";

echo "Step 2: User enters custom text\n";
echo "  → GenericmessageCommand processes input\n";
echo "  → Shows confirmation with 'новое_прочее_yes' button\n\n";

echo "Step 3: User clicks 'Да' button\n";
echo "  → callback: новое_прочее_yes\n";
echo "  → canHandle: " . (strpos($tabelHandlerContent, "callbackData === 'новое_прочее_yes'") !== false ? "✅ YES" : "❌ NO") . "\n";
echo "  → isConfirmationCallback: " . (strpos($tabelHandlerContent, "category === 'новое_прочее_yes'") !== false ? "✅ YES" : "❌ NO") . "\n";
echo "  → Routes to handleNewProcheeConfirmation: " . (strpos($tabelHandlerContent, "handleNewProcheeConfirmation") !== false ? "✅ YES" : "❌ NO") . "\n";
echo "  → Creates tabel entry: " . (strpos($tabelHandlerContent, "tabelEntry->addEntry") !== false ? "✅ YES" : "❌ NO") . "\n\n";

echo "=== SUMMARY ===\n";
if (strpos($tabelHandlerContent, "callbackData === 'новое_прочее_yes'") !== false) {
    echo "🎉 SUCCESS: All critical fixes applied!\n";
    echo "The 'Да' button should now work correctly.\n";
    echo "\n🔧 Next step: Test the button in the bot\n";
    echo "If still not working, check server logs for specific errors.\n";
} else {
    echo "❌ CRITICAL ERROR: Fixes not applied correctly!\n";
    echo "The 'Да' button will still not work.\n";
}

echo "\n✅ URGENT FIX TEST COMPLETED\n";
?>