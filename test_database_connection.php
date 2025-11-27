<?php
// Test database connection

echo "=== Database Connection Test ===\n\n";

// Include necessary files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

// Test database connection
try {
    echo "Creating DatabaseService instance...\n";
    $dbService = new DatabaseService();
    
    echo "Testing database connection...\n";
    
    // Try to get a connection
    $reflection = new ReflectionClass($dbService);
    $method = $reflection->getMethod('getConnection');
    $method->setAccessible(true);
    $connection = $method->invoke($dbService);
    
    echo "Database connection successful!\n";
    echo "Connection host info: " . $connection->host_info . "\n\n";
    
    // Test a simple query
    echo "Testing simple query...\n";
    $result = $connection->query("SELECT 1 as test");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Query result: " . $row['test'] . "\n";
        echo "Database is working correctly!\n";
    } else {
        echo "Query failed: " . $connection->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n";