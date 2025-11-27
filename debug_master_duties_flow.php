<?php
// Debug the complete master duties flow to identify where it breaks

echo "=== MASTER DUTIES FLOW DEBUG ===\n\n";

// Step 1: User clicks "Исполнение обязанностей мастера" button
echo "STEP 1: User clicks 'Исполнение обязанностей мастера' (cat_ТабИсполнОбяз)\n";
echo "-----------------------------------------------------------------------\n";

$initialCallback = 'cat_ТабИсполнОбяз';

echo "1. Callback: {$initialCallback}\n";
echo "2. TabelHandler.canHandle() check:\n";
$canHandle = str_starts_with($initialCallback, 'cat_Таб');
echo "   Result: " . ($canHandle ? 'TRUE ✅' : 'FALSE ❌') . "\n";

if ($canHandle) {
    echo "3. extractCategoryFromCallback() mapping:\n";
    
    // Check if it's in the shortFormats mapping (line 819 in TabelHandler.php)
    $shortFormats = [
        'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
        'cat_ТабИсполнОбяз_yes' => 'Табель_ИсполнениеОбязМастера_yes'
    ];
    
    if (isset($shortFormats[$initialCallback])) {
        $extractedCategory = $shortFormats[$initialCallback];
        echo "   FOUND in shortFormats: '{$extractedCategory}' ✅\n";
    } else {
        echo "   NOT found in shortFormats ❌\n";
        $extractedCategory = substr($initialCallback, strlen('cat_'));
        echo "   Using fallback: '{$extractedCategory}'\n";
    }
    
    echo "4. Routing check in handle() method:\n";
    echo "   Category to route: '{$extractedCategory}'\n";
    
    // Check routing conditions in order (from handle method)
    echo "   a) isIdleMenuCallback(): FALSE (not idle)\n";
    echo "   b) isActivityExecution('{$extractedCategory}'):\n";
    
    // Simulate isActivityExecution logic
    $isActivityExecution = false;
    if (str_contains($extractedCategory, '_yes')) {
        echo "      Contains '_yes': TRUE - would check further\n";
        // Would check project activity mapping
        if (preg_match('/^cat_(.+)_yes$/', $extractedCategory, $matches)) {
            $shortActivityKey = $matches[1];
            $projectActivityCallbacks = [
                'ТабИсполнОбяз' => 'ИсполнениеОбязМастера'
            ];
            
            if (isset($projectActivityCallbacks[$shortActivityKey])) {
                $activityType = $projectActivityCallbacks[$shortActivityKey];
                // Check if in activityTypes array (should be)
                $activityTypes = ['ИсполнениеОбязМастера']; // Simplified
                $isActivityExecution = in_array($activityType, $activityTypes);
            }
        }
    } else {
        echo "      Contains '_yes': FALSE - not activity execution\n";
    }
    
    echo "      Result: " . ($isActivityExecution ? 'TRUE' : 'FALSE') . "\n";
    
    echo "   c) isActivityConfirmation('{$extractedCategory}'):\n";
    // This should also be FALSE for initial button click
    $isActivityConfirmation = str_contains($extractedCategory, '_yes');
    echo "      Contains '_yes': " . ($isActivityConfirmation ? 'TRUE' : 'FALSE') . "\n";
    echo "      Result: " . ($isActivityConfirmation ? 'TRUE' : 'FALSE') . "\n";
    
    echo "   d) Should route to: handleOtherWorkTypes()\n";
    
    echo "5. handleOtherWorkTypes() processing:\n";
    echo "   Category: '{$extractedCategory}'\n";
    
    // Check the specific handler condition (lines 2820-2826)
    $handlerMatch = ($extractedCategory === 'Табель_ИсполнениеОбязМастера' || $extractedCategory === 'cat_ТабИсполнОбяз');
    echo "   Handler condition check:\n";
    echo "     category === 'Табель_ИсполнениеОбязМастера': " . ($extractedCategory === 'Табель_ИсполнениеОбязМастера' ? 'TRUE' : 'FALSE') . "\n";
    echo "     category === 'cat_ТабИсполнОбяз': " . ($extractedCategory === 'cat_ТабИсполнОбяз' ? 'TRUE' : 'FALSE') . "\n";
    echo "   Combined result: " . ($handlerMatch ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    if ($handlerMatch) {
        echo "   → Should call: getConfirmationMessage('ИсполнениеОбязМастера', projectCode)\n";
        echo "   → Should create confirmation keyboard with: 'cat_ТабИсполнОбяз_yes'\n";
        echo "   → Should send confirmation message ✅\n";
    } else {
        echo "   → Would fall through to 'Unknown command' ❌\n";
    }
}

echo "\n";

// Step 2: User clicks "Да" (Yes) button  
echo "STEP 2: User clicks 'Да' (cat_ТабИсполнОбяз_yes)\n";
echo "-------------------------------------------------------\n";

$confirmationCallback = 'cat_ТабИсполнОбяз_yes';

echo "1. Callback: {$confirmationCallback}\n";
echo "2. extractCategoryFromCallback() mapping:\n";

if (isset($shortFormats[$confirmationCallback])) {
    $confirmationCategory = $shortFormats[$confirmationCallback];
    echo "   FOUND in shortFormats: '{$confirmationCategory}' ✅\n";
} else {
    echo "   NOT found in shortFormats ❌\n";
    $confirmationCategory = substr($confirmationCallback, strlen('cat_'));
    echo "   Using fallback: '{$confirmationCategory}'\n";
}

echo "3. Routing check in handle() method:\n";
echo "   Category to route: '{$confirmationCategory}'\n";

echo "   a) isActivityExecution('{$confirmationCategory}'):\n";
// This should be TRUE for the _yes callback
$confirmIsActivityExecution = str_contains($confirmationCategory, '_yes');
if ($confirmIsActivityExecution) {
    echo "      Contains '_yes': TRUE\n";
    echo "      Should check project activity mapping and return TRUE ✅\n";
} else {
    echo "      Contains '_yes': FALSE ❌\n";
}

echo "   Result: " . ($confirmIsActivityExecution ? 'TRUE' : 'FALSE') . "\n";

if ($confirmIsActivityExecution) {
    echo "   → Should route to: handleActivityExecution()\n";
    echo "   → Should create tabel entry ✅\n";
} else {
    echo "   → Would route elsewhere ❌\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";

echo "\nCONCLUSION:\n";
echo "If the button is not working, the issue is likely in:\n";
echo "1. extractCategoryFromCallback() not mapping 'cat_ТабИсполнОбяз' correctly\n";
echo "2. handleOtherWorkTypes() handler condition not matching\n";
echo "3. isActivityExecution() not detecting 'cat_ТабИсполнОбяз_yes' correctly\n";
echo "4. Missing 'ИсполнениеОбязМастера' in activityTypes array\n";

echo "\nNEXT STEPS:\n";
echo "1. Verify the mapping in extractCategoryFromCallback() (line 819)\n";
echo "2. Check the handler condition in handleOtherWorkTypes() (lines 2820-2826)\n";
echo "3. Verify 'ИсполнениеОбязМастера' exists in activityTypes array (line 82)\n";
?>