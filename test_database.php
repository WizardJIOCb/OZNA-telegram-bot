<?php
// Test database connection and user data retrieval

echo "=== Test Database Connection and User Data ===\n";

// This is a simplified test - in the actual bot, we would have the user ID from the callback
// For testing purposes, we'll use a placeholder
$userId = 123456789; // Placeholder user ID

echo "Testing with user ID: " . $userId . "\n";

// Try to include the database class
if (file_exists(__DIR__ . '/pDB.php')) {
    echo "pDB.php file exists\n";
    // Try to include it
    try {
        include_once __DIR__ . '/pDB.php';
        echo "pDB.php included successfully\n";
        
        // Try to call getUserData
        if (class_exists('pDB')) {
            echo "pDB class exists\n";
            // Note: We can't actually call getUserData without a real database connection
            // But we can check if the method exists
            $methods = get_class_methods('pDB');
            if (in_array('getUserData', $methods)) {
                echo "getUserData method exists\n";
            } else {
                echo "getUserData method does not exist\n";
            }
        } else {
            echo "pDB class does not exist\n";
        }
    } catch (Exception $e) {
        echo "Error including pDB.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "pDB.php file does not exist\n";
}

echo "\nTest completed!\n";
?>