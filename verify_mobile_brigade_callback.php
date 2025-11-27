<?php
// Verification script to test mobile brigade callback flow

echo "=== Mobile Brigade Callback Flow Verification ===\n\n";

// Test the callback data that the mobile brigade button sends
$callbackData = 'category_Табель_Мобильная_Бригада';
echo "1. Mobile brigade button sends callback data: {$callbackData}\n";

// Simulate category extraction (as done in CallbackHandlerFactory)
$category = substr($callbackData, strlen('category_'));
echo "2. CallbackHandlerFactory extracts category: {$category}\n";

// Test if TabelHandler would handle this
$tabelCanHandle = strpos($callbackData, 'category_Табель') === 0;
echo "3. TabelHandler->canHandle('{$callbackData}'): " . ($tabelCanHandle ? 'YES' : 'NO') . "\n";

if ($tabelCanHandle) {
    echo "4. TabelHandler will process this callback\n";
    
    // Simulate routing in TabelHandler->handleOtherWorkTypes
    echo "5. In TabelHandler->handleOtherWorkTypes, checking category: '{$category}'\n";
    
    // Check the conditions
    $isMobileBrigade = ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг');
    echo "6. Category matches mobile brigade condition: " . ($isMobileBrigade ? 'YES' : 'NO') . "\n";
    
    if ($isMobileBrigade) {
        echo "7. ✓ Would call handleMobileBrigadeDateSelection()\n";
        echo "8. ✓ Would show date selection with prefix: category_Табель_Мобильная_Бригада\n";
    } else {
        echo "7. ✗ Would NOT call handleMobileBrigadeDateSelection()\n";
        echo "8. ✗ Mobile brigade button would not work\n";
    }
} else {
    echo "4. TabelHandler will NOT process this callback\n";
    echo "5. Mobile brigade button would not work\n";
}

echo "\n=== Conclusion ===\n";
echo "If all steps show YES/✓, the mobile brigade button should work correctly.\n";
echo "If any step shows NO/✗, there's an issue that needs to be fixed.\n";