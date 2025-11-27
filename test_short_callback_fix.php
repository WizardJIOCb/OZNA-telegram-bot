<?php
// Test script to verify short callback format handling

echo "Testing short callback format handling...\n";
echo "=====================================\n\n";

// Test the early exit condition logic
$testCallbacks = [
    'cat_ТабЭлОб_РабПоГр',  // Electrical equipment schedule works
    'cat_ТабЭлОб',          // Electrical equipment menu
    'category_Табель_РаботыПоГрафику_Электрооборудование',  // Full format
    'category_Табель',      // Main menu
    'adm_req_123',          // Admin request
    'acc_lvl_123',          // Access level
    'some_other_callback'   // Should be rejected
];

foreach ($testCallbacks as $callback_data) {
    echo "Testing callback: '{$callback_data}'\n";
    
    // Check the early exit condition
    $shouldPass = (strpos($callback_data, 'category_') === 0 || 
                   strpos($callback_data, 'adm_req_') === 0 || 
                   strpos($callback_data, 'acc_lvl_') === 0 || 
                   strpos($callback_data, 'cat_Таб') === 0);
    
    echo "  strpos 'category_' result: " . (strpos($callback_data, 'category_') === 0 ? 'true' : 'false') . "\n";
    echo "  strpos 'adm_req_' result: " . (strpos($callback_data, 'adm_req_') === 0 ? 'true' : 'false') . "\n";
    echo "  strpos 'acc_lvl_' result: " . (strpos($callback_data, 'acc_lvl_') === 0 ? 'true' : 'false') . "\n";
    echo "  strpos 'cat_Таб' result: " . (strpos($callback_data, 'cat_Таб') === 0 ? 'true' : 'false') . "\n";
    echo "  Should pass early exit check: " . ($shouldPass ? 'YES' : 'NO') . "\n";
    echo "  Result: " . ($shouldPass ? 'PROCESSED' : 'REJECTED') . "\n\n";
}

echo "✅ Test completed!\n";
echo "The short callback format 'cat_Таб*' should now be properly handled.\n";