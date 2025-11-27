<?php
/**
 * Test script to check if we can use existing database methods
 */

require_once __DIR__ . '/Custome/DB.php';

try {
    echo "Testing existing database methods...\n";
    
    // Test getUserData method with a test user ID
    $userData = pDB::getUserData(1);
    echo "getUserData result: " . print_r($userData, true) . "\n";
    
    echo "Existing database methods test completed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}