<?php
/**
 * Validate the fix for the electrical equipment button
 */

// Simulate the exact same check that's now in CallbackqueryCommand.php
function shouldProcessCallback($callback_data) {
    return (strpos($callback_data, 'category_') === 0 || 
            strpos($callback_data, 'adm_req_') === 0 || 
            strpos($callback_data, 'acc_lvl_') === 0 || 
            strpos($callback_data, 'cat_Таб') === 0);
}

// Test cases
$testCases = [
    'cat_ТабЭлОб',                    // Electrical equipment button (the problematic one)
    'category_Табель_Электрооборудование', // Full format electrical equipment
    'category_Табель_Дорога',         // Regular category
    'adm_req_12345',                  // Admin request
    'acc_lvl_2',                      // Access level
    'some_other_data'                 // Should be rejected
];

echo "=== VALIDATING CALLBACK FIX ===\n\n";

foreach ($testCases as $index => $callbackData) {
    $shouldProcess = shouldProcessCallback($callbackData);
    $status = $shouldProcess ? "ACCEPTED" : "REJECTED";
    
    echo "Test " . ($index + 1) . ": '{$callbackData}'\n";
    echo "Result: {$status}\n\n";
    
    // Special check for our main issue
    if ($callbackData === 'cat_ТабЭлОб') {
        if ($shouldProcess) {
            echo "SUCCESS: The electrical equipment button callback is now accepted!\n";
        } else {
            echo "FAILURE: The electrical equipment button callback is still being rejected.\n";
        }
    }
}

echo "=== VALIDATION COMPLETE ===\n";
?>