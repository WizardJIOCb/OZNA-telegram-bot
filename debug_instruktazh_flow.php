<?php
// Debug the complete Instruktazh flow to see where it fails

echo "=== INSTRUKTAZH FLOW DEBUG ===\n\n";

// Step 1: User clicks "Инструктаж" button
echo "STEP 1: User clicks 'Инструктаж' (cat_ТабИнстр)\n";
echo "-------------------------------------------------------\n";

$initialCallback = 'cat_ТабИнстр';

// This should be handled by handleOtherWorkTypes -> shortCallbackMappings
echo "1. Callback: {$initialCallback}\n";
echo "2. Should be handled by: handleOtherWorkTypes() -> shortCallbackMappings\n";
echo "3. Should create confirmation with: cat_ТабИнстр_yes\n";
echo "4. Result: Shows confirmation dialog\n\n";

// Step 2: User clicks "Да" (Yes) button  
echo "STEP 2: User clicks 'Да' (cat_ТабИнстр_yes)\n";
echo "-------------------------------------------------------\n";

$confirmationCallback = 'cat_ТабИнстр_yes';

echo "1. Callback: {$confirmationCallback}\n";
echo "2. Routing check order:\n";

// Check routing order manually
echo "   a) isActivityExecution('{$confirmationCallback}'):\n";

// Manual check for isActivityExecution
$isExecution = false;
if (preg_match('/^cat_(.+)_yes$/', $confirmationCallback, $matches)) {
    $shortActivityKey = $matches[1];
    echo "      - Matches cat_(.+)_yes pattern: YES\n";
    echo "      - Extracted key: {$shortActivityKey}\n";
    
    $basicCommissioningActivities = [
        'ТабИнстр' => 'Инструктаж',
        'ТабДорога' => 'Дорога',
        'ТабПодгПоезд' => 'ПодготовкаКПоездке',
        'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
    ];
    
    if (isset($basicCommissioningActivities[$shortActivityKey])) {
        echo "      - Found in basicCommissioningActivities: YES\n";
        echo "      - Activity type: {$basicCommissioningActivities[$shortActivityKey]}\n";
        $isExecution = true;
    } else {
        echo "      - Found in basicCommissioningActivities: NO\n";
    }
} else {
    echo "      - Matches cat_(.+)_yes pattern: NO\n";
}

echo "      - isActivityExecution result: " . ($isExecution ? "TRUE" : "FALSE") . "\n\n";

if ($isExecution) {
    echo "   ✓ Should route to: handleActivityExecution()\n";
    echo "   ✓ Should create tabel entry for: Инструктаж\n";
    echo "   ✓ Expected result: SUCCESS\n\n";
} else {
    echo "   b) isActivityConfirmation('{$confirmationCallback}'):\n";
    
    // Manual check for isActivityConfirmation
    $isConfirmation = false;
    if (preg_match('/^cat_(ТабИнстр|ТабДорога|ТабПодгПоезд|ТабСдачаОтч|ТабОхрСигн|ТабОтоплВент|ТабТехОб|ТабМетрол|ТабКомплОпр|ТабРежНал|ТабПНРаботы)_yes$/', $confirmationCallback)) {
        $isConfirmation = true;
    }
    
    echo "      - Matches commissioning pattern: " . ($isConfirmation ? "TRUE" : "FALSE") . "\n\n";
    
    if ($isConfirmation) {
        echo "   ✓ Should route to: handleActivityConfirmation()\n";
        echo "   ✓ But this is wrong - confirmation already happened!\n";
        echo "   ✗ Expected result: ERROR\n\n";
    } else {
        echo "   c) Falls to: handleOtherWorkTypes()\n";
        echo "   ✗ Result: 'неизвестная команда табеля'\n\n";
    }
}

echo "=== ISSUE ANALYSIS ===\n\n";

if ($isExecution) {
    echo "✓ The routing logic is CORRECT.\n";
    echo "✓ cat_ТабИнстр_yes should be handled by isActivityExecution().\n";
    echo "✓ This should create a tabel entry successfully.\n\n";
    echo "If it's still not working, possible causes:\n";
    echo "1. 🔄 Server is using cached/old version of the file\n";
    echo "2. 🗂️ Server is reading from a different TabelHandler.php file\n";
    echo "3. 🔍 Check server logs for actual execution path\n";
    echo "4. 🚫 User data validation failing (FIO, functional_direction)\n";
    echo "5. 📋 TabelEntry->addEntry() method failing\n\n";
    echo "RECOMMENDATION: Check user profile and server logs\n";
} else {
    echo "✗ The routing logic has issues.\n";
    echo "✗ cat_ТабИнстр_yes is not being handled correctly.\n";
    echo "✗ Need to fix the isActivityExecution() method.\n";
}

echo "\n=== COMPARISON WITH WORKING BUTTON ===\n\n";

$workingCallback = 'cat_ТабЭлОб_РабПоГр_yes';
echo "Working button: {$workingCallback}\n";

if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $workingCallback, $matches)) {
    echo "✓ Matches electrical equipment pattern: cat_ТабЭлОб_(.+)_yes\n";
    echo "✓ Routes to: isActivityExecution() -> handleActivityExecution()\n";
    echo "✓ Result: SUCCESS\n\n";
}

echo "Broken button: {$confirmationCallback}\n";
echo "✗ Does NOT match electrical equipment pattern\n";
echo "? Should match commissioning pattern in isActivityExecution()\n";
echo "? Routes to: isActivityExecution() -> handleActivityExecution()\n";
echo "? Expected result: SUCCESS\n";
?>