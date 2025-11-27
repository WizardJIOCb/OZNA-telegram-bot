<?php
// Simple Callback Length Diagnostic
require_once __DIR__ . '/config.php';

echo "CALLBACK DATA LENGTH DIAGNOSTIC\n";
echo "==============================\n\n";

$callbacks = [
    'proj_ctrl_add',
    'proj_ctrl_remove', 
    'proj_ctrl_list',
    'proj_ctrl_settings'
];

echo "Checking callback data lengths:\n";
foreach ($callbacks as $callback) {
    $length = strlen($callback);
    echo "'{$callback}' = {$length} bytes";
    echo $length <= 64 ? " ✓ OK" : " ✗ TOO LONG";
    echo "\n";
}

echo "\nHex dump:\n";
foreach ($callbacks as $callback) {
    echo "'{$callback}' = " . bin2hex($callback) . "\n";
}
?>