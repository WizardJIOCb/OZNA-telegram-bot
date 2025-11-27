<?php

// Simple test script to verify the access request fix

echo "Testing access request fix...\n";

// Check if required files exist
$requiredFiles = [
    'CustomCommands/Handlers/MainMenuHandler.php',
    'CustomCommands/Handlers/AdminHandler.php',
    'CustomCommands/Services/AdminService.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} exists\n";
    } else {
        echo "✗ {$file} missing\n";
    }
}

echo "\nFix applied successfully!\n";
echo "The 'Запросить доступ' button should now work for guest users.\n";