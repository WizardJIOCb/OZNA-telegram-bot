<?php
// MASTER DUTIES BUTTON DIAGNOSTIC TOOL
// This tool helps identify the exact issue in production environment

echo "=== MASTER DUTIES BUTTON DIAGNOSTIC ===\n";
echo "Run this script to identify the production issue\n";
echo "==========================================\n\n";

// Step 1: Check if the mapping exists
echo "1. CHECKING CALLBACK MAPPING:\n";
echo "-----------------------------\n";

// Read the actual TabelHandler.php file to check mapping
$tabelHandlerFile = 'CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    $content = file_get_contents($tabelHandlerFile);
    
    // Check for the specific mapping
    if (strpos($content, "'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера'") !== false) {
        echo "✅ Callback mapping EXISTS in file\n";
    } else {
        echo "❌ Callback mapping MISSING from file\n";
        echo "PROBLEM: The mapping is not in the file!\n";
    }
    
    // Check for the handler condition
    if (strpos($content, "if (\$category === 'Табель_ИсполнениеОбязМастера' || \$category === 'cat_ТабИсполнОбяз')") !== false) {
        echo "✅ Handler condition EXISTS in file\n";
    } else {
        echo "❌ Handler condition MISSING from file\n";
        echo "PROBLEM: The handler condition is not in the file!\n";
    }
    
    // Check for activityTypes
    if (strpos($content, "'ИсполнениеОбязМастера'") !== false) {
        echo "✅ Activity type EXISTS in file\n";
    } else {
        echo "❌ Activity type MISSING from file\n";
        echo "PROBLEM: ИсполнениеОбязМастера is not in activityTypes!\n";
    }
    
} else {
    echo "❌ TabelHandler.php file NOT FOUND\n";
    echo "PROBLEM: File path is incorrect!\n";
}

echo "\n2. CHECKING MESSAGE SERVICE BUTTON:\n";
echo "-----------------------------------\n";

// Check MessageService for button creation
$messageServiceFile = 'CustomCommands/Services/MessageService.php';
if (file_exists($messageServiceFile)) {
    $content = file_get_contents($messageServiceFile);
    
    // Check for the button definition
    if (strpos($content, "'callback_data' => 'cat_ТабИсполнОбяз'") !== false) {
        echo "✅ Button definition EXISTS in MessageService\n";
    } else {
        echo "❌ Button definition MISSING from MessageService\n";
        echo "PROBLEM: Button is not being created!\n";
    }
} else {
    echo "❌ MessageService.php file NOT FOUND\n";
}

echo "\n3. CHECKING TABEL ENTRY ACTIVITY MAP:\n";
echo "-------------------------------------\n";

// Check TabelEntry for activity mapping
$tabelEntryFile = 'CustomCommands/Services/TabelEntry.php';
if (file_exists($tabelEntryFile)) {
    $content = file_get_contents($tabelEntryFile);
    
    // Check for the activity mapping
    if (strpos($content, "'ИсполнениеОбязМастера' => '👷 Исполнение обязанностей мастера'") !== false) {
        echo "✅ Activity mapping EXISTS in TabelEntry\n";
    } else {
        echo "❌ Activity mapping MISSING from TabelEntry\n";
        echo "PROBLEM: Activity text mapping is missing!\n";
    }
} else {
    echo "❌ TabelEntry.php file NOT FOUND\n";
}

echo "\n4. RECOMMENDED DEBUG STEPS:\n";
echo "-----------------------------\n";

echo "To identify the exact issue, add temporary debug logging:\n\n";

echo "A) Add to TabelHandler->handle() method (around line 580):\n";
echo "   error_log(\"DEBUG: Received callback: \$callbackData\");\n";
echo "   error_log(\"DEBUG: Extracted category: \$category\");\n\n";

echo "B) Add to handleOtherWorkTypes() method (around line 2820):\n";
echo "   error_log(\"DEBUG: handleOtherWorkTypes called with: \$category\");\n";
echo "   if (\$category === 'Табель_ИсполнениеОбязМастера' || \$category === 'cat_ТабИсполнОбяз') {\n";
echo "       error_log(\"DEBUG: MASTER DUTIES HANDLER TRIGGERED!\");\n";
echo "   }\n\n";

echo "C) Check server error logs after clicking the button\n\n";

echo "5. IMMEDIATE FIXES TO TRY:\n";
echo "----------------------------\n";

echo "If mappings are missing, add these to TabelHandler.php:\n\n";

echo "A) In extractCategoryFromCallback method shortFormats array:\n";
echo "   'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',\n";
echo "   'cat_ТабИсполнОбяз_yes' => 'Табель_ИсполнениеОбязМастера_yes',\n\n";

echo "B) In handleOtherWorkTypes method (around line 2820):\n";
echo "   if (\$category === 'Табель_ИсполнениеОбязМастера' || \$category === 'cat_ТабИсполнОбяз') {\n";
echo "       error_log(\"TabelHandler: handling master duties execution\");\n";
echo "       \$projectCode = \$conversation->notes['project_code'] ?? 'Unknown';\n";
echo "       \$confirmationText = \$this->tabelEntry->getConfirmationMessage('ИсполнениеОбязМастера', \$projectCode);\n";
echo "       \$keyboard = \$this->messageService->createConfirmationKeyboard(\"cat_ТабИсполнОбяз_yes\");\n";
echo "       return \$this->messageService->sendMessage(\$chatId, \$confirmationText, \$keyboard);\n";
echo "   }\n\n";

echo "C) In activityTypes array (around line 82):\n";
echo "   'ИсполнениеОбязМастера',\n\n";

echo "D) In isActivityExecution method projectActivityCallbacks array (around line 1555):\n";
echo "   'ТабИсполнОбяз' => 'ИсполнениеОбязМастера'\n\n";

echo "After making changes, restart the web server/PHP-FPM to ensure changes take effect.\n\n";

echo "=== DIAGNOSTIC COMPLETE ===\n";
echo "Follow the recommended steps above to identify and fix the issue.\n";
?>