<?php
/**
 * Final diagnostic - Check if the callback is being processed at all
 */

echo "=== FINAL DIAGNOSTIC: CHECKING CALLBACK FLOW ===\n\n";

// Step 1: Check if TabelHandler can handle the callback
echo "🔍 STEP 1: Check TabelHandler::canHandle()\n";
$callbackData = 'category_Табель_Пусконаладка';
echo "Callback data: '{$callbackData}'\n";

// Simulate TabelHandler::canHandle logic
if (str_starts_with($callbackData, 'category_Табель') || 
    str_starts_with($callbackData, 'cat_Таб')) {
    echo "✅ TabelHandler::canHandle() would return TRUE\n";
} else {
    echo "❌ TabelHandler::canHandle() would return FALSE\n";
}

echo "\n";

// Step 2: Check if CallbackHandlerFactory would route to TabelHandler
echo "🔍 STEP 2: Check CallbackHandlerFactory routing\n";
if (str_starts_with($callbackData, 'category_Табель') || 
    str_starts_with($callbackData, 'cat_Таб')) {
    echo "✅ CallbackHandlerFactory would route to TabelHandler\n";
} else {
    echo "❌ CallbackHandlerFactory would NOT route to TabelHandler\n";
}

echo "\n";

// Step 3: Check for potential routing conflicts
echo "🔍 STEP 3: Check for routing conflicts\n";
$potentialConflicts = [
    'AdminHandler' => str_starts_with($callbackData, 'admin_'),
    'AccessLevelHandler' => str_starts_with($callbackData, 'access_level_'),
    'ProfileHandler' => str_starts_with($callbackData, 'profile_'),
    'HelpHandler' => str_starts_with($callbackData, 'help_')
];

$hasConflicts = false;
foreach ($potentialConflicts as $handler => $wouldHandle) {
    if ($wouldHandle) {
        echo "⚠️  CONFLICT: {$handler} would also handle this callback\n";
        $hasConflicts = true;
    }
}

if (!$hasConflicts) {
    echo "✅ No routing conflicts detected\n";
}

echo "\n";

// Step 4: Check the exact condition in TabelHandler
echo "🔍 STEP 4: Check TabelHandler condition execution\n";
$category = substr($callbackData, strlen('category_'));
echo "Extracted category: '{$category}'\n";
echo "Checking condition: if (\$category === 'Табель_Пусконаладка')\n";

if ($category === 'Табель_Пусконаладка') {
    echo "✅ CONDITION MATCHES: handleCommissioningDateSelection() should be called\n";
} else {
    echo "❌ CONDITION FAILS: handleCommissioningDateSelection() would NOT be called\n";
    echo "   Category comparison:\n";
    echo "   Expected: 'Табель_Пусконаладка'\n";
    echo "   Actual:   '{$category}'\n";
    echo "   Lengths:  Expected=" . strlen('Табель_Пусконаладка') . ", Actual=" . strlen($category) . "\n";
}

echo "\n";

// Step 5: Final diagnosis
echo "🎯 DIAGNOSIS:\n";
echo "=============\n";

if ($category === 'Табель_Пусконаладка') {
    echo "✅ The routing logic should work correctly.\n";
    echo "🔧 LIKELY CAUSES:\n";
    echo "   1. The callback is not reaching TabelHandler at all\n";
    echo "   2. There's an exception/error in handleCommissioningDateSelection()\n";
    echo "   3. The webhook is not receiving the callback from Telegram\n";
    echo "   4. Database/conversation issues\n\n";
    
    echo "🛠️  DEBUGGING STEPS:\n";
    echo "   1. Check telegram_bot_error.log for errors when clicking 'Отдел ПНР'\n";
    echo "   2. Add error_log() at the start of TabelHandler::handle() method\n";
    echo "   3. Add error_log() at the start of handleCommissioningDateSelection()\n";
    echo "   4. Test with a simpler callback to isolate the issue\n";
    
} else {
    echo "❌ The category is not matching correctly.\n";
    echo "🔧 This indicates an encoding or parsing issue.\n";
}

echo "\n";

// Step 6: Simple fix recommendation
echo "🚀 QUICK FIX ATTEMPT:\n";
echo "=====================\n";
echo "Add this debug code to the start of TabelHandler::handleOtherWorkTypes():\n\n";
echo "```php\n";
echo "error_log(\"TabelHandler: DEBUG - handleOtherWorkTypes called with category: '{$category}'\");\n";
echo "error_log(\"TabelHandler: DEBUG - category === 'Табель_Пусконаладка': \" . (\$category === 'Табель_Пусконаладка' ? 'true' : 'false'));\n";
echo "if (\$category === 'Табель_Пусконаладка') {\n";
echo "    error_log(\"TabelHandler: DEBUG - MATCH FOUND! Calling handleCommissioningDateSelection\");\n";
echo "    return \$this->handleCommissioningDateSelection(\$chatId);\n";
echo "}\n";
echo "```\n\n";

echo "This will help identify exactly where the flow is breaking.\n";
?>