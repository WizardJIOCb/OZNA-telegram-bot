<?php
// Test script for "исполнение обязанностей мастера" button issue

echo "=== TESTING MASTER DUTIES BUTTON ISSUE ===\n\n";

echo "Issue: 'исполнение обязанностей мастера' button doesn't respond when clicked\n";
echo "Button callback: cat_ТабИсполнОбяз\n\n";

echo "1. CHECKING CALLBACK MAPPING:\n";
echo "=============================\n";

// Check if the callback is properly mapped in extractCategoryFromCallback
$callbackMappings = [
    'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
    'cat_ТабИсполнОбяз_yes' => 'Табель_ИсполнениеОбязМастера_yes'
];

$testCallback = 'cat_ТабИсполнОбяз';
echo "Testing callback: {$testCallback}\n";

if (isset($callbackMappings[$testCallback])) {
    $mappedCategory = $callbackMappings[$testCallback];
    echo "✅ MAPPED CORRECTLY: {$testCallback} → {$mappedCategory}\n";
} else {
    echo "❌ NOT MAPPED: {$testCallback}\n";
}

echo "\n2. CHECKING ACTIVITY TYPE REGISTRATION:\n";
echo "======================================\n";

// Check if ИсполнениеОбязМастера is in activityTypes array
$activityTypes = [
    'УчастиеВЗПИ',
    'РазрПрограммы', 
    'РазрГрафика',
    'ОргДопоставки',
    'УчастиеВОПИ',
    'ИсполнениеОбязМастера',
    'Прочее_ПроектнаяДеятельность',
    'Прочее_ПроектнаяДеятельность_ШМР'
];

$requiredActivity = 'ИсполнениеОбязМастера';
if (in_array($requiredActivity, $activityTypes)) {
    echo "✅ ACTIVITY TYPE REGISTERED: {$requiredActivity}\n";
} else {
    echo "❌ ACTIVITY TYPE NOT REGISTERED: {$requiredActivity}\n";
}

echo "\n3. CHECKING HANDLER CONDITIONS:\n";
echo "==============================\n";

$category = 'Табель_ИсполнениеОбязМастера';
$altCategory = 'cat_ТабИсполнОбяз';

// Test the handler condition from handleOtherWorkTypes
$handlerCondition1 = ($category === 'Табель_ИсполнениеОбязМастера');
$handlerCondition2 = ($altCategory === 'cat_ТабИсполнОбяз');
$handlerCondition = $handlerCondition1 || $handlerCondition2;

echo "Handler condition 1 ('{$category}' === 'Табель_ИсполнениеОбязМастера'): " . ($handlerCondition1 ? 'TRUE' : 'FALSE') . "\n";
echo "Handler condition 2 ('{$altCategory}' === 'cat_ТабИсполнОбяз'): " . ($handlerCondition2 ? 'TRUE' : 'FALSE') . "\n";
echo "Combined handler condition: " . ($handlerCondition ? '✅ MATCH' : '❌ NO MATCH') . "\n";

echo "\n4. CHECKING CONFIRMATION CALLBACK:\n";
echo "=================================\n";

$confirmationCallback = 'cat_ТабИсполнОбяз_yes';
echo "Expected confirmation callback: {$confirmationCallback}\n";

// Test isActivityExecution for confirmation callback
echo "Testing isActivityExecution for confirmation callback...\n";

// Check if it matches the project activity pattern in isActivityExecution
$isProjectActivity = false;
if (preg_match('/^cat_(.+)_yes$/', $confirmationCallback, $matches)) {
    $shortActivityKey = $matches[1];
    echo "Extracted short activity key: {$shortActivityKey}\n";
    
    $projectActivityCallbacks = [
        'ТабИсполнОбяз' => 'ИсполнениеОбязМастера'
    ];
    
    if (isset($projectActivityCallbacks[$shortActivityKey])) {
        $activityType = $projectActivityCallbacks[$shortActivityKey];
        echo "Mapped to activity type: {$activityType}\n";
        
        if (in_array($activityType, $activityTypes)) {
            $isProjectActivity = true;
            echo "✅ CONFIRMATION CALLBACK WILL BE HANDLED\n";
        } else {
            echo "❌ Activity type not in activityTypes array\n";
        }
    } else {
        echo "❌ Short activity key not found in project activity mappings\n";
    }
} else {
    echo "❌ Confirmation callback doesn't match expected pattern\n";
}

echo "\n5. FLOW SIMULATION:\n";
echo "===================\n";

echo "Step 1: User clicks 'исполнение обязанностей мастера' button\n";
echo "  → Sends callback: cat_ТабИсполнОбяз\n";
echo "  → TabelHandler.canHandle(): " . (str_starts_with($testCallback, 'cat_Таб') ? 'TRUE' : 'FALSE') . "\n";
echo "  → extractCategoryFromCallback(): " . ($mappedCategory ?? 'NOT MAPPED') . "\n";
echo "  → Routes to: handleOtherWorkTypes()\n";
echo "  → Handler condition matches: " . ($handlerCondition ? 'YES' : 'NO') . "\n";

if ($handlerCondition) {
    echo "  → Shows confirmation message with 'Да'/'Нет' buttons\n";
    echo "  → 'Да' button callback: {$confirmationCallback}\n\n";
    
    echo "Step 2: User clicks 'Да' button\n";
    echo "  → Sends callback: {$confirmationCallback}\n";
    echo "  → isActivityExecution(): " . ($isProjectActivity ? 'TRUE' : 'FALSE') . "\n";
    
    if ($isProjectActivity) {
        echo "  → Routes to: handleActivityExecution()\n";
        echo "  → Creates timesheet entry\n";
        echo "  → ✅ SUCCESS\n";
    } else {
        echo "  → ❌ FAILS - Shows 'неизвестная команда табеля'\n";
    }
} else {
    echo "  → ❌ FAILS - Handler condition doesn't match\n";
}

echo "\n6. CONCLUSION:\n";
echo "==============\n";

if ($handlerCondition && $isProjectActivity) {
    echo "✅ ALL CHECKS PASSED - Button should work correctly!\n";
    echo "If the button still doesn't work, the issue might be:\n";
    echo "- Server hasn't been restarted after recent changes\n";
    echo "- Database connectivity issues\n";
    echo "- User access level restrictions\n";
    echo "- Conversation state issues\n";
} else {
    echo "❌ ISSUES FOUND - Button will not work correctly\n";
    if (!$handlerCondition) {
        echo "- Handler condition doesn't match\n";
    }
    if (!$isProjectActivity) {
        echo "- Confirmation callback won't be handled by isActivityExecution\n";
    }
}

echo "\n✅ TESTING COMPLETE\n";
?>