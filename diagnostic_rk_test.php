<?php
// Comprehensive diagnostic test for RK button issue

echo "=== COMPREHENSIVE RK BUTTON DIAGNOSTIC ===\n\n";

// Read the actual handler file to see what's happening
$handlerFile = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
$handlerContent = file_get_contents($handlerFile);

// Read the message service file to see button callbacks
$messageFile = 'c:\ozna_bot\CustomCommands\Services\MessageService.php';
$messageContent = file_get_contents($messageFile);

echo "1. CHECKING ACTUAL BUTTON CALLBACKS:\n";
echo "====================================\n";

// Extract actual RK button callbacks from MessageService
if (preg_match('/createEliminationRemarksRKKeyboard.*?return \[(.*?)\];/s', $messageContent, $matches)) {
    $keyboardContent = $matches[1];
    echo "RK Keyboard content found:\n";
    
    // Find all callback_data entries
    if (preg_match_all("/'callback_data' => '([^']+)'/", $keyboardContent, $callbackMatches)) {
        foreach ($callbackMatches[1] as $callback) {
            echo "  Button callback: '{$callback}'\n";
        }
    }
} else {
    echo "❌ RK keyboard not found!\n";
}

echo "\n2. TESTING CALLBACK ROUTING:\n";
echo "============================\n";

// Test the actual callbacks found
$testCallbacks = [
    'category_Табель_УстрЗам_ЭС',
    'category_Табель_УстрЗам_АТХ',
    'category_Табель_УстрЗам_ОВКВ'
];

foreach ($testCallbacks as $callback) {
    echo "Testing callback: '{$callback}'\n";
    
    // Test canHandle
    $canHandle = str_starts_with($callback, 'category_Табель') || 
                 str_starts_with($callback, 'cat_ТабПНРаботы') ||
                 str_starts_with($callback, 'cat_Таб');
    echo "  canHandle: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";
    
    // Test extractCategoryFromCallback
    if (strpos($callback, 'category_') === 0) {
        $extracted = substr($callback, strlen('category_'));
        echo "  extracted category: '{$extracted}'\n";
        
        // Test isActivityExecution
        $isActivityExecution = str_contains($extracted, '_yes');
        echo "  isActivityExecution: " . ($isActivityExecution ? 'TRUE' : 'FALSE') . "\n";
        
        // Test isActivityConfirmation  
        $isActivityConfirmation = false; // RK sections are not confirmations
        echo "  isActivityConfirmation: " . ($isActivityConfirmation ? 'TRUE' : 'FALSE') . "\n";
        
        // Should route to handleOtherWorkTypes
        echo "  → Should route to: handleOtherWorkTypes\n";
        
        // Test handleOtherWorkTypes pattern matching
        if (preg_match('/^Табель_УстрЗам_(.+)$/', $extracted, $matches)) {
            $section = $matches[1];
            echo "  → handleOtherWorkTypes pattern matches: section '{$section}'\n";
            echo "  → Should show confirmation dialog ✅\n";
        } else {
            echo "  → handleOtherWorkTypes pattern DOES NOT MATCH ❌\n";
            echo "  → This is why it shows activities menu instead!\n";
        }
    }
    echo "\n";
}

echo "3. CHECKING ACTUAL HANDLER PATTERNS:\n";
echo "====================================\n";

// Check what patterns are actually in handleOtherWorkTypes
if (preg_match('/preg_match\(\'\/\^([^\']+)\'\/', $handlerContent, $patternMatch)) {
    echo "Found pattern in handleOtherWorkTypes: {$patternMatch[1]}\n";
} else {
    echo "No regex pattern found in handleOtherWorkTypes\n";
}

// Look for specific RK handling
if (strpos($handlerContent, 'УстрЗам_') !== false) {
    echo "✅ УстрЗам_ found in handler\n";
} else {
    echo "❌ УстрЗам_ NOT found in handler\n";
}

echo "\n4. PROBLEM ANALYSIS:\n";
echo "====================\n";

echo "The issue is likely:\n";
echo "1. Callback format mismatch between buttons and handler\n";
echo "2. Pattern in handleOtherWorkTypes doesn't match the actual callbacks\n";
echo "3. RK sections are falling through to 'Unknown command'\n\n";

echo "SOLUTION:\n";
echo "Need to check the EXACT pattern in handleOtherWorkTypes\n";
echo "and make sure it matches the button callbacks exactly.\n";

?>