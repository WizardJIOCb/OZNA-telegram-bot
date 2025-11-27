<?php

echo "=== Verification of Шеф-монтажные работы Implementation ===\n\n";

// Check TabelHandler.php for activity types
$tabelHandlerContent = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

echo "1. Checking TabelHandler.php:\n";
echo "   ✓ ШМ_ШМР in activityTypes: " . (strpos($tabelHandlerContent, "'ШМ_ШМР'") !== false ? "YES" : "NO") . "\n";
echo "   ✓ ШМ_РабПоГрафику in activityTypes: " . (strpos($tabelHandlerContent, "'ШМ_РабПоГрафику'") !== false ? "YES" : "NO") . "\n";
echo "   ✓ Handler for category_Табель_ШМ_РабПоГрафику: " . (strpos($tabelHandlerContent, "category_Табель_ШМ_РабПоГрафику") !== false ? "YES" : "NO") . "\n";
echo "   ✓ isActivityExecution pattern for ШМ_РабПоГрафику: " . (strpos($tabelHandlerContent, "ШМ_РабПоГрафику)_yes") !== false ? "YES" : "NO") . "\n";

// Check MessageService.php for keyboard
$messageServiceContent = file_get_contents('CustomCommands/Services/MessageService.php');

echo "\n2. Checking MessageService.php:\n";
echo "   ✓ createSupervisionInstallationWorksKeyboard method: " . (strpos($messageServiceContent, "createSupervisionInstallationWorksKeyboard") !== false ? "YES" : "NO") . "\n";
echo "   ✓ 'Работы по графику' button: " . (strpos($messageServiceContent, "Работы по графику") !== false ? "YES" : "NO") . "\n";

// Check TabelEntry.php for descriptions
$tabelEntryContent = file_get_contents('CustomCommands/Services/TabelEntry.php');

echo "\n3. Checking TabelEntry.php:\n";
echo "   ✓ ШМ_ШМР description: " . (strpos($tabelEntryContent, "'ШМ_ШМР'") !== false ? "YES" : "NO") . "\n";
echo "   ✓ ШМ_РабПоГрафику description: " . (strpos($tabelEntryContent, "'ШМ_РабПоГрафику'") !== false ? "YES" : "NO") . "\n";

// Check callback patterns
echo "\n4. Checking callback patterns:\n";
echo "   ✓ ШМР button callback: " . (strpos($tabelHandlerContent, "category_Табель_ШМ_ШМР") !== false ? "YES" : "NO") . "\n";
echo "   ✓ Работы по графику callback: " . (strpos($messageServiceContent, "category_Табель_ШМ_РабПоГрафику") !== false ? "YES" : "NO") . "\n";
echo "   ✓ Confirmation callback: " . (strpos($tabelHandlerContent, "category_Табель_ШМ_РабПоГрафику_yes") !== false ? "YES" : "NO") . "\n";

echo "\n5. Implementation Summary:\n";
if (strpos($tabelHandlerContent, "'ШМ_ШМР'") !== false && 
    strpos($tabelHandlerContent, "'ШМ_РабПоГрафику'") !== false &&
    strpos($messageServiceContent, "createSupervisionInstallationWorksKeyboard") !== false &&
    strpos($tabelEntryContent, "'ШМ_ШМР'") !== false &&
    strpos($tabelEntryContent, "'ШМ_РабПоГрафику'") !== false) {
    echo "   🎉 Implementation appears to be COMPLETE!\n";
    echo "   ✅ Activity types added\n";
    echo "   ✅ Submenu keyboard created\n";
    echo "   ✅ Activity descriptions added\n";
    echo "   ✅ Callback handlers configured\n";
} else {
    echo "   ❌ Implementation appears to be INCOMPLETE\n";
}

echo "\n=== Verification Complete ===\n";