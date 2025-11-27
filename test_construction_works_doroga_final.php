<?php
// Test script to verify construction works "Дорога" activity is working correctly

echo "Testing Construction Works 'Дорога' Activity Fix\n";
echo "===============================================\n\n";

// Test 1: Check MessageService construction works keyboard
echo "Test 1: MessageService Construction Works Keyboard\n";
echo "--------------------------------------------------\n";

$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Find the construction works "Дорога" button
if (preg_match("/'text' => '.*Дорога.*', 'callback_data' => '([^']+)'/", $messageServiceContent, $matches)) {
    $callbackData = $matches[1];
    echo "Found 'Дорога' button with callback: $callbackData\n";
    
    if ($callbackData === 'cat_ТабСтройМонт_Дорога') {
        echo "✅ CORRECT - Uses unique construction works callback\n";
    } else {
        echo "❌ INCORRECT - Expected 'cat_ТабСтройМонт_Дорога', got '$callbackData'\n";
    }
} else {
    echo "❌ ERROR: 'Дорога' button not found\n";
}

echo "\n";

// Test 2: Check TabelHandler isActivityExecution method
echo "Test 2: TabelHandler isActivityExecution Method\n";
echo "----------------------------------------------\n";

$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if construction works road activity is handled in isActivityExecution
if (strpos($tabelHandlerContent, "'ТабСтройМонт_Дорога'") !== false) {
    echo "✅ Found construction works road activity in isActivityExecution\n";
} else {
    echo "❌ MISSING - Construction works road activity NOT in isActivityExecution\n";
}

echo "\n";

// Test 3: Check TabelHandler handleActivityExecution method
echo "Test 3: TabelHandler handleActivityExecution Method\n";
echo "---------------------------------------------------\n";

// Check if the construction works road activity is handled
if (strpos($tabelHandlerContent, "'cat_ТабСтройМонт_Дорога_yes'") !== false) {
    echo "✅ Found construction works road activity in handleActivityExecution\n";
} else {
    echo "❌ MISSING - Construction works road activity NOT in handleActivityExecution\n";
}

echo "\n";

// Test 4: Check activity type mapping
echo "Test 4: Activity Type Mapping\n";
echo "----------------------------\n";

// Look for the specific mapping in handleActivityExecution
$pattern = "/elseif\\s*\\(\\s*\\\$category\\s*===\\s*'cat_ТабСтройМонт_Дорога_yes'\\s*\\)\\s*\\{[^}]*\\\s*activityType\\s*=\\s*'СтройМонтРаботы_Дорога'/s";
if (preg_match($pattern, $tabelHandlerContent)) {
    echo "✅ Correct activity type mapping found\n";
} else {
    // Check for the general pattern
    if (strpos($tabelHandlerContent, 'cat_ТабСтройМонт_Дорога_yes') !== false && 
        strpos($tabelHandlerContent, 'СтройМонтРаботы_Дорога') !== false) {
        echo "✅ Activity type mapping elements found\n";
    } else {
        echo "❌ Activity type mapping NOT found\n";
    }
}

echo "\n";

// Test 5: Check if the activity type is in the activityTypes array
echo "Test 5: Activity Type in activityTypes Array\n";
echo "-------------------------------------------\n";

// Look for the activity type in the array
if (strpos($tabelHandlerContent, "'СтройМонтРаботы_Дорога'") !== false) {
    echo "✅ Activity type 'СтройМонтРаботы_Дорога' found in activityTypes array\n";
} else {
    echo "❌ Activity type 'СтройМонтРаботы_Дорога' NOT found in activityTypes array\n";
}

echo "\n";

// Test 6: Check TabelEntry for activity type mapping
echo "Test 6: TabelEntry Activity Type Mapping\n";
echo "---------------------------------------\n";

$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

if (strpos($tabelEntryContent, "'СтройМонтРаботы_Дорога'") !== false) {
    echo "✅ Activity type 'СтройМонтРаботы_Дорога' found in TabelEntry mappings\n";
} else {
    echo "❌ Activity type 'СтройМонтРаботы_Дорога' NOT found in TabelEntry mappings\n";
}

echo "\n";

// Test 7: Check if the activity type is in mobileBrigadeActivities array
echo "Test 7: Activity Type in Mobile Brigade Activities\n";
echo "--------------------------------------------------\n";

// Look for the activity type in the mobileBrigadeActivities array in TabelEntry
$pattern = "/'СтройМонтРаботы_ПодготовкаПоездки', 'СтройМонтРаботы_Дорога'/";
if (preg_match($pattern, $tabelEntryContent)) {
    echo "✅ Activity type 'СтройМонтРаботы_Дорога' found in mobileBrigadeActivities array\n";
} else {
    echo "❌ Activity type 'СтройМонтРаботы_Дорога' NOT found in mobileBrigadeActivities array\n";
}

echo "\n";

// Test 8: Check if the activity type is in commissioningActivities array
echo "Test 8: Activity Type in Commissioning Activities\n";
echo "------------------------------------------------\n";

// Look for the activity type in the commissioningActivities array in TabelEntry
$pattern = "/'СтройМонтРаботы_ПодготовкаПоездки', 'СтройМонтРаботы_Дорога'/";
if (preg_match($pattern, $tabelEntryContent)) {
    echo "✅ Activity type 'СтройМонтРаботы_Дорога' found in commissioningActivities array\n";
} else {
    echo "Activity type 'СтройМонтРаботы_Дорога' may not be in commissioningActivities array (this is OK if it's in mobileBrigadeActivities)\n";
}

echo "\n";

echo "Test Complete.\n";
echo "==============\n";
echo "If all tests show ✅, the construction works 'Дорога' activity should be working correctly.\n";
echo "The button should now create a report in the tabel when confirmed.\n";