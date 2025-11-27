<?php
/**
 * Simple test script to check file-based access requests functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Simple File Test ===\n\n";

// Test reading access requests from file
$accessRequestsFile = __DIR__ . '/data/access_requests.json';

echo "Checking if file exists: " . ($accessRequestsFile) . "\n";
if (file_exists($accessRequestsFile)) {
    echo "File exists\n";
    
    $jsonData = file_get_contents($accessRequestsFile);
    if ($jsonData !== false) {
        echo "File content read successfully\n";
        
        $requests = json_decode($jsonData, true);
        if (is_array($requests)) {
            echo "JSON decoded successfully\n";
            echo "Total requests found: " . count($requests) . "\n";
            
            if (!empty($requests)) {
                echo "Displaying first request:\n";
                $firstRequest = $requests[0];
                print_r($firstRequest);
                
                echo "\nUser ID: " . $firstRequest['user_id'] . "\n";
                echo "User Name: " . $firstRequest['user_name'] . "\n";
                echo "Timestamp: " . date('Y-m-d H:i:s', $firstRequest['timestamp']) . "\n";
            }
        } else {
            echo "Failed to decode JSON\n";
        }
    } else {
        echo "Failed to read file content\n";
    }
} else {
    echo "File does not exist\n";
}

echo "\n=== Test Complete ===\n";