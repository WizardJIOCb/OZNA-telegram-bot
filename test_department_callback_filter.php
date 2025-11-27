<?php

echo "=== TESTING DEPARTMENT CALLBACK FILTERING ===\n\n";

// Test the main department button callbacks
$test_callbacks = [
    'cat_ТабПНРаботы' => 'ПНР Department',
    'category_Табель_ШМР' => 'ШМР Department (main)',
    'category_Табель_Пусконаладка' => 'Commissioning Department',
    'category_Табель_МобБриг' => 'Mobile Brigade',
    'cat_ШМР_ПодгРаб' => 'ШМР Preparatory Works (recent log)',
];

echo "🔍 TESTING CALLBACK FILTER LOGIC\n";
echo "=================================\n";

foreach ($test_callbacks as $callback => $description) {
    echo "\nTesting: '{$callback}' ({$description})\n";
    
    // Simulate the CallbackqueryCommand filter logic
    $should_pass = !(
        strpos($callback, 'category_') !== 0 && 
        strpos($callback, 'adm_req_') !== 0 && 
        strpos($callback, 'acc_lvl_') !== 0 && 
        strpos($callback, 'cat_Таб') !== 0 &&
        strpos($callback, 'cat_ШМР_') !== 0 &&
        strpos($callback, 'cat_РЭРњР') !== 0 &&
        strpos($callback, 'РЭРњР') === false &&  // Our recent fix
        strpos($callback, 'cat_supervision_') !== 0 &&
        strpos($callback, 'proj_ctrl_') !== 0 &&
        strpos($callback, 'proj_list_') !== 0 &&
        strpos($callback, 'cat_Гарантии') !== 0
    );
    
    if ($should_pass) {
        echo "✅ PASSES filter - will reach handlers\n";
    } else {
        echo "❌ BLOCKED by filter - will not reach handlers\n";
        
        // Show which condition is blocking it
        if (strpos($callback, 'category_') === 0) {
            echo "   Passes: starts with 'category_'\n";
        } elseif (strpos($callback, 'cat_Таб') === 0) {
            echo "   Passes: starts with 'cat_Таб'\n";
        } elseif (strpos($callback, 'cat_ШМР_') === 0) {
            echo "   Passes: starts with 'cat_ШМР_'\n";
        } else {
            echo "   ⚠️  Does not match any allowed patterns!\n";
        }
    }
}

echo "\n🔧 ANALYSIS RESULT\n";
echo "==================\n";

$blocked_count = 0;
foreach ($test_callbacks as $callback => $description) {
    $should_pass = !(
        strpos($callback, 'category_') !== 0 && 
        strpos($callback, 'adm_req_') !== 0 && 
        strpos($callback, 'acc_lvl_') !== 0 && 
        strpos($callback, 'cat_Таб') !== 0 &&
        strpos($callback, 'cat_ШМР_') !== 0 &&
        strpos($callback, 'cat_РЭРњР') !== 0 &&
        strpos($callback, 'РЭРњР') === false &&
        strpos($callback, 'cat_supervision_') !== 0 &&
        strpos($callback, 'proj_ctrl_') !== 0 &&
        strpos($callback, 'proj_list_') !== 0 &&
        strpos($callback, 'cat_Гарантии') !== 0
    );
    if (!$should_pass) $blocked_count++;
}

if ($blocked_count > 0) {
    echo "❌ {$blocked_count} department buttons are being BLOCKED!\n";
    echo "Need to update CallbackqueryCommand filter.\n";
} else {
    echo "✅ All department buttons should pass through filter.\n";
    echo "Issue might be in handler registration or routing.\n";
}

?>