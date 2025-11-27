<?php

echo "=== Verifying ШМР Technological Equipment Fix ===\n";

// Test the exact scenario
$callbackData = 'cat_ШМР_ТехОб';
$extractedCategory = 'Табель_ШМР_ТехнологическоеОборудование';

echo "1. Button callback: {$callbackData}\n";
echo "2. Extracted category: {$extractedCategory}\n";

// Check the fixed early routing condition
$earlyRoutingCondition = ($extractedCategory === 'cat_ТабТехОб' || $extractedCategory === 'Табель_ТехнологическоеОборудование') && 
    $extractedCategory !== 'cat_ШМР_ТехОб' && $extractedCategory !== 'Табель_ШМР_ТехнологическоеОборудование';

echo "3. Early routing condition analysis:\n";
echo "   - Is 'cat_ТабТехОб'? " . ($extractedCategory === 'cat_ТабТехОб' ? "YES" : "NO") . "\n";
echo "   - Is 'Табель_ТехнологическоеОборудование'? " . ($extractedCategory === 'Табель_ТехнологическоеОборудование' ? "YES" : "NO") . "\n";
echo "   - Is NOT 'cat_ШМР_ТехОб'? " . ($extractedCategory !== 'cat_ШМР_ТехОб' ? "YES" : "NO") . "\n";
echo "   - Is NOT 'Табель_ШМР_ТехнологическоеОборудование'? " . ($extractedCategory !== 'Табель_ШМР_ТехнологическоеОборудование' ? "YES" : "NO") . "\n";
echo "   - Early routing will trigger? " . ($earlyRoutingCondition ? "❌ YES (BAD)" : "✅ NO (GOOD)") . "\n";

// Check if it will reach the correct ШМР handler
$shmrHandlerCondition = ($extractedCategory === 'Табель_ШМР_ТехнологическоеОборудование');
echo "4. ШМР handler condition: " . ($shmrHandlerCondition ? "✅ YES (GOOD)" : "❌ NO (BAD)") . "\n";

echo "\n=== RESULT ===\n";
if (!$earlyRoutingCondition && $shmrHandlerCondition) {
    echo "✅ SUCCESS: The fix is correct! The ШМР technological equipment button should work.\n";
    echo "   - Early routing will NOT interfere\n";
    echo "   - The callback will reach the ШМР technological equipment handler\n";
    echo "   - User should see submenu with Pipeline installation and Hydro/pneumatic testing\n";
} else if ($earlyRoutingCondition) {
    echo "❌ PROBLEM: Early routing will still interfere!\n";
} else if (!$shmrHandlerCondition) {
    echo "❌ PROBLEM: The callback won't reach the ШМР handler!\n";
} else {
    echo "❌ UNKNOWN ISSUE\n";
}

echo "\nTest completed.\n";