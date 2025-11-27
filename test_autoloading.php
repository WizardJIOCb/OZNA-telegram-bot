<?php

/**
 * Test script to verify autoloading functionality
 */

// Include the autoloader
require_once __DIR__ . '/custom_autoload.php';

echo "Testing autoloading functionality\n";
echo "===============================\n\n";

try {
    // Test if we can load the DatabaseService class
    echo "1. Testing DatabaseService class loading:\n";
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService')) {
        echo "✓ DatabaseService class loaded successfully\n";
        
        // Try to instantiate the class
        $dbService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        echo "✓ DatabaseService instance created successfully\n\n";
    } else {
        echo "✗ DatabaseService class could not be loaded\n\n";
    }
    
    // Test if we can load the AdminService class
    echo "2. Testing AdminService class loading:\n";
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\AdminService')) {
        echo "✓ AdminService class loaded successfully\n";
        
        // Try to instantiate the class
        // We need to create a mock MessageService first
        if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\MessageService')) {
            $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
            $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
            echo "✓ AdminService instance created successfully\n\n";
        } else {
            echo "✗ MessageService class could not be loaded\n\n";
        }
    } else {
        echo "✗ AdminService class could not be loaded\n\n";
    }
    
    // Test if we can load the MessageService class
    echo "3. Testing MessageService class loading:\n";
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\MessageService')) {
        echo "✓ MessageService class loaded successfully\n";
        
        // Try to instantiate the class
        $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
        echo "✓ MessageService instance created successfully\n\n";
    } else {
        echo "✗ MessageService class could not be loaded\n\n";
    }
    
    echo "Autoloading test completed!\n";
    
} catch (Exception $e) {
    echo "Error during autoloading test: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "Error trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal error during autoloading test: " . $e->getMessage() . "\n";
    echo "Error trace: " . $e->getTraceAsString() . "\n";
}