<?php
// Test script to verify access level callback handling

echo "Testing access level callback handling...\n";

// Simulate the callback data that would be sent by the "Управление пользователями" button
$callback_data = 'acc_lvl_manage_users';

echo "Callback data: " . $callback_data . "\n";

// Test the condition that was preventing the callback from being processed
$shouldProcess = (strpos($callback_data, 'category_') !== 0 && strpos($callback_data, 'adm_req_') !== 0 && strpos($callback_data, 'acc_lvl_') !== 0);

if ($shouldProcess) {
    echo "ERROR: Callback would be filtered out (early exit condition)\n";
} else {
    echo "SUCCESS: Callback will be processed\n";
}

// Test with the old condition (for comparison)
$oldShouldProcess = (strpos($callback_data, 'category_') !== 0 && strpos($callback_data, 'adm_req_') !== 0);

if ($oldShouldProcess) {
    echo "OLD CONDITION: Callback would be filtered out\n";
} else {
    echo "OLD CONDITION: Callback would be processed\n";
}

echo "Test completed.\n";
?>