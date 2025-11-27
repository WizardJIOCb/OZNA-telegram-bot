<?php
// Test the early exit logic from CallbackqueryCommand

echo "=== EARLY EXIT LOGIC TEST ===\n\n";

$testCallbacks = [
    'cat_ТабУстрЗамРК',
    'category_Табель_УЗ_РК',
    'category_test',
    'adm_req_test',
    'acc_lvl_test',
    'cat_Таб',
    'cat_ТабЭлОб_РабПоГр',
    'random_callback'
];

echo "Testing the early exit filter logic:\n";
echo "===================================\n\n";

foreach ($testCallbacks as $callback_data) {
    echo "Testing: '{$callback_data}'\n";
    echo "  Length: " . strlen($callback_data) . " bytes\n";
    echo "  Hex: " . bin2hex($callback_data) . "\n";
    
    // Test each condition
    $conditions = [
        'category_' => strpos($callback_data, 'category_') === 0,
        'adm_req_' => strpos($callback_data, 'adm_req_') === 0,
        'acc_lvl_' => strpos($callback_data, 'acc_lvl_') === 0,
        'cat_Таб' => strpos($callback_data, 'cat_Таб') === 0,
        'proj_ctrl_' => strpos($callback_data, 'proj_ctrl_') === 0,
        'proj_list_' => strpos($callback_data, 'proj_list_') === 0
    ];
    
    echo "  Condition checks:\n";
    foreach ($conditions as $pattern => $result) {
        echo "    {$pattern}: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    }
    
    // Check if ANY condition is true (will pass through filter)
    $passesFilter = false;
    foreach ($conditions as $result) {
        if ($result) {
            $passesFilter = true;
            break;
        }
    }
    
    echo "  RESULT: " . ($passesFilter ? '✅ PASSES FILTER (continues to handlers)' : '❌ BLOCKED BY FILTER (early exit)') . "\n\n";
}

echo "=== ANALYSIS ===\n";
echo "If a callback shows 'BLOCKED BY FILTER', it will never reach the handlers.\n";
echo "That's why you get 'unknown command' - the callback is rejected before\n";
echo "it can be processed by TabelHandler or any other handler.\n";

?>