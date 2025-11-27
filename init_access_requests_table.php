<?php
/**
 * Script to initialize the access requests table in the database
 */

require_once __DIR__ . '/Custome/DB.php';

try {
    echo "Initializing access requests table...\n";
    
    // Create the access requests table
    if (pDB::createAccessRequestsTable()) {
        echo "Access requests table created or already exists.\n";
    } else {
        echo "Failed to create access requests table.\n";
        exit(1);
    }
    
    echo "Database initialization completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}