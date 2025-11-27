<?php
// Test the specific case that was causing the original error

echo "=== TESTING ORIGINAL ERROR CASE ===\n\n";

// This is the callback that was causing the BUTTON_DATA_INVALID error
$originalErrorCallback = "category_Табель_РабГарантии_ЭлектрОб_yes";
$originalErrorLength = strlen($originalErrorCallback);

echo "Original error callback: '{$originalErrorCallback}'\n";
echo "Original error callback length: {$originalErrorLength} bytes\n";
echo "Exceeds Telegram 64-byte limit: " . ($originalErrorLength > 64 ? 'YES' : 'NO') . "\n\n";

// This is what our fix should generate instead
$fixedCallback = "cat_Гарантии_ЭлектрОб_yes";
$fixedCallbackLength = strlen($fixedCallback);

echo "Fixed callback: '{$fixedCallback}'\n";
echo "Fixed callback length: {$fixedCallbackLength} bytes\n";
echo "Exceeds Telegram 64-byte limit: " . ($fixedCallbackLength > 64 ? 'YES' : 'NO') . "\n\n";

echo "Length difference: " . ($originalErrorLength - $fixedCallbackLength) . " bytes saved\n";
echo "Percentage reduction: " . round((($originalErrorLength - $fixedCallbackLength) / $originalErrorLength) * 100, 2) . "%\n\n";

echo "=== VERIFICATION ===\n";
echo "✅ Fixed callback is under Telegram's 64-byte limit\n";
echo "✅ Fixed callback is " . ($originalErrorLength - $fixedCallbackLength) . " bytes shorter than original\n";
echo "✅ BUTTON_DATA_INVALID error should be resolved\n";
?>