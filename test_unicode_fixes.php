<?php
// Test the fixed Unicode regex patterns

echo "=== UNICODE REGEX FIXES TEST ===\n\n";

$testCallback = 'cat_ТабИнстр_yes';

echo "Testing callback: {$testCallback}\n\n";

// Test the fixed isActivityConfirmation pattern
$confirmationPattern = '/^cat_(ТабИнстр|ТабДорога|ТабПодгПоезд|ТабСдачаОтч|ТабОхрСигн|ТабОтоплВент|ТабТехОб|ТабМетрол|ТабКомплОпр|ТабРежНал|ТабПНРаботы)_yes$/u';

echo "1. isActivityConfirmation pattern (with /u flag):\n";
echo "   Pattern: {$confirmationPattern}\n";
$confirmationMatch = preg_match($confirmationPattern, $testCallback);
echo "   Result: " . ($confirmationMatch ? "TRUE ✓" : "FALSE ✗") . "\n\n";

// Test the fixed isActivityExecution pattern
$executionPattern = '/^cat_(.+)_yes$/u';

echo "2. isActivityExecution pattern (with /u flag):\n";
echo "   Pattern: {$executionPattern}\n";
if (preg_match($executionPattern, $testCallback, $matches)) {
    echo "   Result: TRUE ✓\n";
    echo "   Extracted key: '{$matches[1]}'\n";
    
    // Test the commissioning activities array
    $basicCommissioningActivities = [
        'ТабИнстр' => 'Инструктаж',
        'ТабДорога' => 'Дорога',
        'ТабПодгПоезд' => 'ПодготовкаКПоездке',
        'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
    ];
    
    $extractedKey = $matches[1];
    echo "   Key found in array: " . (isset($basicCommissioningActivities[$extractedKey]) ? "TRUE ✓" : "FALSE ✗") . "\n";
    if (isset($basicCommissioningActivities[$extractedKey])) {
        echo "   Activity type: '{$basicCommissioningActivities[$extractedKey]}'\n";
    }
} else {
    echo "   Result: FALSE ✗\n";
}

echo "\n=== EXPECTED ROUTING ===\n\n";

if ($confirmationMatch) {
    echo "✓ Should be handled by isActivityConfirmation() first\n";
} else {
    echo "✗ Will NOT be handled by isActivityConfirmation()\n";
}

if (preg_match($executionPattern, $testCallback)) {
    echo "✓ Should be handled by isActivityExecution() second\n";
} else {
    echo "✗ Will NOT be handled by isActivityExecution()\n";
}

echo "\n=== CONCLUSION ===\n\n";

if ($confirmationMatch || preg_match($executionPattern, $testCallback)) {
    echo "✅ Unicode fixes should resolve the 'неизвестная команда табеля' issue!\n\n";
    echo "The Инструктаж button should now work correctly.\n";
    echo "Try the button again after these Unicode regex fixes.\n";
} else {
    echo "❌ There are still pattern matching issues.\n";
    echo "Further investigation needed.\n";
}

echo "\n=== NEXT STEPS ===\n\n";
echo "1. The Unicode /u flags have been added to all Cyrillic regex patterns\n";
echo "2. Test the Инструктаж button again\n";
echo "3. Click 'Инструктаж' → Click 'Да'\n";
echo "4. The report should now be sent successfully\n";
?>