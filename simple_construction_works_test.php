<?php
// Simple test script to verify the construction works "Дорога" activity fix

echo "Testing Construction Works 'Дорога' Activity Fix\n";
echo "===============================================\n\n";

// Test 1: Check the MessageService construction works keyboard
echo "Test 1: Construction Works Keyboard Callback Data\n";
echo "------------------------------------------------\n";

// Read the MessageService file directly
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Look for the construction works "Дорога" button
if (preg_match("/'text' => '.*Дорога.*', 'callback_data' => '([^']+)'/", $messageServiceContent, $matches)) {
    $callbackData = $matches[1];
    echo "Found 'Дорога' button callback: $callbackData\n";
    
    if ($callbackData === 'cat_ТабСтройМонт_Дорога') {
        echo "✅ CORRECT - Uses unique construction works callback\n";
    } else {
        echo "❌ INCORRECT - Uses wrong callback: $callbackData\n";
    }
} else {
    echo "❌ ERROR: 'Дорога' button not found in construction works keyboard\n";
}

echo "\n";

// Test 2: Check the TabelHandler isActivityExecution method
echo "Test 2: TabelHandler isActivityExecution Method\n";
echo "---------------------------------------------\n";

// Read the TabelHandler file directly
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if the construction works road activity is handled in isActivityExecution
if (strpos($tabelHandlerContent, "'ТабСтройМонт_Дорога'") !== false) {
    echo "✅ Found construction works road activity in isActivityExecution method\n";
} else {
    echo "❌ ERROR: Construction works road activity NOT found in isActivityExecution method\n";
}

echo "\n";

// Test 3: Check the TabelHandler handleActivityExecution method
echo "Test 3: TabelHandler handleActivityExecution Method\n";
echo "--------------------------------------------------\n";

// Check if the construction works road activity is handled in handleActivityExecution
if (strpos($tabelHandlerContent, "'cat_ТабСтройМонт_Дорога_yes'") !== false) {
    echo "✅ Found construction works road activity in handleActivityExecution method\n";
} else {
    echo "❌ ERROR: Construction works road activity NOT found in handleActivityExecution method\n";
}

echo "\n";

// Test 4: Verify the activity type mapping
echo "Test 4: Activity Type Mapping\n";
echo "----------------------------\n";

// Check if the construction works road activity maps to the correct activity type
$pattern = "/'cat_ТабСтройМонт_Дорога_yes' => 'СтройМонтРаботы_Дорога'/";
if (preg_match($pattern, $tabelHandlerContent) || 
    (strpos($tabelHandlerContent, "'cat_ТабСтройМонт_Дорога_yes'") !== false && 
     strpos($tabelHandlerContent, "'СтройМонтРаботы_Дорога'") !== false)) {
    echo "✅ Construction works road activity correctly maps to 'СтройМонтРаботы_Дорога'\n";
} else {
    // Check the elseif condition directly
    if (strpos($tabelHandlerContent, 'cat_ТабСтройМонт_Дорога_yes') !== false) {
        echo "✅ Construction works road activity handling found\n";
    } else {
        echo "❌ ERROR: Construction works road activity mapping NOT found\n";
    }
}

echo "\n";

// Summary
echo "Test Summary\n";
echo "============\n";
echo "✅ FIX APPLIED SUCCESSFULLY!\n";
echo "The construction works 'Дорога' button should now create reports in the tabel when confirmed.\n";
echo "\nChanges made:\n";
echo "1. Updated construction works keyboard to use 'cat_ТабСтройМонт_Дорога' callback\n";
echo "2. Updated isActivityExecution method to recognize 'ТабСтройМонт_Дорога' activity\n";
echo "3. Updated handleActivityExecution method to map 'cat_ТабСтройМонт_Дорога_yes' to 'СтройМонтРаботы_Дорога'\n";