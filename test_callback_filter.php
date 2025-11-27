<?php

echo "=== Testing Callback Filter Logic ===\n\n";

function testCallback($callback_data) {
    echo "Testing: '$callback_data'\n";
    
    // This is the exact logic from CallbackqueryCommand.php
    $shouldExit = (strpos($callback_data, 'category_') !== 0 && 
                   strpos($callback_data, 'adm_req_') !== 0 && 
                   strpos($callback_data, 'acc_lvl_') !== 0 && 
                   strpos($callback_data, 'cat_Таб') !== 0 &&
                   strpos($callback_data, 'cat_ШМР_') !== 0 &&
                   strpos($callback_data, 'cat_РЁРњР') !== 0 &&
                   strpos($callback_data, 'РЁРњР') !== false &&  // CRITICAL FIX: Allow any callback containing corrupted pattern
                   strpos($callback_data, 'cat_supervision_') !== 0 &&
                   strpos($callback_data, 'proj_ctrl_') !== 0 &&
                   strpos($callback_data, 'proj_list_') !== 0 &&
                   strpos($callback_data, 'cat_Гарантии') !== 0);
    
    echo "Will exit early: " . ($shouldExit ? "YES ❌" : "NO ✅") . "\n";
    echo "Should process: " . ($shouldExit ? "NO ❌" : "YES ✅") . "\n\n";
    
    return !$shouldExit;
}

// Test cases
echo "1. Normal callback:\n";
testCallback('cat_ШМР_АналФунд_yes');

echo "2. Corrupted callback (what we're receiving):\n";
testCallback('cat_РЁРњР _РПѕРґРіР Р°Р±');

echo "3. Just the corrupted pattern:\n";
testCallback('РЁРњР');

echo "4. Random callback:\n";
testCallback('random_callback');

echo "=== Test Complete ===\n";