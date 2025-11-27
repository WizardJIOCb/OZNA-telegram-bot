<?php

// Check if required PHP extensions are loaded
$requiredExtensions = [
    'mbstring',
    'json',
    'curl'
];

echo "Checking required PHP extensions:\n";
echo "================================\n";

foreach ($requiredExtensions as $extension) {
    if (extension_loaded($extension)) {
        echo "✓ {$extension} - LOADED\n";
    } else {
        echo "✗ {$extension} - NOT LOADED\n";
    }
}

echo "\nAll required extensions checked.\n";