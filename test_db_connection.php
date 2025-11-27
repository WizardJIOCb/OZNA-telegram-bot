<?php
/**
 * Test script to check database connection and create access requests table
 */

require_once __DIR__ . '/Custome/DB.php';

try {
    echo "Testing database connection...\n";
    
    // Test basic database connection by selecting a user
    $result = pDB::select("SELECT COUNT(*) as count FROM ozna_data LIMIT 1");
    if ($result) {
        echo "Database connection successful. Found " . $result[0]['count'] . " records in ozna_data table.\n";
    } else {
        echo "Failed to query database.\n";
        exit(1);
    }
    
    // Try to create the access requests table
    echo "Creating access requests table...\n";
    $result = pDB::createAccessRequestsTable();
    
    if ($result) {
        echo "Access requests table created or already exists.\n";
    } else {
        echo "Failed to create access requests table.\n";
        exit(1);
    }
    
    echo "Database test completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}