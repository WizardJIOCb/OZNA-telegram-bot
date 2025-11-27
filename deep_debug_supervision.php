<?php

echo "=== DEEP DEBUG: Why Sub-activities Don't Appear ===\n\n";

// Read the TabelHandler file
$tabelContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

echo "1. CHECKING EARLY ROUTING POSITIONS:\n";

// Find warranty works early routing
$warrantyPattern = "if (\$category === 'Табель_ШМ_РабГарантии') {";
$warrantyPos = strpos($tabelContent, $warrantyPattern);

// Find supervision installation works early routing
$supervisionPattern = "if (\$category === 'Табель_ШМ_ШМР') {";
$supervisionPos = strpos($tabelContent, $supervisionPattern);

if ($warrantyPos !== false) {
    echo "   ✅ Warranty early routing found at position: {$warrantyPos}\n";
} else {
    echo "   ❌ Warranty early routing NOT found\n";
}

if ($supervisionPos !== false) {
    echo "   ✅ Supervision early routing found at position: {$supervisionPos}\n";
} else {
    echo "   ❌ Supervision early routing NOT found\n";
}

echo "\n2. CHECKING IF THEY'RE IN THE RIGHT METHOD:\n";

// Check if they're in the main handle() method
$handleMethodStart = strpos($tabelContent, 'public function handle(CallbackQuery $callbackQuery');
$handleOtherWorkTypesStart = strpos($tabelContent, 'private function handleOtherWorkTypes(');

if ($handleMethodStart !== false && $warrantyPos !== false) {
    if ($warrantyPos > $handleMethodStart && ($handleOtherWorkTypesStart === false || $warrantyPos < $handleOtherWorkTypesStart)) {
        echo "   ✅ Warranty routing is in main handle() method\n";
    } else {
        echo "   ❌ Warranty routing is NOT in main handle() method\n";
    }
}

if ($handleMethodStart !== false && $supervisionPos !== false) {
    if ($supervisionPos > $handleMethodStart && ($handleOtherWorkTypesStart === false || $supervisionPos < $handleOtherWorkTypesStart)) {
        echo "   ✅ Supervision routing is in main handle() method\n";
    } else {
        echo "   ❌ Supervision routing is NOT in main handle() method\n";
    }
}

echo "\n3. CHECKING CALLBACK DATA IN MESSAGESERVICE:\n";

$messageServiceContent = file_get_contents('c:\ozna_bot\CustomCommands\Services\MessageService.php');

// Check supervision keyboard in supervision menu
if (strpos($messageServiceContent, "🔧 Шеф-монтажные работы") !== false) {
    echo "   ✅ Supervision installation works button exists in supervision menu\n";
    
    // Extract the callback data
    if (preg_match("/'text' => '🔧 Шеф-монтажные работы', 'callback_data' => '([^']+)'/", $messageServiceContent, $matches)) {
        $callbackData = $matches[1];
        echo "   Callback data: '{$callbackData}'\n";
        
        if ($callbackData === 'category_Табель_ШМ_ШМР') {
            echo "   ✅ Callback data is correct\n";
        } else {
            echo "   ❌ Callback data is WRONG - should be 'category_Табель_ШМ_ШМР'\n";
        }
    }
} else {
    echo "   ❌ Supervision installation works button NOT found\n";
}

echo "\n4. CHECKING IF METHODS EXIST:\n";

if (strpos($tabelContent, 'handleSupervisionInstallationWorksMenu') !== false) {
    echo "   ✅ handleSupervisionInstallationWorksMenu method exists\n";
} else {
    echo "   ❌ handleSupervisionInstallationWorksMenu method MISSING\n";
}

if (strpos($messageServiceContent, 'createSupervisionInstallationWorksKeyboard') !== false) {
    echo "   ✅ createSupervisionInstallationWorksKeyboard method exists\n";
} else {
    echo "   ❌ createSupervisionInstallationWorksKeyboard method MISSING\n";
}

echo "\n5. CHECKING ROUTING ORDER CONFLICTS:\n";

// Check if there are other handlers that might catch this callback first
$activityExecutionPos = strpos($tabelContent, 'if ($this->isActivityExecution($category))');
$activityConfirmationPos = strpos($tabelContent, 'if ($this->isActivityConfirmation($category))');

if ($supervisionPos !== false && $activityExecutionPos !== false) {
    if ($supervisionPos < $activityExecutionPos) {
        echo "   ✅ Supervision routing comes BEFORE activity execution check\n";
    } else {
        echo "   ❌ Activity execution check comes BEFORE supervision routing (PROBLEM!)\n";
    }
}

echo "\n6. POSSIBLE ISSUES:\n";
echo "   A) The early routing might not be in the right place\n";
echo "   B) Another handler might be catching the callback first\n";
echo "   C) The callback data might be wrong\n";
echo "   D) There might be an exception being thrown\n";

echo "\n7. NEXT STEPS:\n";
echo "   - Check the bot logs for 🎯 debug messages when clicking the button\n";
echo "   - Verify the exact callback data being sent\n";
echo "   - Make sure no other handlers are intercepting the callback\n";

?>