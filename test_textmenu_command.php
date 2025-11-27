<?php

// Test script to verify TextmenuCommand functionality
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing TextmenuCommand functionality...\n";

// Check if TextmenuCommand exists and has the correct name
$commandPath = __DIR__ . '/CustomCommands/TextmenuCommand.php';
if (file_exists($commandPath)) {
    echo "✓ TextmenuCommand file exists\n";
    
    // Read the file to check the command name
    $content = file_get_contents($commandPath);
    if (strpos($content, "protected \$name = 'genericmessage'") !== false) {
        echo "✓ TextmenuCommand has correct name 'genericmessage'\n";
    } else {
        echo "✗ TextmenuCommand does not have correct name\n";
    }
} else {
    echo "✗ TextmenuCommand file does not exist\n";
}

// Check if GenericmessageCommand is disabled
$activePath = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
$disabledPath = __DIR__ . '/CustomCommands/GenericmessageCommand.disabled';
if (file_exists($activePath)) {
    echo "✗ GenericmessageCommand is still active\n";
} else {
    echo "✓ GenericmessageCommand is not active\n";
}

if (file_exists($disabledPath)) {
    echo "✓ GenericmessageCommand is properly disabled\n";
} else {
    echo "? GenericmessageCommand disabled file not found\n";
}

echo "\nTest completed.\n";