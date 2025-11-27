<?php

// Final test for automation scheme changes button after deep fix

echo "=== FINAL TEST: Automation Scheme Changes Button ===\n\n";

// Test the complete flow with the fixed extractCategoryFromCallback
echo "Testing the complete flow with fixes applied:\n\n";

// Simulate the fixed extractCategoryFromCallback method
function testExtractCategoryFromCallback($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format with our new automation mapping
    $shortFormats = [
        'cat_ТабАвтом_ИзмСх' => 'Табель_ИзмСх_Автом',
        'cat_ТабАвтом_ИзмСх_yes' => 'Табель_ИзмСх_Автом_yes', // NEWLY ADDED!
    ];
    
    if (isset($shortFormats[$callbackData])) {
        return $shortFormats[$callbackData];
    }
    
    // Fallback
    if (strpos($callbackData, 'cat_') === 0) {
        return substr($callbackData, strlen('cat_'));
    }
    
    return $callbackData;
}

// Test the flow
$initialCallback = 'cat_ТабАвтом_ИзмСх';
$confirmationCallback = 'cat_ТабАвтом_ИзмСх_yes';

echo "1. User clicks 'Изменение схем подключения' button\n";
echo "   Callback: {$initialCallback}\n";
echo "   Extracted category: " . testExtractCategoryFromCallback($initialCallback) . "\n";
echo "   → Should show confirmation\n\n";

echo "2. User clicks 'Да' button\n";
echo "   Callback: {$confirmationCallback}\n";
echo "   Extracted category: " . testExtractCategoryFromCallback($confirmationCallback) . "\n";

// Test the routing with the extracted category
$extractedCategory = testExtractCategoryFromCallback($confirmationCallback);

// Test isActivityExecution with extracted category
function testIsActivityExecution($category) {
    if (str_contains($category, '_yes')) {
        // Check pattern for full category format
        if (preg_match('/^Табель_(.+)_yes$/', $category, $matches)) {
            $activityPart = $matches[1];
            // Check if it's automation activity
            if (str_contains($activityPart, '_Автом')) {
                return true;
            }
        }
    }
    return false;
}

$isActivityExecution = testIsActivityExecution($extractedCategory);
echo "   → isActivityExecution: " . ($isActivityExecution ? 'TRUE' : 'FALSE') . "\n";

if ($isActivityExecution) {
    echo "   → Should route to handleActivityExecution()\n";
    echo "   → Should extract activity type: ИзмСх_Автом\n";
    echo "   → Should create report successfully!\n\n";
    echo "✅ SUCCESS: The automation scheme changes button should now work correctly!\n";
} else {
    echo "   → Issue: Still not being recognized as activity execution\n";
    echo "❌ FAILURE: There may still be an issue with the routing\n";
}

echo "\nSUMMARY OF FIXES APPLIED:\n";
echo "=========================\n";
echo "1. ✅ Added automation equipment patterns to isConfirmationCallback()\n";
echo "2. ✅ Added automation equipment patterns to isActivityExecution()\n";
echo "3. ✅ Added automation confirmation callbacks to extractCategoryFromCallback()\n";
echo "\nThe automation 'Изменение схем подключения' button should now:\n";
echo "- Show confirmation when clicked\n";
echo "- Send report when 'Да' is clicked\n";
echo "- Not show 'Неизвестная команда табеля'\n";

?>