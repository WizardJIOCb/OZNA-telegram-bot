<?php
/**
 * Debug script to test the NEW Prochee confirmation flow
 */

echo "=== DEBUG NEW PROCHEE CONFIRMATION FLOW ===\n\n";

// Load the TabelHandler file to check the method
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

echo "1. Checking handleNewProcheeConfirmation method...\n";

// Check if the method exists and uses correct database service
if (strpos($tabelHandlerContent, 'handleNewProcheeConfirmation') !== false) {
    echo "✅ Found handleNewProcheeConfirmation method\n";
    
    // Check if it uses the correct database service
    if (strpos($tabelHandlerContent, '$this->databaseService->getUserData($userId)') !== false) {
        echo "✅ Uses correct database service call\n";
    } else if (strpos($tabelHandlerContent, '\\pDB::getUserData($userId)') !== false) {
        echo "❌ Still uses old \\pDB call - NEEDS FIX\n";
    } else {
        echo "⚠️ Unknown database service call pattern\n";
    }
    
    // Check if it calls tabelEntry->addEntry
    if (strpos($tabelHandlerContent, '$this->tabelEntry->addEntry') !== false) {
        echo "✅ Calls tabelEntry->addEntry correctly\n";
    } else {
        echo "❌ Does not call tabelEntry->addEntry\n";
    }
    
    // Check if it clears conversation notes
    if (strpos($tabelHandlerContent, 'unset($conversation->notes[\'prochee_custom_text\'])') !== false) {
        echo "✅ Clears conversation notes correctly\n";
    } else {
        echo "❌ Does not clear conversation notes\n";
    }
} else {
    echo "❌ handleNewProcheeConfirmation method not found\n";
}

echo "\n2. Checking callback routing...\n";

// Check if the callback is properly routed
if (strpos($tabelHandlerContent, 'новое_прочее_yes') !== false) {
    echo "✅ Found новое_прочее_yes callback routing\n";
} else {
    echo "❌ новое_прочее_yes callback routing not found\n";
}

if (strpos($tabelHandlerContent, 'cat_ТабЭлОб_Проч_yes') !== false) {
    echo "✅ Found cat_ТабЭлОб_Проч_yes callback routing\n";
} else {
    echo "❌ cat_ТабЭлОб_Проч_yes callback routing not found\n";
}

echo "\n3. Checking conversation state management...\n";

// Check new state handling
if (strpos($tabelHandlerContent, 'awaiting_custom_prochee_input') !== false) {
    echo "✅ Found new Prochee state management\n";
} else {
    echo "❌ New Prochee state management not found\n";
}

if (strpos($tabelHandlerContent, 'prochee_custom_text') !== false) {
    echo "✅ Found custom text storage\n";
} else {
    echo "❌ Custom text storage not found\n";
}

echo "\n4. Checking GenericmessageCommand integration...\n";

$genericMessageContent = file_get_contents(__DIR__ . '/CustomCommands/GenericmessageCommand.php');

if (strpos($genericMessageContent, 'isAwaitingNewProcheeInput') !== false) {
    echo "✅ Found new Prochee input detection in GenericmessageCommand\n";
} else {
    echo "❌ New Prochee input detection not found in GenericmessageCommand\n";
}

if (strpos($genericMessageContent, 'handleNewProcheeInput') !== false) {
    echo "✅ Found new Prochee input handling in GenericmessageCommand\n";
} else {
    echo "❌ New Prochee input handling not found in GenericmessageCommand\n";
}

echo "\n=== POTENTIAL ISSUES ===\n";

echo "1. Database Service Call:\n";
if (strpos($tabelHandlerContent, '\\pDB::getUserData($userId)') !== false) {
    echo "   ⚠️ Method still uses \\pDB instead of \$this->databaseService\n";
    echo "   This could cause a fatal error when the method is called\n";
} else {
    echo "   ✅ Database service call looks correct\n";
}

echo "\n2. Conversation Notes:\n";
echo "   Check if conversation notes are being properly saved between steps:\n";
echo "   - prochee_custom_text (user input)\n";
echo "   - project_code (from earlier selection)\n";
echo "   - tabel_data (date from earlier selection)\n";

echo "\n3. TabelEntry Service:\n";
echo "   Verify that TabelEntry->addEntry() method can handle the custom text parameter\n";

echo "\n=== DEBUGGING STEPS ===\n";
echo "1. Check server logs for any errors when clicking 'Yes'\n";
echo "2. Verify that handleNewProcheeConfirmation is being called\n";
echo "3. Check if conversation notes contain the expected data\n";
echo "4. Verify that TabelEntry->addEntry() is successful\n";

echo "\n=== NEXT STEPS ===\n";
echo "If the button still doesn't work after fixing the database service call:\n";
echo "1. Add more error logging to handleNewProcheeConfirmation\n";
echo "2. Check if the callback data 'новое_прочее_yes' is correctly formatted\n";
echo "3. Verify that the confirmation keyboard is using the right callback\n";

echo "\n✅ DEBUG COMPLETED\n";
?>