<?php
// Debug script to check construction works "Дорога" activity implementation

echo "Debugging Construction Works 'Дорога' Activity\n";
echo "=============================================\n\n";

// Check MessageService construction works keyboard
echo "1. Checking MessageService construction works keyboard:\n";
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Find the construction works "Дорога" button
if (preg_match("/'text' => '.*Дорога.*', 'callback_data' => '([^']+)'/", $messageServiceContent, $matches)) {
    $callbackData = $matches[1];
    echo "   Found 'Дорога' button with callback: $callbackData\n";
    
    if ($callbackData === 'cat_ТабСтройМонт_Дорога') {
        echo "   ✅ CORRECT - Uses unique construction works callback\n";
    } else {
        echo "   ❌ INCORRECT - Expected 'cat_ТабСтройМонт_Дорога', got '$callbackData'\n";
    }
} else {
    echo "   ❌ ERROR: 'Дорога' button not found\n";
}

echo "\n";

// Check TabelHandler isActivityExecution method
echo "2. Checking TabelHandler isActivityExecution method:\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if construction works road activity is handled
if (strpos($tabelHandlerContent, "'ТабСтройМонт_Дорога'") !== false) {
    echo "   ✅ Found construction works road activity in isActivityExecution\n";
} else {
    echo "   ❌ MISSING - Construction works road activity NOT in isActivityExecution\n";
}

echo "\n";

// Check TabelHandler handleActivityExecution method
echo "3. Checking TabelHandler handleActivityExecution method:\n";

// Check if the construction works road activity is handled
if (strpos($tabelHandlerContent, "'cat_ТабСтройМонт_Дорога_yes'") !== false) {
    echo "   ✅ Found construction works road activity in handleActivityExecution\n";
} else {
    echo "   ❌ MISSING - Construction works road activity NOT in handleActivityExecution\n";
}

echo "\n";

// Check activity type mapping
echo "4. Checking activity type mapping:\n";

// Look for the specific mapping in handleActivityExecution
$pattern = "/elseif\\s*\\(\\s*\\\$category\\s*===\\s*'cat_ТабСтройМонт_Дорога_yes'\\s*\\)\\s*\\{[^}]*\\\s*activityType\\s*=\\s*'СтройМонтРаботы_Дорога'/s";
if (preg_match($pattern, $tabelHandlerContent)) {
    echo "   ✅ Correct activity type mapping found\n";
} else {
    // Check for the general pattern
    if (strpos($tabelHandlerContent, 'cat_ТабСтройМонт_Дорога_yes') !== false && 
        strpos($tabelHandlerContent, 'СтройМонтРаботы_Дорога') !== false) {
        echo "   ✅ Activity type mapping elements found\n";
    } else {
        echo "   ❌ Activity type mapping NOT found\n";
    }
}

echo "\n";

// Check if the activity type is in the activityTypes array
echo "5. Checking if activity type is in activityTypes array:\n";

// Look for the activity type in the array
if (strpos($tabelHandlerContent, "'СтройМонтРаботы_Дорога'") !== false) {
    echo "   ✅ Activity type 'СтройМонтРаботы_Дорога' found in activityTypes array\n";
} else {
    echo "   ❌ Activity type 'СтройМонтРаботы_Дорога' NOT found in activityTypes array\n";
}

echo "\n";

// Check TabelEntry for activity type mapping
echo "6. Checking TabelEntry for activity type mapping:\n";
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

if (strpos($tabelEntryContent, "'СтройМонтРаботы_Дорога'") !== false) {
    echo "   ✅ Activity type 'СтройМонтРаботы_Дорога' found in TabelEntry mappings\n";
} else {
    echo "   ❌ Activity type 'СтройМонтРаботы_Дорога' NOT found in TabelEntry mappings\n";
}

echo "\n";

echo "Debug complete.\n";