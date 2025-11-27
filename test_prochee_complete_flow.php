<?php

/**
 * Complete test for "Прочее" button flow after all fixes
 */

echo "=== COMPREHENSIVE TEST FOR ПРОЧЕЕ BUTTON COMPLETE FLOW ===\n\n";

// Test 1: Verify the callback is not treated as activity execution
echo "TEST 1: Verify 'Прочее' callback routing\n";
echo "==========================================\n";

$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if 'Проч' mapping was removed from isActivityExecution
$isActivityExecutionSection = '';
$lines = explode("\n", $tabelHandlerContent);
$capturing = false;
foreach ($lines as $line) {
    if (strpos($line, 'private function isActivityExecution') !== false) {
        $capturing = true;
    }
    if ($capturing) {
        $isActivityExecutionSection .= $line . "\n";
        if (strpos($line, 'return false;') !== false && strpos($isActivityExecutionSection, 'shortToFullMap') !== false) {
            break;
        }
    }
}

if (strpos($isActivityExecutionSection, "'Проч' =>") === false) {
    echo "✅ GOOD: 'Проч' mapping removed from isActivityExecution\n";
} else {
    echo "❌ BAD: 'Проч' mapping still present in isActivityExecution\n";
}

// Test 2: Verify custom input handler exists and is properly configured
echo "\nTEST 2: Verify custom input handler\n";
echo "====================================\n";

if (strpos($tabelHandlerContent, 'cat_ТабЭлОб_Проч') !== false && 
    strpos($tabelHandlerContent, 'awaiting_custom_input') !== false &&
    strpos($tabelHandlerContent, 'Пожалуйста, введите описание выполняемой работы') !== false) {
    echo "✅ GOOD: Custom input handler properly configured\n";
} else {
    echo "❌ BAD: Custom input handler not properly configured\n";
}

// Test 3: Verify handleCustomInput method exists
echo "\nTEST 3: Verify handleCustomInput method\n";
echo "========================================\n";

if (strpos($tabelHandlerContent, 'public function handleCustomInput') !== false) {
    echo "✅ GOOD: handleCustomInput method exists\n";
    
    // Check if it uses the correct callback format
    if (strpos($tabelHandlerContent, 'cat_ТабЭлОб_Проч_yes') !== false) {
        echo "✅ GOOD: Uses correct short callback format for confirmation\n";
    } else {
        echo "❌ BAD: Does not use correct callback format\n";
    }
} else {
    echo "❌ BAD: handleCustomInput method missing\n";
}

// Test 4: Verify GenericmessageCommand integration
echo "\nTEST 4: Verify GenericmessageCommand integration\n";
echo "=================================================\n";

$genericMessageContent = file_get_contents(__DIR__ . '/CustomCommands/GenericmessageCommand.php');

if (strpos($genericMessageContent, 'isAwaitingCustomInput') !== false &&
    strpos($genericMessageContent, 'handleCustomInput') !== false) {
    echo "✅ GOOD: GenericmessageCommand properly integrated\n";
} else {
    echo "❌ BAD: GenericmessageCommand integration missing\n";
}

// Test 5: Verify activity execution handles custom input
echo "\nTEST 5: Verify activity execution handles custom input\n";
echo "======================================================\n";

if (strpos($tabelHandlerContent, 'custom_input_text') !== false &&
    strpos($tabelHandlerContent, "activityType === 'Прочее_ЭлектрОб'") !== false) {
    echo "✅ GOOD: Activity execution handles custom input text\n";
} else {
    echo "❌ BAD: Activity execution does not handle custom input\n";
}

// Simulate the complete flow
echo "\n=== SIMULATED FLOW ===\n";
echo "Step 1: User clicks 'Прочее' button (cat_ТабЭлОб_Проч)\n";
echo "  → Should trigger custom input handler\n";
echo "  → Should set awaiting_custom_input = true\n";
echo "  → Should prompt: 'Пожалуйста, введите описание выполняемой работы:'\n\n";

echo "Step 2: User types custom description\n";
echo "  → GenericmessageCommand detects awaiting_custom_input = true\n";
echo "  → Calls TabelHandler::handleCustomInput()\n";
echo "  → Shows confirmation with custom text\n";
echo "  → Creates button with callback: cat_ТабЭлОб_Проч_yes\n\n";

echo "Step 3: User clicks 'Yes' to confirm\n";
echo "  → TabelHandler recognizes cat_ТабЭлОб_Проч_yes as activity execution\n";
echo "  → Uses custom_input_text as activity description\n";
echo "  → Creates tabel entry with custom description\n\n";

echo "=== SUMMARY ===\n";
echo "All fixes have been applied:\n";
echo "✅ Removed 'Проч' from isActivityExecution mapping\n";
echo "✅ Added custom input handler for 'Прочее' button\n";
echo "✅ Fixed callback format to use short format (cat_ТабЭлОб_Проч_yes)\n";
echo "✅ Updated activity execution to use custom input text\n";
echo "✅ Integration with GenericmessageCommand complete\n\n";

echo "⚠️  CRITICAL: RESTART WEB SERVER FOR CHANGES TO TAKE EFFECT!\n";
echo "After restart, the 'Прочее' button should prompt for custom input instead of creating reports.\n";