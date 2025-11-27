<?php
// Verify that our Admin button fix is working correctly

echo "Verifying Admin button fix...\n";

// Check if TextmenuCommand is disabled
if (file_exists(__DIR__ . '/CustomCommands/TextmenuCommand.disabled')) {
    echo "✓ TextmenuCommand has been disabled\n";
} else {
    echo "✗ TextmenuCommand is still active\n";
}

// Check if GenericmessageCommand exists
if (file_exists(__DIR__ . '/CustomCommands/GenericmessageCommand.php')) {
    echo "✓ GenericmessageCommand exists\n";
    
    // Read the file to check if our debug code is present
    $content = file_get_contents(__DIR__ . '/CustomCommands/GenericmessageCommand.php');
    
    if (strpos($content, 'error_log("GenericmessageCommand handling admin button for user:') !== false) {
        echo "✓ Debug code is present in GenericmessageCommand\n";
    } else {
        echo "✗ Debug code is missing from GenericmessageCommand\n";
    }
    
    // Check if the Admin button cases are present
    if (strpos($content, "case '⚙️ Админка':") !== false && strpos($content, "case 'Админка':") !== false) {
        echo "✓ Admin button cases are present in GenericmessageCommand\n";
    } else {
        echo "✗ Admin button cases are missing from GenericmessageCommand\n";
    }
} else {
    echo "✗ GenericmessageCommand is missing\n";
}

echo "Verification complete.\n";
?>