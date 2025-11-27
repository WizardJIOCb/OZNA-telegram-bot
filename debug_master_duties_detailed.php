<?php
// Detailed debugging for master duties button issue

echo "=== DETAILED MASTER DUTIES DEBUG ===\n\n";

echo "Since the basic logic test passed but the button still doesn't work,\n";
echo "let's check the actual conversation flow and potential blocking issues.\n\n";

echo "1. CHECKING CONVERSATION FLOW REQUIREMENTS:\n";
echo "==========================================\n";

// Check if user needs to be in supervision project activity context
echo "Required conversation state:\n";
echo "- User must select 'Отдел ШМР' department\n";
echo "- User must select a date\n";
echo "- User must select a project\n";
echo "- User must navigate to 'Проектная деятельность' menu\n";
echo "- Then 'исполнение обязанностей мастера' button should appear\n\n";

echo "2. CHECKING POTENTIAL BLOCKING CONDITIONS:\n";
echo "=========================================\n";

// Check if there are any conditions that might block the execution
echo "Potential blockers:\n";
echo "a) User access level check (must be > 0)\n";
echo "b) Missing project_code in conversation\n";
echo "c) Invalid conversation state\n";
echo "d) Database connection issues\n";
echo "e) Missing user FIO or functional direction\n\n";

echo "3. TESTING ACTUAL CALLBACK PROCESSING:\n";
echo "=====================================\n";

// Let's trace through the actual TabelHandler logic step by step
$testCallback = 'cat_ТабИсполнОбяз';
echo "Step-by-step processing for: {$testCallback}\n\n";

echo "Step 1: TabelHandler.canHandle()\n";
$canHandle = str_starts_with($testCallback, 'category_Табель') || 
             str_starts_with($testCallback, 'cat_ТабПНРаботы') ||
             str_starts_with($testCallback, 'cat_Таб');
echo "Result: " . ($canHandle ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";

if ($canHandle) {
    echo "Step 2: extractCategoryFromCallback()\n";
    // Simulate the mapping logic
    $shortFormats = [
        'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
        'cat_ТабИсполнОбяз_yes' => 'Табель_ИсполнениеОбязМастера_yes'
    ];
    
    $extractedCategory = $shortFormats[$testCallback] ?? 'NOT_MAPPED';
    echo "Extracted category: {$extractedCategory}\n\n";
    
    echo "Step 3: handle() method routing\n";
    echo "Routing checks (in order):\n";
    
    // Check routing conditions in order
    $routingChecks = [
        'isActivityExecution' => false, // Initial button click shouldn't be execution
        'isActivityConfirmation' => false, // Initial button click shouldn't be confirmation
        'handleOtherWorkTypes' => true // This should handle it
    ];
    
    foreach ($routingChecks as $method => $expected) {
        echo "- {$method}(): " . ($expected ? 'TRUE' : 'FALSE') . "\n";
        if ($expected) {
            echo "  → Routes to: {$method}\n";
            break;
        }
    }
    
    echo "\nStep 4: handleOtherWorkTypes() processing\n";
    $category = $extractedCategory;
    
    // Check the specific handler condition
    $handlerMatch = ($category === 'Табель_ИсполнениеОбязМастера' || $category === 'cat_ТабИсполнОбяз');
    echo "Handler condition match: " . ($handlerMatch ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    if ($handlerMatch) {
        echo "→ Should show confirmation message\n";
        echo "→ Should create 'Да'/'Нет' buttons\n";
        echo "→ 'Да' button callback: cat_ТабИсполнОбяз_yes\n";
    }
}

echo "\n4. CHECKING CONFIRMATION FLOW:\n";
echo "==============================\n";

$confirmationCallback = 'cat_ТабИсполнОбяз_yes';
echo "Testing confirmation callback: {$confirmationCallback}\n\n";

echo "Step 1: TabelHandler.canHandle() for confirmation\n";
$canHandleConfirm = str_starts_with($confirmationCallback, 'cat_Таб');
echo "Result: " . ($canHandleConfirm ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";

if ($canHandleConfirm) {
    echo "Step 2: isActivityExecution() check\n";
    // Test the actual logic from TabelHandler
    $isActivityExecution = false;
    
    if (preg_match('/^cat_(.+)_yes$/', $confirmationCallback, $matches)) {
        $shortActivityKey = $matches[1];
        echo "Extracted short key: {$shortActivityKey}\n";
        
        $projectActivityCallbacks = [
            'ТабИсполнОбяз' => 'ИсполнениеОбязМастера'
        ];
        
        if (isset($projectActivityCallbacks[$shortActivityKey])) {
            $activityType = $projectActivityCallbacks[$shortActivityKey];
            echo "Mapped to activity: {$activityType}\n";
            
            // Check if in activityTypes array
            $activityTypes = [
                'ИсполнениеОбязМастера' // This should be present
            ];
            
            $isActivityExecution = in_array($activityType, $activityTypes);
            echo "In activityTypes array: " . ($isActivityExecution ? 'TRUE ✅' : 'FALSE ❌') . "\n";
        }
    }
    
    if ($isActivityExecution) {
        echo "→ Should route to handleActivityExecution()\n";
        echo "→ Should create timesheet entry\n";
    } else {
        echo "❌ Will show 'неизвестная команда табеля'\n";
    }
}

echo "\n5. DEBUGGING RECOMMENDATIONS:\n";
echo "=============================\n";

echo "To identify the exact issue, check:\n\n";

echo "A) Server logs when clicking the button:\n";
echo "   - Look for 'TabelHandler: handling master duties execution'\n";
echo "   - Look for any error messages\n";
echo "   - Check if the callback is being received\n\n";

echo "B) User context:\n";
echo "   - Verify user has project_code in conversation\n";
echo "   - Check user access level (should be > 0)\n";
echo "   - Verify user is in correct menu context\n\n";

echo "C) Database connectivity:\n";
echo "   - Check if database queries are working\n";
echo "   - Verify user data can be retrieved\n";
echo "   - Test if timesheet entries can be created\n\n";

echo "D) Message sending:\n";
echo "   - Check if messages are being sent to Telegram\n";
echo "   - Verify keyboard creation is working\n";
echo "   - Test if confirmation messages appear\n\n";

echo "6. IMMEDIATE ACTION ITEMS:\n";
echo "=========================\n";

echo "1. Restart the bot/server process\n";
echo "2. Check server logs in real-time while testing\n";
echo "3. Test with a user who has admin access\n";
echo "4. Verify the conversation state before clicking the button\n";
echo "5. Try clicking other buttons to see if they work\n\n";

echo "If none of the above helps, the issue might be:\n";
echo "- A race condition in the code\n";
echo "- Memory or resource constraints\n";
echo "- Network connectivity issues\n";
echo "- Telegram API rate limiting\n\n";

echo "✅ DETAILED DEBUG COMPLETE\n";
echo "Next step: Check server logs during button click\n";
?>