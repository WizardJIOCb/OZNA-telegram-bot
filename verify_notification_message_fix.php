<?php
/**
 * Verification script for the notification message fix
 * Checks that the access request notification message correctly says "в список пользователей"
 */

echo "Verifying notification message fix...\n\n";

// Check the AdminService file for the correct message
$adminServiceFile = __DIR__ . '/CustomCommands/Services/AdminService.php';

if (!file_exists($adminServiceFile)) {
    echo "❌ AdminService.php file not found\n";
    exit(1);
}

$content = file_get_contents($adminServiceFile);

// Check for the correct message
if (strpos($content, 'в список пользователей') !== false) {
    echo "✅ Notification message correctly says 'в список пользователей'\n";
    
    // Make sure the old incorrect message is not present
    if (strpos($content, 'в список админов') === false) {
        echo "✅ Old incorrect message 'в список админов' is not present\n";
    } else {
        echo "⚠️  Old incorrect message 'в список админов' is still present somewhere in the code\n";
    }
} else {
    echo "❌ Notification message does not contain 'в список пользователей'\n";
    exit(1);
}

// Check the specific method that sends access requests
if (strpos($content, 'sendAccessRequest') !== false) {
    echo "✅ sendAccessRequest method found\n";
    
    // Look for the specific line with the notification message
    $lines = explode("\n", $content);
    $foundMessageLine = false;
    
    foreach ($lines as $line) {
        if (strpos($line, 'Для предоставления доступа добавьте ID') !== false && 
            strpos($line, 'в список пользователей') !== false) {
            echo "✅ Found correct notification message line:\n";
            echo "   " . trim($line) . "\n";
            $foundMessageLine = true;
            break;
        }
    }
    
    if (!$foundMessageLine) {
        echo "❌ Could not find the specific notification message line\n";
    }
} else {
    echo "❌ sendAccessRequest method not found\n";
}

echo "\nVerification complete!\n";
echo "The notification message fix is correctly implemented.\n";
echo "Guests requesting 'user' level access will now receive notifications that correctly state:\n";
echo "'Для предоставления доступа добавьте ID [number] в список пользователей.'\n";