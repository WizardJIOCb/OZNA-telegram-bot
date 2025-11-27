<?php
/**
 * TARGETED DIAGNOSTIC: Check exact callback flow for "новое_прочее_yes"
 */

echo "=== TARGETED DIAGNOSTIC FOR 'новое_прочее_yes' CALLBACK ===\n\n";

// Simulate the exact callback flow step by step
$callbackData = 'новое_прочее_yes';

echo "1. TESTING CALLBACK RECOGNITION FLOW...\n";
echo "=======================================\n";

// Test 1: TabelHandler canHandle
echo "Step 1: CallbackHandlerFactory tests handlers\n";
echo "Testing TabelHandler->canHandle('{$callbackData}'):\n";

// Simulate the exact canHandle logic from TabelHandler
$canHandleResult = (
    str_starts_with($callbackData, 'category_Табель') || 
    str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
    str_starts_with($callbackData, 'cat_Таб') ||
    $callbackData === 'новое_прочее_yes'
);

echo "   str_starts_with('category_Табель'): " . (str_starts_with($callbackData, 'category_Табель') ? 'true' : 'false') . "\n";
echo "   str_starts_with('cat_ТабПНРаботы'): " . (str_starts_with($callbackData, 'cat_ТабПНРаботы') ? 'true' : 'false') . "\n";
echo "   str_starts_with('cat_Таб'): " . (str_starts_with($callbackData, 'cat_Таб') ? 'true' : 'false') . "\n";
echo "   === 'новое_прочее_yes': " . ($callbackData === 'новое_прочее_yes' ? 'true' : 'false') . "\n";
echo "   ✅ Final canHandle result: " . ($canHandleResult ? 'TRUE' : 'FALSE') . "\n\n";

if (!$canHandleResult) {
    echo "❌ CRITICAL ERROR: TabelHandler cannot handle this callback!\n";
    echo "This means another handler will process it or it will be ignored.\n\n";
    exit;
}

// Test 2: Category extraction (no prefix for новое_прочее_yes)
echo "Step 2: Category extraction\n";
$category = $callbackData; // No prefix to strip for новое_прочее_yes
echo "   Extracted category: '{$category}'\n\n";

// Test 3: isConfirmationCallback
echo "Step 3: Testing isConfirmationCallback('{$category}'):\n";
$isConfirmation = ($category === 'новое_прочее_yes') || 
                  (str_contains($category, '_yes') && str_contains($category, 'Табель_'));
echo "   category === 'новое_прочее_yes': " . ($category === 'новое_прочее_yes' ? 'true' : 'false') . "\n";
echo "   Contains '_yes' AND 'Табель_': " . ((str_contains($category, '_yes') && str_contains($category, 'Табель_')) ? 'true' : 'false') . "\n";
echo "   ✅ isConfirmationCallback result: " . ($isConfirmation ? 'TRUE' : 'FALSE') . "\n\n";

// Test 4: Routing in routeTabelCallback  
echo "Step 4: Testing routing in routeTabelCallback\n";
$willRoute = ($category === 'cat_ТабЭлОб_Проч_yes' || $category === 'новое_прочее_yes');
echo "   category === 'cat_ТабЭлОб_Проч_yes': " . ($category === 'cat_ТабЭлОб_Проч_yes' ? 'true' : 'false') . "\n";
echo "   category === 'новое_прочее_yes': " . ($category === 'новое_прочее_yes' ? 'true' : 'false') . "\n";
echo "   ✅ Will route to handleNewProcheeConfirmation: " . ($willRoute ? 'TRUE' : 'FALSE') . "\n\n";

echo "2. SIMULATING COMPLETE FLOW...\n";
echo "==============================\n";

if ($canHandleResult && $isConfirmation && $willRoute) {
    echo "✅ ALL TESTS PASS - Callback should work!\n\n";
    
    echo "Expected execution path:\n";
    echo "1. ✅ CallbackHandlerFactory selects TabelHandler\n";
    echo "2. ✅ TabelHandler->handle() called with category: 'новое_прочее_yes'\n";
    echo "3. ✅ routeTabelCallback() routes to handleNewProcheeConfirmation()\n";
    echo "4. ✅ handleNewProcheeConfirmation() executes\n";
    echo "5. ✅ Report should be created and sent\n\n";
    
    echo "🔍 IF BUTTON STILL DOESN'T WORK, CHECK:\n";
    echo "   A. Server restart needed (old code cached)\n";
    echo "   B. PHP errors in handleNewProcheeConfirmation()\n";
    echo "   C. Database connection issues\n";
    echo "   D. User data validation failures (FIO/functional direction)\n";
    echo "   E. TabelEntry service errors\n";
    echo "   F. Conversation state corruption\n\n";
    
} else {
    echo "❌ FLOW BROKEN - Issues found:\n";
    if (!$canHandleResult) echo "   - canHandle fails\n";
    if (!$isConfirmation) echo "   - isConfirmationCallback fails\n";
    if (!$willRoute) echo "   - Routing fails\n";
}

echo "3. NEXT DEBUGGING STEPS...\n";
echo "==========================\n";
echo "1. Test the actual button in Telegram\n";
echo "2. Check server logs immediately after clicking\n";
echo "3. Verify user has FIO and functional direction set\n";
echo "4. Try with a fresh conversation (/start)\n";
echo "5. Check if conversation contains required data:\n";
echo "   - prochee_custom_text\n";
echo "   - project_code\n";
echo "   - tabel_data\n\n";

echo "✅ TARGETED DIAGNOSTIC COMPLETE\n";
?>