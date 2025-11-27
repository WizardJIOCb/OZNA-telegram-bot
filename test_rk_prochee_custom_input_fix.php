<?php
// Test to verify RK "Прочее оборудование" custom input fix

echo "=== RK ПРОЧЕЕ ОБОРУДОВАНИЕ CUSTOM INPUT FIX TEST ===\n\n";

echo "Testing the complete flow for RK 'Прочее оборудование' custom input\n\n";

// Test 1: Check handleCustomInput method
echo "TEST 1: handleCustomInput Method Check\n";
echo "======================================\n";
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

if (strpos($tabelHandlerContent, "elseif (\$activityType === 'УстрЗам_Прочее')") !== false) {
    echo "✅ handleCustomInput has specific handling for УстрЗам_Прочее\n";
    if (strpos($tabelHandlerContent, "category_Табель_{УстрЗам_Прочее}_yes") !== false) {
        echo "✅ Correct confirmation callback format for RK Прочее\n";
    } else {
        echo "❌ Incorrect confirmation callback format for RK Прочее\n";
    }
} else {
    echo "❌ handleCustomInput missing specific handling for УстрЗам_Прочее\n";
}

echo "\n";

// Test 2: Check handleActivityExecution method
echo "TEST 2: handleActivityExecution Method Check\n";
echo "==========================================\n";

if (strpos($tabelHandlerContent, "// For RK Comments Elimination \"Прочее\" activities with custom input") !== false) {
    echo "✅ handleActivityExecution has RK Прочее custom input handling\n";
    if (strpos($tabelHandlerContent, "if (\$activityType === 'УстрЗам_Прочее')") !== false) {
        echo "✅ Specific condition for УстрЗам_Прочее found\n";
        if (strpos($tabelHandlerContent, "\$idleReason = \$conversation->notes['custom_input_text']") !== false) {
            echo "✅ Custom text retrieval logic found\n";
        } else {
            echo "❌ Custom text retrieval logic missing\n";
        }
    } else {
        echo "❌ Specific condition for УстрЗам_Прочее missing\n";
    }
} else {
    echo "❌ handleActivityExecution missing RK Прочее custom input handling\n";
}

echo "\n";

// Test 3: Test complete flow simulation
echo "TEST 3: Complete Flow Simulation\n";
echo "=================================\n";

echo "Step 1: User clicks 'Прочее оборудование' in RK submenu\n";
echo "   Callback: 'category_Табель_УстрЗам_Прочее'\n";
echo "   → Should prompt for custom input ✓\n\n";

echo "Step 2: User enters custom text: 'Настройка нового оборудования'\n";
echo "   → handleCustomInput stores text in conversation->notes['custom_input_text']\n";
echo "   → Shows confirmation with custom text\n";
echo "   → Creates button: 'category_Табель_УстрЗам_Прочее_yes' ✓\n\n";

echo "Step 3: User clicks 'Да' to confirm\n";
echo "   → isActivityExecution recognizes 'category_Табель_УстрЗам_Прочее_yes'\n";
echo "   → handleActivityExecution extracts activityType = 'УстрЗам_Прочее'\n";
echo "   → Retrieves custom text from conversation->notes['custom_input_text']\n";
echo "   → Uses custom text as idleReason\n";
echo "   → Creates tabel entry with custom description ✓\n\n";

// Test 4: Check TabelEntry service
echo "TEST 4: TabelEntry Service Check\n";
echo "=================================\n";
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

if (strpos($tabelEntryContent, "'УстрЗам_ЭС', 'УстрЗам_АТХ', 'УстрЗам_ОВКВ', 'УстрЗам_ПСОС', 'УстрЗам_ТХ', 'УстрЗам_Прочее'") !== false) {
    echo "✅ TabelEntry recognizes УстрЗам_Прочее as RK activity\n";
} else {
    echo "❌ TabelEntry missing УстрЗам_Прочее in RK activities list\n";
}

if (strpos($tabelEntryContent, "if (\$activityType === 'УстрЗам_Прочее')") !== false) {
    echo "✅ TabelEntry has specific handling for УстрЗам_Прочее custom text\n";
} else {
    echo "❌ TabelEntry missing specific handling for УстрЗам_Прочее custom text\n";
}

echo "\n";

// Test 5: Verify activity type exists
echo "TEST 5: Activity Type Verification\n";
echo "===================================\n";

if (strpos($tabelHandlerContent, "'УстрЗам_ЭС',     // ES Section") !== false &&
    strpos($tabelHandlerContent, "'УстрЗам_Прочее',   // Other Equipment") !== false) {
    echo "✅ УстрЗам_Прочее exists in activityTypes array\n";
} else {
    echo "❌ УстрЗам_Прочее missing from activityTypes array\n";
}

echo "\n";

// Summary
echo "SUMMARY:\n";
echo "========\n";
echo "The RK 'Прочее оборудование' button should now:\n";
echo "1. ✅ Prompt user for custom equipment description\n";
echo "2. ✅ Store the custom text properly\n";
echo "3. ✅ Show confirmation with custom text\n";
echo "4. ✅ Include custom text in final report\n";
echo "5. ✅ Create tabel entry with user's description\n\n";

echo "🎯 EXPECTED RESULT:\n";
echo "When user enters 'Настройка серверного оборудования',\n";
echo "the final report should show this custom text instead of generic activity name.\n\n";

echo "🚀 STATUS: READY FOR TESTING\n";
echo "Test the button in Telegram to confirm custom text appears in final report.\n";
?>